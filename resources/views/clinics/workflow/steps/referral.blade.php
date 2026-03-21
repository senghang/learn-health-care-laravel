<div class="card-hd" style="flex-wrap:wrap;gap:8px;padding:14px 18px 10px">
    <div style="flex:1;min-width:0">
        <div class="card-hd-title">
            <i class="bi bi-send-fill" style="color:#ff9800"></i>ការបញ្ជូន
            <small style="font-size:11px;color:#bbb;font-weight:400">/ Referral</small>
        </div>
        <div style="font-size:10.5px;color:#aaa;margin-top:3px">ជំហាន {{ $stepIdx+1 }} នៃ {{ count($steps) }} / Step {{ $stepIdx+1 }} of {{ count($steps) }}</div>
    </div>
</div>
<div style="height:3px;background:#f0f2ff"><div style="height:100%;width:{{ round($stepIdx/count($steps)*100) }}%;background:linear-gradient(90deg,#4154f1,#717ff5)"></div></div>

<div class="card-bd">
    <form id="stepForm" method="POST" action="{{ route('workflow.step.save', [$visit->code, 'referral']) }}">
        @csrf @method('PATCH')

        {{-- Direction toggle --}}
        <div class="d-flex gap-2 mb-4 flex-wrap">
            <button type="button" onclick="setDirection('TO')" id="btnTO"
                class="btn" style="background:#fff8ee;color:#c97700;border:1px solid #ffd080;font-weight:700">
                <i class="bi bi-arrow-right-circle-fill"></i>ចេញ / Outbound (TO)
            </button>
            <button type="button" onclick="setDirection('FROM')" id="btnFROM"
                class="btn" style="background:#eef4ff;color:#2563eb;border:1px solid #aac4ff">
                <i class="bi bi-arrow-left-circle-fill"></i>ចូល / Inbound (FROM)
            </button>
        </div>
        <input type="hidden" name="direction" id="directionInput"
               value="{{ old('direction', $visit->referrals->first()?->direction ?? 'TO') }}"/>

        <div class="note note-warn mb-3">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>ប្រើ <strong>referral_number</strong> — មិនមែន <code>letter_number</code> / Use field <strong>referral_number</strong>, not letter_number</div>
        </div>

        <div class="sec-block" style="border-color:#ff9800;background:#fff8ee">
            <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                <span style="font-size:20px" id="dirIcon">➡️</span>
                <strong style="color:#c97700" id="dirLabel">ការបញ្ជូនចេញ / Outbound Referral (TO)</strong>
            </div>
            <div class="row g-3">
                <div class="col-12 col-sm-6 col-md-4">
                    <div class="fld">
                        <label class="flbl"><span class="km">លេខសំបុត្រ</span><span class="en">/ Referral Number</span><span class="req">*</span></label>
                        <input name="referral_number" class="form-control"
                               placeholder="HCPP/REF/2025/001"
                               value="{{ old('referral_number', $visit->referrals->first()?->referral_number ?? '') }}"/>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="fld">
                        <label class="flbl"><span class="km">ការដឹកជញ្ជូន</span><span class="en">/ Transport</span></label>
                        <select name="transportation" class="form-select">
                            @foreach(['Ambulance','Private Vehicle','Motorcycle','Walk'] as $t)
                            <option value="{{ $t }}" {{ old('transportation',$visit->referrals->first()?->transportation) === $t ? 'selected':'' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="fld">
                        <label class="flbl"><span class="km">បានទូរស័ព្ទ?</span><span class="en">/ Has Called?</span></label>
                        <select name="has_called" class="form-select">
                            <option value="1" {{ old('has_called',$visit->referrals->first()?->has_called) ? 'selected':'' }}>បាន / Yes</option>
                            <option value="0" {{ !old('has_called',$visit->referrals->first()?->has_called ?? true) ? 'selected':'' }}>មិនទាន់ / No</option>
                        </select>
                    </div>
                </div>
                <div class="col-12">
                    <div class="fld">
                        <label class="flbl"><span class="km">ហេតុផល</span><span class="en">/ Reason</span></label>
                        <textarea name="reason" class="form-control" rows="2" placeholder="ហេតុផល… / Reason…">{{ old('reason', $visit->referrals->first()?->reason ?? '') }}</textarea>
                    </div>
                </div>
                <div class="col-6 col-sm-3">
                    <div class="fld">
                        <label class="flbl"><span class="km">ឈ្មោះអ្នកទំនាក់</span><span class="en">/ Caretaker</span></label>
                        <input name="caretaker_name" class="form-control"
                               value="{{ old('caretaker_name', $visit->referrals->first()?->caretaker_name ?? '') }}"/>
                    </div>
                </div>
                <div class="col-6 col-sm-3">
                    <div class="fld">
                        <label class="flbl"><span class="km">ទូរស័ព្ទ</span><span class="en">/ Phone</span></label>
                        <input name="caretaker_phone" type="tel" class="form-control"
                               value="{{ old('caretaker_phone', $visit->referrals->first()?->caretaker_phone ?? '') }}"/>
                    </div>
                </div>
                <div class="col-6 col-sm-3">
                    <div class="fld">
                        <label class="flbl"><span class="km">បញ្ជូនដោយ</span><span class="en">/ Referred By</span></label>
                        <input name="referred_by" class="form-control"
                               value="{{ old('referred_by', $visit->referrals->first()?->referred_by ?? '') }}"/>
                    </div>
                </div>
                <div class="col-6 col-sm-3">
                    <div class="fld">
                        <label class="flbl"><span class="km">ទទួលដោយ</span><span class="en">/ Received By</span></label>
                        <input name="received_by" class="form-control"
                               value="{{ old('received_by', $visit->referrals->first()?->received_by ?? '') }}"/>
                    </div>
                </div>
                <div class="col-12">
                    <div class="fld">
                        <label class="flbl"><span class="km">ថ្នាំ</span><span class="en">/ Medications Sent</span></label>
                        <input name="medications" class="form-control"
                               value="{{ old('medications', $visit->referrals->first()?->medications ?? '') }}"/>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function setDirection(dir) {
    document.getElementById('directionInput').value = dir;
    if (dir === 'TO') {
        document.getElementById('dirIcon').textContent = '➡️';
        document.getElementById('dirLabel').textContent = 'ការបញ្ជូនចេញ / Outbound Referral (TO)';
        document.getElementById('btnTO').style.fontWeight = '700';
        document.getElementById('btnFROM').style.fontWeight = '400';
    } else {
        document.getElementById('dirIcon').textContent = '⬅️';
        document.getElementById('dirLabel').textContent = 'ការបញ្ជូនចូល / Inbound Referral (FROM)';
        document.getElementById('btnFROM').style.fontWeight = '700';
        document.getElementById('btnTO').style.fontWeight = '400';
    }
}
</script>
@endpush
