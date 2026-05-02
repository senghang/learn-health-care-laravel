<?php

namespace App\Services;

use App\Models\MedicineModel;
use App\Models\StockBalanceModel;
use App\Models\StockMovementModel;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * InventoryService — canonical path for all MANUAL stock movements.
 *
 * ══════════════════════════════════════════════════════════════════════════════
 * TWO STOCK TABLES — DO NOT CONFUSE
 * ══════════════════════════════════════════════════════════════════════════════
 *
 *   stock_movements       — manual warehouse operations (this service)
 *                           Types: in, return, out, expired, adjustment
 *
 *   inventory_transactions — clinical dispenses (PharmacyService / StockService)
 *                           Types: dispense, return
 *
 * Both tables affect medicines.stock. StockBalanceModel is the denorm summary.
 *
 * ══════════════════════════════════════════════════════════════════════════════
 * CONCURRENCY SAFETY
 * ══════════════════════════════════════════════════════════════════════════════
 *
 * Every write method:
 *   1. Opens a DB transaction
 *   2. Acquires SELECT ... FOR UPDATE on the medicine row
 *   3. Reads stock_before INSIDE the lock (no stale reads)
 *   4. Writes stock + movement record atomically
 *   5. Syncs StockBalanceModel
 */
final class InventoryService
{
    // ── Receive (stock-in) ────────────────────────────────────────────────────

    /**
     * Record a stock-in or supplier-return movement.
     *
     * @param  array{
     *   quantity: int,
     *   type: 'in'|'return',
     *   reference?: string,
     *   supplier?: string,
     *   unit_cost?: float,
     *   expiry_date?: string,
     *   batch_no?: string,
     *   note?: string,
     * } $data
     * @throws \RuntimeException if quantity <= 0
     */
    public function receiveStock(MedicineModel $medicine, array $data): StockMovementModel
    {
        $qty = (int)($data['quantity'] ?? 0);
        if ($qty <= 0) {
            throw new \RuntimeException('Quantity to receive must be greater than zero.');
        }

        return DB::transaction(function () use ($medicine, $data, $qty) {
            $locked = MedicineModel::lockForUpdate()->findOrFail($medicine->id);
            $before = $locked->stock;
            $after  = $before + $qty;

            $locked->increment('stock', $qty);

            $movement = StockMovementModel::create([
                'clinic_id'     => currentClinic()->id,
                'medicine_id'   => $locked->id,
                'medicine_code' => $locked->code,
                'medicine_name' => $locked->name,
                'type'          => $data['type'] ?? 'in',
                'quantity'      => $qty,
                'stock_before'  => $before,
                'stock_after'   => $after,
                'reference'     => $data['reference'] ?? null,
                'supplier'      => $data['supplier'] ?? null,
                'unit_cost'     => isset($data['unit_cost']) ? (float)$data['unit_cost'] : null,
                'expiry_date'   => $data['expiry_date'] ?? null,
                'batch_no'      => $data['batch_no'] ?? null,
                'note'          => $data['note'] ?? null,
                'recorded_by'   => $data['recorded_by'] ?? auth()->user()?->name,
            ]);

            StockBalanceModel::syncFromMedicine($locked->fresh());

            return $movement;
        });
    }

    // ── Remove (stock-out, expired) ───────────────────────────────────────────

    /**
     * Record a manual stock-out or expiry write-off.
     *
     * @param  array{
     *   quantity: int,
     *   type: 'out'|'expired',
     *   reference?: string,
     *   note?: string,
     * } $data
     * @throws \RuntimeException on insufficient stock ('out' type only)
     */
    public function removeStock(MedicineModel $medicine, array $data): StockMovementModel
    {
        $qty  = (int)($data['quantity'] ?? 0);
        $type = $data['type'] ?? 'out';

        if ($qty <= 0) {
            throw new \RuntimeException('Quantity to remove must be greater than zero.');
        }

        return DB::transaction(function () use ($medicine, $data, $qty, $type) {
            $locked = MedicineModel::lockForUpdate()->findOrFail($medicine->id);
            $before = $locked->stock;

            // 'out' (dispensed/sold) must have sufficient stock.
            // 'expired' write-off is capped at available stock (can't expire more than you have).
            if ($locked->stock < $qty) {
                if ($type === 'out') {
                    throw new \RuntimeException(
                        "Insufficient stock for {$locked->name}: have {$locked->stock}, removing {$qty}."
                    );
                }
                // expired: cap at available stock (write off whatever remains)
                $qty = $locked->stock;
            }

            $after = $before - $qty;
            $locked->update(['stock' => $after]);

            $movement = StockMovementModel::create([
                'clinic_id'     => currentClinic()->id,
                'medicine_id'   => $locked->id,
                'medicine_code' => $locked->code,
                'medicine_name' => $locked->name,
                'type'          => $type,
                'quantity'      => $qty,
                'stock_before'  => $before,
                'stock_after'   => $after,
                'reference'     => $data['reference'] ?? null,
                'note'          => $data['note'] ?? null,
                'recorded_by'   => $data['recorded_by'] ?? auth()->user()?->name,
            ]);

            StockBalanceModel::syncFromMedicine($locked->fresh());

            return $movement;
        });
    }

