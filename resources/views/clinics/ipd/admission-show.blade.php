@extends('clinics.layout.app')
@section('title', 'IPD — ' . $admission->code)
@section('content')

@php
    $isAdmitted = $admission->isAdmitted();
    $los = $admission->length_of_stay ?? 0;
    $patient = $admission->patient;
    $statusColors = [
        'admitted'    => ['#e8f8ef','#2eca6a'],
        'discharged'  => ['#e6e9f0','#4154f1'],
        'transferred' => ['#fff3e8','#ff771d'],
        'deceased'    => ['#f5f5f5','#666'],
        'cancelled'   => ['#fde8e8','#e74c3c'],
    ];
    [$sbg, $scol] = $statusColors[$admission->status] ?? ['#f5f5f5','#888'];
@endphp

<x-page-header title="{{ $admission->code }}" subtitle="IPD Admission Detail"
    :breadcrumbs="[
        ['label'=>'ដើម','url'=>route('dashboard')],
        ['label'=>'Admissions','url'=>route('admissions.index')],
        ['label'=>$admission->code],
    ]">
    @if($isAdmitted)
    <button type="button" class="btn btn-outline-warning btn-sm" onclick="openModal('transferBedModal')">
        <i class="bi bi-arrow-left-right"></i> Transfer Bed
    </button>
    <button type="button" class="btn btn-danger btn-sm" onclick="openModal('dischargeModal')">
        <i class="bi bi-box-arrow-right"></i> Discharge
    </button>
    @endif
</x-page-header>

<div class="flow-panel mb-3">
    <div class="flow-panel-title">
        <i class="bi bi-diagram-3-fill me-1"></i> Inpatient Care Flow
    </div>
    <div class="flow-steps">
        <span class="flow-step">Patient admitted</span>
        <span class="flow-step">Bed assigned</span>
        <span class="flow-step">Treatment plan</span>
        <span class="flow-step">Medication orders</span>
        <span class="flow-step">Discharge summary</span>
    </div>
</div>

@if(session('flash'))
<div class="note note-success mb-3"><i class="bi bi-check-circle-fill"></i> {{ session('flash') }}</div>
@endif
@if(session('flash_error'))
<div class="note note-danger mb-3"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('flash_error') }}</div>
@endif
@if($errors->any())
<div class="note note-danger mb-3">
    <i class="bi bi-exclamation-triangle-fill"></i>
    @foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach
</div>
@endif

@if($isAdmitted)
<div class="note note-info mb-3">
    <i class="bi bi-lightning-charge-fill"></i>
    Quick actions: add treatment and medication as orders come in, then transfer bed or discharge when ready.
</div>
@endif

<div class="row g-3">

