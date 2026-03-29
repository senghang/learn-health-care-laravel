@extends('clinics.layout.app')
@section('title', 'Imaging: ' . $imagery->code)

@section('content')
<x-page-header title="Imaging Order" :subtitle="$imagery->code"
    :breadcrumbs="[['label'=>__('app.nav.dashboard'),'url'=>route('dashboard')],['label'=>'Imaging','url'=>route('imagery.index')],['label'=>$imagery->code]]">
    <a href="{{ route('imagery.index') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
</x-page-header>

<div class="row g-3">
    <div class="col-12 col-lg-8">
        {{-- Order info --}}
        <div class="card-emr mb-3">
            <div class="card-hd">
                <div class="card-hd-title"><i class="bi bi-camera-fill" style="color:#4154f1"></i> Order Details</div>
                @php $sc = ['requested'=>'#ff771d','completed'=>'#2eca6a'][$imagery->status] ?? '#aaa'; @endphp
                <span style="background:{{ $sc }}22;color:{{ $sc }};font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px">{{ ucfirst($imagery->status) }}</span>
            </div>
            <div class="card-bd">
                <div class="row g-3" style="font-size:13px">
                    <div class="col-6 col-sm-3"><strong>Code:</strong><br>{{ $imagery->code }}</div>
                    <div class="col-6 col-sm-3"><strong>Category:</strong><br>{{ ucfirst(str_replace('_',' ',$imagery->category ?? '—')) }}</div>
                    <div class="col-6 col-sm-3"><strong>Requested:</strong><br>{{ $imagery->requested_at?->format('d/m/Y H:i') }}</div>
                    <div class="col-6 col-sm-3"><strong>By:</strong><br>{{ $imagery->requested_by ?? '—' }}</div>
                </div>
            </div>
        </div>

        {{-- Results --}}
        <div class="card-emr">
            <div class="card-hd"><div class="card-hd-title"><i class="bi bi-image-fill" style="color:#2eca6a"></i> Results</div></div>
            <div class="card-bd">
                @if($imagery->status === 'requested')
                    <form method="POST" action="{{ route('imagery.update', $imagery->code) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="action" value="result"/>
                        <div class="row g-3">
                            <div class="col-12">
                                <label style="font-size:11px;font-weight:600;color:#888">Findings</label>
                                <textarea name="result" class="form-control" rows="4" placeholder="Radiologist findings…"></textarea>
                            </div>
                            <div class="col-12">
                                <label style="font-size:11px;font-weight:600;color:#888">Conclusion / Impression</label>
                                <textarea name="conclusion" class="form-control" rows="3" placeholder="Summary impression…"></textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success mt-3"><i class="bi bi-check2-circle"></i> Save Result</button>
                    </form>
                @else
                    @foreach($imagery->results as $result)
                    <div style="padding:12px;border:1px solid #e8f8ef;border-radius:10px;background:#fafffe;margin-bottom:10px">
                        <div class="row g-2" style="font-size:13px">
                            <div class="col-12"><strong>Findings:</strong><br>{{ $result->result ?? '—' }}</div>
                            <div class="col-12"><strong>Conclusion:</strong><br>{{ $result->conclusion ?? '—' }}</div>
                        </div>
                        @if($result->images)
                            <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:10px">
                                @foreach($result->images as $imgPath)
                                    <img src="{{ asset('storage/'.$imgPath) }}" style="width:80px;height:80px;object-fit:cover;border-radius:8px;border:1px solid #e2e8f0"/>
                                @endforeach
                            </div>
                        @endif
                        @if($result->verified_at)
                            <div style="font-size:10px;color:#2eca6a;margin-top:8px"><i class="bi bi-shield-check"></i> Verified by {{ $result->verified_by }} at {{ $result->verified_at->format('d/m H:i') }}</div>
                        @else
                            <form method="POST" action="{{ route('imagery.update', $imagery->code) }}" class="mt-2">
                                @csrf @method('PATCH')
                                <input type="hidden" name="action" value="verify"/>
                                <button type="submit" class="btn btn-outline-success btn-sm"><i class="bi bi-shield-check"></i> Verify</button>
                            </form>
                        @endif
                    </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        @if($imagery->patient)
        <div class="card-emr" style="position:sticky;top:76px">
            <div class="card-hd" style="background:#f6f9ff"><div class="card-hd-title"><i class="bi bi-person-fill" style="color:#4154f1"></i> Patient</div></div>
            <div class="card-bd">
                <div style="font-weight:700;color:#012970">{{ $imagery->patient->full_name }}</div>
                <div style="font-size:11px;color:#aaa;margin-bottom:8px">{{ $imagery->patient->code }}</div>
                <a href="{{ route('patients.show', $imagery->patient->code) }}" class="btn btn-outline-primary btn-sm btn-w100"><i class="bi bi-person-fill"></i> View Patient</a>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
