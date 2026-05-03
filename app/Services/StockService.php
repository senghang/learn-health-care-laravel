<?php

namespace App\Services;

use App\Models\DispenseModel;
use App\Models\InventoryTransactionModel;
use App\Models\MedicineModel;
use App\Models\StockBalanceModel;
use App\Models\StockMovementModel;
use Illuminate\Support\Facades\DB;

/**
 * StockService — single canonical stock management service.
 *
 * Responsibilities:
 *   - Pre-flight stock validation
 *   - Invoice-linked dispense (OPD billing flow)
 *   - Prescription-linked dispense (pharmacy queue flow)
 *   - Stock return on invoice void
 *   - Manual stock-in / stock-out movements
 *   - stock_balances sync after every movement
 *
 * All public methods are static (utility style).
 * All writes run inside DB::transaction.
 */
final class StockService
{
    private function __construct() {} // Static-only

    // ──────────────────────────────────────────────────────────────────────────
    // Validation
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Pre-flight stock check for a list of medicines.
     * Returns array of error strings; empty = all OK.
     *
     * @param  array $items  [['medicine_id'=>N, 'qty'=>N], ...]
     */
    public static function validate(array $items): array
    {
        $errors = [];

        foreach ($items as $item) {
            if (empty($item['medicine_id'])) continue;
            $qty = (int) ($item['qty'] ?? 1);
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

    // ──────────────────────────────────────────────────────────────────────────
    // Dispense
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Deduct stock for medicines on an invoice (billing flow).
     * Writes inventory_transactions and syncs stock_balances.
     *
     * @param  array  $items        [['medicine_id'=>N, 'qty'=>N, 'unit_price'=>N], ...]
     * @param  string $invoiceCode
     * @param  string $visitCode
     * @param  string $patientCode
     * @throws \RuntimeException on insufficient stock
     */
    public static function dispense(
        array  $items,
        string $invoiceCode,
        string $visitCode,
        string $patientCode
    ): void {
        DB::transaction(function () use ($items, $invoiceCode, $visitCode, $patientCode) {
            foreach ($items as $item) {
                if (empty($item['medicine_id'])) continue;
                $qty = (int) ($item['qty'] ?? 1);
                if ($qty <= 0) continue;

                $med = MedicineModel::lockForUpdate()->find($item['medicine_id']);
                if (!$med) continue;

                if ($med->stock < $qty) {
                    throw new \RuntimeException(
                        "Out of stock: {$med->name} (have {$med->stock}, need {$qty})"
                    );
                }

                $before = $med->stock;
                $med->decrement('stock', $qty);

                InventoryTransactionModel::create([
                    'clinic_id'     => $med->clinic_id,
                    'medicine_id'   => $med->id,
                    'medicine_code' => $med->code,
                    'medicine_name' => $med->name,
                    'visit_code'    => $visitCode,
                    'invoice_code'  => $invoiceCode,
                    'patient_code'  => $patientCode,
                    'type'          => 'dispense',
                    'quantity'      => -$qty,
                    'stock_before'  => $before,
                    'stock_after'   => $before - $qty,
                    'unit_price'    => (float) ($item['unit_price'] ?? $med->price ?? 0),
                    'total_price'   => (float) ($item['unit_price'] ?? $med->price ?? 0) * $qty,
                ]);

                StockBalanceModel::syncFromMedicine($med->fresh());
            }
        });
    }

    /**
     * Dispense medicines for a prescription (pharmacy queue flow).
     * Creates pharmacy_dispenses records in addition to inventory_transactions.
     *
     * @param  array  $items              [['medicine_id'=>N, 'qty'=>N, 'unit_price'=>N], ...]
     * @param  string $prescriptionCode
     * @param  string $visitCode
     * @param  string $patientCode
     * @param  string $dispensedBy        Pharmacist name
     */
    public static function dispenseByPrescription(
        array  $items,
        string $prescriptionCode,
        string $visitCode,
        string $patientCode,
        string $dispensedBy = ''
    ): void {
        DB::transaction(function () use ($items, $prescriptionCode, $visitCode, $patientCode, $dispensedBy) {
            foreach ($items as $item) {
                if (empty($item['medicine_id'])) continue;
                $qty = (int) ($item['qty'] ?? 1);
                if ($qty <= 0) continue;

                $med = MedicineModel::lockForUpdate()->find($item['medicine_id']);
                if (!$med || $med->stock < $qty) continue;

                $before = $med->stock;
                $med->decrement('stock', $qty);

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
                    'stock_after'   => $before - $qty,
                    'unit_price'    => (float) ($item['unit_price'] ?? $med->price ?? 0),
                    'total_price'   => (float) ($item['unit_price'] ?? $med->price ?? 0) * $qty,
                ]);

                DispenseModel::create([
                    'clinic_id'         => $med->clinic_id,
                    'code'              => ClinicCodeService::next($med->clinic_id, 'DSP'),
                    'prescription_code' => $prescriptionCode,
                    'medicine_id'       => $med->id,
                    'medicine_name'     => $med->name,
                    'quantity'          => $qty,
                    'patient_code'      => $patientCode,
                    'visit_code'        => $visitCode,
                    'dispensed_by'      => $dispensedBy ?: auth()->user()?->name,
                    'dispensed_at'      => now(),
                    'status'            => 'dispensed',
                ]);

                StockBalanceModel::syncFromMedicine($med->fresh());
            }
        });
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Returns
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Reverse all dispenses linked to an invoice (called when invoice is voided).
     */
    public static function returnStock(string $invoiceCode): void
    {
        DB::transaction(function () use ($invoiceCode) {
            $txns = InventoryTransactionModel::where('invoice_code', $invoiceCode)
                ->where('type', 'dispense')
                ->get();

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

    // ──────────────────────────────────────────────────────────────────────────
    // Manual Movements
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Record a manual stock-in (receiving inventory from supplier).
     *
     * @param  array $meta  Extra columns: supplier, batch_no, expiry_date, unit_cost, reference, note
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
     * Record a manual stock-out (wastage, expiry write-off, etc.).
     *
     * @param  array $meta  Extra columns: note, reference, recorded_by
     * @throws \RuntimeException on insufficient stock
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
}
