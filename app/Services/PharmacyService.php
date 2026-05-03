<?php

namespace App\Services;

use App\Models\DispenseModel;
use App\Models\InventoryTransactionModel;
use App\Models\InpatientMedicationModel;
use App\Models\MedicineModel;
use App\Models\PrescriptionModel;
use App\Models\StockBalanceModel;
use Illuminate\Support\Facades\DB;

/**
 * PharmacyService — THE single canonical path for all stock deductions.
 *
 * ══════════════════════════════════════════════════════════════════════════════
 * WHY THIS CLASS EXISTS
 * ══════════════════════════════════════════════════════════════════════════════
 *
 * Before this class, three fragmented paths deducted stock:
 *
 *   Path A — PrescriptionService::dispense()
 *     ✓ medicines.stock  ✗ InventoryTransaction  ✗ Dispense  ✗ StockBalance
 *
 *   Path B — StockService::dispense() [invoice path]
 *     ✓ medicines.stock  ✓ InventoryTransaction  ✗ Dispense  ✗ StockBalance
 *
 *   Path C — StockService::dispenseByPrescription()
 *     ✓ medicines.stock  ✓ InventoryTransaction  ✓ Dispense  ✓ StockBalance
 *
 * This class unifies all paths into a single implementation that:
 *   1. Locks rows in consistent ID order (deadlock prevention)
 *   2. Is idempotent (checks existing DispenseModel records)
 *   3. Writes full audit trail (InventoryTransaction + Dispense + StockBalance)
 *   4. Handles the medication_code → medicine_id resolution gap
 *
 * ══════════════════════════════════════════════════════════════════════════════
 * LOCK ORDER — DEADLOCK PREVENTION
 * ══════════════════════════════════════════════════════════════════════════════
 *
 * Always acquire row locks in ascending medicine_id order.
 * Two concurrent transactions covering the same medicines will wait
 * for the first to complete rather than deadlock.
 *
 * ══════════════════════════════════════════════════════════════════════════════
 * IDEMPOTENCY
 * ══════════════════════════════════════════════════════════════════════════════
 *
 * Each (prescription_code, medicine_id) pair in `dispenses` acts as an
 * idempotency key. Re-calling dispense() for an already-dispensed item
 * skips it rather than double-deducting stock.
 *
 * LIMITATION: If the same medicine appears twice in one prescription (different
 * dosages), both rows map to the same medicine_id. The second will be skipped.
 * This is an accepted trade-off; duplicate medicines in one Rx is atypical.
 *
 * ══════════════════════════════════════════════════════════════════════════════
 * STOCK DEDUCTION TIMING
 * ══════════════════════════════════════════════════════════════════════════════
 *
 * Stock is deducted at DISPENSE time, NOT at prescribe time.
 * The prescription is the doctor's order; inventory commitment happens
 * when the pharmacist physically hands out the medicine.
 */
final class PharmacyService
{
    // ── OPD Dispensing ────────────────────────────────────────────────────────

