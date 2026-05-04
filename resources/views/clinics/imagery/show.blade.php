@extends('clinics.layout.app')
@section('title', 'Imaging: ' . $imagery->code)

@section('content')

@php
    $sc = $imagery->status_color;
    $catColors = ['X-ray'=>'#4154f1','Ultrasound'=>'#9b59b6','CT'=>'#ff771d','MRI'=>'#e74c3c','ECG'=>'#2eca6a','Endoscopy'=>'#3498db'];
    $cc = $catColors[$imagery->category] ?? '#aaa';
@endphp

<x-ui.page-header
    km="រូបភាពវេជ្ជសាស្ត្រ"
    title="{{ $imagery->code }}"
    :breadcrumbs="[['label'=>'ដើម','url'=>url('/')],['label'=>'Imaging','url'=>route('imagery.index')],['label'=>$imagery->code]]">
    <x-slot:actions>
        <x-ui.button href="{{ route('imagery.index') }}" variant="secondary"><x-slot:icon><i class="bi bi-arrow-left"></i></x-slot:icon>ត្រឡប់</x-ui.button>
    </x-slot:actions>
</x-ui.page-header>

<div class="row g-3">

<div class="col-12 col-lg-8">

  {{-- Order info --}}
  <div class="card-emr mb-3">
    <div class="card-hd">
      <div class="card-hd-title"><i class="bi bi-camera-fill" style="color:{{ $cc }}"></i> Order Details</div>
      <span style="background:{{ $sc }}22;color:{{ $sc }};font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px">{{ ucfirst($imagery->status) }}</span>
    </div>
    <div class="card-bd">
      <div class="row g-3" style="font-size:13px">
        <div class="col-6 col-sm-3"><div style="color:#aaa;font-size:10px">Code</div><strong style="color:#4154f1;font-family:monospace">{{ $imagery->code }}</strong></div>
        <div class="col-6 col-sm-3"><div style="color:#aaa;font-size:10px">Category</div><span class="badge-s" style="background:{{ $cc }}15;color:{{ $cc }}">{{ $imagery->category }}</span></div>
        <div class="col-6 col-sm-3"><div style="color:#aaa;font-size:10px">Requested</div><strong>{{ $imagery->requested_at?->format('d/m/Y H:i') }}</strong></div>
        <div class="col-6 col-sm-3"><div style="color:#aaa;font-size:10px">By</div><strong>{{ $imagery->requested_by ?? '—' }}</strong></div>
        @if($imagery->title)
        <div class="col-12"><div style="color:#aaa;font-size:10px">Title</div><strong>{{ $imagery->title }}</strong></div>
        @endif
      </div>
    </div>
  </div>

  {{-- Results --}}
  @foreach($imagery->results as $result)
  <div class="card-emr mb-3">
    <div class="card-hd">
      <div class="card-hd-title"><i class="bi bi-file-earmark-image" style="color:#ff771d"></i> {{ $result->name }}</div>
      @if($result->is_verified)
      <span style="font-size:10px;color:#2eca6a;font-weight:700"><i class="bi bi-shield-check-fill"></i> Verified</span>
      @endif
    </div>
    <div class="card-bd">

      {{-- Result entry form --}}
      <form method="POST" action="{{ route('imagery.update', $imagery->code) }}">
        @csrf @method('PATCH')
        <input type="hidden" name="action" value="result"/>

        <div class="row g-3 mb-3">
          <div class="col-12">
            <div class="fld">
              <label class="flbl"><span class="km">លទ្ធផល</span><span class="en">/ Findings</span></label>
              <textarea name="result" class="form-control" rows="4" placeholder="Radiologist findings…">{{ $result->result }}</textarea>
            </div>
          </div>
          <div class="col-12">
            <div class="fld">
              <label class="flbl"><span class="km">សេចក្តីសន្និដ្ឋាន</span><span class="en">/ Conclusion</span></label>
              <textarea name="conclusion" class="form-control" rows="2" placeholder="Impression / Summary…">{{ $result->conclusion }}</textarea>
            </div>
          </div>
        </div>

        {{-- Existing images --}}
        @if($result->images && count($result->images) > 0)
        <div style="margin-bottom:12px">
          <div style="font-size:10px;font-weight:700;color:#aaa;text-transform:uppercase;margin-bottom:6px">Attached Images</div>
          <div style="display:flex;gap:8px;flex-wrap:wrap">
            @foreach($result->images as $imgPath)
            <img src="{{ asset('storage/' . $imgPath) }}" style="width:100px;height:100px;object-fit:cover;border-radius:10px;border:2px solid #e6e9f0"/>
            @endforeach
          </div>
        </div>
        @endif

        <div style="display:flex;gap:8px;flex-wrap:wrap">
          <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check2-circle"></i> Save Results</button>
        </div>
      </form>

      {{-- Verify action --}}
      @if(!$result->is_verified && ($result->result || $result->conclusion))
      <div style="margin-top:12px;padding-top:12px;border-top:1px solid #e6e9f0">
        <form method="POST" action="{{ route('imagery.update', $imagery->code) }}">
          @csrf @method('PATCH')
          <input type="hidden" name="action" value="verify"/>
          <button type="submit" class="btn btn-outline-success btn-sm"><i class="bi bi-shield-check"></i> Verify Result</button>
        </form>
      </div>
      @endif

      @if($result->is_verified)
      <div style="margin-top:10px;font-size:11px;color:#2eca6a">
        <i class="bi bi-shield-check-fill"></i> Verified by {{ $result->verified_by }} · {{ $result->verified_at->format('d/m/Y H:i') }}
      </div>
      @endif
    </div>
  </div>
  @endforeach

  @if($imagery->results->isEmpty())
  <div class="card-emr">
    <div class="card-bd" style="text-align:center;padding:30px;color:#bbb">
      <i class="bi bi-camera" style="font-size:24px;opacity:.3"></i>
      <div style="margin-top:8px">No results recorded yet.</div>
    </div>
  </div>
  @endif