{{-- ── Left sidebar ─────────────────────────────────────────────────────────── --}}
<div class="col-12 col-lg-4">

    {{-- Patient info --}}
    <div class="card-emr mb-3">
        <div class="card-hd">
            <div class="card-hd-title"><i class="bi bi-person-fill" style="color:#4154f1"></i> Patient</div>
            <a href="{{ route('patients.show', $patient->code) }}" class="btn btn-sm btn-outline-primary" title="View patient">
                <i class="bi bi-box-arrow-up-right"></i>
            </a>
        </div>
        <div class="card-bd">
            {{-- Avatar --}}
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px">
                <div style="width:46px;height:46px;border-radius:50%;background:#eef0fd;color:#4154f1;display:flex;align-items:center;justify-content:center;font-size:17px;font-weight:800;flex-shrink:0">
                    {{ strtoupper(mb_substr($patient->surname ?? '?', 0, 1)) }}
                </div>
                <div>
                    <div style="font-weight:800;color:#1a1f36;font-size:14px">{{ $patient->surname }} {{ $patient->name }}</div>
                    <div style="font-size:11px;color:#aaa">{{ $patient->code }}</div>
                </div>
            </div>
            <div class="row g-1" style="font-size:12px">
                <div class="col-5" style="color:#888">Sex / Age</div>
                <div class="col-7" style="font-weight:600;color:#333">
                    {{ $patient->sex ?? '—' }} / {{ $patient->age ?? '—' }} yrs
                </div>
                @if($patient->blood_type)
                <div class="col-5" style="color:#888">Blood Type</div>
                <div class="col-7">
                    <span style="font-weight:800;color:#e74c3c;font-size:13px">{{ $patient->blood_type }}</span>
                </div>
                @endif
                @if($patient->phone)
                <div class="col-5" style="color:#888">Phone</div>
                <div class="col-7" style="font-weight:600">{{ $patient->phone }}</div>
                @endif
                @if($patient->emergency_contact_name)
                <div class="col-5" style="color:#888">Emergency</div>
                <div class="col-7">
                    <div style="font-weight:600">{{ $patient->emergency_contact_name }}</div>
                    @if($patient->emergency_contact_phone)
                    <div style="font-size:11px;color:#aaa">{{ $patient->emergency_contact_phone }}</div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Admission info --}}
    <div class="card-emr mb-3">
        <div class="card-hd">
            <div class="card-hd-title"><i class="bi bi-clipboard2-pulse-fill" style="color:#4154f1"></i> Admission</div>
            <span style="font-size:10.5px;padding:2px 9px;border-radius:8px;font-weight:700;background:{{ $sbg }};color:{{ $scol }}">
                {{ ucfirst($admission->status) }}
            </span>
        </div>
        <div class="card-bd">
            <div class="row g-1" style="font-size:12px">
                <div class="col-5" style="color:#888">Code</div>
                <div class="col-7"><code style="color:#4154f1">{{ $admission->code }}</code></div>

                <div class="col-5" style="color:#888">Type</div>
                <div class="col-7" style="font-weight:600">{{ $admission->admission_type ?? '—' }}</div>

                <div class="col-5" style="color:#888">Admitted</div>
                <div class="col-7">{{ $admission->admitted_at?->format('d M Y H:i') ?? '—' }}</div>

                <div class="col-5" style="color:#888">Expected Disch.</div>
                <div class="col-7" style="color:{{ $isAdmitted && $admission->expected_discharge_at?->isPast() ? '#e74c3c' : '#333' }}">
                    {{ $admission->expected_discharge_at?->format('d M Y') ?? '—' }}
                </div>

                <div class="col-5" style="color:#888">LOS</div>
                <div class="col-7" style="font-weight:800;color:#4154f1;font-size:14px">{{ $los }} day{{ $los != 1 ? 's' : '' }}</div>

                <div class="col-12" style="border-top:1px solid #e6e9f0;margin:6px 0;padding-top:6px"></div>

                <div class="col-5" style="color:#888">Attending Dr.</div>
                <div class="col-7" style="font-weight:600">{{ $admission->attending_doctor ?? '—' }}</div>

                <div class="col-5" style="color:#888">Admitting Dr.</div>
                <div class="col-7">{{ $admission->admitting_doctor ?? '—' }}</div>

                <div class="col-5" style="color:#888">Nurse</div>
                <div class="col-7">{{ $admission->primary_nurse ?? '—' }}</div>

                @if($admission->admission_reason)
                <div class="col-12" style="border-top:1px solid #e6e9f0;margin:6px 0;padding-top:6px"></div>
                <div class="col-12" style="color:#888;font-size:11px">Admission Reason</div>
                <div class="col-12" style="color:#333;font-size:12px;background:#f9fafb;padding:8px;border-radius:8px;margin-top:4px">
                    {{ $admission->admission_reason }}
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Ward / Bed --}}
    <div class="card-emr mb-3">
        <div class="card-hd">
            <div class="card-hd-title"><i class="bi bi-building-fill" style="color:#4154f1"></i> Ward / Bed</div>
            @if($isAdmitted)
            <button type="button" class="btn btn-sm btn-outline-warning" onclick="openModal('transferBedModal')" title="Transfer bed">
                <i class="bi bi-arrow-left-right"></i>
            </button>
            @endif
        </div>
        <div class="card-bd">
            @if($admission->bed)
            <div style="text-align:center;padding:8px 0 14px">
                <div style="font-size:28px;font-weight:900;color:#4154f1">{{ $admission->bed->name }}</div>
                <div style="font-size:11px;color:#aaa">{{ $admission->bed->code }}</div>
                @php
                    $bedStatusColors = ['available'=>'#2eca6a','occupied'=>'#4154f1','cleaning'=>'#ff771d','reserved'=>'#9b59b6','maintenance'=>'#e74c3c'];
                    $bedCol = $bedStatusColors[$admission->bed->status] ?? '#aaa';
                @endphp
                <span style="font-size:10px;background:{{ $bedCol }}22;color:{{ $bedCol }};padding:2px 9px;border-radius:6px;font-weight:700">
                    {{ ucfirst($admission->bed->status) }}
                </span>
            </div>
            @endif
            <div class="row g-1" style="font-size:12px">
                <div class="col-4" style="color:#888">Ward</div>
                <div class="col-8" style="font-weight:600">{{ $admission->ward?->name ?? '—' }}</div>
                <div class="col-4" style="color:#888">Room</div>
                <div class="col-8">{{ $admission->room?->name ?? '—' }}</div>
                <div class="col-4" style="color:#888">Bed Type</div>
                <div class="col-8">{{ $admission->bed?->type ?? '—' }}</div>
            </div>
        </div>
    </div>

    {{-- Discharge summary (if discharged) --}}
    @if($admission->isDischarged())
    <div class="card-emr mb-3">
        <div class="card-hd" style="background:#e6e9f0">
            <div class="card-hd-title"><i class="bi bi-clipboard2-check-fill" style="color:#4154f1"></i> Discharge Summary</div>
        </div>
        <div class="card-bd">
            <div class="row g-1" style="font-size:12px">
                <div class="col-5" style="color:#888">Discharged</div>
                <div class="col-7">{{ $admission->discharged_at?->format('d M Y H:i') }}</div>
                <div class="col-5" style="color:#888">Type</div>
                <div class="col-7" style="font-weight:600">{{ $admission->discharge_type ?? '—' }}</div>
                <div class="col-5" style="color:#888">Condition</div>
                <div class="col-7">
                    @php
                        $condColors = ['Recovered'=>'#2eca6a','Improved'=>'#2eca6a','Unchanged'=>'#ff771d','Worsened'=>'#e74c3c','Deceased'=>'#666'];
                        $condCol = $condColors[$admission->discharge_condition] ?? '#888';
                    @endphp
                    <span style="font-weight:700;color:{{ $condCol }}">{{ $admission->discharge_condition ?? '—' }}</span>
                </div>
                <div class="col-5" style="color:#888">By</div>
                <div class="col-7">{{ $admission->discharged_by ?? '—' }}</div>
                @if($admission->discharge_summary)
                <div class="col-12" style="border-top:1px solid #e6e9f0;margin:6px 0;padding-top:6px"></div>
                <div class="col-12" style="color:#888;font-size:11px">Summary</div>
                <div class="col-12" style="font-size:12px;background:#f6f8fa;padding:10px;border-radius:8px;margin-top:4px;white-space:pre-line">{{ $admission->discharge_summary }}</div>
                @endif
            </div>
        </div>
    </div>
    @endif