    /**
     * Validate stock availability for a prescription without committing.
     *
     * @return array{
     *   items: array<array{
     *     medication: \App\Models\PrescriptionMedicationModel,
     *     medicine: ?MedicineModel,
     *     needed: int,
     *     available: ?int,
     *     sufficient: ?bool,
     *     already_dispensed: bool,
     *     can_dispense: bool,
     *   }>,
     *   errors: string[],
     *   warnings: string[],
     *   can_full_dispense: bool,
     * }
     */
    public function checkStock(PrescriptionModel $rx): array
    {
        $clinicId = currentClinic()->id;
        $items    = [];
        $errors   = [];
        $warnings = [];

        foreach ($rx->medications as $item) {
            $medicine         = null;
            $alreadyDispensed = false;
            $needed           = (int) ceil($item->total_qty);

            if ($item->medication_code) {
                $medicine = MedicineModel::where('clinic_id', $clinicId)
                    ->where('code', $item->medication_code)
                    ->first();
            }

            if ($medicine) {
                $alreadyDispensed = DispenseModel::where('prescription_code', $rx->code)
                    ->where('medicine_id', $medicine->id)
                    ->whereNotNull('dispensed_at')
                    ->where('status', 'dispensed')
                    ->exists();
            }

            $available = $medicine?->stock;
            $sufficient = ($available === null) ? null : ($available >= $needed);

            // Errors: blocking issues
            if (!$item->medication_code) {
                $warnings[] = "{$item->medicine_name}: no catalogue code — cannot track stock.";
            } elseif (!$medicine) {
                $errors[] = "{$item->medicine_name}: not found in active formulary.";
            } elseif (!$alreadyDispensed && !$sufficient) {
                $errors[] = "{$medicine->name}: need {$needed}, have {$available}.";
            }

            // Warnings: low stock after dispense
            if ($medicine && !$alreadyDispensed && $sufficient) {
                $afterDispense = $available - $needed;
                if ($afterDispense <= ($medicine->stock_alert ?? 10)) {
                    $warnings[] = "{$medicine->name}: will reach low-stock level ({$afterDispense} remaining).";
                }
            }

            $items[] = [
                'medication'        => $item,
                'medicine'          => $medicine,
                'needed'            => $needed,
                'available'         => $available,
                'sufficient'        => $sufficient,
                'already_dispensed' => $alreadyDispensed,
                'can_dispense'      => $medicine !== null && !$alreadyDispensed && $sufficient === true,
            ];
        }

        $blockingErrors = array_filter($errors); // errors = must-fix before dispensing
        return [
            'items'            => $items,
            'errors'           => array_values($errors),
            'warnings'         => array_values($warnings),
            'can_full_dispense' => count($blockingErrors) === 0,
        ];
    }

    /**
     * Fully dispense all pending medications in a prescription.
     *
     * All-or-nothing: if any dispensable item fails stock check, the entire
     * transaction rolls back. Items with no catalogue code are skipped (warned).
     * Items already dispensed are skipped silently (idempotent).
     *
     * @throws \RuntimeException on insufficient stock or already fully dispensed
     * @return array{dispensed: string[], skipped: string[], errors: string[]}
     */
    public function dispense(PrescriptionModel $rx, string $dispensedBy): array
    {
        if ($rx->dispensed_status === 'dispensed') {
            throw new \RuntimeException('Prescription ' . $rx->code . ' is already fully dispensed.');
        }

        $clinicId = currentClinic()->id;
        $result   = ['dispensed' => [], 'skipped' => [], 'errors' => []];

        DB::transaction(function () use ($rx, $dispensedBy, $clinicId, &$result) {
            // Resolve medicines and sort by ID for consistent lock order (deadlock prevention)
            $resolved = $this->resolveMedicines($rx->medications, $clinicId);
            $sorted   = collect($resolved)->sortBy(fn($r) => $r['medicine']?->id ?? PHP_INT_MAX);

            foreach ($sorted as $row) {
                [$item, $medicine, $needed] = [$row['medication'], $row['medicine'], $row['needed']];

                if (!$item->medication_code) {
                    $result['skipped'][] = $item->medicine_name . ' — no catalogue code';
                    continue;
                }

                if (!$medicine) {
                    $result['errors'][] = $item->medicine_name . ' — not found in formulary';
                    continue;
                }

                // Idempotency: skip if this medicine already dispensed for this Rx
                $alreadyDispensed = DispenseModel::where('prescription_code', $rx->code)
                    ->where('medicine_id', $medicine->id)
                    ->where('status', 'dispensed')
                    ->exists();

                if ($alreadyDispensed) {
                    $result['skipped'][] = $medicine->name . ' — already dispensed';
                    continue;
                }

                // Acquire pessimistic lock AFTER determining we need this row
                $locked = MedicineModel::where('clinic_id', $clinicId)
                    ->where('id', $medicine->id)
                    ->lockForUpdate()
                    ->first();

                if (!$locked || $locked->stock < $needed) {
                    throw new \RuntimeException(
                        "Insufficient stock for {$medicine->name}: " .
                        "need {$needed}, have " . ($locked?->stock ?? 0) . "."
                    );
                }

                $this->deductAndLog($locked, $needed, $rx, $dispensedBy);
                $result['dispensed'][] = $locked->name . " ({$needed})";
            }

            // Update prescription header
            $allPending = collect($result['skipped'])->isEmpty() && collect($result['errors'])->isEmpty();
            $anyDispensed = !empty($result['dispensed']);

            if ($anyDispensed) {
                $newStatus = ($allPending && empty($result['errors'])) ? 'dispensed' : 'partial';
                $rx->update([
                    'dispensed_status' => $newStatus,
                    'dispensed_by'     => $dispensedBy,
                ]);
            }
        });

        return $result;
    }