</div>{{-- /col-lg-8 --}}

<div class="col-12 col-lg-4">
  @if($imagery->patient)
  <div class="card-emr mb-3">
    <div class="card-hd" style="background:#f6f8fa"><div class="card-hd-title"><i class="bi bi-person-fill" style="color:#4154f1"></i> Patient</div></div>
    <div class="card-bd">
      <div style="font-weight:700;color:#1a1f36;font-size:14px">{{ $imagery->patient->full_name }}</div>
      <div style="font-size:11px;color:#aaa;font-family:monospace;margin-bottom:8px">{{ $imagery->patient->code }}</div>
      <a href="{{ route('patients.show', $imagery->patient->code) }}" class="btn btn-outline-primary btn-sm btn-w100"><i class="bi bi-person-fill"></i> View Patient</a>
    </div>
  </div>
  @endif

  @if($imagery->visit)
  <div class="card-emr mb-3">
    <div class="card-hd" style="background:#f6f8fa"><div class="card-hd-title"><i class="bi bi-clipboard2-pulse" style="color:#2eca6a"></i> Visit</div></div>
    <div class="card-bd">
      <code style="font-size:12px;color:#4154f1">{{ $imagery->visit->code }}</code>
      <span class="badge-s {{ $imagery->visit->visit_type === 'IPD' ? 'b-ipd' : 'b-opd' }}" style="margin-left:6px">{{ $imagery->visit->visit_type }}</span>
      <a href="{{ url('/workflow/' . $imagery->visit->code) }}" class="btn btn-outline-primary btn-sm btn-w100 mt-2"><i class="bi bi-arrow-right"></i> Go to Visit</a>
    </div>
  </div>
  @endif

  {{-- Timeline --}}
  <div class="card-emr">
    <div class="card-hd" style="background:#f6f8fa"><div class="card-hd-title"><i class="bi bi-clock-history" style="color:#9b59b6"></i> Timeline</div></div>
    <div class="card-bd" style="font-size:12px;line-height:2">
      <div><i class="bi bi-circle-fill" style="color:#ff771d;font-size:8px"></i> Requested: {{ $imagery->requested_at?->format('d/m/Y H:i') }}</div>
      @if($imagery->collected_at)
      <div><i class="bi bi-circle-fill" style="color:#2eca6a;font-size:8px"></i> Completed: {{ $imagery->collected_at->format('d/m/Y H:i') }}</div>
      @endif
    </div>
  </div>
</div>

</div>
@endsection
