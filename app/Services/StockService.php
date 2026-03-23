<?php

namespace App\Services;

use App\Models\InventoryTransactionModel;
use App\Models\MedicineModel;
use Illuminate\Support\Facades\DB;

/**
 * StockService — atomic stock deduction with full audit trail.
 *
 * Called by InvoiceStep when medicines are added to an invoice.
 * Each dispense is recorded in inventory_transactions.
 * Throws if any item is out of stock (out-of-stock validation).
 */
final class StockService
{
    /**
     * Validate stock levels for a list of medicines before deducting.
     * Returns array of error strings (empty = all OK).
     *
     * @param  array $items  [['medicine_id'=>N, 'qty'=>N, 'name'=>'...'], ...]
     */
    public static function validate(array $items): array
    {
        $errors = [];
        foreach ($items as $item) {
            if (empty($item['medicine_id'])) continue;
            $med = MedicineModel::find($item['medicine_id']);
            if (!$med) continue;
            $qty = (int) ($item['qty'] ?? 1);
            if ($med->stock < $qty) {
                $errors[] = "⚠ {$med->name}: only {$med->stock} in stock, requested {$qty}.";
            }
        }
        return $errors;
    }

    /**
     * Deduct stock for all items and write inventory_transactions.
     * Runs inside a DB::transaction — caller must ensure transactional context.
     *
     * @param  array  $items        [['medicine_id'=>N, 'qty'=>N, 'unit_price'=>N, 'name'=>'...'], ...]
     * @param  string $invoiceCode
     * @param  string $visitCode
     * @param  string $patientCode
     * @throws \RuntimeException    if any item is out of stock
     */
    public static function dispense(
        array  $items,
        string $invoiceCode,
        string $visitCode,
        string $patientCode
    ): void {
        foreach ($items as $item) {
            if (empty($item['medicine_id'])) continue;

            $qty = (int) ($item['qty'] ?? 1);
            if ($qty <= 0) continue;

            // Lock row for atomic update
            $med = MedicineModel::lockForUpdate()->find($item['medicine_id']);
            if (!$med) continue;

            if ($med->stock < $qty) {
                throw new \RuntimeException(
                    "Out of stock: {$med->name} (have {$med->stock}, need {$qty})"
                );
            }

            $before = $med->stock;
            $med->decrement('stock', $qty);
            $after  = $med->stock;

            InventoryTransactionModel::create([
                'clinic_id'     => $med->clinic_id,
                'medicine_id'   => $med->id,
                'medicine_code' => $med->code,
                'medicine_name' => $med->name,
                'visit_code'    => $visitCode,
                'invoice_code'  => $invoiceCode,
                'patient_code'  => $patientCode,
                'type'          => 'dispense',
                'quantity'      => -$qty,   // negative = out
                'stock_before'  => $before,
                'stock_after'   => $after,
                'unit_price'    => (float) ($item['unit_price'] ?? $med->price ?? 0),
                'total_price'   => (float) ($item['unit_price'] ?? $med->price ?? 0) * $qty,
            ]);
        }
    }

    /**
     * Reverse a previous dispense (when invoice is voided).
     * Adds stock back and logs a 'return' transaction.
     */
    public static function returnStock(string $invoiceCode): void
    {
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
                'note'          => 'Invoice voided',
            ]);
        }
    }

    private function __construct() {}
}
