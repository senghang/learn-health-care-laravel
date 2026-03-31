@extends('clinics.layout.app')
@section('title', 'Lab: ' . $lab->code)

@section('content')

@php
    $sc = $lab->status_color;
    $isPending = $lab->isPending();
    $resultsCount = $lab->results->count();
    $filledCount = $lab->results->whereNotNull('value')->count();
    $allVerified = $lab->results->every(fn($r) => $r->verified_at !== null);
@endphp

<x-page-header title="មន្ទីរពិសោធន៍" :subtitle="$lab->code"
    :breadcrumbs="[
        ['label'=>'ដើម','url'=>url('/')],
        ['label'=>'Laboratory','url'=>route('laboratory.index')],
        ['label'=>$lab->code],
    ]">
    <a href="{{ route('laboratory.index') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-arrow-left"></i> ត្រឡប់</a>
</x-page-header>

<div class="row g-3">

{{-- ══ LEFT: Order Details + Results ══════════════════════════════════════ --}}
<div class="col-12 col-lg-8">

  {{-- Order Header --}}
  <div class="card-emr mb-3">
    <div class="card-hd">
      <div class="card-hd-title"><i class="bi bi-droplet-fill" style="color:#4154f1"></i> Order Details</div>
      <span style="background:{{ $sc }}22;color:{{ $sc }};font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px">
        {{ ucfirst($lab->status) }}
      </span>
    </div>
    <div class="card-bd">
      <div class="row g-3" style="font-size:13px">
        <div class="col-6 col-sm-3"><div style="color:#aaa;font-size:10px">Code</div><strong style="color:#4154f1;font-family:monospace">{{ $lab->code }}</strong></div>
        <div class="col-6 col-sm-3"><div style="color:#aaa;font-size:10px">Category</div><strong>{{ $lab->category ?? '—' }}</strong></div>
        <div class="col-6 col-sm-3"><div style="color:#aaa;font-size:10px">Requested</div><strong>{{ $lab->requested_at?->format('d/m/Y H:i') }}</strong></div>
        <div class="col-6 col-sm-3"><div style="color:#aaa;font-size:10px">By</div><strong>{{ $lab->requested_by ?? '—' }}</strong></div>
        @if($lab->urgency !== 'normal')
        <div class="col-6 col-sm-3">
          <div style="color:#aaa;font-size:10px">Urgency</div>
          <span class="badge-s" style="background:{{ $lab->urgency_color }}15;color:{{ $lab->urgency_color }}">{{ ucfirst($lab->urgency) }}</span>
        </div>
        @endif
        @if($lab->collected_at)
        <div class="col-6 col-sm-3"><div style="color:#aaa;font-size:10px">Collected</div><strong>{{ $lab->collected_at->format('d/m/Y H:i') }}</strong></div>
        <div class="col-6 col-sm-3"><div style="color:#aaa;font-size:10px">By</div><strong>{{ $lab->collected_by }}</strong></div>
        @endif
      </div>

      {{-- Action buttons based on status --}}
      <div style="display:flex;gap:8px;margin-top:16px;padding-top:12px;border-top:1px solid #f0f2ff;flex-wrap:wrap">
        @if($lab->status === 'requested')
        <form method="POST" action="{{ route('laboratory.update', $lab->code) }}">
          @csrf @method('PATCH')
          <input type="hidden" name="action" value="collect"/>
          <button type="submit" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-test-tube"></i> Mark as Collected
          </button>
        </form>
        @endif

        @if(!$allVerified && $filledCount > 0)
        <form method="POST" action="{{ route('laboratory.update', $lab->code) }}">
          @csrf @method('PATCH')
          <input type="hidden" name="action" value="verify"/>
          <button type="submit" class="btn btn-sm btn-outline-success">
            <i class="bi bi-shield-check"></i> Verify All Results
          </button>
        </form>
        @endif
      </div>
    </div>
  </div>

  {{-- Results Table --}}
  <div class="card-emr mb-3">
    <div class="card-hd">
      <div class="card-hd-title"><i class="bi bi-table" style="color:#ff771d"></i> Results ({{ $filledCount }}/{{ $resultsCount }})</div>
    </div>

    @if($lab->results->isNotEmpty())
    <form method="POST" action="{{ route('laboratory.update', $lab->code) }}">
      @csrf @method('PATCH')
      <input type="hidden" name="action" value="results"/>

      <div class="card-bd" style="padding:0;overflow-x:auto">
        <table style="width:100%;font-size:12px;border-collapse:collapse">
          <thead>
            <tr style="background:#f6f9ff;color:#888;font-size:10px;text-transform:uppercase;letter-spacing:.5px">
              <th style="padding:10px 12px;text-align:left">Test Name</th>
              <th style="padding:10px 8px;text-align:left">Category</th>
              <th style="padding:10px 8px;text-align:left;min-width:120px">Result</th>
              <th style="padding:10px 8px;text-align:left">Ref. Range</th>
              <th style="padding:10px 8px;text-align:left;min-width:120px">Interpretation</th>
              <th style="padding:10px 8px;text-align:center">Flag</th>
              <th style="padding:10px 8px;text-align:center">Verified</th>
            </tr>
          </thead>
          <tbody>
            @foreach($lab->results as $result)
            @php $isCrit = $result->is_abnormal; @endphp
            <tr style="border-bottom:1px solid #f8f9ff;{{ $isCrit ? 'background:#fff5f5' : '' }}">
              <td style="padding:8px 12px;font-weight:700;color:#012970">
                <input type="hidden" name="results[{{ $result->id }}][id]" value="{{ $result->id }}"/>
                {{ $result->name }}
                @if($result->value_unit) <span style="font-weight:400;color:#aaa">({{ $result->value_unit }})</span> @endif
              </td>
              <td style="padding:8px;color:#888">{{ $result->category ?? '—' }}</td>
              <td style="padding:8px">
                <input type="text" name="results[{{ $result->id }}][value]" class="form-control form-control-sm {{ $isCrit ? 'border-danger' : '' }}"
                       value="{{ $result->value }}" placeholder="Enter result"
                       style="{{ $isCrit ? 'color:#e74c3c;font-weight:700' : '' }}"/>
              </td>
              <td style="padding:8px">
                <input type="text" name="results[{{ $result->id }}][reference_range]" class="form-control form-control-sm"
                       value="{{ $result->reference_range }}" placeholder="e.g. 0–5"/>
              </td>
              <td style="padding:8px">
                <select name="results[{{ $result->id }}][interpretation]" class="form-select form-select-sm"
                        style="{{ $isCrit ? 'color:#e74c3c;font-weight:700;border-color:#e74c3c' : '' }}">
                  @foreach([''=>'— Select —','Normal'=>'Normal','Negative'=>'Negative','Positive'=>'Positive','High'=>'High','Low'=>'Low','Critical'=>'Critical','Borderline'=>'Borderline'] as $v=>$l)
                  <option value="{{ $v }}" {{ $result->interpretation===$v?'selected':'' }}>{{ $l }}</option>
                  @endforeach
                </select>
              </td>
              <td style="padding:8px;text-align:center">
                @if($result->flag)
                <span style="font-size:10px;font-weight:800;padding:2px 6px;border-radius:6px;background:{{ $result->flag_color }}15;color:{{ $result->flag_color }}">
                  {{ $result->flag }}
                </span>
                @else
                  <span style="color:#ddd">—</span>
                @endif
              </td>
              <td style="padding:8px;text-align:center">
                @if($result->verified_at)
                <span style="color:#2eca6a;font-weight:700;font-size:11px">
                  <i class="bi bi-shield-check-fill"></i> {{ $result->verified_at->format('d/m H:i') }}
                </span>
                @else
                <span style="color:#ddd;font-size:11px">Pending</span>
                @endif
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div style="padding:12px 16px;border-top:1px solid #f0f2ff">
        <button type="submit" class="btn btn-primary btn-sm">
          <i class="bi bi-check2-circle"></i> Save Results
        </button>
      </div>
    </form>
    @else
    <div class="card-bd" style="text-align:center;padding:30px;color:#bbb">
      <i class="bi bi-flask" style="font-size:24px;opacity:.3"></i>
      <div style="margin-top:8px">No test items added to this order.</div>
    </div>
    @endif
  </div>

