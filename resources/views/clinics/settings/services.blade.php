@extends('clinics.layout.app')
@section('title', __('app.settings.services'))
@section('content')

<x-ui.page-header
    :km="__('app.settings.services')"
    title="Service Master"
    :breadcrumbs="[
        ['label'=>'ដើម','url'=>route('dashboard')],
        ['label'=>__('app.settings.title'),'url'=>route('settings.general')],
        ['label'=>__('app.settings.services')],
    ]">
</x-ui.page-header>

@include('clinics.settings._subnav', ['active' => 'settings.services'])

<div class="row g-3">
    {{-- Add form --}}
    <div class="col-12 col-lg-4">
        <x-ui.card style="position:sticky;top:76px">
            <x-slot:header>
                <x-ui.card-header label="Add Service" icon="bi-plus-circle-fill"/>
            </x-slot:header>
            <form method="POST" action="{{ route('settings.service.store') }}">
                @csrf
                <div class="fld">
                    <label class="flbl">
                        <span class="km">Code</span>
                        <span style="font-size:9px;background:#e8f8ef;color:#1D9E75;padding:1px 6px;border-radius:8px;margin-left:4px">AUTO</span>
                    </label>
                    <input class="form-control ro" value="Auto-generated on save" readonly
                           style="color:#aaa;font-style:italic"/>
                </div>
                <div class="fld">
                    <label class="flbl"><span class="km">{{ __('app.name') }}</span><span class="req">*</span></label>
                    <input name="name" class="form-control" value="{{ old('name') }}" placeholder="OPD Consultation"/>
                </div>
                <div class="fld">
                    <label class="flbl"><span class="km">ឈ្មោះ (KH)</span></label>
                    <input name="name_kh" class="form-control" value="{{ old('name_kh') }}" placeholder="ការពិនិត្យ OPD"/>
                </div>
                <div class="fld">
                    <label class="flbl"><span class="km">ប្រភេទ</span><span class="en">/ Category</span></label>
                    <select name="category" class="form-select">
                        <option value="">— Select —</option>
                        @foreach(['Consultation','Laboratory','Imaging','Procedure','Pharmacy','Other'] as $cat)
                        <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="fld">
                    <label class="flbl"><span class="km">{{ __('app.billing.grand_total') }} (KHR)</span><span class="req">*</span></label>
                    <input name="price" type="number" class="form-control" value="{{ old('price', 0) }}" min="0"/>
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
                <x-ui.card-header :label="__('app.settings.services')" icon="bi-list-check" :count="$services->total()"/>
            </x-slot:header>

            <x-ui.table>
                <x-slot:head>
                    <tr>
                        <x-ui.table-th>Code</x-ui.table-th>
                        <x-ui.table-th>Name</x-ui.table-th>
                        <x-ui.table-th>KH Name</x-ui.table-th>
                        <x-ui.table-th>Category</x-ui.table-th>
                        <x-ui.table-th align="right">Price (KHR)</x-ui.table-th>
                        <x-ui.table-th>Status</x-ui.table-th>
                        <x-ui.table-th></x-ui.table-th>
                    </tr>
                </x-slot:head>
                <x-slot:body>
                @forelse($services as $svc)
                <tr class="hover:bg-[#fafbff] transition-colors">
                    <x-ui.table-td>
                        <code class="text-[#4154f1] text-[11px]">{{ $svc->code }}</code>
                    </x-ui.table-td>
                    <x-ui.table-td>
                        <span class="font-semibold text-[#012970] text-sm">{{ $svc->name }}</span>
                    </x-ui.table-td>
                    <x-ui.table-td>
                        <span class="text-sm text-[#888]">{{ $svc->name_kh ?? '—' }}</span>
                    </x-ui.table-td>
                    <x-ui.table-td>
                        @if($svc->category)
                            <x-ui.badge variant="primary" size="sm">{{ $svc->category }}</x-ui.badge>
                        @endif
                    </x-ui.table-td>
                    <x-ui.table-td align="right">
                        <span class="font-bold text-sm">{{ khr_fmt($svc->price) }}</span>
                    </x-ui.table-td>
                    <x-ui.table-td>
                        <x-ui.badge :variant="$svc->is_active ? 'success' : 'danger'" size="sm">
                            {{ $svc->is_active ? 'Active' : 'Inactive' }}
                        </x-ui.badge>
                    </x-ui.table-td>
                    <x-ui.table-td>
                        <form method="POST" action="{{ route('settings.service.update', $svc->id) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="name"      value="{{ $svc->name }}">
                            <input type="hidden" name="price"     value="{{ $svc->price }}">
                            <input type="hidden" name="is_active" value="{{ $svc->is_active ? '0' : '1' }}">
                            <button type="submit" class="w-7 h-7 flex items-center justify-center rounded-md text-[#64748b] hover:bg-[#f1f5f9] transition-colors text-xs"
                                    title="{{ $svc->is_active ? 'Deactivate' : 'Activate' }}">
                                <i class="bi bi-{{ $svc->is_active ? 'pause-fill' : 'play-fill' }}"></i>
                            </button>
                        </form>
                    </x-ui.table-td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <x-ui.empty-state icon="bi-list-check" title="No services yet" description="Add your first billable service." compact/>
                    </td>
                </tr>
                @endforelse
                </x-slot:body>
            </x-ui.table>

            <x-ui.pagination :paginator="$services" class="mt-4 px-4 pb-4"/>
        </x-ui.card>
    </div>
</div>

@endsection
