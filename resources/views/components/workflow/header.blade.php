@props(['visit'])

@php
    // Patient age
    $dob = $visit->date_of_birth ?? $visit->dob ?? null;
    $age = null;
    if ($dob) {
        try { $age = \Carbon\Carbon::parse($dob)->age; } catch (\Throwable $e) {}
    }

    // Sex
    $sex = $visit->sex ?? $visit->gender ?? null;
    $sexColor  = $sex === 'F' || strtolower((string)$sex) === 'female' ? '#e91e8c' : '#4154f1';
    $sexLabel  = $sex ? strtoupper(substr((string)$sex, 0, 1)) : null;

    // Allergies (from triage if loaded)
    $allergies = null;
    if (isset($visit->triage) && $visit->triage) {
        $allergies = $visit->triage->allergies ?? null;
    }
    // Fallback: try loading from relationship if not eager-loaded
    if (!$allergies && method_exists($visit, 'triage')) {
        $allergies = optional($visit->triage)->allergies ?? null;
    }

    // Triage level (from triage relationship)
    $triageLevel = optional($visit->triage)->triage_level ?? null;
    $triageCols  = [
        'Emergency' => ['#e74c3c', '#fde8e8'],
        'Urgent'    => ['#ff771d', '#fff3e8'],
        'Standard'  => ['#4154f1', '#eef0fd'],
        'Low'       => ['#64748b', '#f1f5f9'],
    ];
    [$tCol, $tBg] = $triageCols[$triageLevel] ?? ['#94a3b8', '#f1f5f9'];
@endphp

<div class="pg-header" style="flex-wrap:wrap;gap:10px">

    {{-- ── Left: patient name + breadcrumbs ──────────────────────────── --}}
    <div style="flex:1;min-width:0">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
            {{-- Avatar / initials --}}
            <div style="width:40px;height:40px;border-radius:10px;
                        background:{{ $visit->visit_type === 'IPD' ? '#fff3e8' : '#eef0fd' }};
                        color:{{ $visit->visit_type === 'IPD' ? '#ff771d' : '#4154f1' }};
                        display:flex;align-items:center;justify-content:center;
                        font-size:15px;font-weight:900;flex-shrink:0">
                {{ strtoupper(substr($visit->surname ?? $visit->name ?? '?', 0, 1)) }}
            </div>

            <div>
                <h1 class="pg-title" style="font-size:17px;margin:0">
                    {{ $visit->surname }}, {{ $visit->name ?? $visit->given_name ?? '' }}
                    @if($age)<small style="font-size:12px;color:#94a3b8;font-weight:400"> · {{ $age }}y</small>@endif
                    @if($sexLabel)
                    <span style="font-size:11px;background:{{ $sexColor }}18;color:{{ $sexColor }};
                                 padding:1px 6px;border-radius:6px;font-weight:700;vertical-align:middle">
                        {{ $sexLabel }}
                    </span>
                    @endif
                </h1>
                <div class="breadcrumb-row" style="margin-top:2px">
                    <a href="{{ url('/') }}">ដើម</a>
                    <span>›</span>
                    <a href="{{ url('/workflow') }}">Visits</a>
                    <span>›</span>
                    <span style="color:#4154f1;font-weight:600">{{ $visit->code }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Right: badges + actions ────────────────────────────────────── --}}
    <div class="d-flex gap-2 align-items-center flex-wrap">

        {{-- Visit type --}}
        <span class="badge-s {{ $visit->visit_type === 'IPD' ? 'b-ipd' : 'b-opd' }}">
            {{ $visit->visit_type }}
        </span>

        {{-- Status --}}
        @if(is_null($visit->discharged_at))
            <span class="badge-s b-active">
                <i class="bi bi-circle-fill" style="font-size:7px"></i> Active
            </span>
        @else
            <span class="badge-s b-done">✓ Done</span>
        @endif

        {{-- Triage level --}}
        @if($triageLevel)
        <span style="font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;
                     background:{{ $tBg }};color:{{ $tCol }};border:1px solid {{ $tCol }}33">
            {{ $triageLevel }}
        </span>
        @endif

        {{-- Allergy warning --}}
        @if($allergies)
        <span title="{{ $allergies }}"
              style="font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;
                     background:#fde8e8;color:#e74c3c;border:1px solid #e74c3c44;
                     cursor:help;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
            ⚠ Allergy: {{ \Illuminate\Support\Str::limit($allergies, 30) }}
        </span>
        @endif

        {{-- Visit time --}}
        <span style="font-size:11px;color:#aaa;background:#f6f9ff;padding:4px 10px;border-radius:20px;border:1px solid #e6eaf5;white-space:nowrap">
            <i class="bi bi-clock" style="font-size:10px"></i>
            {{ $visit->admitted_at?->format('d/m/Y H:i') ?? '—' }}
        </span>

        <a href="{{ url('/workflow') }}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-arrow-left"></i> ត្រឡប់
        </a>
    </div>
</div>
