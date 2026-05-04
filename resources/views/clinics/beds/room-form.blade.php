@extends('clinics.layout.app')
@section('title', ($room ? 'Edit Room' : 'New Room') . ' — ' . $ward->name)
@section('content')

<x-page-header :title="$room ? 'Edit Room' : 'New Room'"
    :subtitle="$ward->name_kh ?? $ward->name"
    :breadcrumbs="[
        ['label'=>'ដើម','url'=>route('dashboard')],
        ['label'=>'Wards','url'=>route('beds.index')],
        ['label'=>$ward->name,'url'=>route('beds.ward',$ward->id)],
        ['label'=>$room ? 'Edit Room' : 'New Room'],
    ]">
    <a href="{{ route('beds.ward', $ward->id) }}" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</x-page-header>

<div class="row">
<div class="col-12 col-md-6">
<div class="card-emr">
    <div class="card-hd">
        <div class="card-hd-title">
            <i class="bi bi-door-open" style="color:#ff771d"></i>
            {{ $room ? 'Edit Room' : 'Add Room' }}
            <span style="font-size:11px;color:#6b7280;font-weight:400">— {{ $ward->name_kh ?? $ward->name }}</span>
        </div>
    </div>
    <div class="card-bd">
        <form method="POST" action="{{ $room ? route('beds.room.update', [$ward->id, $room->id]) : route('beds.room.store', $ward->id) }}">
            @csrf
            @if($room) @method('PATCH') @endif

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
                <label class="flbl"><span class="km">Code</span>
                    <span style="font-size:9px;background:#e8f8ef;color:#1D9E75;padding:1px 6px;border-radius:8px;margin-left:4px">AUTO</span>
                </label>
                <input class="form-control ro"
                       value="{{ $room?->code ?? 'Auto-generated on save' }}"
                       readonly style="{{ !$room ? 'color:#aaa;font-style:italic' : '' }}"/>
            </div>

            {{-- Name --}}
            <x-form.field name="name" km="ឈ្មោះបន្ទប់" en="Room Name" :required="true"
                          placeholder="Room 201, Theatre A…" :value="old('name', $room?->name)"/>

            <div class="row g-3">
                {{-- Type --}}
                <div class="col-6">
                    <x-form.select name="type" km="ប្រភេទ" en="Room Type" :required="true"
                        :options="[
                            'general'     => 'General',
                            'isolation'   => 'Isolation',
                            'icu'         => 'ICU',
                            'theatre'     => 'Theatre / OR',
                            'observation' => 'Observation',
                        ]"
                        :value="old('type', $room?->type ?? 'general')"/>
                </div>
                {{-- Floor --}}
                <div class="col-6">
                    <x-form.field name="floor" km="ជាន់" en="Floor" type="number" :required="true"
                                  placeholder="1" :value="old('floor', $room?->floor ?? 1)"/>
                </div>
            </div>

            @if($room)
            <div class="fld">
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;font-weight:600;color:#374151">
                    <input type="checkbox" name="is_active" value="1"
                           {{ old('is_active', $room->is_active) ? 'checked' : '' }}
                           style="width:16px;height:16px;accent-color:#4154f1">
                    Active
                </label>
            </div>
            @endif

            <div class="d-flex gap-2 pt-3" style="border-top:1px solid #e6e9f0">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check2-circle"></i>
                    {{ $room ? 'Update Room' : 'Create Room' }}
                </button>
                <a href="{{ route('beds.ward', $ward->id) }}" class="btn btn-outline-primary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
</div>
</div>

@endsection
