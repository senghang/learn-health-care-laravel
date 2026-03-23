@extends('clinics.layout.app')
@section('title', 'Stock In')
@section('content')

<x-page-header title="ស្តុកចូល" subtitle="Stock In"
    :breadcrumbs="[['label'=>'ដើម','url'=>url('/')],['label'=>'Inventory','url'=>route('inventory.products')],['label'=>'Stock In']]">
</x-page-header>

<div class="row g-3">

{{-- ── Stock In Form ──────────────────────────────────────────── --}}
<div class="col-12 col-lg-4">
  <div class="card-emr" style="position:sticky;top:76px">
    <div class="card-hd" style="background:#e8f8ef">
      <div class="card-hd-title">
        <i class="bi bi-box-arrow-in-down-right" style="color:#2eca6a"></i>
        ចូលស្តុក <small style="font-weight:400;color:#aaa">/ Receive Stock</small>
      </div>
    </div>
    <div class="card-bd">

      @if($errors->any())
      <div class="note note-danger mb-3">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <ul style="margin:0;padding-left:14px;font-size:12px">
          @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
      </div>
      @endif

      <form method="POST" action="{{ route('inventory.stock-in.store') }}" novalidate>
        @csrf
        <div class="fld">
          <label class="flbl"><span class="km">ថ្នាំ / ផលិតផល</span><span class="en">/ Product</span><span class="req">*</span></label>
          <select name="medicine_id" class="form-select" required id="medSelect" onchange="updateStock(this)">
            <option value="">— ជ្រើស —</option>
            @foreach($medicines as $med)
            <option value="{{ $med->id }}"
                    data-stock="{{ $med->stock }}"
                    data-unit="{{ $med->unit }}"
                    {{ (request('medicine') == $med->id || old('medicine_id') == $med->id) ? 'selected' : '' }}>
              {{ $med->name }} ({{ $med->code }}) — Stock: {{ $med->stock }}
            </option>
            @endforeach
          </select>
          <div id="currentStock" style="font-size:11px;color:#aaa;margin-top:4px"></div>
        </div>

        <div class="row g-2">
          <div class="col-6">
            <x-form.field name="quantity" km="បរិមាណ" en="Quantity" type="number"
                          :value="old('quantity',1)" placeholder="0" required/>
          </div>
          <div class="col-6">
            <x-form.select name="type" km="ប្រភេទ" en="Type"
                :options="['in'=>'Stock In','return'=>'Return']"
                :value="old('type','in')"/>
          </div>
        </div>

        <x-form.field name="reference" km="លេខយោង" en="PO / Reference"
                      placeholder="PO-2026-001" :value="old('reference')"/>
        <x-form.field name="supplier" km="អ្នកផ្គត់ផ្គង់" en="Supplier"
                      :value="old('supplier')"/>

        <div class="row g-2">
          <div class="col-6">
            <x-form.field name="unit_cost" km="ថ្លៃ/ឯកតា" en="Unit Cost KHR"
                          type="number" :value="old('unit_cost')" placeholder="0"/>
          </div>
          <div class="col-6">
            <x-form.field name="expiry_date" km="ថ្ងៃផុត" en="Expiry Date"
                          type="date" :value="old('expiry_date')"/>
          </div>
        </div>

        <x-form.field name="batch_no" km="លេខ Batch" en="Batch No."
                      :value="old('batch_no')"/>
        <x-form.field name="note" km="ចំណាំ" en="Note"
                      :value="old('note')" textarea :rows="2"/>

        <button type="submit" class="btn btn-success btn-w100 mt-1">
          <i class="bi bi-check2-circle"></i> រក្សាទុក / Save Stock In
        </button>
      </form>

    </div>
  </div>
</div>

{{-- ── Movement History ───────────────────────────────────────── --}}
<div class="col-12 col-lg-8">
  <div class="card-emr">
    <div class="card-hd">
      <div class="card-hd-title">
        <i class="bi bi-clock-history"></i>
        ប្រវត្តិចូលស្តុក <small style="font-weight:400;color:#aaa">/ Receive History</small>
      </div>
      <form method="GET" class="d-flex gap-2" style="flex-shrink:0">
        <input type="text" name="search" class="form-control form-control-sm"
               placeholder="Search…" value="{{ request('search') }}" style="width:140px"/>
        <input type="date" name="date" class="form-control form-control-sm"
               value="{{ request('date') }}" style="width:140px"/>
        <button type="submit" class="btn btn-sm btn-outline-primary"><i class="bi bi-funnel"></i></button>
        @if(request()->hasAny(['search','date']))
          <a href="{{ route('inventory.stock-in') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x"></i></a>
        @endif
      </form>
    </div>
    <div class="card-bd" style="padding:0">
      <div class="table-responsive">
        <table class="tbl">
          <thead>
            <tr>
              <th>ថ្ងៃ / Date</th>
              <th>ផលិតផល / Product</th>
              <th>ប្រភេទ</th>
              <th>បរិមាណ</th>
              <th>ស្តុកមុន</th>
              <th>ស្តុកក្រោយ</th>
              <th>យោង / Ref</th>
              <th>ដោយ</th>
            </tr>
          </thead>
          <tbody>
          @forelse($movements as $mv)
          <tr>
            <td>
              <div style="font-size:12px;font-weight:600;color:#012970">{{ $mv->created_at->format('d/m/Y') }}</div>
              <div style="font-size:10px;color:#aaa">{{ $mv->created_at->format('H:i') }}</div>
            </td>
            <td>
              <div style="font-weight:600;font-size:12.5px">{{ $mv->medicine_name }}</div>
              <code style="font-size:10px;color:#4154f1">{{ $mv->medicine_code }}</code>
            </td>
            <td>
              <span class="badge-s" style="background:#e8f8ef;color:#2eca6a;font-size:10px">
                <i class="bi bi-box-arrow-in-down-right" style="font-size:9px"></i>
                {{ $mv->type === 'return' ? 'Return' : 'Stock In' }}
              </span>
            </td>
            <td style="font-weight:800;color:#2eca6a;font-size:15px">+{{ $mv->quantity }}</td>
            <td style="color:#aaa;font-size:12px">{{ $mv->stock_before }}</td>
            <td style="font-weight:700;color:#012970">{{ $mv->stock_after }}</td>
            <td style="font-size:11px">
              @if($mv->reference)<div style="color:#4154f1">{{ $mv->reference }}</div>@endif
              @if($mv->supplier)<div style="color:#aaa">{{ $mv->supplier }}</div>@endif
            </td>
            <td style="font-size:11px;color:#aaa">{{ $mv->recorded_by ?? '—' }}</td>
          </tr>
          @empty
          <tr><td colspan="8" style="text-align:center;padding:28px;color:#bbb">
            <div style="font-size:28px;margin-bottom:8px;opacity:.3">📦</div>
            No stock-in records found
          </td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
  @if($movements->hasPages())
  <div class="mt-3">{{ $movements->links() }}</div>
  @endif
</div>

</div>{{-- /row --}}

@push('scripts')
<script>
function updateStock(sel) {
    const opt = sel.options[sel.selectedIndex];
    const el  = document.getElementById('currentStock');
    if (!opt || !opt.value) { el.textContent = ''; return; }
    const stock = opt.dataset.stock;
    const unit  = opt.dataset.unit || '';
    el.innerHTML = `Current stock: <strong style="color:#2eca6a">${stock} ${unit}</strong>`;
}
document.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('medSelect');
    if (sel && sel.value) updateStock(sel);
});
</script>
@endpush

@endsection
