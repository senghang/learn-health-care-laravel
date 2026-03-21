@php
    $referrals        = $referrals        ?? collect([]);
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
    $referredAt       = old('referred_at',      df_input_dt($firstRef?->referred_at) ?: df_now_input());
    $transportOptions = ['Ambulance' => 'Ambulance', 'Private Vehicle' => 'Private Vehicle', 'Motorcycle' => 'Motorcycle', 'Walk' => 'Walk'];
    $refCount         = $referrals->count();
    $dirIcon          = $direction === 'FROM' ? '⬅️' : '➡️';
    $dirLabel         = $direction === 'FROM' ? 'ការបញ្ជូនចូល / Inbound Referral (FROM)' : 'ការបញ្ជូនចេញ / Outbound Referral (TO)';
@endphp

<x-step.card step-id="referral" :visit="$visit" :step-idx="$stepIdx" :steps="$steps"
    icon="bi-send-fill" icon-color="#ff9800"
    km="ការបញ្ជូន" en="Referral"
    :badge="$refCount > 0 ? $refCount . ' saved' : null"
    badge-color="#ff9800">

    {{-- Direction toggle --}}
    <div class="d-flex gap-2 mb-4 flex-wrap">
        <button type="button" id="btnTO" onclick="setDirection('TO')"
                class="btn"
                style="background:#fff8ee;color:#c97700;border:1px solid #ffd080;font-weight:{{ $direction === 'TO' ? '700' : '400' }}">
            <i class="bi bi-arrow-right-circle-fill"></i> ចេញ / Outbound (TO)
        </button>
        <button type="button" id="btnFROM" onclick="setDirection('FROM')"
                class="btn"
                style="background:#eef4ff;color:#2563eb;border:1px solid #aac4ff;font-weight:{{ $direction === 'FROM' ? '700' : '400' }}">
            <i class="bi bi-arrow-left-circle-fill"></i> ចូល / Inbound (FROM)
        </button>
    </div>
    <input type="hidden" name="direction" id="directionInput" value="{{ $direction }}"/>

    <x-step.note type="warning">
        ប្រើ <strong>referral_number</strong> — មិនមែន letter_number /
        Use field <strong>referral_number</strong>, not letter_number
    </x-step.note>

    <x-form.section title="Referral Details" km="ព័ត៌មានបញ្ជូន" color="#ff9800">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px">
            <span style="font-size:20px" id="dirIcon">{{ $dirIcon }}</span>
            <strong style="color:#c97700" id="dirLabel">{{ $dirLabel }}</strong>
        </div>
        <div class="row g-3">
            <div class="col-12 col-sm-6 col-md-4">
                <x-form.field name="referral_number" km="លេខសំបុត្រ" en="Referral Number"
                              :required="true" error-msg="Referral Number"
                              placeholder="HCPP/REF/2025/001" :value="$refNumber"/>
            </div>
            <div class="col-6 col-md-4">
                <x-form.select name="transportation" km="ការដឹកជញ្ជូន" en="Transport"
                               :options="$transportOptions" :value="$transport"/>
            </div>
            <div class="col-6 col-md-4">
                <x-form.select name="has_called" km="បានទូរស័ព្ទ?" en="Has Called?"
                               :options="['1' => 'បាន / Yes', '0' => 'មិនទាន់ / No']"
                               :value="$hasCalled ? '1' : '0'"/>
            </div>
            <div class="col-12">
                <x-form.field name="reason" km="ហេតុផល" en="Reason"
                              :textarea="true" rows="2"
                              placeholder="ហេតុផល… / Reason…" :value="$reason"/>
            </div>
            <div class="col-6 col-sm-3">
                <x-form.field name="caretaker_name" km="ឈ្មោះអ្នកទំនាក់" en="Caretaker"
                              :value="$caretakerName"/>
            </div>
            <div class="col-6 col-sm-3">
                <x-form.field name="caretaker_phone" km="ទូរស័ព្ទ" en="Phone"
                              type="tel" :value="$caretakerPhone"/>
            </div>
            <div class="col-6 col-sm-3">
                <x-form.field name="referred_by" km="បញ្ជូនដោយ" en="Referred By"
                              :value="$referredBy"/>
            </div>
            <div class="col-6 col-sm-3">
                <x-form.field name="received_by" km="ទទួលដោយ" en="Received By"
                              :value="$receivedBy"/>
            </div>
            <div class="col-12 col-sm-6">
                <x-form.field name="referred_at" km="ពេលវេលា" en="Referred At"
                              type="datetime-local" :value="$referredAt"/>
            </div>
            <div class="col-12">
                <x-form.field name="medications" km="ថ្នាំ" en="Medications Sent"
                              :value="$medications"/>
            </div>
        </div>
    </x-form.section>

</x-step.card>

<script>
function setDirection(dir) {
    document.getElementById('directionInput').value = dir;
    const isFrom = dir === 'FROM';
    document.getElementById('dirIcon').textContent  = isFrom ? '⬅️' : '➡️';
    document.getElementById('dirLabel').textContent = isFrom
        ? 'ការបញ្ជូនចូល / Inbound Referral (FROM)'
        : 'ការបញ្ជូនចេញ / Outbound Referral (TO)';
    document.getElementById('btnTO').style.fontWeight   = isFrom ? '400' : '700';
    document.getElementById('btnFROM').style.fontWeight = isFrom ? '700' : '400';
}
</script>