</div>

{{-- ── Right main area ──────────────────────────────────────────────────────── --}}
<div class="col-12 col-lg-8">

    {{-- ── Treatments ──────────────────────────────────────────────────────── --}}
    <div class="card-emr mb-3">
        <div class="card-hd">
            <div class="card-hd-title"><i class="bi bi-clipboard2-heart-fill" style="color:#4154f1"></i> Treatments
                <span style="font-size:10.5px;background:#eef0fd;color:#4154f1;padding:1px 8px;border-radius:8px;font-weight:700;margin-left:4px">
                    {{ $admission->treatments->count() }}
                </span>
            </div>
            @if($isAdmitted)
            <button type="button" class="btn btn-primary btn-sm" onclick="openModal('addTreatmentModal')">
                <i class="bi bi-plus-lg"></i> Add
            </button>
            @endif
        </div>
        <div class="card-bd" style="padding:0">
            @forelse($admission->treatments->sortByDesc('ordered_at') as $trt)
            @php
                $trtColors = [
                    'ordered'     => ['#eef0fd','#4154f1'],
                    'in_progress' => ['#e8f8ef','#2eca6a'],
                    'completed'   => ['#f5f5f5','#888'],
                    'cancelled'   => ['#fde8e8','#e74c3c'],
                    'held'        => ['#fff3e8','#ff771d'],
                ];
                [$tbg, $tcol] = $trtColors[$trt->status] ?? ['#f5f5f5','#888'];
                $trtTypeIcons = [
                    'medication'   => 'bi-capsule',
                    'procedure'    => 'bi-scissors',
                    'therapy'      => 'bi-activity',
                    'nursing_care' => 'bi-heart-pulse-fill',
                    'diet'         => 'bi-egg-fried',
                    'other'        => 'bi-three-dots',
                ];
                $trtIcon = $trtTypeIcons[$trt->treatment_type] ?? 'bi-clipboard2';
            @endphp
            <div style="padding:12px 16px;border-bottom:1px solid #f5f6ff;display:flex;align-items:flex-start;gap:12px">
                <div style="width:34px;height:34px;border-radius:9px;background:{{ $tbg }};color:{{ $tcol }};display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;margin-top:2px">
                    <i class="bi {{ $trtIcon }}"></i>
                </div>
                <div style="flex:1;min-width:0">
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                        <span style="font-weight:700;color:#1a1f36;font-size:13px">{{ $trt->name }}</span>
                        <span style="font-size:9.5px;background:{{ $tbg }};color:{{ $tcol }};padding:1px 7px;border-radius:6px;font-weight:700">{{ ucfirst(str_replace('_',' ',$trt->status)) }}</span>
                        <span style="font-size:10px;background:#f5f6ff;color:#888;padding:1px 7px;border-radius:6px">{{ ucfirst(str_replace('_',' ',$trt->treatment_type)) }}</span>
                    </div>
                    @if($trt->instructions)
                    <div style="font-size:12px;color:#555;margin-top:3px">{{ $trt->instructions }}</div>
                    @endif
                    <div style="font-size:10.5px;color:#aaa;margin-top:4px;display:flex;gap:12px;flex-wrap:wrap">
                        @if($trt->frequency)<span><i class="bi bi-clock"></i> {{ $trt->frequency }}</span>@endif
                        @if($trt->route)<span><i class="bi bi-arrow-right-circle"></i> {{ $trt->route }}</span>@endif
                        @if($trt->duration)<span><i class="bi bi-hourglass-split"></i> {{ $trt->duration }}</span>@endif
                        <span><i class="bi bi-person"></i> {{ $trt->ordered_by }}</span>
                        <span><i class="bi bi-calendar3"></i> {{ $trt->ordered_at?->format('d M H:i') }}</span>
                    </div>
                </div>
                <div style="font-size:10px;color:#bbb;flex-shrink:0"><code>{{ $trt->code }}</code></div>
            </div>
            @empty
            <div style="text-align:center;padding:28px;color:#bbb">
                <div style="font-size:28px;margin-bottom:6px;opacity:.3">📋</div>
                No treatments ordered yet
                @if($isAdmitted)
                <div><button type="button" class="btn btn-primary btn-sm mt-2" onclick="openModal('addTreatmentModal')">
                    <i class="bi bi-plus-lg"></i> Add Treatment
                </button></div>
                @endif
            </div>
            @endforelse
        </div>
    </div>

    {{-- ── IPD Medications ─────────────────────────────────────────────────── --}}
    <div class="card-emr mb-3">
        <div class="card-hd">
            <div class="card-hd-title">
                <i class="bi bi-capsule-pill" style="color:#2eca6a"></i> IPD Medications
                <span style="font-size:10.5px;background:#e8f8ef;color:#2eca6a;padding:1px 8px;border-radius:8px;font-weight:700;margin-left:4px">
                    {{ $admission->inpatientMedications->count() }}
                </span>
            </div>
            <div style="display:flex;align-items:center;gap:6px">
                <span style="font-size:10px;color:#aaa"><i class="bi bi-box-arrow-down" style="color:#2eca6a"></i> Stock deducted on order</span>
                @if($isAdmitted)
                <button type="button" class="btn btn-success btn-sm" onclick="openModal('addMedModal')">
                    <i class="bi bi-plus-lg"></i> Add
                </button>
                @endif
            </div>
        </div>
        <div class="card-bd" style="padding:0">
            @forelse($admission->inpatientMedications->sortByDesc('start_date') as $med)
            @php
                $medStatusColors = [
                    'active'       => ['#e8f8ef','#2eca6a'],
                    'completed'    => ['#f5f5f5','#888'],
                    'discontinued' => ['#fde8e8','#e74c3c'],
                    'held'         => ['#fff3e8','#ff771d'],
                ];
                [$mbg, $mcol] = $medStatusColors[$med->status] ?? ['#f5f5f5','#888'];
            @endphp
            <div style="padding:12px 16px;border-bottom:1px solid #f5f6ff;display:flex;align-items:flex-start;gap:12px">
                <div style="width:34px;height:34px;border-radius:9px;background:{{ $mbg }};color:{{ $mcol }};display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;margin-top:2px">
                    <i class="bi bi-capsule"></i>
                </div>
                <div style="flex:1;min-width:0">
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                        <span style="font-weight:700;color:#1a1f36;font-size:13px">{{ $med->medicine_name }}</span>
                        @if($med->dosage)
                        <span style="font-size:10.5px;background:#eef0fd;color:#4154f1;padding:1px 7px;border-radius:6px">{{ $med->dosage }}</span>
                        @endif
                        <span style="font-size:9.5px;background:{{ $mbg }};color:{{ $mcol }};padding:1px 7px;border-radius:6px;font-weight:700">{{ ucfirst($med->status) }}</span>
                        <span style="font-size:9.5px;background:#f0faf5;color:#2eca6a;padding:1px 7px;border-radius:6px" title="Stock was deducted when this order was placed">
                            <i class="bi bi-box-arrow-down"></i> Stock deducted
                        </span>
                    </div>
                    <div style="font-size:10.5px;color:#aaa;margin-top:4px;display:flex;gap:12px;flex-wrap:wrap">
                        @if($med->frequency)<span><i class="bi bi-clock"></i> {{ $med->frequency }}</span>@endif
                        @if($med->route)<span><i class="bi bi-arrow-right-circle"></i> {{ $med->route }}</span>@endif
                        @if($med->quantity)<span><i class="bi bi-hash"></i> Qty {{ $med->quantity }}</span>@endif
                        <span><i class="bi bi-calendar3"></i> {{ $med->start_date?->format('d M Y') }}@if($med->end_date) → {{ $med->end_date->format('d M Y') }}@endif</span>
                    </div>
                    @if($med->administered_at)
                    <div style="font-size:10.5px;color:#2eca6a;margin-top:3px">
                        <i class="bi bi-check-circle-fill"></i>
                        Administered by {{ $med->administered_by }} at {{ $med->administered_at->format('d M Y H:i') }}
                    </div>
                    @endif
                </div>
                <code style="font-size:10px;color:#bbb;flex-shrink:0">{{ $med->code }}</code>
            </div>
            @empty
            <div style="text-align:center;padding:28px;color:#bbb">
                <div style="font-size:28px;margin-bottom:6px;opacity:.3">💊</div>
                No IPD medications ordered yet
                @if($isAdmitted)
                <div><button type="button" class="btn btn-success btn-sm mt-2" onclick="openModal('addMedModal')">
                    <i class="bi bi-plus-lg"></i> Add Medication
                </button></div>
                @endif
            </div>
            @endforelse
        </div>
    </div>

