@extends('clinics.layout.app')
@section('title', $medicine ? 'Edit Product' : 'New Product')

@section('content')

<x-ui.page-header
    :km="$medicine ? 'កែប្រែផលិតផល' : 'ផលិតផលថ្មី'"
    :title="$medicine ? 'Edit Product' : 'New Product'"
    :breadcrumbs="[
        ['label' => __('app.home'), 'url' => route('dashboard')],
        ['label' => 'Inventory', 'url' => route('inventory.products')],
        ['label' => $medicine ? 'Edit' : 'New'],
    ]">
    <x-slot:actions>
        <x-ui.button href="{{ route('inventory.products') }}" variant="secondary" size="sm">
            <x-slot:icon><i class="bi bi-arrow-left" aria-hidden="true"></i></x-slot:icon>
            Back
        </x-ui.button>
    </x-slot:actions>
</x-ui.page-header>

@if($errors->any())
    <x-ui.alert type="error" class="mb-4">
        <ul class="list-disc pl-4 text-xs space-y-0.5">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </x-ui.alert>
@endif

<form method="POST"
      action="{{ $medicine ? route('inventory.product.update', $medicine->id) : route('inventory.product.store') }}"
      novalidate>
    @csrf
    @if($medicine) @method('PATCH') @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- ── Product Info ──────────────────────────────────────── --}}
        <div class="lg:col-span-2">
            <x-ui.card>
                <x-slot:header>
                    <div class="flex items-center gap-2 px-5 py-4" style="border-bottom:1px solid #e6e9f0">
                        <i class="bi bi-box-seam-fill" style="color:#4154f1;font-size:15px" aria-hidden="true"></i>
                        <span class="text-sm font-bold" style="color:#1a1f36">Product Info</span>
                        <span class="text-xs" style="color:#6b7280">/ ព័ត៌មានផលិតផល</span>
                    </div>
                </x-slot:header>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold" style="color:#374151">
                            លេខ Code <span style="color:#ef4444">*</span>
                        </label>
                        <x-forms.input name="code" :value="old('code', $medicine?->code)"
                                       required :readonly="(bool)$medicine" />
                        @error('code')
                            <p class="text-xs flex items-center gap-1" style="color:#ef4444">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="col-span-2 space-y-1.5">
                        <label class="block text-xs font-semibold" style="color:#374151">
                            ឈ្មោះ / Name (English) <span style="color:#ef4444">*</span>
                        </label>
                        <x-forms.input name="name" :value="old('name', $medicine?->name)" required />
                        @error('name')
                            <p class="text-xs flex items-center gap-1" style="color:#ef4444">
                                <i class="bi bi-exclamation-circle-fill"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="col-span-2 md:col-span-1 space-y-1.5">
                        <label class="block text-xs font-semibold" style="color:#374151">ឈ្មោះខ្មែរ / Name (Khmer)</label>
                        <x-forms.input name="name_kh" :value="old('name_kh', $medicine?->name_kh)" />
                    </div>

                    <div class="col-span-2 md:col-span-2 space-y-1.5">
                        <label class="block text-xs font-semibold" style="color:#374151">ឈ្មោះទូទៅ / Generic Name</label>
                        <x-forms.input name="generic_name" :value="old('generic_name', $medicine?->generic_name)" />
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold" style="color:#374151">ប្រភេទ / Category</label>
                        <input name="category" list="catlist"
                               value="{{ old('category', $medicine?->category) }}"
                               placeholder="Antibiotic, Analgesic…"
                               class="w-full text-sm rounded-lg border border-[#e2e8f0] bg-white px-3 py-2.5 text-[#374151] placeholder-[#9ca3af] focus:outline-none focus:border-[#4154f1] focus:ring-2 focus:ring-[#4154f1]/20 transition-colors" />
                        <datalist id="catlist">
                            @foreach($categories as $cat)<option value="{{ $cat }}">@endforeach
                        </datalist>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold" style="color:#374151">ទំរង់ / Form</label>
                        <x-forms.select name="form">
                            <option value="">—</option>
                            @foreach(['Tablet','Capsule','Syrup','Injection','Cream','Drops','Powder','Other'] as $f)
                                <option value="{{ $f }}" @selected(old('form', $medicine?->form) === $f)>{{ $f }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold" style="color:#374151">កម្លាំង / Strength</label>
                        <x-forms.input name="strength" placeholder="500mg / 250mg/5ml"
                                       :value="old('strength', $medicine?->strength)" />
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold" style="color:#374151">ឯកតា / Unit</label>
                        <x-forms.input name="unit" placeholder="Tablet, Vial, Bottle"
                                       :value="old('unit', $medicine?->unit)" />
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold" style="color:#374151">
                            តម្លៃ KHR / Unit Price <span style="color:#ef4444">*</span>
                        </label>
                        <x-forms.input type="number" name="price" required placeholder="0"
                                       :value="old('price', $medicine?->price)" />
                    </div>

                    @if(!$medicine)
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold" style="color:#374151">
                                ស្តុកដើម / Initial Stock <span style="color:#ef4444">*</span>
                            </label>
                            <x-forms.input type="number" name="stock" required placeholder="0"
                                           :value="old('stock', 0)" />
                        </div>
                    @endif

                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold" style="color:#374151">
                            ដែនកំណត់ / Low Stock Alert <span style="color:#ef4444">*</span>
                        </label>
                        <x-forms.input type="number" name="stock_alert" required placeholder="10"
                                       :value="old('stock_alert', $medicine?->stock_alert ?? 10)" />
                    </div>

                    @if($medicine)
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold" style="color:#374151">ស្ថានភាព / Status</label>
                            <x-forms.select name="is_active">
                                <option value="1" @selected(old('is_active', $medicine->is_active ? '1' : '0') === '1')>Active</option>
                                <option value="0" @selected(old('is_active', $medicine->is_active ? '1' : '0') === '0')>Inactive</option>
                            </x-forms.select>
                        </div>
                    @endif

                </div>
            </x-ui.card>
        </div>

        {{-- ── Sidebar: Save ─────────────────────────────────────── --}}
        <div>
            <x-ui.card class="sticky top-20">
                <x-slot:header>
                    <div class="flex items-center gap-2 px-5 py-4" style="border-bottom:1px solid #e6e9f0">
                        <i class="bi bi-save-fill" style="color:#4154f1;font-size:15px" aria-hidden="true"></i>
                        <span class="text-sm font-bold" style="color:#1a1f36">Save</span>
                    </div>
                </x-slot:header>

                <div class="space-y-2">
                    <x-ui.button type="submit" variant="primary" :fullWidth="true">
                        <x-slot:icon><i class="bi bi-check2-circle" aria-hidden="true"></i></x-slot:icon>
                        {{ $medicine ? 'Update Product' : 'Create Product' }}
                    </x-ui.button>
                    <x-ui.button href="{{ route('inventory.products') }}" variant="secondary" :fullWidth="true">
                        Cancel
                    </x-ui.button>
                </div>

                @if($medicine)
                    <div class="mt-4 pt-4 space-y-2" style="border-top:1px solid #e6e9f0">
                        @php
                            $isOut = $medicine->stock === 0;
                            $isLow = !$isOut && $medicine->isLowStock();
                            $sCol  = $isOut ? '#e74c3c' : ($isLow ? '#ff771d' : '#2eca6a');
                        @endphp
                        <div class="flex items-center justify-between text-xs">
                            <span style="color:#6b7280">Current Stock</span>
                            <strong style="color:{{ $sCol }}">{{ $medicine->stock }} {{ $medicine->unit }}</strong>
                        </div>
                        <div class="flex items-center justify-between text-xs">
                            <span style="color:#6b7280">Created</span>
                            <span style="color:#1a1f36">{{ $medicine->created_at?->format('d/m/Y') }}</span>
                        </div>
                        <a href="{{ route('inventory.stock-in') }}?medicine={{ $medicine->id }}"
                           class="flex items-center gap-1.5 text-xs font-semibold hover:underline" style="color:#2eca6a">
                            <i class="bi bi-plus-circle" aria-hidden="true"></i> Add Stock
                        </a>
                    </div>
                @endif
            </x-ui.card>
        </div>

    </div>{{-- /grid --}}
</form>

@endsection