    /**
     * Partially dispense a selected subset of medication items.
     * Only items whose PrescriptionMedicationModel ID is in $selectedIds are processed.
     *
     * @param  int[] $selectedIds  PrescriptionMedicationModel->id values to dispense
     * @throws \RuntimeException on insufficient stock
     */
    public function dispensePartial(PrescriptionModel $rx, array $selectedIds, string $dispensedBy): array
    {
        $clinicId = currentClinic()->id;
        $result   = ['dispensed' => [], 'skipped' => [], 'errors' => []];

        DB::transaction(function () use ($rx, $selectedIds, $dispensedBy, $clinicId, &$result) {
            $selectedMeds = $rx->medications->whereIn('id', $selectedIds);
            $resolved     = $this->resolveMedicines($selectedMeds, $clinicId);
            $sorted       = collect($resolved)->sortBy(fn($r) => $r['medicine']?->id ?? PHP_INT_MAX);

            foreach ($sorted as $row) {
                [$item, $medicine, $needed] = [$row['medication'], $row['medicine'], $row['needed']];

                if (!$medicine) {
                    $result['errors'][] = $item->medicine_name . ' — not found in formulary';
                    continue;
                }

                $alreadyDispensed = DispenseModel::where('prescription_code', $rx->code)
                    ->where('medicine_id', $medicine->id)
                    ->where('status', 'dispensed')
                    ->exists();

                if ($alreadyDispensed) {
                    $result['skipped'][] = $medicine->name . ' — already dispensed';
                    continue;
                }

                $locked = MedicineModel::where('clinic_id', $clinicId)
                    ->where('id', $medicine->id)
                    ->lockForUpdate()
                    ->first();

                if (!$locked || $locked->stock < $needed) {
                    throw new \RuntimeException(
                        "Insufficient stock for {$medicine->name}: " .
                        "need {$needed}, have " . ($locked?->stock ?? 0) . "."
                    );
                }

                $this->deductAndLog($locked, $needed, $rx, $dispensedBy);
                $result['dispensed'][] = $locked->name . " ({$needed})";
            }

            if (!empty($result['dispensed'])) {
                // Check if all items across the full prescription are now dispensed
                $allDispensed = $this->allItemsDispensed($rx, $clinicId);
                $rx->update([
                    'dispensed_status' => $allDispensed ? 'dispensed' : 'partial',
                    'dispensed_by'     => $dispensedBy,
                ]);
            }
        });

        return $result;
    }

    // ── IPD Medication Administration ─────────────────────────────────────────

