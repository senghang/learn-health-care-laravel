@extends('clinics.layout.app')
@section('title', __('app.settings.medicines'))
@section('content')

<x-ui.page-header
    :km="__('app.settings.medicines')"
    title="Medicine Master"
    :breadcrumbs="[
        ['label'=>'ដើម','url'=>route('dashboard')],
        ['label'=>__('app.settings.title'),'url'=>route('settings.general')],
        ['label'=>__('app.settings.medicines')],
    ]">
</x-ui.page-header>

@include('clinics.settings._subnav', ['active' => 'settings.medicines'])

<div class="row g-3">
    {{-- Add form --}}
    <div class="col-12 col-lg-4">
        <x-ui.card style="position:sticky;top:76px">
            <x-slot:header>
                <x-ui.card-header label="Add Medicine" icon="bi-plus-circle-fill"/>
            </x-slot:header>
            <form method="POST" action="{{ route('settings.medicine.store') }}">
                @csrf
                <div class="row g-2">
                    <div class="col-6">
                        <div class="fld">
                            <label class="flbl">
                                <span>Code</span>
                                <span style="font-size:9px;background:#e8f8ef;color:#1D9E75;padding:1px 6px;border-radius:8px;margin-left:4px">AUTO</span>
                            </label>
                            <input class="form-control ro" value="Auto-generated on save" readonly
                                   style="color:#aaa;font-style:italic"/>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="fld">
                            <label class="flbl"><span>Form</span></label>
                            <select name="form" class="form-select">
                                <option value="">—</option>
                                @foreach(['Tablet','Capsule','Syrup','Injection','Cream','Drops','Inhaler'] as $f)
                                <option value="{{ $f }}" {{ old('form') === $f ? 'selected' : '' }}>{{ $f }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="fld">
                    <label class="flbl"><span>Name</span><span class="req">*</span></label>
                    <input name="name" class="form-control" value="{{ old('name') }}" placeholder="Amoxicillin"/>
                </div>
                <div class="fld">
                    <label class="flbl"><span>ឈ្មោះ (KH)</span></label>
                    <input name="name_kh" class="form-control" value="{{ old('name_kh') }}"/>
                </div>
                <div class="fld">
                    <label class="flbl"><span>Generic Name</span></label>
                    <input name="generic_name" class="form-control" value="{{ old('generic_name') }}"/>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <div class="fld">
                            <label class="flbl"><span>Strength</span></label>
                            <input name="strength" class="form-control" value="{{ old('strength') }}" placeholder="500mg"/>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="fld">
                            <label class="flbl"><span>Unit</span></label>
                            <input name="unit" class="form-control" value="{{ old('unit') }}" placeholder="tablet"/>
                        </div>
                    </div>
                </div>
                <div class="row g-2">
                    <div class="col-4">
                        <div class="fld">
                            <label class="flbl"><span>Price</span><span class="req">*</span></label>
                            <input name="price" type="number" class="form-control" value="{{ old('price', 0) }}" min="0"/>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="fld">
                            <label class="flbl"><span>Stock</span><span class="req">*</span></label>
                            <input name="stock" type="number" class="form-control" value="{{ old('stock', 0) }}" min="0"/>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="fld">
                            <label class="flbl"><span>Alert ≤</span></label>
                            <input name="stock_alert" type="number" class="form-control" value="{{ old('stock_alert', 10) }}" min="0"/>
                        </div>
                    </div>
                </div>
                <x-ui.button type="submit" variant="primary" :fullWidth="true">
                    <x-slot:icon><i class="bi bi-plus-circle"></i></x-slot:icon>
                    {{ __('app.add') }}
                </x-ui.button>
            </form>
        </x-ui.card>
    </div>

    {{-- List --}}
    <div class="col-12 col-lg-8">
        <x-ui.card>
            <x-slot:header>
                <x-ui.card-header :label="__('app.settings.medicines')" icon="bi-capsule-fill" :count="$medicines->total()"/>
            </x-slot:header>

            <x-ui.table>
                <x-slot:head>
                    <tr>
                        <x-ui.table-th>Code</x-ui.table-th>
                        <x-ui.table-th>Name</x-ui.table-th>
                        <x-ui.table-th>Form</x-ui.table-th>
                        <x-ui.table-th>Strength</x-ui.table-th>
                        <x-ui.table-th align="right">Price</x-ui.table-th>
                        <x-ui.table-th align="right">Stock</x-ui.table-th>
                        <x-ui.table-th></x-ui.table-th>
                    </tr>
                </x-slot:head>
                <x-slot:body>
                @forelse($medicines as $med)
                @php $lowStock = $med->stock <= $med->stock_alert; @endphp
                <tr class="{{ $lowStock ? 'bg-[#fff8ee]' : 'hover:bg-[#f9fafb]' }} transition-colors">
                    <x-ui.table-td>
                        <code class="text-xs font-mono" style="color:#e91e8c">{{ $med->code }}</code>
                    </x-ui.table-td>
                    <x-ui.table-td>
                        <div class="font-semibold text-[#1a1f36] text-sm">{{ $med->name }}</div>
                        @if($med->name_kh)<div class="text-[10px] text-[#888]">{{ $med->name_kh }}</div>@endif
                        @if($med->generic_name)<div class="text-[10px] text-[#6b7280]">{{ $med->generic_name }}</div>@endif
                    </x-ui.table-td>
                    <x-ui.table-td>
                        @if($med->form)
                            <span class="text-[10px] px-1.5 py-0.5 rounded-lg" style="background:#fbeaf0;color:#D4537E">{{ $med->form }}</span>
                        @endif
                    </x-ui.table-td>
                    <x-ui.table-td>
                        <span class="text-[11px] text-[#888]">{{ $med->strength }}</span>
                    </x-ui.table-td>
                    <x-ui.table-td align="right">
                        <span class="text-xs">{{ khr_fmt($med->price) }}</span>
                    </x-ui.table-td>
                    <x-ui.table-td align="right">
                        <span class="font-bold text-sm" style="color:{{ $lowStock ? '#e74c3c' : '#2eca6a' }}">{{ $med->stock }}</span>
                        @if($lowStock)
                            <x-ui.badge variant="danger" size="sm" class="ml-1">LOW</x-ui.badge>
                        @endif
                    </x-ui.table-td>
                    <x-ui.table-td>
                        <form method="POST" action="{{ route('settings.medicine.update', $med->id) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="name"         value="{{ $med->name }}">
                            <input type="hidden" name="generic_name" value="{{ $med->generic_name }}">
                            <input type="hidden" name="form"         value="{{ $med->form }}">
                            <input type="hidden" name="strength"     value="{{ $med->strength }}">
                            <input type="hidden" name="price"        value="{{ $med->price }}">
                            <input type="hidden" name="stock"        value="{{ $med->stock }}">
                            <input type="hidden" name="stock_alert"  value="{{ $med->stock_alert }}">
                            <input type="hidden" name="is_active"    value="{{ $med->is_active ? '0' : '1' }}">
                            <button type="submit" class="w-7 h-7 flex items-center justify-center rounded-md text-[#64748b] hover:bg-[#f1f5f9] transition-colors text-xs"
                                    title="{{ $med->is_active ? 'Deactivate' : 'Activate' }}">
                                <i class="bi bi-{{ $med->is_active ? 'pause-fill' : 'play-fill' }}"></i>
                            </button>
                        </form>
                    </x-ui.table-td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <x-ui.empty-state icon="bi-capsule" title="No medicines yet" description="Add your first medicine to the formulary." compact/>
                    </td>
                </tr>
                @endforelse
                </x-slot:body>
            </x-ui.table>

            <x-ui.pagination :paginator="$medicines" class="mt-4 px-4 pb-4"/>
        </x-ui.card>
    </div>
</div>

@endsection