</div>{{-- /col-lg-8 --}}
</div>{{-- /row --}}


{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- MODALS                                                                    --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}

@if($isAdmitted)

{{-- ── Add Treatment Modal ──────────────────────────────────────────────── --}}
<div id="addTreatmentModal" class="ipd-modal" style="display:none">
    <div class="ipd-modal-box" style="width:540px">
        <div class="ipd-modal-hd">
            <span><i class="bi bi-clipboard2-heart-fill" style="color:#4154f1"></i> Add Treatment Order</span>
            <button type="button" onclick="closeModal('addTreatmentModal')" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg"></i></button>
        </div>
        <form method="POST" action="{{ route('admissions.treatment', $admission->code) }}" novalidate>
            @csrf
            <div class="ipd-modal-bd">
                <div class="row g-3">
                    <div class="col-12 col-sm-6">
                        <label class="form-label fw-semibold" style="font-size:12px">Type <span style="color:#e74c3c">*</span></label>
                        <select name="treatment_type" class="form-select" required>
                            <option value="">— Select —</option>
                            <option value="medication">Medication</option>
                            <option value="procedure">Procedure</option>
                            <option value="therapy">Therapy</option>
                            <option value="nursing_care">Nursing Care</option>
                            <option value="diet">Diet</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label fw-semibold" style="font-size:12px">Frequency</label>
                        <select name="frequency" class="form-select">
                            <option value="">— Select —</option>
                            <option value="STAT">STAT (immediately)</option>
                            <option value="OD">OD (once daily)</option>
                            <option value="BD">BD (twice daily)</option>
                            <option value="TDS">TDS (3× daily)</option>
                            <option value="QID">QID (4× daily)</option>
                            <option value="Q4H">Q4H (every 4h)</option>
                            <option value="Q6H">Q6H (every 6h)</option>
                            <option value="Q8H">Q8H (every 8h)</option>
                            <option value="PRN">PRN (as needed)</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold" style="font-size:12px">Treatment Name <span style="color:#e74c3c">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. IV Fluids, Wound dressing, Physiotherapy…" required maxlength="200"/>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label fw-semibold" style="font-size:12px">Route</label>
                        <select name="route" class="form-select">
                            <option value="">— Select —</option>
                            <option value="Oral">Oral</option>
                            <option value="IV">IV (Intravenous)</option>
                            <option value="IM">IM (Intramuscular)</option>
                            <option value="SC">SC (Subcutaneous)</option>
                            <option value="Topical">Topical</option>
                            <option value="Inhalation">Inhalation</option>
                            <option value="PR">PR (Rectal)</option>
                            <option value="PV">PV (Vaginal)</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label fw-semibold" style="font-size:12px">Duration</label>
                        <input type="text" name="duration" class="form-control" placeholder="e.g. 3 days, until stable"/>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold" style="font-size:12px">Instructions</label>
                        <textarea name="instructions" class="form-control" rows="2" placeholder="Administration instructions…"></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold" style="font-size:12px">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Additional notes…"></textarea>
                    </div>
                </div>
            </div>
            <div class="ipd-modal-ft">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle-fill"></i> Add Treatment</button>
                <button type="button" onclick="closeModal('addTreatmentModal')" class="btn btn-outline-secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- ── Add Medication Modal ─────────────────────────────────────────────── --}}