    /**
     * Administer an IPD medication dose — deducts stock.
     *
     * Called when nursing staff records that a medication was given to an
     * admitted patient. Creates InventoryTransaction + updates StockBalance.
     *
     * @throws \RuntimeException if insufficient stock
     */
    public function administerIPD(InpatientMedicationModel $med, string $administeredBy): void
    {
        DB::transaction(function () use ($med, $administeredBy) {
            $clinicId = currentClinic()->id;
            $qty      = (int) ceil((float) $med->quantity);

            if ($qty <= 0) {
                throw new \RuntimeException('Quantity must be greater than zero.');
            }

            $medicine = MedicineModel::where('clinic_id', $clinicId)
                ->where('id', $med->medicine_id)
                ->lockForUpdate()
                ->first();

            if (!$medicine) {
                throw new \RuntimeException("Medicine not found in formulary.");
            }

            if ($medicine->stock < $qty) {
                throw new \RuntimeException(
                    "Insufficient stock for {$medicine->name}: need {$qty}, have {$medicine->stock}."
                );
            }

            $before = $medicine->stock;
            $medicine->decrement('stock', $qty);
            $after = $medicine->stock;

            // Inventory transaction (type='dispense' — the only out-type in the enum)
            InventoryTransactionModel::create([
                'medicine_id'   => $medicine->id,
                'medicine_code' => $medicine->code,
                'medicine_name' => $medicine->name,
                'visit_code'    => $med->visit_code,
                'patient_code'  => $med->patient_code,
                'type'          => 'dispense',
                'quantity'      => -$qty,
                'stock_before'  => $before,
                'stock_after'   => $after,
                'unit_price'    => $medicine->price,
                'total_price'   => $medicine->price * $qty,
                'note'          => 'IPD admin: ' . $med->code,
            ]);

            StockBalanceModel::syncFromMedicine($medicine);

            // Mark the medication order as administered
            $med->update([
                'status'          => 'completed',
                'administered_by' => $administeredBy,
                'administered_at' => now(),
            ]);
        });
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Resolve medications to their MedicineModel and needed quantity.
     * Handles the medication_code (string) → medicine_id (int) gap.
     *
     * @return array<array{medication: mixed, medicine: ?MedicineModel, needed: int}>
     */
    private function resolveMedicines(iterable $medications, int $clinicId): array
    {
        $resolved = [];
        foreach ($medications as $item) {
            $medicine = null;
            if ($item->medication_code) {
                $medicine = MedicineModel::where('clinic_id', $clinicId)
                    ->where('code', $item->medication_code)
                    ->first();
            }
            $resolved[] = [
                'medication' => $item,
                'medicine'   => $medicine,
                'needed'     => (int) ceil($item->total_qty),
            ];
        }
        return $resolved;
    }

    /**
     * Core stock deduction + audit writes for one medication item.
     * Caller must hold the pessimistic lock on $medicine before calling.
     */
    private function deductAndLog(
        MedicineModel   $medicine,
        int             $qty,
        PrescriptionModel $rx,
        string          $dispensedBy
    ): void {
        $clinicId = currentClinic()->id;
        $before   = $medicine->stock;

        $medicine->decrement('stock', $qty);
        $after = $medicine->stock;

        // Inventory transaction — full audit trail
        InventoryTransactionModel::create([
            'medicine_id'   => $medicine->id,
            'medicine_code' => $medicine->code,
            'medicine_name' => $medicine->name,
            'visit_code'    => $rx->visit_code,
            'patient_code'  => $rx->patient_code,
            'type'          => 'dispense',
            'quantity'      => -$qty,
            'stock_before'  => $before,
            'stock_after'   => $after,
            'unit_price'    => $medicine->price,
            'total_price'   => $medicine->price * $qty,
            'note'          => 'Rx: ' . $rx->code,
        ]);

        // Pharmacy dispense record (per-item log)
        DispenseModel::create([
            'code'              => ClinicCodeService::next($clinicId, 'DSP'),
            'prescription_code' => $rx->code,
            'medicine_id'       => $medicine->id,
            'medicine_name'     => $medicine->name,
            'quantity'          => $qty,
            'patient_code'      => $rx->patient_code,
            'visit_code'        => $rx->visit_code,
            'dispensed_by'      => $dispensedBy,
            'dispensed_at'      => now(),
            'status'            => 'dispensed',
        ]);

        // Sync denormalized balance table
        StockBalanceModel::syncFromMedicine($medicine);
    }

    /**
     * Check whether all catalogued items in a prescription have a dispense record.
     * Used to auto-detect when partial dispense completes the full prescription.
     */
    private function allItemsDispensed(PrescriptionModel $rx, int $clinicId): bool
    {
        foreach ($rx->medications as $item) {
            if (!$item->medication_code) continue; // free-text — skip

            $medicine = MedicineModel::where('clinic_id', $clinicId)
                ->where('code', $item->medication_code)
                ->first();
            if (!$medicine) continue;

            $hasRecord = DispenseModel::where('prescription_code', $rx->code)
                ->where('medicine_id', $medicine->id)
                ->where('status', 'dispensed')
                ->exists();

            if (!$hasRecord) return false;
        }
        return true;
    }
}
