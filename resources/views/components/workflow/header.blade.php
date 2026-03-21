<div class="pg-header">
    <div>
        <h1 class="pg-title">
            {{ $visit->surname }}, {{ $visit->given_name }} — {{ $visit->code }}
        </h1>

        <div class="breadcrumb-row">
            <a href="{{ route('dashboard') }}">ដើម</a>
            <span>›</span>
            <a href="{{ route('visits.index') }}">Visits</a>
            <span>›</span>
            <span>{{ $visit->code }}</span>
        </div>
    </div>

    <div class="d-flex gap-2 align-items-center flex-wrap">
        <span class="badge-s {{ $visit->visit_type === 'IPD' ? 'b-ipd' : 'b-opd' }}">
            {{ $visit->visit_type }}
        </span>

        <a href="{{ route('visits.index') }}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-arrow-left"></i> ត្រឡប់
        </a>
    </div>
</div>