<div id="addMedModal" class="ipd-modal" style="display:none">
    <div class="ipd-modal-box" style="width:580px">
        <div class="ipd-modal-hd">
            <span><i class="bi bi-capsule-pill" style="color:#2eca6a"></i> Add IPD Medication</span>
            <button type="button" onclick="closeModal('addMedModal')" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg"></i></button>
        </div>
        <form method="POST" action="{{ route('admissions.medication', $admission->code) }}" novalidate>
            @csrf
            <div class="ipd-modal-bd">
                <div class="note note-success mb-3" style="font-size:11px">
                    <i class="bi bi-box-arrow-down"></i>
                    Stock will be deducted immediately when this medication is ordered.
                </div>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold" style="font-size:12px">Medicine <span style="color:#e74c3c">*</span></label>
                        <select name="medicine_id" class="form-select" required>
                            <option value="">— Select medicine —</option>
                            @foreach($medicines as $med)
                            <option value="{{ $med->id }}">
                                {{ $med->name }}@if($med->form) ({{ $med->form }})@endif@if($med->strength) {{ $med->strength }}@endif — Stock: {{ $med->stock }} {{ $med->unit }}
                            </option>
                            @endforeach
                        </select>
                        @if($medicines->isEmpty())
                        <div class="note note-warn mt-2" style="font-size:11px"><i class="bi bi-exclamation-triangle"></i> No medicines in stock.</div>
                        @endif
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label fw-semibold" style="font-size:12px">Dosage</label>
                        <input type="text" name="dosage" class="form-control" placeholder="e.g. 500mg, 2 tabs"/>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label fw-semibold" style="font-size:12px">Qty per dose <span style="color:#e74c3c">*</span></label>
                        <input type="number" name="quantity" class="form-control" placeholder="1" min="0.01" step="0.01" value="1" required/>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label fw-semibold" style="font-size:12px">Route</label>
                        <select name="route" class="form-select">
                            <option value="">— Select —</option>
                            <option value="Oral">Oral</option>
                            <option value="IV">IV</option>
                            <option value="IM">IM</option>
                            <option value="SC">SC</option>
                            <option value="Topical">Topical</option>
                            <option value="Inhalation">Inhalation</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label fw-semibold" style="font-size:12px">Frequency</label>
                        <select name="frequency" class="form-select">
                            <option value="">— Select —</option>
                            <option value="STAT">STAT</option>
                            <option value="OD">OD (once daily)</option>
                            <option value="BD">BD (twice daily)</option>
                            <option value="TDS">TDS (3× daily)</option>
                            <option value="QID">QID (4× daily)</option>
                            <option value="Q4H">Q4H</option>
                            <option value="Q6H">Q6H</option>
                            <option value="Q8H">Q8H</option>
                            <option value="PRN">PRN</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label fw-semibold" style="font-size:12px">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}"/>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label fw-semibold" style="font-size:12px">End Date</label>
                        <input type="date" name="end_date" class="form-control"/>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold" style="font-size:12px">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Special instructions…"></textarea>
                    </div>
                </div>
            </div>
            <div class="ipd-modal-ft">
                <button type="submit" class="btn btn-success"><i class="bi bi-check-circle-fill"></i> Add Medication</button>
                <button type="button" onclick="closeModal('addMedModal')" class="btn btn-outline-secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- ── Transfer Bed Modal ───────────────────────────────────────────────── --}}
