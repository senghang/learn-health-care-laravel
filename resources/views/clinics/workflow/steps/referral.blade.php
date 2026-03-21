@php
    $progressPct      = round($stepIdx / count($steps) * 100);
    $progressWidth    = $progressPct . '%';
    $saveUrl          = url('/workflow/' . $visit->code . '/referral/save');
    $firstRef         = $referrals->first() ?? null;
    $direction        = old('direction',        $firstRef?->direction          ?? 'TO');
    $refNumber        = old('referral_number',  $firstRef?->referral_number    ?? '');
    $transport        = old('transportation',   $firstRef?->transportation     ?? 'Ambulance');
    $hasCalled        = old('has_called',       $firstRef?->has_called         ?? true);
    $reason           = old('reason',           $firstRef?->reason             ?? '');
    $caretakerName    = old('caretaker_name',   $firstRef?->caretaker_name     ?? '');
    $caretakerPhone   = old('caretaker_phone',  $firstRef?->caretaker_phone    ?? '');
    $referredBy       = old('referred_by',      $firstRef?->referred_by        ?? auth()->user()?->name ?? '');
    $receivedBy       = old('received_by',      $firstRef?->received_by        ?? '');
    $medications      = old('medications',      $firstRef?->medications        ?? '');
    $referredAt       = old('referred_at',      $firstRef?->referred_at?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i'));
    $transportOptions = ['Ambulance', 'Private Vehicle', 'Motorcycle', 'Walk'];
    $refCount         = $referrals->count();
    $dirIcon          = $direction === 'FROM' ? '⬅️' : '➡️';
    $dirLabel         = $direction === 'FROM' ? 'ការបញ្ជូនចូល / Inbound Referral (FROM)' : 'ការបញ្ជូនចេញ / Outbound Referral (TO)';
    $btnTOWeight      = $direction === 'TO'   ? '700' : '400';
    $btnFROMWeight    = $direction === 'FROM' ? '700' : '400';
    $hasCalledYes     = $hasCalled ? 'selected' : '';
    $hasCalledNo      = !$hasCalled ? 'selected' : '';
@endphp

{{-- ── Header ─────────────────────────────────────────────────────────────── --}}
<div class="card-hd" style="flex-wrap:wrap;gap:8px;padding:14px 18px 10px">
    <div style="flex:1;min-width:0">
        <div class="card-hd-title">
            <i class="bi bi-send-fill" style="color:#ff9800"></i>ការបញ្ជូន
            <small style="font-size:11px;color:#bbb;font-weight:400">/ Referral</small>
        </div>
        <div style="font-size:10.5px;color:#aaa;margin-top:3px">
            ជំហាន {{ $stepIdx+1 }} នៃ {{ count($steps) }} / Step {{ $stepIdx+1 }} of {{ count($steps) }}
        </div>
    </div>
    @if($refCount > 0)
    <span style="font-size:11px;background:#fff3e8;color:#ff9800;padding:3px 10px;border-radius:20px;border:1px solid #ffd0a8;font-weight:700;flex-shrink:0">
        <i class="bi bi-check-circle-fill"></i> {{ $refCount }} Referral{{ $refCount > 1 ? 's' : '' }} Saved
    </span>
    @endif
</div>
<div style="height:3px;background:#f0f2ff">
    <div style="height:100%;width:{{ $progressWidth }};background:linear-gradient(90deg,#4154f1,#717ff5)"></div>
</div>

<div class="card-bd">

    {{-- ── Saved referrals summary ─────────────────────────────────────────── --}}
    @if($refCount > 0)
    <div style="margin-bottom:20px">
        <div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:#ff9800;margin-bottom:10px">
            <i class="bi bi-clock-history"></i> ការបញ្ជូនដែលបានដាក់ / Saved Referrals
        </div>
        @foreach($referrals as $ref)
        @php
            $refDir = $ref->direction === 'FROM' ? '⬅️ Inbound (FROM)' : '➡️ Outbound (TO)';
        @endphp
        <div style="border:1.5px solid #ffd0a8;background:#fff8ee;border-radius:10px;padding:12px 16px;margin-bottom:8px">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                <code style="font-size:11px;color:#ff9800;background:#fff3e8;padding:2px 8px;border-radius:6px;border:1px solid #ffd0a8">{{ $ref->code }}</code>
                <span style="font-size:12px;font-weight:700;color:#c97700">{{ $refDir }}</span>
                @if($ref->referral_number)
                <span style="font-size:11px;color:#555">No: {{ $ref->referral_number }}</span>
                @endif
                <span style="font-size:10.5px;color:#aaa;margin-left:auto">{{ $ref->created_at?->format('d/m/Y H:i') }}</span>
            </div>
            @if($ref->reason)
            <div style="font-size:11px;color:#666;margin-top:6px;padding-top:6px;border-top:1px solid #ffd0a8">
                {{ Str::limit($ref->reason, 120) }}
            </div>
            @endif
        </div>
        @endforeach

        <div class="note note-info mb-4">
            <i class="bi bi-info-circle-fill"></i>
            <div>ការបំពេញខាងក្រោមនឹងបង្កើតការបញ្ជូនថ្មី / Submitting below creates an additional referral.</div>
        </div>
    </div>
    @endif

    {{-- ── New referral form ───────────────────────────────────────────────── --}}
    <form id="stepForm" method="POST" action="{{ $saveUrl }}">
        @csrf
        @method('PATCH')

        {{-- Direction toggle --}}
        <div class="d-flex gap-2 mb-4 flex-wrap">
            <button type="button" onclick="setDirection('TO')" id="btnTO"
                class="btn" style="background:#fff8ee;color:#c97700;border:1px solid #ffd080;font-weight:{{ $btnTOWeight }}">
                <i class="bi bi-arrow-right-circle-fill"></i> ចេញ / Outbound (TO)
            </button>
            <button type="button" onclick="setDirection('FROM')" id="btnFROM"
                class="btn" style="background:#eef4ff;color:#2563eb;border:1px solid #aac4ff;font-weight:{{ $btnFROMWeight }}">
                <i class="bi bi-arrow-left-circle-fill"></i> ចូល / Inbound (FROM)
            </button>
        </div>
        <input type="hidden" name="direction" id="directionInput" value="{{ $direction }}"/>

        <div class="sec-block" style="border-left:4px solid #ff9800;background:#fff8ee;border-radius:10px;padding:14px 16px">
            <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                <span style="font-size:20px" id="dirIcon">{{ $dirIcon }}</span>
                <strong style="color:#c97700" id="dirLabel">{{ $dirLabel }}</strong>
            </div>

            <div class="row g-3">
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="fld">
                        <label class="flbl"><span class="km">លេខសំបុត្រ</span><span class="en">/ Referral No.</span><span class="req">*</span></label>
                        <input name="referral_number" class="form-control"
                               placeholder="HCPP/REF/2025/001" value="{{ $refNumber }}"
                               required data-error-msg="Referral Number"/>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="fld">
                        <label class="flbl"><span class="km">ថ្ងៃបញ្ជូន</span><span class="en">/ Referred At</span></label>
                        <input type="datetime-local" name="referred_at" class="form-control" value="{{ $referredAt }}"/>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="fld">
                        <label class="flbl"><span class="km">ការដឹកជញ្ជូន</span><span class="en">/ Transport</span></label>
                        <select name="transportation" class="form-select">
                            @foreach($transportOptions as $t)
                            @php $sel = $transport === $t ? 'selected' : ''; @endphp
                            <option value="{{ $t }}" {{ $sel }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="fld">
                        <label class="flbl"><span class="km">បានទូរស័ព្ទ?</span><span class="en">/ Has Called?</span></label>
                        <select name="has_called" class="form-select">
                            <option value="1" {{ $hasCalledYes }}>បាន / Yes</option>
                            <option value="0" {{ $hasCalledNo }}>មិនទាន់ / No</option>
                        </select>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="fld">
                        <label class="flbl"><span class="km">បញ្ជូនដោយ</span><span class="en">/ Referred By</span></label>
                        <input name="referred_by" class="form-control" value="{{ $referredBy }}"/>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="fld">
                        <label class="flbl"><span class="km">ទទួលដោយ</span><span class="en">/ Received By</span></label>
                        <input name="received_by" class="form-control" value="{{ $receivedBy }}"/>
                    </div>
                </div>
                <div class="col-12">
                    <div class="fld">
                        <label class="flbl"><span class="km">ហេតុផល</span><span class="en">/ Reason</span></label>
                        <textarea name="reason" class="form-control" rows="3"
                                  placeholder="ហេតុផល… / Reason for referral…">{{ $reason }}</textarea>
                    </div>
                </div>

                {{-- Caretaker --}}
                <div style="width:100%;padding:0 12px">
                    <div style="font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:#aaa;margin:4px 0 8px">
                        អ្នកអមជូន / Caretaker
                    </div>
                </div>
                <div class="col-6 col-sm-4">
                    <div class="fld">
                        <label class="flbl"><span class="km">ឈ្មោះ</span><span class="en">/ Name</span></label>
                        <input name="caretaker_name" class="form-control" value="{{ $caretakerName }}"/>
                    </div>
                </div>
                <div class="col-6 col-sm-4">
                    <div class="fld">
                        <label class="flbl"><span class="km">ទូរស័ព្ទ</span><span class="en">/ Phone</span></label>
                        <input name="caretaker_phone" type="tel" class="form-control" value="{{ $caretakerPhone }}"/>
                    </div>
                </div>
                <div class="col-12 col-sm-4">
                    <div class="fld">
                        <label class="flbl"><span class="km">ថ្នាំដែលបន្ត</span><span class="en">/ Medications Sent</span></label>
                        <input name="medications" class="form-control" value="{{ $medications }}"
                               placeholder="Medications sent with patient"/>
                    </div>
                </div>
            </div>
        </div>

    </form>
</div>

<script>
function setDirection(dir) {
    document.getElementById('directionInput').value = dir;
    if (dir === 'TO') {
        document.getElementById('dirIcon').textContent   = '➡️';
        document.getElementById('dirLabel').textContent  = 'ការបញ្ជូនចេញ / Outbound Referral (TO)';
        document.getElementById('btnTO').style.fontWeight   = '700';
        document.getElementById('btnFROM').style.fontWeight = '400';
    } else {
        document.getElementById('dirIcon').textContent   = '⬅️';
        document.getElementById('dirLabel').textContent  = 'ការបញ្ជូនចូល / Inbound Referral (FROM)';
        document.getElementById('btnFROM').style.fontWeight = '700';
        document.getElementById('btnTO').style.fontWeight   = '400';
    }
}
</script>
