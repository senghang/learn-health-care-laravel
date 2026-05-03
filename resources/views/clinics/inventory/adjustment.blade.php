@extends('clinics.layout.app')
@section('title', 'Stock Adjustment')
@section('content')

<div class="flex items-center justify-between mb-4 flex-wrap gap-3">
    <div>
        <x-ui.breadcrumbs :items="[['label'=>'ដើម','url'=>route('dashboard')],['label'=>'Inventory','url'=>route('inventory.products')],['label'=>'Adjustment']]" />
        <h1 class="text-xl font-black mt-1" style="color:#012970">កែតម្រូវស្តុក <span class="text-sm font-normal text-slate-400">/ Physical Count Adjustment</span></h1>
    </div>
    <div>
        <x-ui.button variant="secondary" size="sm" href="{{ route('inventory.movements') }}">
            <i class="bi bi-journal-text"></i> All Movements
        </x-ui.button>
    </div>
</div>

@if(session('flash'))
    <x-ui.alert type="success" class="mb-3">{{ session('flash') }}</x-ui.alert>
@endif
@if(session('error'))
    <x-ui.alert type="error" class="mb-3">{{ session('error') }}</x-ui.alert>
@endif

<div class="row g-3">

{{-- Form --}}
<div class="col-12 col-lg-4">
  <x-ui.card style="position:sticky;top:76px">
    <x-slot:header>
        <x-ui.card-header km="Physical Count" icon="bi-sliders"/>
    </x-slot:header>
    <div class="note" style="background:#fff8e1;color:#92400e;border:1px solid #fde68a;margin-bottom:14px;font-size:12px">
      <i class="bi bi-info-circle-fill"></i>
      Enter the <strong>actual counted quantity</strong>. The system will compute the delta and update stock accordingly.
    </div>

    @if($errors->any())
    <x-ui.alert type="error" class="mb-3">
      <ul style="margin:0;padding-left:14px;font-size:12px">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </x-ui.alert>
    @endif

    <form method="POST" action="{{ route('inventory.adjustment.store') }}" novalidate>
      @csrf
      <div class="fld">
        <label class="flbl"><span class="km">ថ្នាំ / ផលិតផល</span><span class="en">/ Product</span><span class="req">*</span></label>
        <select name="medicine_id" class="form-select" required id="medSelect" onchange="showCurrentStock(this)">
          <option value="">— ជ្រើស —</option>
          @foreach($medicines as $med)
          <option value="{{ $med->id }}" data-stock="{{ $med->stock }}" data-unit="{{ $med->unit }}"
                  {{ old('medicine_id') == $med->id ? 'selected' : '' }}>
            {{ $med->name }} — Current: {{ $med->stock }} {{ $med->unit }}
          </option>
          @endforeach
        </select>
      </div>

      <div id="stockDisplay" style="display:none;margin-bottom:12px;padding:10px;background:#f6f9ff;border-radius:8px;font-size:12px">
        <div>Book stock: <strong id="bookStockVal" style="color:#012970;font-size:16px"></strong> <span id="stockUnit" style="color:#aaa"></span></div>
      </div>

      <div class="fld">
        <label class="flbl"><span class="km">ចំនួនរាប់ជាក់ស្ដែង</span><span class="en">/ Physical Count (new qty)</span><span class="req">*</span></label>
        <input type="number" name="new_qty" class="form-control" id="newQtyInput"
               value="{{ old('new_qty', 0) }}" min="0" required oninput="calcDelta()"/>
      </div>

      <div id="deltaDisplay" style="display:none;margin-bottom:12px;padding:10px;border-radius:8px;font-size:13px;text-align:center">
        <div style="font-weight:700;font-size:12px;color:#666;margin-bottom:4px">Adjustment Delta</div>
        <div id="deltaVal" style="font-size:22px;font-weight:800"></div>
      </div>

      <div class="fld">
        <label class="flbl"><span class="km">ហេតុផល</span><span class="en">/ Reason</span></label>
        <textarea name="note" class="form-control" rows="2"
                  placeholder="Physical inventory count, shrinkage, damage…">{{ old('note') }}</textarea>
      </div>

      <button type="submit" class="btn btn-warning btn-w100 mt-1">
        <i class="bi bi-check2-circle"></i> Confirm Adjustment
      </button>
    </form>
  </x-ui.card>
