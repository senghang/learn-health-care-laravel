@extends('clinics.layout.app')
@section('title', ($bed ? 'Edit Bed' : 'Add Bed') . ' — ' . $ward->name)
@section('content')

<x-page-header :title="$bed ? 'Edit Bed' : 'គ្រែថ្មី'"
    :subtitle="$bed ? 'Edit Bed — '.$ward->name : 'Add Bed — '.$ward->name"
    :breadcrumbs="[
        ['label'=>'ដើម','url'=>route('dashboard')],
        ['label'=>'Wards','url'=>route('beds.index')],
        ['label'=>$ward->name,'url'=>route('beds.ward',$ward->id)],
        ['label'=>$bed ? 'Edit Bed' : 'Add Bed'],
    ]">
    <a href="{{ route('beds.ward', $ward->id) }}" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</x-page-header>

<div class="row g-3">
<div class="col-12 col-md-6 col-lg-5">
<div class="card-emr">
    <div class="card-hd">
        <div class="card-hd-title">
            <i class="bi bi-grid-fill" style="color:#4154f1"></i>
            {{ $bed ? 'Edit Bed' : 'Add Bed' }}
            <span style="font-size:11px;color:#6b7280;font-weight:400">— {{ $ward->name_kh ?? $ward->name }}</span>
        </div>
    </div>
    <div class="card-bd">
        <form method="POST"
              action="{{ $bed
                ? route('beds.bed.update', [$ward->id, $bed->id])
                : route('beds.bed.store', $ward->id) }}">
            @csrf
            @if($bed) @method('PATCH') @endif

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
                       value="{{ $bed?->code ?? 'Auto-generated on save' }}"
                       readonly
                       style="{{ !$bed ? 'color:#aaa;font-style:italic' : 'font-family:monospace;color:#4154f1;font-weight:700' }}"/>
            </div>

            {{-- Bed name / label --}}
            <x-form.field name="name" km="លេខ/ឈ្មោះគ្រែ" en="Bed Name / Label"
                          :required="true" placeholder="Bed 3A, Cot 2…"
                          :value="old('name', $bed?->name)"/>

            {{-- Room --}}
            <div class="fld">
                <label class="flbl">
                    <span class="km">បន្ទប់</span>
                    <span class="en">/ Room</span>
                    <span class="req">*</span>
                </label>
                <select name="room_id" class="form-select {{ $errors->has('room_id') ? 'is-invalid' : '' }}" required>
                    <option value="">— Select room —</option>
                    @foreach($rooms as $room)
                    <option value="{{ $room->id }}"
                        {{ old('room_id', $bed?->room_id ?? request('room')) == $room->id ? 'selected' : '' }}>
                        {{ $room->name }}
                        ({{ ucfirst($room->type) }}, Floor {{ $room->floor }})
                    </option>
                    @endforeach
                </select>
                @error('room_id')
                    <div class="field-error">{{ $message }}</div>
                @enderror
                @if($rooms->isEmpty())
                <div class="note note-warn" style="margin-top:8px;padding:8px 12px;font-size:12px">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    No rooms in this ward.
                    <a href="{{ route('beds.room.create', $ward->id) }}">Create a room first →</a>
                </div>
                @endif
            </div>

            {{-- Bed type --}}
            <x-form.select name="type" km="ប្រភេទគ្រែ" en="Bed Type"
                :options="[
                    'standard'    => 'Standard',
                    'icu'         => 'ICU',
                    'paediatric'  => 'Paediatric / Cot',
                    'bariatric'   => 'Bariatric',
                    'delivery'    => 'Delivery / Labour',
                    'observation' => 'Observation',
                ]"
                :value="old('type', $bed?->type ?? 'standard')"/>

            @if($bed)
            {{-- Status (edit only) --}}
            <x-form.select name="status" km="ស្ថានភាព" en="Status"
                :options="collect(\App\Models\BedModel::STATUSES)->mapWithKeys(fn($s) => [$s => ucfirst($s)])->all()"
                :value="old('status', $bed->status)"/>

            {{-- Active --}}
            <div class="fld">
                <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;font-weight:600;color:#374151">
                    <input type="checkbox" name="is_active" value="1"
                           {{ old('is_active', $bed->is_active) ? 'checked' : '' }}
                           style="width:16px;height:16px;accent-color:#4154f1">
                    Active
                </label>
            </div>
            @endif

            <div class="d-flex gap-2 pt-3" style="border-top:1px solid #e6e9f0">
                <button type="submit" class="btn btn-primary"
                        {{ $rooms->isEmpty() ? 'disabled' : '' }}>
                    <i class="bi bi-check2-circle"></i>
                    {{ $bed ? 'Update Bed' : 'Add Bed' }}
                </button>
                <a href="{{ route('beds.ward', $ward->id) }}" class="btn btn-outline-primary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
</div>

{{-- Live bed preview --}}
<div class="col-12 col-md-6 col-lg-4">
    <div class="card-emr">
        <div class="card-hd">
            <div class="card-hd-title">
                <i class="bi bi-eye-fill" style="color:#9b59b6"></i> Preview
            </div>
        </div>
        <div class="card-bd" style="text-align:center;padding:32px">
            <div id="bedPreview" style="display:inline-block;width:96px;border-radius:12px;border:2px solid #2eca6a;background:#e8f8ef;padding:14px 8px;text-align:center">
                <div id="previewIcon" style="font-size:28px;margin-bottom:6px">🛏</div>
                <div id="previewName" style="font-size:14px;font-weight:800;color:#2eca6a">New Bed</div>
                <div style="font-size:10px;color:#6b7280;margin-top:3px">available</div>
            </div>
            <div style="font-size:11px;color:#6b7280;margin-top:16px">
                Status colours update when you change status during edit.
            </div>
        </div>
    </div>
</div>
</div>

<script>
// Live preview — update label from name field
var nameField = document.querySelector('[name="name"]');
if (nameField) {
    nameField.addEventListener('input', function() {
        var el = document.getElementById('previewName');
        if (el) el.textContent = this.value || 'New Bed';
    });
}
</script>
@endsection
