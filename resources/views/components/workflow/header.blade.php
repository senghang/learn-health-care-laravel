@props(['visit'])

<div class="pg-header">
    <div>
        <h1 class="pg-title" style="font-size:18px">
            {{ $visit->surname }}, {{ $visit->name ?? $visit->given_name ?? '' }}
            <small style="font-size:13px;color:#aaa;font-weight:400">— {{ $visit->code }}</small>
        </h1>
        <div class="breadcrumb-row">
            <a href="{{ url('/') }}">ដើម</a>
            <span>›</span>
            <a href="{{ url('/visits') }}">Visits</a>
            <span>›</span>
            <span style="color:#4154f1;font-weight:600">{{ $visit->code }}</span>
        </div>
    </div>

    <div class="d-flex gap-2 align-items-center flex-wrap">
        <span class="badge-s {{ $visit->visit_type === 'IPD' ? 'b-ipd' : 'b-opd' }}">
            {{ $visit->visit_type }}
        </span>
        @if(is_null($visit->discharged_at))
            <span class="badge-s b-active">
                <i class="bi bi-circle-fill" style="font-size:7px"></i> Active
            </span>
        @else
            <span class="badge-s b-done">✓ Done</span>
        @endif

        <span style="font-size:11px;color:#aaa;background:#f6f9ff;padding:4px 10px;border-radius:20px;border:1px solid #e6eaf5">
            <i class="bi bi-clock" style="font-size:10px"></i>
            {{ $visit->admitted_at?->format('d/m/Y H:i') ?? '—' }}
        </span>

        <a href="{{ url('/visits') }}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-arrow-left"></i> ត្រឡប់
        </a>
    </div>
</div>