</div>{{-- /col-lg-8 --}}

{{-- ══ RIGHT: Patient info + Visit link ══════════════════════════════════ --}}
<div class="col-12 col-lg-4">

  @if($lab->patient)
  <div class="card-emr mb-3">
    <div class="card-hd" style="background:#f6f9ff"><div class="card-hd-title"><i class="bi bi-person-fill" style="color:#4154f1"></i> Patient</div></div>
    <div class="card-bd">
      <div style="font-weight:700;color:#012970;font-size:14px">{{ $lab->patient->full_name }}</div>
      <div style="font-size:11px;color:#aaa;font-family:monospace;margin-bottom:8px">{{ $lab->patient->code }}</div>
      @if($lab->patient->sex) <div style="font-size:12px;color:#555">{{ $lab->patient->sex === 'M' ? '♂ Male' : '♀ Female' }} @if($lab->patient->age) · {{ $lab->patient->age }}y @endif</div> @endif
      @if($lab->patient->phone) <div style="font-size:12px;color:#555"><i class="bi bi-telephone" style="color:#bbb"></i> {{ $lab->patient->phone }}</div> @endif
      <a href="{{ route('patients.show', $lab->patient->code) }}" class="btn btn-outline-primary btn-sm btn-w100 mt-2"><i class="bi bi-person-fill"></i> View Patient</a>
    </div>
  </div>
  @endif

  @if($lab->visit)
  <div class="card-emr mb-3">
    <div class="card-hd" style="background:#f6f9ff"><div class="card-hd-title"><i class="bi bi-clipboard2-pulse" style="color:#2eca6a"></i> Visit</div></div>
    <div class="card-bd">
      <code style="font-size:12px;color:#4154f1">{{ $lab->visit->code }}</code>
      <span class="badge-s {{ $lab->visit->visit_type === 'IPD' ? 'b-ipd' : 'b-opd' }}" style="margin-left:6px">{{ $lab->visit->visit_type }}</span>
      <div style="font-size:11px;color:#aaa;margin-top:4px">{{ $lab->visit->admitted_at?->format('d/m/Y H:i') }}</div>
      <a href="{{ url('/workflow/' . $lab->visit->code) }}" class="btn btn-outline-primary btn-sm btn-w100 mt-2"><i class="bi bi-arrow-right"></i> Go to Visit</a>
    </div>
  </div>
  @endif

  {{-- Order timeline --}}
  <div class="card-emr">
    <div class="card-hd" style="background:#f6f9ff"><div class="card-hd-title"><i class="bi bi-clock-history" style="color:#9b59b6"></i> Timeline</div></div>
    <div class="card-bd" style="font-size:12px;line-height:2">
      <div><i class="bi bi-circle-fill" style="color:#ff771d;font-size:8px"></i> Requested: {{ $lab->requested_at?->format('d/m/Y H:i') }} <span style="color:#aaa">by {{ $lab->requested_by }}</span></div>
      @if($lab->collected_at)
      <div><i class="bi bi-circle-fill" style="color:#9b59b6;font-size:8px"></i> Collected: {{ $lab->collected_at->format('d/m/Y H:i') }} <span style="color:#aaa">by {{ $lab->collected_by }}</span></div>
      @endif
      @if($lab->status === 'completed')
      <div><i class="bi bi-circle-fill" style="color:#2eca6a;font-size:8px"></i> Completed: {{ $lab->updated_at->format('d/m/Y H:i') }}</div>
      @endif
      @if($allVerified)
      <div><i class="bi bi-shield-check-fill" style="color:#2eca6a;font-size:10px"></i> All results verified</div>
      @endif
    </div>
  </div>

</div>{{-- /col-lg-4 --}}

</div>
@endsection