    // ── Adjust (physical count) ───────────────────────────────────────────────

    /**
     * Set stock to a new absolute count (physical inventory count).
     *
     * The delta (newQty - currentStock) can be positive or negative.
     * Records an 'adjustment' movement with the actual delta stored in note.
     *
     * @throws \RuntimeException if newQty < 0
     */
    public function adjustToCount(MedicineModel $medicine, int $newQty, string $note, string $recordedBy): StockMovementModel
    {
        if ($newQty < 0) {
            throw new \RuntimeException('Adjusted stock count cannot be negative.');
        }

        return DB::transaction(function () use ($medicine, $newQty, $note, $recordedBy) {
            $locked = MedicineModel::lockForUpdate()->findOrFail($medicine->id);
            $before = $locked->stock;
            $delta  = $newQty - $before;

            $locked->update(['stock' => $newQty]);

            $deltaStr = $delta >= 0 ? "+{$delta}" : "{$delta}";
            $movement = StockMovementModel::create([
                'clinic_id'     => currentClinic()->id,
                'medicine_id'   => $locked->id,
                'medicine_code' => $locked->code,
                'medicine_name' => $locked->name,
                'type'          => 'adjustment',
                'quantity'      => abs($delta),
                'stock_before'  => $before,
                'stock_after'   => $newQty,
                'note'          => "Physical count: {$newQty} (delta {$deltaStr})" . ($note ? " — {$note}" : ''),
                'recorded_by'   => $recordedBy,
            ]);

            StockBalanceModel::syncFromMedicine($locked->fresh());

            return $movement;
        });
    }

    // ── Queries ───────────────────────────────────────────────────────────────

    public function getMovements(array $filters = [], int $perPage = 30): LengthAwarePaginator
    {
        $clinicId = currentClinic()->id;

        return StockMovementModel::with('medicine')
            ->where('clinic_id', $clinicId)
            ->when($filters['search'] ?? null, fn($q, $s) =>
                $q->where('medicine_name', 'like', "%{$s}%")
                  ->orWhere('reference', 'like', "%{$s}%")
                  ->orWhere('supplier', 'like', "%{$s}%")
                  ->orWhere('batch_no', 'like', "%{$s}%")
            )
            ->when($filters['type'] ?? null,  fn($q, $v) => $q->where('type', $v))
            ->when($filters['date'] ?? null,  fn($q, $v) => $q->whereDate('created_at', $v))
            ->when($filters['month'] ?? null, fn($q, $v) => $q->whereRaw("TO_CHAR(created_at,'YYYY-MM') = ?", [$v]))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Merged ledger for a single medicine:
     * stock_movements (manual) + inventory_transactions (clinical) sorted by date.
     */
    public function getMedicineLedger(MedicineModel $medicine): array
    {
        $clinicId = currentClinic()->id;

        $manual = StockMovementModel::where('medicine_id', $medicine->id)
            ->where('clinic_id', $clinicId)
            ->orderByDesc('created_at')
            ->limit(100)
            ->get()
            ->map(fn($m) => [
                'source'    => 'manual',
                'date'      => $m->created_at,
                'type'      => $m->type,
                'quantity'  => in_array($m->type, ['in', 'return']) ? "+{$m->quantity}" : "-{$m->quantity}",
                'before'    => $m->stock_before,
                'after'     => $m->stock_after,
                'reference' => $m->reference ?? $m->batch_no,
                'note'      => $m->note,
                'by'        => $m->recorded_by,
            ]);

        $clinical = \App\Models\InventoryTransactionModel::where('medicine_id', $medicine->id)
            ->where('clinic_id', $clinicId)
            ->orderByDesc('created_at')
            ->limit(100)
            ->get()
            ->map(fn($t) => [
                'source'    => 'clinical',
                'date'      => $t->created_at,
                'type'      => $t->type,
                'quantity'  => $t->quantity >= 0 ? "+{$t->quantity}" : "{$t->quantity}",
                'before'    => $t->stock_before,
                'after'     => $t->stock_after,
                'reference' => $t->invoice_code ?? $t->visit_code,
                'note'      => $t->note,
                'by'        => $t->created_by ?? '—',
            ]);

        return $manual->concat($clinical)
            ->sortByDesc('date')
            ->take(150)
            ->values()
            ->all();
    }
}
