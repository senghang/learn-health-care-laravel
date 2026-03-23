<?php

namespace App\Http\Controllers\Clinics\Inventory;

use App\Http\Controllers\Controller;
use App\Models\MedicineModel;
use App\Models\StockMovementModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\InventoryTransactionModel;
use Illuminate\Http\Response;
use Illuminate\View\View;

class InventoryController extends Controller
{
    private int $clinicId;

    public function __construct()
    {
        $this->clinicId = currentClinic()->id;
    }

    // ── Products (medicine master) ────────────────────────────────────────────

    public function products(Request $request): View
    {
        $medicines = MedicineModel::where('clinic_id', $this->clinicId)
            ->when($request->filled('search'), fn($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('name_kh', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%")
                  ->orWhere('generic_name', 'like', "%{$request->search}%")
            )
            ->when($request->filled('category'), fn($q) => $q->where('category', $request->category))
            ->when($request->status === 'low',  fn($q) => $q->whereColumn('stock', '<=', 'stock_alert')->where('stock', '>', 0))
            ->when($request->status === 'out',  fn($q) => $q->where('stock', 0))
            ->when($request->status === 'active', fn($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        $categories = MedicineModel::where('clinic_id', $this->clinicId)
            ->whereNotNull('category')->distinct()->pluck('category')->sort()->values();

        $stats = [
            'total'    => MedicineModel::where('clinic_id', $this->clinicId)->count(),
            'low'      => MedicineModel::where('clinic_id', $this->clinicId)->whereColumn('stock', '<=', 'stock_alert')->where('stock', '>', 0)->count(),
            'out'      => MedicineModel::where('clinic_id', $this->clinicId)->where('stock', 0)->count(),
            'value'    => MedicineModel::where('clinic_id', $this->clinicId)->selectRaw('SUM(stock * price) as total')->value('total') ?? 0,
        ];

        return view('clinics.inventory.products', compact('medicines', 'categories', 'stats'));
    }

    public function productCreate(): View
    {
        $categories = MedicineModel::where('clinic_id', $this->clinicId)
            ->whereNotNull('category')->distinct()->pluck('category')->sort()->values();
        return view('clinics.inventory.product-form', ['medicine' => null, 'categories' => $categories]);
    }

    public function productStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            // code is auto-generated
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

        $med = MedicineModel::create(array_merge($data, [
                'code'      => \App\Services\ClinicCodeService::next(currentClinic()->id, 'MED'),
                'clinic_id' => $this->clinicId,
            ]));

        // Record initial stock as a stock-in movement
        if ($data['stock'] > 0) {
            StockMovementModel::create([
                'clinic_id'     => $this->clinicId,
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

        return redirect()->route('inventory.products')
            ->with('flash', "Product {$med->code} created.");
    }

    public function productEdit(int $id): View
    {
        $medicine   = MedicineModel::where('clinic_id', $this->clinicId)->findOrFail($id);
        $categories = MedicineModel::where('clinic_id', $this->clinicId)
            ->whereNotNull('category')->distinct()->pluck('category')->sort()->values();
        return view('clinics.inventory.product-form', compact('medicine', 'categories'));
    }

    public function productUpdate(Request $request, int $id): RedirectResponse
    {
        $med = MedicineModel::where('clinic_id', $this->clinicId)->findOrFail($id);

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

        return redirect()->route('inventory.products')
            ->with('flash', 'Product updated.');
    }

    // ── Stock In ──────────────────────────────────────────────────────────────

    public function stockIn(Request $request): View
    {
        $movements = StockMovementModel::where('clinic_id', $this->clinicId)
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

        $medicines = MedicineModel::where('clinic_id', $this->clinicId)
            ->where('is_active', true)->orderBy('name')->get(['id','code','name','unit','stock']);

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

        $med = MedicineModel::where('clinic_id', $this->clinicId)->findOrFail($data['medicine_id']);
        $before = $med->stock;
        $after  = $before + $data['quantity'];

        $med->increment('stock', $data['quantity']);

        StockMovementModel::create(array_merge($data, [
            'clinic_id'     => $this->clinicId,
            'medicine_code' => $med->code,
            'medicine_name' => $med->name,
            'stock_before'  => $before,
            'stock_after'   => $after,
            'recorded_by'   => auth()->user()?->name,
        ]));

        return back()->with('flash', "Stock updated: {$med->name} +{$data['quantity']} (now {$after})");
    }

    // ── Stock Out ─────────────────────────────────────────────────────────────

    public function stockOut(Request $request): View
    {
        $movements = StockMovementModel::where('clinic_id', $this->clinicId)
            ->whereIn('type', ['out', 'expired', 'adjustment'])
            ->when($request->filled('search'), fn($q) =>
                $q->where('medicine_name', 'like', "%{$request->search}%")
                  ->orWhere('reference', 'like', "%{$request->search}%")
            )
            ->when($request->filled('date'), fn($q) => $q->whereDate('created_at', $request->date))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $medicines = MedicineModel::where('clinic_id', $this->clinicId)
            ->where('is_active', true)->orderBy('name')->get(['id','code','name','unit','stock']);

        return view('clinics.inventory.stock-out', compact('movements', 'medicines'));
    }

    public function stockOutStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'medicine_id' => 'required|integer|exists:medicines,id',
            'quantity'    => 'required|integer|min:1',
            'type'        => 'required|in:out,expired,adjustment',
            'reference'   => 'nullable|string|max:80',
            'note'        => 'nullable|string',
        ]);

        $med = MedicineModel::where('clinic_id', $this->clinicId)->findOrFail($data['medicine_id']);

        if ($med->stock < $data['quantity'] && $data['type'] !== 'adjustment') {
            return back()->withErrors(['quantity' => "Insufficient stock. Available: {$med->stock}"])->withInput();
        }

        $before = $med->stock;
        $after  = max(0, $before - $data['quantity']);
        $med->update(['stock' => $after]);

        StockMovementModel::create(array_merge($data, [
            'clinic_id'     => $this->clinicId,
            'medicine_code' => $med->code,
            'medicine_name' => $med->name,
            'stock_before'  => $before,
            'stock_after'   => $after,
            'recorded_by'   => auth()->user()?->name,
        ]));

        return back()->with('flash', "Stock updated: {$med->name} -{$data['quantity']} (now {$after})");
    }

    // ── Inventory Report (moved from ReportController) ────────────────────────

    public function report(): View
    {
        $clinicId = $this->clinicId;

        $stats = [
            'total' => MedicineModel::where('clinic_id', $clinicId)->count(),
            'low'   => MedicineModel::where('clinic_id', $clinicId)->whereColumn('stock', '<=', 'stock_alert')->where('stock', '>', 0)->count(),
            'out'   => MedicineModel::where('clinic_id', $clinicId)->where('stock', '<=', 0)->count(),
            'value' => MedicineModel::where('clinic_id', $clinicId)->selectRaw('COALESCE(SUM(stock::numeric * price),0) as total')->value('total') ?? 0,
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
