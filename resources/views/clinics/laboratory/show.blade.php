@extends('clinics.layout.app')
@section('title', 'Lab: ' . $lab->code)

@section('content')
<x-page-header :title="'Lab Order'" :subtitle="$lab->code"
    :breadcrumbs="[['label'=>__('app.nav.dashboard'),'url'=>route('dashboard')],['label'=>__('app.laboratory'),'url'=>route('laboratory.index')],['label'=>$lab->code]]">
    <a href="{{ route('laboratory.index') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
</x-page-header>

<div class="row g-3">
    {{-- Left: Order details + Results --}}
    <div class="col-12 col-lg-8">
        {{-- Order info --}}
        <div class="card-emr mb-3">
            <div class="card-hd">
                <div class="card-hd-title"><i class="bi bi-droplet-fill" style="color:#4154f1"></i> Order Details</div>
                @php
                    $sc = ['requested'=>'#ff771d','collected'=>'#4154f1','processing'=>'#9b59b6','completed'=>'#2eca6a'][$lab->status] ?? '#aaa';
                @endphp
                <span style="background:{{ $sc }}22;color:{{ $sc }};font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px">
                    {{ ucfirst($lab->status) }}
                </span>
            </div>
            <div class="card-bd">
                <div class="row g-3" style="font-size:13px">
                    <div class="col-6 col-sm-3"><strong>Code:</strong><br>{{ $lab->code }}</div>
                    <div class="col-6 col-sm-3"><strong>Patient:</strong><br>{{ $lab->patient?->full_name ?? $lab->patient_code }}</div>
                    <div class="col-6 col-sm-3"><strong>Requested:</strong><br>{{ $lab->requested_at?->format('d/m/Y H:i') }}</div>
                    <div class="col-6 col-sm-3"><strong>By:</strong><br>{{ $lab->requested_by ?? '—' }}</div>
                    @if($lab->collected_at)
                    <div class="col-6 col-sm-3"><strong>Collected:</strong><br>{{ $lab->collected_at->format('d/m/Y H:i') }}</div>
                    <div class="col-6 col-sm-3"><strong>By:</strong><br>{{ $lab->collected_by ?? '—' }}</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Results --}}
        <div class="card-emr">
            <div class="card-hd">
                <div class="card-hd-title"><i class="bi bi-clipboard2-data-fill" style="color:#2eca6a"></i> Test Results</div>
            </div>
            <div class="card-bd">
                @if($lab->status === 'requested')
                    {{-- Collect sample action --}}
                    <form method="POST" action="{{ route('laboratory.update', $lab->code) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="action" value="collect"/>
                        <div class="text-center" style="padding:20px">
                            <div style="font-size:32px;margin-bottom:8px">🧪</div>
                            <p style="color:#888;font-size:13px">Sample not yet collected</p>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle"></i> Mark as Collected</button>
                        </div>
                    </form>
                @elseif($lab->status === 'collected' || $lab->status === 'processing')
                    {{-- Record results form --}}
                    <form method="POST" action="{{ route('laboratory.update', $lab->code) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="action" value="results"/>
                        @foreach($lab->results as $i => $result)
                        <div style="padding:12px;border:1px solid #f0f2ff;border-radius:10px;margin-bottom:10px">
                            <input type="hidden" name="results[{{ $i }}][id]" value="{{ $result->id }}"/>
                            <div style="font-weight:700;color:#012970;margin-bottom:8px">{{ $result->name }}</div>
                            <div class="row g-2">
                                <div class="col-12 col-sm-6">
                                    <label style="font-size:11px;font-weight:600;color:#888">Result</label>
                                    <textarea name="results[{{ $i }}][result]" class="form-control" rows="2" placeholder="Result value…">{{ $result->result }}</textarea>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label style="font-size:11px;font-weight:600;color:#888">Conclusion</label>
                                    <textarea name="results[{{ $i }}][conclusion]" class="form-control" rows="2" placeholder="Conclusion…">{{ $result->conclusion }}</textarea>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        <button type="submit" class="btn btn-success mt-2"><i class="bi bi-check2-circle"></i> Save Results</button>
                    </form>
                @else
                    {{-- Display completed results --}}
                    @foreach($lab->results as $result)
                    <div style="padding:12px;border:1px solid #e8f8ef;border-radius:10px;margin-bottom:10px;background:#fafffe">
                        <div style="font-weight:700;color:#012970;margin-bottom:6px">{{ $result->name }}</div>
                        <div class="row g-2" style="font-size:13px">
                            <div class="col-6"><strong>Result:</strong><br>{{ $result->result ?? '—' }}</div>
                            <div class="col-6"><strong>Conclusion:</strong><br>{{ $result->conclusion ?? '—' }}</div>
                        </div>
                        @if($result->verified_at)
                            <div style="font-size:10px;color:#2eca6a;margin-top:6px"><i class="bi bi-shield-check"></i> Verified by {{ $result->verified_by }} at {{ $result->verified_at->format('d/m H:i') }}</div>
                        @endif
                    </div>
                    @endforeach

                    @if($lab->results->contains(fn($r) => !$r->verified_at))
                    <form method="POST" action="{{ route('laboratory.update', $lab->code) }}" class="mt-2">
                        @csrf @method('PATCH')
                        <input type="hidden" name="action" value="verify"/>
                        <button type="submit" class="btn btn-outline-success btn-sm"><i class="bi bi-shield-check"></i> Verify All Results</button>
                    </form>
                    @endif
                @endif
            </div>
        </div>
    </div>

    {{-- Right: Patient info --}}
    <div class="col-12 col-lg-4">
        @if($lab->patient)
        <div class="card-emr" style="position:sticky;top:76px">
            <div class="card-hd" style="background:#f6f9ff"><div class="card-hd-title"><i class="bi bi-person-fill" style="color:#4154f1"></i> Patient</div></div>
            <div class="card-bd">
                <div style="font-weight:700;color:#012970">{{ $lab->patient->surname }}, {{ $lab->patient->name }}</div>
                <div style="font-size:11px;color:#aaa;margin-bottom:8px">{{ $lab->patient->code }} · {{ $lab->patient->phone ?? '—' }}</div>
                <a href="{{ route('patients.show', $lab->patient->code) }}" class="btn btn-outline-primary btn-sm btn-w100">
                    <i class="bi bi-person-fill"></i> View Patient
                </a>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
