<?php

namespace App\Services;

use App\Models\MedicineModel;
use App\Models\PrescriptionMedicationModel;
use App\Models\PrescriptionModel;

/**
 * PrescriptionService
 *
 * Business logic for prescription management.
 *
 * STOCK STRATEGY
 * ──────────────
 * Stock is NOT decremented at prescription time.
 * The prescription is a doctor's order — stock is reserved at dispense time.
 * This prevents stock from being locked prematurely if dispensing is delayed
 * or the prescription is cancelled.
 *
 * RISK: Between prescribing and dispensing, another prescription could claim
 * the same stock. The dispense() method uses pessimistic row-level locking
 * (SELECT ... FOR UPDATE) inside a DB transaction to prevent this race condition.
 */
class PrescriptionService
{
    /**
     * Sync medication items for a prescription.
     *
     * Wipes existing medications and rebuilds from the provided array.
     * Always snapshots medicine_name so the label is preserved even if the
     * medicine is later soft-deleted from the catalogue.
     *
     * @param PrescriptionModel $rx
     * @param array             $meds  Validated medication rows
     */
    public function syncMedications(PrescriptionModel $rx, array $meds): void
    {
        // Force-delete clears both active and soft-deleted rows,
        // avoiding unique-key conflicts on re-save.
        $rx->medications()->forceDelete();

        foreach ($meds as $med) {
            $name = trim($med['medicine_name'] ?? '');
            if ($name === '') continue;

            PrescriptionMedicationModel::create([
                'prescription_code' => $rx->code,
                'medication_code'   => $med['medicine_code'] ?? null,
                'medicine_name'     => $name,
                'strength'          => $med['strength']   ?? null,
                'form'              => $med['form']        ?? null,
                'method'            => $med['method']      ?? null,
                'unit'              => $med['unit']        ?? null,
                'morning'           => $med['morning']     ?? 0,
                'afternoon'         => $med['afternoon']   ?? 0,
                'evening'           => $med['evening']     ?? 0,
                'night'             => $med['night']       ?? 0,
                'days'              => $med['days']        ?? 1,
                'interval'          => $med['interval']    ?? null,
                'note'              => $med['note']        ?? null,
            ]);
        }
    }

    /**
     * Returns stock warnings for medication items — informational only.
     *
     * Non-blocking: warnings are shown to the prescriber but do not prevent
     * the prescription from being saved. Actual stock enforcement happens
     * at dispense time.
     *
     * @param  array $meds       Medication rows (must have medicine_code + dose fields)
     * @param  int   $clinicId
     * @return array<array{name: string, stock: int, needed: int, code: string}>
     */
    public function checkStockWarnings(array $meds, int $clinicId): array
    {
        $warnings = [];

        foreach ($meds as $med) {
            $code = $med['medicine_code'] ?? null;
            if (!$code) continue;

            $needed = (
                (float)($med['morning']   ?? 0) +
                (float)($med['afternoon'] ?? 0) +
                (float)($med['evening']   ?? 0) +
                (float)($med['night']     ?? 0)
            ) * max(1, (int)($med['days'] ?? 1));

            if ($needed <= 0) continue;

            $medicine = MedicineModel::where('clinic_id', $clinicId)
                ->where('code', $code)
                ->first(['name', 'stock', 'code']);

            if ($medicine && $medicine->stock < (int)ceil($needed)) {
                $warnings[] = [
                    'code'   => $medicine->code,
                    'name'   => $medicine->name,
                    'stock'  => $medicine->stock,
                    'needed' => (int)ceil($needed),
                ];
            }
        }

        return $warnings;
    }

    /**
     * Fully dispense a prescription.
     *
     * Delegates to PharmacyService — the single canonical stock deduction path.
     * PharmacyService writes: InventoryTransactionModel + DispenseModel + StockBalanceModel.
     *
     * @throws \RuntimeException if any medicine has insufficient stock
     */
    public function dispense(PrescriptionModel $rx, string $dispensedBy): void
    {
        app(PharmacyService::class)->dispense($rx, $dispensedBy);
    }
}
