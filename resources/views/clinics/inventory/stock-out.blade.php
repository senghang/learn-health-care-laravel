@extends('clinics.layout.app')
@section('title', 'Stock Out')
@section('content')

<x-page-header title="ស្តុកចេញ" subtitle="Stock Out"
    :breadcrumbs="[['label'=>'ដើម','url'=>url('/')],['label'=>'Inventory','url'=>route('inventory.products')],['label'=>'Stock Out']]">
</x-page-header>

<div class="row g-3">

{{-- ── Stock Out Form ─────────────────────────────────────────── --}}
<div class="col-12 col-lg-4">
  <div class="card-emr" style="position:sticky;top:76px">
    <div class="card-hd" style="background:#fde8e8">
      <div class="card-hd-title">
        <i class="bi bi-box-arrow-up-right" style="color:#e74c3c"></i>
        ចេញស្តុក <small style="font-weight:400;color:#aaa">/ Dispense / Remove</small>
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

      <form method="POST" action="{{ route('inventory.stock-out.store') }}" novalidate>
        @csrf
        <div class="fld">
          <label class="flbl"><span class="km">ថ្នាំ / ផលិតផល</span><span class="en">/ Product</span><span class="req">*</span></label>
          <select name="medicine_id" class="form-select" required id="medSelect" onchange="updateStock(this)">
            <option value="">— ជ្រើស —</option>
            @foreach($medicines as $med)
            @php $isLow = $med->stock <= 10 && $med->stock > 0; $isOut = $med->stock === 0; @endphp
            <option value="{{ $med->id }}"
                    data-stock="{{ $med->stock }}"
                    data-unit="{{ $med->unit }}"
                    style="{{ $isOut ? 'color:#e74c3c' : ($isLow ? 'color:#ff771d':'') }}"
                    {{ old('medicine_id') == $med->id ? 'selected' : '' }}>
              {{ $med->name }} — {{ $isOut ? '⚠ OUT' : "Stock: {$med->stock}" }}
            </option>
            @endforeach
          </select>
          <div id="stockInfo" style="font-size:11px;margin-top:5px"></div>
        </div>

        <div class="row g-2">
          <div class="col-6">
            <x-form.field name="quantity" km="បរិមាណ" en="Quantity" type="number"
                          :value="old('quantity',1)" placeholder="0" required/>
          </div>
          <div class="col-6">
            <x-form.select name="type" km="ហេតុផល" en="Reason"
                :options="['out'=>'Dispensed','expired'=>'Expired','adjustment'=>'Adjustment']"
                :value="old('type','out')"/>
          </div>
        </div>

        <x-form.field name="reference" km="យោង / Visit Code" en="Reference"
                      placeholder="V20260322001 or note" :value="old('reference')"/>
        <x-form.field name="note" km="ចំណាំ" en="Note"
                      :value="old('note')" textarea :rows="2"/>

        <button type="submit" class="btn btn-danger btn-w100 mt-1">
          <i class="bi bi-check2-circle"></i> រក្សាទុក / Save Stock Out
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
        ប្រវត្តិចេញស្តុក <small style="font-weight:400;color:#aaa">/ Dispense History</small>
      </div>
      <form method="GET" class="d-flex gap-2" style="flex-shrink:0">
        <input type="text" name="search" class="form-control form-control-sm"
               placeholder="Search…" value="{{ request('search') }}" style="width:140px"/>
        <input type="date" name="date" class="form-control form-control-sm"
               value="{{ request('date') }}" style="width:130px"/>
        <button type="submit" class="btn btn-sm btn-outline-primary"><i class="bi bi-funnel"></i></button>
        @if(request()->hasAny(['search','date']))
          <a href="{{ route('inventory.stock-out') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x"></i></a>
        @endif
      </form>
    </div>
    <div class="card-bd" style="padding:0">
      <div class="table-responsive">
        <table class="tbl">
          <thead>
            <tr>
              <th>ថ្ងៃ</th><th>ផលិតផល</th><th>ប្រភេទ</th>
              <th>បរិមាណ</th><th>ស្តុកមុន</th><th>ស្តុកក្រោយ</th><th>យោង</th><th>ដោយ</th>
            </tr>
          </thead>
          <tbody>
          @forelse($movements as $mv)
          @php
            $typeColors = ['out'=>'#e74c3c','expired'=>'#9b59b6','adjustment'=>'#ff771d'];
            $typeLabels = ['out'=>'Dispensed','expired'=>'Expired','adjustment'=>'Adjustment'];
            $col = $typeColors[$mv->type] ?? '#aaa';
          @endphp
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
              <span class="badge-s" style="background:{{ $col }}22;color:{{ $col }};font-size:10px;border:1px solid {{ $col }}44">
                {{ $typeLabels[$mv->type] ?? $mv->type }}
              </span>
            </td>
            <td style="font-weight:800;color:#e74c3c;font-size:15px">-{{ $mv->quantity }}</td>
            <td style="color:#aaa;font-size:12px">{{ $mv->stock_before }}</td>
            <td style="font-weight:700;color:{{ $mv->stock_after <= 0 ? '#e74c3c' : '#012970' }}">
              {{ $mv->stock_after }}
            </td>
            <td style="font-size:11px;color:#4154f1">{{ $mv->reference ?? '—' }}</td>
            <td style="font-size:11px;color:#aaa">{{ $mv->recorded_by ?? '—' }}</td>
          </tr>
          @empty
          <tr><td colspan="8" style="text-align:center;padding:28px;color:#bbb">
            <div style="font-size:28px;margin-bottom:8px;opacity:.3">📤</div>
            No stock-out records found
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

</div>

@push('scripts')
<script>
function updateStock(sel) {
    const opt = sel.options[sel.selectedIndex];
    const el  = document.getElementById('stockInfo');
    if (!opt || !opt.value) { el.textContent = ''; return; }
    const stock = parseInt(opt.dataset.stock);
    const unit  = opt.dataset.unit || '';
    const col   = stock === 0 ? '#e74c3c' : stock <= 10 ? '#ff771d' : '#2eca6a';
    el.innerHTML = `Available: <strong style="color:${col};font-size:14px">${stock}</strong> ${unit}`
        + (stock === 0 ? ' <span style="color:#e74c3c;font-size:11px">⚠ Out of stock</span>' : '');
}
document.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('medSelect');
    if (sel && sel.value) updateStock(sel);
});
</script>
@endpush

@endsection
