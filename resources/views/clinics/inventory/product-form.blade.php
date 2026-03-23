@extends('clinics.layout.app')
@section('title', $medicine ? 'Edit Product' : 'New Product')
@section('content')

<x-page-header
    :title="$medicine ? 'កែប្រែផលិតផល' : 'ផលិតផលថ្មី'"
    :subtitle="$medicine ? 'Edit Product' : 'New Product'"
    :breadcrumbs="[
        ['label'=>'ដើម','url'=>url('/')],
        ['label'=>'Products','url'=>route('inventory.products')],
        ['label'=>$medicine ? 'Edit' : 'New'],
    ]">
    <a href="{{ route('inventory.products') }}" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</x-page-header>

@if($errors->any())
<div class="note note-danger mb-3">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <ul style="margin:0;padding-left:16px;font-size:12px">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST"
      action="{{ $medicine ? route('inventory.product.update', $medicine->id) : route('inventory.product.store') }}"
      novalidate>
@csrf
@if($medicine) @method('PATCH') @endif

<div class="row g-3">
<div class="col-12 col-lg-8">

  <div class="card-emr mb-3">
    <div class="card-hd"><div class="card-hd-title"><i class="bi bi-box-seam-fill" style="color:#4154f1"></i> ព័ត៌មានផលិតផល / Product Info</div></div>
    <div class="card-bd">
      <div class="row g-3">
        <div class="col-6 col-md-4">
          <x-form.field name="code" km="លេខ Code" en="Product Code"
                        :value="old('code',$medicine?->code)"
                        :readonly="(bool)$medicine" required/>
        </div>
        <div class="col-12 col-md-8">
          <x-form.field name="name" km="ឈ្មោះ" en="Name (English)" :value="old('name',$medicine?->name)" required/>
        </div>
        <div class="col-12 col-md-6">
          <x-form.field name="name_kh" km="ឈ្មោះខ្មែរ" en="Name (Khmer)" :value="old('name_kh',$medicine?->name_kh)"/>
        </div>
        <div class="col-12 col-md-6">
          <x-form.field name="generic_name" km="ឈ្មោះទូទៅ" en="Generic Name" :value="old('generic_name',$medicine?->generic_name)"/>
        </div>
        <div class="col-6 col-md-4">
          <div class="fld">
            <label class="flbl"><span class="km">ប្រភេទ</span><span class="en">/ Category</span></label>
            <input name="category" class="form-control" list="catlist"
                   value="{{ old('category',$medicine?->category) }}" placeholder="Antibiotic, Analgesic…"/>
            <datalist id="catlist">
              @foreach($categories as $cat)<option value="{{ $cat }}">@endforeach
            </datalist>
          </div>
        </div>
        <div class="col-6 col-md-4">
          <x-form.select name="form" km="ទំរង់" en="Form"
              :options="[''=> '—', 'Tablet'=>'Tablet','Capsule'=>'Capsule','Syrup'=>'Syrup','Injection'=>'Injection','Cream'=>'Cream','Drops'=>'Drops','Powder'=>'Powder','Other'=>'Other']"
              :value="old('form',$medicine?->form)"/>
        </div>
        <div class="col-6 col-md-4">
          <x-form.field name="strength" km="កម្លាំង" en="Strength" placeholder="500mg / 250mg/5ml"
                        :value="old('strength',$medicine?->strength)"/>
        </div>
        <div class="col-6 col-md-4">
          <x-form.field name="unit" km="ឯកតា" en="Unit" placeholder="Tablet, Vial, Bottle"
                        :value="old('unit',$medicine?->unit)"/>
        </div>
        <div class="col-6 col-md-4">
          <x-form.field name="price" km="តម្លៃ KHR" en="Unit Price" type="number" required
                        :value="old('price',$medicine?->price)" placeholder="0"/>
        </div>
        @if(!$medicine)
        <div class="col-6 col-md-4">
          <x-form.field name="stock" km="ស្តុកដើម" en="Initial Stock" type="number" required
                        :value="old('stock',0)" placeholder="0"/>
        </div>
        @endif
        <div class="col-6 col-md-4">
          <x-form.field name="stock_alert" km="ដែនកំណត់ទាប" en="Low Stock Alert" type="number" required
                        :value="old('stock_alert',$medicine?->stock_alert ?? 10)" placeholder="10"/>
        </div>
        @if($medicine)
        <div class="col-6 col-md-4">
          <div class="fld">
            <label class="flbl"><span class="km">ស្ថានភាព</span><span class="en">/ Status</span></label>
            <select name="is_active" class="form-select">
              <option value="1" {{ old('is_active',$medicine->is_active ? '1':'0') === '1' ? 'selected':'' }}>Active</option>
              <option value="0" {{ old('is_active',$medicine->is_active ? '1':'0') === '0' ? 'selected':'' }}>Inactive</option>
            </select>
          </div>
        </div>
        @endif
      </div>
    </div>
  </div>

</div>
<div class="col-12 col-lg-4">
  <div class="card-emr" style="position:sticky;top:76px">
    <div class="card-hd" style="background:#f6f9ff"><div class="card-hd-title"><i class="bi bi-save-fill" style="color:#4154f1"></i> រក្សាទុក</div></div>
    <div class="card-bd">
      <button type="submit" class="btn btn-primary btn-w100 mb-2">
        <i class="bi bi-check2-circle"></i> {{ $medicine ? 'Update Product' : 'Create Product' }}
      </button>
      <a href="{{ route('inventory.products') }}" class="btn btn-outline-primary btn-w100">Cancel</a>

      @if($medicine)
      <div style="margin-top:16px;padding-top:12px;border-top:1px solid #f0f2ff;font-size:11px;color:#aaa">
        <div><i class="bi bi-box-seam"></i> Stock: <strong style="color:{{ $medicine->stock===0?'#e74c3c':($medicine->isLowStock()?'#ff771d':'#2eca6a') }}">{{ $medicine->stock }} {{ $medicine->unit }}</strong></div>
        <div><i class="bi bi-calendar"></i> Created: {{ $medicine->created_at?->format('d/m/Y') }}</div>
        <div style="margin-top:8px">
          <a href="{{ route('inventory.stock-in') }}?medicine={{ $medicine->id }}" style="color:#2eca6a;font-size:11px">
            <i class="bi bi-plus-circle"></i> Add Stock
          </a>
        </div>
      </div>
      @endif
    </div>
  </div>
</div>
</div>
</form>
@endsection