<div id="transferBedModal" class="ipd-modal" style="display:none">
    <div class="ipd-modal-box" style="width:460px">
        <div class="ipd-modal-hd">
            <span><i class="bi bi-arrow-left-right" style="color:#ff771d"></i> Transfer Patient Bed</span>
            <button type="button" onclick="closeModal('transferBedModal')" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg"></i></button>
        </div>
        <form method="POST" action="{{ route('admissions.transfer-bed', $admission->code) }}" novalidate>
            @csrf @method('PATCH')
            <div class="ipd-modal-bd">
                <div class="note note-warn mb-3" style="font-size:12px">
                    <i class="bi bi-info-circle-fill"></i>
                    Current bed: <strong>{{ $admission->bed?->name ?? 'None' }}</strong>
                    @if($admission->ward) ({{ $admission->ward->name }})@endif
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size:12px">Ward</label>
                    <select id="transferWardSelect" class="form-select" onchange="loadAvailableBeds(this.value)">
                        <option value="">— Select ward —</option>
                        @foreach($wards as $ward)
                        <option value="{{ $ward->id }}" {{ $admission->ward_id == $ward->id ? 'selected':'' }}>{{ $ward->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold" style="font-size:12px">New Bed <span style="color:#e74c3c">*</span></label>
                    <select name="bed_id" id="transferBedSelect" class="form-select" required>
                        <option value="">— Select ward first —</option>
                    </select>
                    <div id="transferBedLoading" style="display:none;font-size:11px;color:#aaa;margin-top:4px">
                        <i class="bi bi-arrow-repeat"></i> Loading available beds…
                    </div>
                </div>
            </div>
            <div class="ipd-modal-ft">
                <button type="submit" class="btn btn-warning" id="transferBedSubmit">
                    <i class="bi bi-arrow-left-right"></i> Transfer
                </button>
                <button type="button" onclick="closeModal('transferBedModal')" class="btn btn-outline-secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>

{{-- ── Discharge Modal ──────────────────────────────────────────────────── --}}
<div id="dischargeModal" class="ipd-modal" style="display:none">
    <div class="ipd-modal-box" style="width:580px">
        <div class="ipd-modal-hd" style="background:#fff5f5">
            <span><i class="bi bi-box-arrow-right" style="color:#e74c3c"></i> Discharge Patient</span>
            <button type="button" onclick="closeModal('dischargeModal')" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg"></i></button>
        </div>
        <form method="POST" action="{{ route('admissions.discharge', $admission->code) }}" novalidate>
            @csrf
            <div class="ipd-modal-bd">
                <div class="note note-danger mb-3" style="font-size:12px">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    This will close the admission, release the bed, and mark all active treatments as completed.
                </div>
                <div class="row g-3">
                    <div class="col-12 col-sm-6">
                        <label class="form-label fw-semibold" style="font-size:12px">Discharge Type <span style="color:#e74c3c">*</span></label>
                        <select name="discharge_type" class="form-select" required>
                            <option value="">— Select —</option>
                            <option value="Normal">Normal</option>
                            <option value="Against_advice">Against Medical Advice</option>
                            <option value="Transfer">Transfer to Another Facility</option>
                            <option value="Death">Death</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label fw-semibold" style="font-size:12px">Discharge Condition</label>
                        <select name="discharge_condition" class="form-select">
                            <option value="">— Select —</option>
                            <option value="Recovered">Recovered</option>
                            <option value="Improved">Improved</option>
                            <option value="Unchanged">Unchanged</option>
                            <option value="Worsened">Worsened</option>
                            <option value="Deceased">Deceased</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label fw-semibold" style="font-size:12px">Discharged At</label>
                        <input type="datetime-local" name="discharged_at" class="form-control"
                               value="{{ now()->format('Y-m-d\TH:i') }}"/>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label fw-semibold" style="font-size:12px">Visit Outcome</label>
                        <input type="text" name="visit_outcome" class="form-control" placeholder="e.g. Full recovery, Stable"/>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold" style="font-size:12px">Discharge Summary</label>
                        <textarea name="discharge_summary" class="form-control" rows="5"
                                  placeholder="Clinical summary: diagnosis, treatment given, outcome, follow-up instructions…"></textarea>
                    </div>
                </div>
            </div>
            <div class="ipd-modal-ft" style="background:#fff5f5">
                <button type="submit" class="btn btn-danger"
                        data-confirm="Confirm discharge of {{ $patient->surname }}, {{ $patient->name }}? The bed will be released and the IPD stay will be closed."
                        data-confirm-type="warn" data-confirm-title="Confirm Discharge">
                    <i class="bi bi-box-arrow-right"></i> Confirm Discharge
                </button>
                <button type="button" onclick="closeModal('dischargeModal')" class="btn btn-outline-secondary">Cancel</button>
            </div>
        </form>
    </div>
</div>

@endif {{-- isAdmitted --}}


@push('styles')
<style>
.ipd-modal {
    position: fixed; inset: 0; background: rgba(0,0,0,.52);
    z-index: 9000; display: flex; align-items: center; justify-content: center;
    padding: 16px;
}
.ipd-modal-box {
    background: #fff; border-radius: 16px; box-shadow: 0 24px 60px rgba(0,0,0,.22);
    max-width: 100%; max-height: 90vh; display: flex; flex-direction: column;
}
.ipd-modal-hd {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 20px; border-bottom: 1px solid #e6e9f0;
    font-weight: 800; font-size: 14px; color: #1a1f36;
    border-radius: 16px 16px 0 0;
}
.ipd-modal-bd {
    padding: 20px; overflow-y: auto; flex: 1;
}
.ipd-modal-ft {
    display: flex; gap: 8px; padding: 14px 20px;
    border-top: 1px solid #e6e9f0;
    border-radius: 0 0 16px 16px;
}
</style>
@endpush

@push('scripts')
<script>
function openModal(id) { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }

// Close modal on backdrop click
document.querySelectorAll('.ipd-modal').forEach(function(modal) {
    modal.addEventListener('click', function(e) {
        if (e.target === this) closeModal(this.id);
    });
});

// Load available beds by ward (transfer modal)
function loadAvailableBeds(wardId) {
    var select = document.getElementById('transferBedSelect');
    var loading = document.getElementById('transferBedLoading');

    if (!wardId) {
        select.innerHTML = '<option value="">— Select ward first —</option>';
        return;
    }

    loading.style.display = 'block';
    select.innerHTML = '<option value="">Loading…</option>';

    fetch('{{ route('beds.available') }}?ward_id=' + wardId)
        .then(function(r) { return r.json(); })
        .then(function(beds) {
            loading.style.display = 'none';
            if (!beds.length) {
                select.innerHTML = '<option value="">No available beds in this ward</option>';
                return;
            }
            select.innerHTML = '<option value="">— Select bed —</option>';
            beds.forEach(function(bed) {
                var opt = document.createElement('option');
                opt.value = bed.id;
                opt.textContent = bed.name + ' (' + bed.code + ') — ' + (bed.type || 'Standard');
                select.appendChild(opt);
            });
        })
        .catch(function() {
            loading.style.display = 'none';
            select.innerHTML = '<option value="">Failed to load beds</option>';
        });
}

// Auto-load beds when transfer modal opens if ward is pre-selected
var preselectedWard = document.getElementById('transferWardSelect')?.value;
if (preselectedWard) loadAvailableBeds(preselectedWard);
</script>
@endpush

@endsection