</div>

{{-- History --}}
<div class="col-12 col-lg-8">
  <x-ui.card :noPadding="true">
    <x-slot:header>
        <x-ui.card-header km="Adjustment History" icon="bi-clock-history">
            <form method="GET" class="d-flex gap-2" style="flex-shrink:0">
                <input type="text" name="search" class="form-control form-control-sm"
                       value="{{ request('search') }}" placeholder="Medicine…" style="width:160px"/>
                <input type="date" name="date" class="form-control form-control-sm"
                       value="{{ request('date') }}" style="width:140px"/>
                <button type="submit" class="btn btn-sm btn-outline-primary"><i class="bi bi-funnel"></i></button>
                @if(request()->hasAny(['search','date']))
                  <a href="{{ route('inventory.adjustment') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x"></i></a>
                @endif
            </form>
        </x-ui.card-header>
    </x-slot:header>
    <div class="table-responsive">
      <table class="tbl">
        <thead>
          <tr>
            <th>Date</th>
            <th>Medicine</th>
            <th style="text-align:center">Before</th>
            <th style="text-align:center">After</th>
            <th style="text-align:center">Delta</th>
            <th>Note</th>
            <th>By</th>
          </tr>
        </thead>
        <tbody>
        @forelse($movements as $mv)
        @php $delta = $mv->stock_after - $mv->stock_before; @endphp
        <tr>
          <td>
            <div style="font-size:12px;font-weight:600;color:#012970">{{ $mv->created_at->format('d/m/Y') }}</div>
            <div style="font-size:10px;color:#aaa">{{ $mv->created_at->format('H:i') }}</div>
          </td>
          <td>
            <div style="font-weight:600;font-size:12.5px">{{ $mv->medicine_name }}</div>
            <code style="font-size:10px;color:#4154f1">{{ $mv->medicine_code }}</code>
          </td>
          <td style="text-align:center;color:#aaa">{{ $mv->stock_before }}</td>
          <td style="text-align:center;font-weight:700;color:#012970">{{ $mv->stock_after }}</td>
          <td style="text-align:center;font-weight:800;font-size:15px;color:{{ $delta>=0?'#2eca6a':'#e74c3c' }}">
            {{ $delta >= 0 ? "+{$delta}" : "{$delta}" }}
          </td>
          <td style="font-size:11px;color:#666;max-width:200px">{{ Str::limit($mv->note, 60) }}</td>
          <td style="font-size:11px;color:#aaa">{{ $mv->recorded_by ?? '—' }}</td>
        </tr>
        @empty
        <tr><td colspan="7" style="text-align:center;padding:32px;color:#bbb">No adjustments yet</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </x-ui.card>
  @if($movements->hasPages())
    <x-ui.pagination :paginator="$movements" class="mt-3"/>
  @endif
</div>

</div>

@push('scripts')
<script>
function showCurrentStock(sel) {
    const opt = sel.options[sel.selectedIndex];
    const el  = document.getElementById('stockDisplay');
    if (!opt || !opt.value) { el.style.display='none'; return; }
    document.getElementById('bookStockVal').textContent = opt.dataset.stock;
    document.getElementById('stockUnit').textContent = opt.dataset.unit || '';
    el.style.display = 'block';
    calcDelta();
}
function calcDelta() {
    const medSel = document.getElementById('medSelect');
    const newQty = parseInt(document.getElementById('newQtyInput').value || 0);
    const opt    = medSel.options[medSel.selectedIndex];
    const dd     = document.getElementById('deltaDisplay');
    if (!opt || !opt.value) { dd.style.display='none'; return; }
    const book  = parseInt(opt.dataset.stock || 0);
    const delta = newQty - book;
    const dv    = document.getElementById('deltaVal');
    dv.textContent = (delta >= 0 ? '+' : '') + delta;
    dv.style.color = delta === 0 ? '#aaa' : (delta > 0 ? '#2eca6a' : '#e74c3c');
    dd.style.background = delta === 0 ? '#f5f5f5' : (delta > 0 ? '#e8f8ef' : '#fde8e8');
    dd.style.display = 'block';
}
document.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('medSelect');
    if (sel && sel.value) showCurrentStock(sel);
});
</script>
@endpush

@endsection
