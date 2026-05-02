<?php

namespace App\Http\Controllers\Clinics\Inventory;

use App\Http\Controllers\Controller;
use App\Models\MedicineModel;
use App\Models\StockBalanceModel;
use App\Models\StockMovementModel;
use App\Models\InventoryTransactionModel;
use App\Services\ClinicCodeService;
use App\Services\InventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function __construct(private InventoryService $inventory) {}

    // ── Products (medicine master) ────────────────────────────────────────────

    public function products(Request $request): View
    {
        $clinicId = currentClinic()->id;

        $medicines = MedicineModel::where('clinic_id', $clinicId)
            ->when($request->filled('search'), fn($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('name_kh', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%")
                  ->orWhere('generic_name', 'like', "%{$request->search}%")
            )
            ->when($request->filled('category'), fn($q) => $q->where('category', $request->category))
            ->when($request->status === 'low',    fn($q) => $q->whereColumn('stock', '<=', 'stock_alert')->where('stock', '>', 0))
            ->when($request->status === 'out',    fn($q) => $q->where('stock', '<=', 0))
            ->when($request->status === 'active', fn($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        $categories = MedicineModel::where('clinic_id', $clinicId)
            ->whereNotNull('category')->distinct()->pluck('category')->sort()->values();

        $stats = [
            'total' => MedicineModel::where('clinic_id', $clinicId)->count(),
            'low'   => MedicineModel::where('clinic_id', $clinicId)->whereColumn('stock', '<=', 'stock_alert')->where('stock', '>', 0)->count(),
            'out'   => MedicineModel::where('clinic_id', $clinicId)->where('stock', '<=', 0)->count(),
            'value' => MedicineModel::where('clinic_id', $clinicId)->selectRaw('COALESCE(SUM(stock::numeric * price), 0) as total')->value('total') ?? 0,
        ];

        return view('clinics.inventory.products', compact('medicines', 'categories', 'stats'));
    }

    public function productCreate(): View
    {
        $categories = MedicineModel::where('clinic_id', currentClinic()->id)
            ->whereNotNull('category')->distinct()->pluck('category')->sort()->values();
        return view('clinics.inventory.product-form', ['medicine' => null, 'categories' => $categories]);
    }

    public function productStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'         => 'required|string|max:120',
            'name_kh'      => 'nullable|string|max:120',
            'generic_name' => 'nullable|string|max:120',
            'category'     => 'nullable|string|max:60',
            'form'         => 'nullable|string|max:60',
            'strength'     => 'nullable|string|max:60',
            'unit'         => 'nullable|string|max:40',
            'price'        => 'required|numeric|min:0',
            'stock'        => 'required|integer|min:0',
            'stock_alert'  => 'required|integer|min:0',
        ]);

        $clinicId = currentClinic()->id;

        $med = MedicineModel::create(array_merge($data, [
            'code'      => ClinicCodeService::next($clinicId, 'MED'),
            'clinic_id' => $clinicId,
        ]));

        // Record initial stock + seed balance table
        if ($data['stock'] > 0) {
            StockMovementModel::create([
                'clinic_id'     => $clinicId,
                'medicine_id'   => $med->id,
                'medicine_code' => $med->code,
                'medicine_name' => $med->name,
                'type'          => 'in',
                'quantity'      => $data['stock'],
                'stock_before'  => 0,
                'stock_after'   => $data['stock'],
                'note'          => 'Initial stock on product creation',
                'recorded_by'   => auth()->user()?->name,
            ]);
        }

        // Always seed stock_balances for new products
        StockBalanceModel::syncFromMedicine($med);

        return redirect()->route('inventory.products')
            ->with('flash', "Product {$med->code} created.");
    }

    public function productEdit(int $id): View
    {
        $clinicId   = currentClinic()->id;
        $medicine   = MedicineModel::where('clinic_id', $clinicId)->findOrFail($id);
        $categories = MedicineModel::where('clinic_id', $clinicId)
            ->whereNotNull('category')->distinct()->pluck('category')->sort()->values();
        return view('clinics.inventory.product-form', compact('medicine', 'categories'));
    }

    public function productUpdate(Request $request, int $id): RedirectResponse
    {
        $med = MedicineModel::where('clinic_id', currentClinic()->id)->findOrFail($id);

        $med->update($request->validate([
            'name'         => 'required|string|max:120',
            'name_kh'      => 'nullable|string|max:120',
            'generic_name' => 'nullable|string|max:120',
            'category'     => 'nullable|string|max:60',
            'form'         => 'nullable|string|max:60',
            'strength'     => 'nullable|string|max:60',
            'unit'         => 'nullable|string|max:40',
            'price'        => 'required|numeric|min:0',
            'stock_alert'  => 'required|integer|min:0',
            'is_active'    => 'boolean',
        ]));

        // Sync balance whenever price or alert level changes
        StockBalanceModel::syncFromMedicine($med->fresh());

        return redirect()->route('inventory.products')
            ->with('flash', 'Product updated.');
    }

    // ── Stock In ──────────────────────────────────────────────────────────────

    public function stockIn(Request $request): View
    {
        $clinicId = currentClinic()->id;

        $movements = StockMovementModel::where('clinic_id', $clinicId)
            ->whereIn('type', ['in', 'return'])
            ->when($request->filled('search'), fn($q) =>
                $q->where('medicine_name', 'like', "%{$request->search}%")
                  ->orWhere('reference', 'like', "%{$request->search}%")
                  ->orWhere('supplier', 'like', "%{$request->search}%")
            )
            ->when($request->filled('date'), fn($q) => $q->whereDate('created_at', $request->date))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $medicines = MedicineModel::where('clinic_id', $clinicId)
            ->where('is_active', true)->orderBy('name')->get(['id', 'code', 'name', 'unit', 'stock']);

        return view('clinics.inventory.stock-in', compact('movements', 'medicines'));
    }

    public function stockInStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'medicine_id'  => 'required|integer|exists:medicines,id',
            'quantity'     => 'required|integer|min:1',
            'type'         => 'required|in:in,return',
            'reference'    => 'nullable|string|max:80',
            'supplier'     => 'nullable|string|max:120',
            'unit_cost'    => 'nullable|numeric|min:0',
            'expiry_date'  => 'nullable|date',
            'batch_no'     => 'nullable|string|max:60',
            'note'         => 'nullable|string',
        ]);

        $med = MedicineModel::where('clinic_id', currentClinic()->id)->findOrFail($data['medicine_id']);

        try {
            $movement = $this->inventory->receiveStock($med, $data);
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('flash', "Stock updated: {$med->name} +{$data['quantity']} (now {$movement->stock_after})");
    }

    // ── Stock Out ─────────────────────────────────────────────────────────────

    public function stockOut(Request $request): View
    {
        $clinicId = currentClinic()->id;

        $movements = StockMovementModel::where('clinic_id', $clinicId)
            ->whereIn('type', ['out', 'expired', 'adjustment'])
            ->when($request->filled('search'), fn($q) =>
                $q->where('medicine_name', 'like', "%{$request->search}%")
                  ->orWhere('reference', 'like', "%{$request->search}%")
            )
            ->when($request->filled('date'), fn($q) => $q->whereDate('created_at', $request->date))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $medicines = MedicineModel::where('clinic_id', $clinicId)
            ->where('is_active', true)->orderBy('name')->get(['id', 'code', 'name', 'unit', 'stock']);

        return view('clinics.inventory.stock-out', compact('movements', 'medicines'));
    }

    public function stockOutStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'medicine_id' => 'required|integer|exists:medicines,id',
            'quantity'    => 'required|integer|min:1',
            'type'        => 'required|in:out,expired',
            'reference'   => 'nullable|string|max:80',
            'note'        => 'nullable|string',
        ]);

        $med = MedicineModel::where('clinic_id', currentClinic()->id)->findOrFail($data['medicine_id']);

        try {
            $movement = $this->inventory->removeStock($med, $data);
        } catch (\Exception $e) {
            return back()->withErrors(['quantity' => $e->getMessage()])->withInput();
        }

        return back()->with('flash', "Stock updated: {$med->name} -{$data['quantity']} (now {$movement->stock_after})");
    }

    // ── Physical Count Adjustment ─────────────────────────────────────────────

    public function adjustment(Request $request): View
    {
        $clinicId = currentClinic()->id;

        $movements = StockMovementModel::where('clinic_id', $clinicId)
            ->where('type', 'adjustment')
            ->when($request->filled('search'), fn($q) =>
                $q->where('medicine_name', 'like', "%{$request->search}%")
            )
            ->when($request->filled('date'), fn($q) => $q->whereDate('created_at', $request->date))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $medicines = MedicineModel::where('clinic_id', $clinicId)
            ->where('is_active', true)->orderBy('name')->get(['id', 'code', 'name', 'unit', 'stock']);

        return view('clinics.inventory.adjustment', compact('movements', 'medicines'));
    }

    public function adjustmentStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'medicine_id' => 'required|integer|exists:medicines,id',
            'new_qty'     => 'required|integer|min:0',
            'note'        => 'nullable|string|max:255',
        ]);

        $med = MedicineModel::where('clinic_id', currentClinic()->id)->findOrFail($data['medicine_id']);

        try {
            $movement = $this->inventory->adjustToCount(
                $med,
                (int)$data['new_qty'],
                $data['note'] ?? '',
                auth()->user()?->name ?? 'system'
            );
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $delta = $movement->stock_after - $movement->stock_before;
        $deltaStr = $delta >= 0 ? "+{$delta}" : "{$delta}";
        return back()->with('flash', "Adjusted {$med->name}: {$movement->stock_before} → {$movement->stock_after} ({$deltaStr})");
    }

    // ── Unified Movements Ledger ──────────────────────────────────────────────

    public function movements(Request $request): View
    {
        $movements = $this->inventory->getMovements($request->only('search', 'type', 'date', 'month'));

        $typeStats = StockMovementModel::selectRaw("type, COUNT(*) as cnt, SUM(quantity) as total_qty")
            ->where('clinic_id', currentClinic()->id)
            ->groupBy('type')
            ->pluck('total_qty', 'type');

        return view('clinics.inventory.movements', compact('movements', 'typeStats'));
    }

    // ── Per-Medicine Ledger ───────────────────────────────────────────────────

    public function medicineLedger(int $id): View
    {
        $medicine = MedicineModel::where('clinic_id', currentClinic()->id)->findOrFail($id);
        $ledger   = $this->inventory->getMedicineLedger($medicine);
        $balance  = \App\Models\StockBalanceModel::where('medicine_id', $medicine->id)->first();

        return view('clinics.inventory.medicine-ledger', compact('medicine', 'ledger', 'balance'));
    }

    // ── Inventory Report ──────────────────────────────────────────────────────

    public function report(): View
    {
        $clinicId = currentClinic()->id;

        $stats = [
            'total' => MedicineModel::where('clinic_id', $clinicId)->count(),
            'low'   => MedicineModel::where('clinic_id', $clinicId)->whereColumn('stock', '<=', 'stock_alert')->where('stock', '>', 0)->count(),
            'out'   => MedicineModel::where('clinic_id', $clinicId)->where('stock', '<=', 0)->count(),
            'value' => MedicineModel::where('clinic_id', $clinicId)->selectRaw('COALESCE(SUM(stock::numeric * price), 0) as total')->value('total') ?? 0,
        ];

        $lowMeds = MedicineModel::where('clinic_id', $clinicId)
            ->whereColumn('stock', '<=', 'stock_alert')
            ->orderBy('stock')
            ->limit(25)
            ->get();

        $recentTxns = InventoryTransactionModel::where('clinic_id', $clinicId)
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        return view('clinics.inventory.report', compact('stats', 'lowMeds', 'recentTxns'));
    }
}
