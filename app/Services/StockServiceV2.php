<?php

namespace App\Services;

use App\Models\DispenseModel;
use App\Models\InventoryTransactionModel;
use App\Models\MedicineModel;
use App\Models\StockBalanceModel;
use App\Models\StockMovementModel;
use Illuminate\Support\Facades\DB;

/**
 * StockService — inventory business logic.
 *
 * EXTENDS the existing StockService (app/Services/StockService.php).
 * If your existing StockService has dispense() and returnStock(),
 * rename this file to StockServiceV2 or merge the methods.
 *
 * This version adds:
 *   - stock_balances sync after every movement
 *   - dispenses table logging for pharmacy workflow
 *   - validate() for pre-flight stock checks
 */
class StockServiceV2
{
    /**
     * Validate stock availability before dispensing.
     * Returns array of error messages (empty = all good).
     */
    public static function validate(array $items): array
    {
        $errors = [];

        foreach ($items as $item) {
            if (empty($item['medicine_id'])) continue;
            $qty = (int)($item['qty'] ?? 1);
            if ($qty <= 0) continue;

            $med = MedicineModel::find($item['medicine_id']);
            if (!$med) {
                $errors[] = "Medicine ID {$item['medicine_id']} not found.";
                continue;
            }
            if ($med->stock < $qty) {
                $errors[] = "Insufficient stock for {$med->name}: have {$med->stock}, need {$qty}.";
            }
        }

        return $errors;
    }

    /**
     * Dispense medicines for a prescription (transactional).
     * Creates: inventory_transactions + dispenses + updates stock + syncs balance.
     */
    public static function dispense(
        array  $items,
        string $prescriptionCode,
        string $visitCode,
        string $patientCode
    ): void {
        DB::transaction(function () use ($items, $prescriptionCode, $visitCode, $patientCode) {
            foreach ($items as $item) {
                if (empty($item['medicine_id'])) continue;
                $qty = (int)($item['qty'] ?? 1);
                if ($qty <= 0) continue;

                $med = MedicineModel::lockForUpdate()->find($item['medicine_id']);
                if (!$med || $med->stock < $qty) continue;

                $before = $med->stock;
                $med->decrement('stock', $qty);
                $after = $med->stock;

                // Log inventory transaction
                InventoryTransactionModel::create([
                    'clinic_id'     => $med->clinic_id,
                    'medicine_id'   => $med->id,
                    'medicine_code' => $med->code,
                    'medicine_name' => $med->name,
                    'visit_code'    => $visitCode,
                    'invoice_code'  => null,
                    'patient_code'  => $patientCode,
                    'type'          => 'dispense',
                    'quantity'      => -$qty,
                    'stock_before'  => $before,
                    'stock_after'   => $after,
                    'unit_price'    => (float)($item['unit_price'] ?? $med->price ?? 0),
                    'total_price'   => (float)($item['unit_price'] ?? $med->price ?? 0) * $qty,
                ]);

                // Log pharmacy dispense
                DispenseModel::create([
                    'clinic_id'         => $med->clinic_id,
                    'code'              => ClinicCodeService::next($med->clinic_id, 'DSP'),
                    'prescription_code' => $prescriptionCode,
                    'medicine_id'       => $med->id,
                    'medicine_name'     => $med->name,
                    'quantity'          => $qty,
                    'patient_code'      => $patientCode,
                    'visit_code'        => $visitCode,
                    'dispensed_by'      => auth()->user()?->name,
                    'dispensed_at'      => now(),
                    'status'            => 'dispensed',
                ]);

                // Sync stock balance
                StockBalanceModel::syncFromMedicine($med);
            }
        });
    }

    /**
     * Reverse a previous dispense (when invoice is voided).
     */
    public static function returnStock(string $invoiceCode): void
    {
        DB::transaction(function () use ($invoiceCode) {
            $txns = InventoryTransactionModel::where('invoice_code', $invoiceCode)
                ->where('type', 'dispense')->get();

            foreach ($txns as $txn) {
                $qty = abs($txn->quantity);
                $med = MedicineModel::lockForUpdate()->find($txn->medicine_id);
                if (!$med) continue;

                $before = $med->stock;
                $med->increment('stock', $qty);

                InventoryTransactionModel::create([
                    'clinic_id'     => $txn->clinic_id,
                    'medicine_id'   => $txn->medicine_id,
                    'medicine_code' => $txn->medicine_code,
                    'medicine_name' => $txn->medicine_name,
                    'visit_code'    => $txn->visit_code,
                    'invoice_code'  => $invoiceCode,
                    'patient_code'  => $txn->patient_code,
                    'type'          => 'return',
                    'quantity'      => $qty,
                    'stock_before'  => $before,
                    'stock_after'   => $before + $qty,
                    'unit_price'    => $txn->unit_price,
                    'total_price'   => $txn->total_price,
                    'note'          => 'Invoice voided — stock returned',
                ]);

                StockBalanceModel::syncFromMedicine($med->fresh());
            }
        });
    }

    /**
     * Record a manual stock-in movement.
     */
    public static function stockIn(int $medicineId, int $quantity, array $meta = []): void
    {
        DB::transaction(function () use ($medicineId, $quantity, $meta) {
            $med = MedicineModel::lockForUpdate()->findOrFail($medicineId);
            $before = $med->stock;
            $med->increment('stock', $quantity);

            StockMovementModel::create(array_merge([
                'clinic_id'     => $med->clinic_id,
                'medicine_id'   => $med->id,
                'medicine_code' => $med->code,
                'medicine_name' => $med->name,
                'type'          => 'in',
                'quantity'      => $quantity,
                'stock_before'  => $before,
                'stock_after'   => $before + $quantity,
                'recorded_by'   => auth()->user()?->name,
            ], $meta));

            StockBalanceModel::syncFromMedicine($med->fresh());
        });
    }

    /**
     * Record a manual stock-out movement.
     */
    public static function stockOut(int $medicineId, int $quantity, array $meta = []): void
    {
        DB::transaction(function () use ($medicineId, $quantity, $meta) {
            $med = MedicineModel::lockForUpdate()->findOrFail($medicineId);
            if ($med->stock < $quantity) {
                throw new \RuntimeException("Insufficient stock for {$med->name}.");
            }

            $before = $med->stock;
            $med->decrement('stock', $quantity);

            StockMovementModel::create(array_merge([
                'clinic_id'     => $med->clinic_id,
                'medicine_id'   => $med->id,
                'medicine_code' => $med->code,
                'medicine_name' => $med->name,
                'type'          => 'out',
                'quantity'      => $quantity,
                'stock_before'  => $before,
                'stock_after'   => $before - $quantity,
                'recorded_by'   => auth()->user()?->name,
            ], $meta));

            StockBalanceModel::syncFromMedicine($med->fresh());
        });
    }

    private function __construct() {} // Static-only
}
