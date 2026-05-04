@extends('clinics.layout.app')
@section('title', $ward ? 'Edit Ward' : 'New Ward')
@section('content')

<x-page-header :title="$ward ? 'Edit Ward' : 'ផ្នែកថ្មី'"
    :subtitle="$ward ? 'Edit Ward' : 'New Ward'"
    :breadcrumbs="[
        ['label'=>'ដើម','url'=>route('dashboard')],
        ['label'=>'Wards','url'=>route('beds.index')],
        ['label'=>$ward ? 'Edit' : 'New'],
    ]">
    <a href="{{ route('beds.index') }}" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</x-page-header>

<div class="row g-3">
<div class="col-12 col-md-7 col-lg-5">
<div class="card-emr">
    <div class="card-hd">
        <div class="card-hd-title">
            <i class="bi bi-building-fill" style="color:#BA7517"></i>
            {{ $ward ? 'Edit Ward' : 'New Ward' }}
        </div>
    </div>
    <div class="card-bd">
        <form method="POST"
              action="{{ $ward ? route('beds.ward.update', $ward->id) : route('beds.ward.store') }}">
            @csrf
            @if($ward) @method('PATCH') @endif

            @if($errors->any())
            <div class="note note-danger mb-3">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <ul style="margin:0;padding-left:16px">
                    @foreach($errors->all() as $e)<li style="font-size:12px">{{ $e }}</li>@endforeach
                </ul>
            </div>
            @endif

            {{-- Code --}}
            <div class="fld">
                <label class="flbl">
                    <span class="km">Code</span>
                    <span style="font-size:9px;background:#e8f8ef;color:#1D9E75;padding:1px 6px;border-radius:8px;margin-left:4px">AUTO</span>
                </label>
                <input class="form-control ro"
                       value="{{ $ward?->code ?? 'Auto-generated on save' }}"
                       readonly
                       style="{{ !$ward ? 'color:#aaa;font-style:italic' : 'font-family:monospace;color:#4154f1;font-weight:700' }}"/>
            </div>

            {{-- Name (EN) --}}
            <x-form.field name="name" km="ឈ្មោះ (EN)" en="Name" :required="true"
                          placeholder="Paediatrics Ward…"
                          :value="old('name', $ward?->name)"/>

            <div class="row g-3">
                <div class="col-6">
                    <x-form.field name="name_kh" km="ឈ្មោះខ្មែរ" en="Khmer Name"
                                  placeholder="សេវាកុមារ…"
                                  :value="old('name_kh', $ward?->name_kh)"/>
                </div>
                <div class="col-6">
                    <x-form.field name="name_en" km="ឈ្មោះអង់គ្លេស" en="English Name"
                                  placeholder="Paediatrics…"
                                  :value="old('name_en', $ward?->name_en)"/>
                </div>
            </div>

            <div class="row g-3">
                {{-- Type --}}
                <div class="col-6">
                    <x-form.select name="type" km="ប្រភេទ" en="Ward Type" :required="true"
                        :options="[
                            'IPD'        => 'IPD — Inpatient',
                            'OPD'        => 'OPD — Outpatient',
                            'ICU'        => 'ICU',
                            'Emergency'  => 'Emergency',
                            'Theatre'    => 'Theatre / OR',
                            'Outpatient' => 'Outpatient Clinic',
                        ]"
                        :value="old('type', $ward?->type ?? 'IPD')"/>
                </div>
                {{-- Capacity --}}
                <div class="col-6">
                    <x-form.field name="capacity" km="ចំណុះ" en="Capacity (beds)"
                                  type="number" :required="true" placeholder="20"
                                  :value="old('capacity', $ward?->capacity ?? 0)"/>
                </div>
            </div>

            @if($ward)
            <div class="fld">
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;font-weight:600;color:#374151">
                    <input type="checkbox" name="is_active" value="1"
                           {{ old('is_active', $ward->is_active) ? 'checked' : '' }}
                           style="width:16px;height:16px;accent-color:#4154f1">
                    Active
                </label>
            </div>
            @endif

            <div class="d-flex gap-2 pt-3" style="border-top:1px solid #e6e9f0">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check2-circle"></i>
                    {{ $ward ? 'Update Ward' : 'Create Ward' }}
                </button>
                <a href="{{ route('beds.index') }}" class="btn btn-outline-primary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</div>

@if($ward)
{{-- Quick-add room from edit page --}}
<div class="col-12 col-md-5 col-lg-4">
    <div class="card-emr">
        <div class="card-hd">
            <div class="card-hd-title">
                <i class="bi bi-door-open" style="color:#ff771d"></i>
                Rooms in this ward
            </div>
            <a href="{{ route('beds.room.create', $ward->id) }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-plus"></i> Add Room
            </a>
        </div>
        <div class="card-bd" style="padding:0">
            @forelse($ward->rooms ?? [] as $room)
            <div style="display:flex;align-items:center;gap:10px;padding:10px 16px;border-bottom:1px solid #f5f6ff">
                <i class="bi bi-door-open" style="color:#64748b;font-size:14px"></i>
                <div style="flex:1">
                    <div style="font-size:13px;font-weight:600;color:#374151">{{ $room->name }}</div>
                    <div style="font-size:10.5px;color:#6b7280">{{ ucfirst($room->type) }} · Floor {{ $room->floor }} · {{ $room->beds_count ?? 0 }} beds</div>
                </div>
                <div style="display:flex;gap:4px">
                    <a href="{{ route('beds.room.edit', [$ward->id, $room->id]) }}"
                       class="btn btn-sm btn-outline-secondary" style="padding:3px 8px">
                        <i class="bi bi-pencil" style="font-size:11px"></i>
                    </a>
                </div>
            </div>
            @empty
            <div style="text-align:center;padding:24px;color:#cbd5e1;font-size:12px">
                No rooms yet.<br>
                <a href="{{ route('beds.room.create', $ward->id) }}" style="color:#4154f1">Add first room →</a>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endif

</div>
@endsection
