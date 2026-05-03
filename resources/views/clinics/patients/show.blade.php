@extends('clinics.layout.app')
@section('title', $patient->surname . ', ' . $patient->name)

@section('content')

@php
    $ageStr = $patient->birthdate
        ? $patient->birthdate->age . 'y (' . $patient->birthdate->format('d/m/Y') . ')'
        : '—';
    $colors    = ['#4154f1','#2eca6a','#ff771d','#e74c3c','#9b59b6','#00bcd4','#f39c12','#1abc9c'];
    $color     = $colors[abs(crc32($patient->code)) % count($colors)];
    $initials  = strtoupper(substr($patient->surname,0,1).substr($patient->name,0,1));
    $lastVisit = $patient->visits->first();
@endphp

<x-ui.page-header
    :km="$patient->surname.', '.$patient->name"
    title="Patient Profile"
    :breadcrumbs="[
        ['label' => 'ដើម',      'url' => url('/')],
        ['label' => 'អ្នកជំងឺ', 'url' => url('/patients')],
        ['label' => $patient->code],
    ]">
    <x-slot:actions>
        <x-ui.button href="{{ url('/patients/' . $patient->code . '/edit') }}" variant="secondary" size="sm">
            <x-slot:icon><i class="bi bi-pencil-fill"></i></x-slot:icon>
            កែប្រែ
        </x-ui.button>
        <x-ui.button href="{{ url('/workflow/create') }}?patient_code={{ $patient->code }}" variant="primary" size="sm">
            <x-slot:icon><i class="bi bi-plus-lg"></i></x-slot:icon>
            ការចូលថ្មី
        </x-ui.button>
    </x-slot:actions>
</x-ui.page-header>

<div class="row g-3">

{{-- ══════════════════════════════════════════════════════════════════════════
     LEFT: Profile + Address + Contacts + IDs
     ══════════════════════════════════════════════════════════════════════════ --}}
<div class="col-12 col-lg-4">

  {{-- ── Avatar + Core Info ────────────────────────────────────────────────── --}}
  <x-ui.card class="mb-3">
    <div style="text-align:center;padding:24px 18px 16px">
      <div style="width:72px;height:72px;border-radius:18px;background:linear-gradient(135deg,{{ $color }},{{ $color }}cc);color:#fff;font-size:26px;font-weight:800;display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
        {{ $initials }}
      </div>
      <div style="font-size:18px;font-weight:800;color:#012970">{{ $patient->surname }}, {{ $patient->name }}</div>
      <div style="font-size:12px;color:#aaa;margin-top:3px;font-family:monospace">{{ $patient->code }}</div>

      <div style="display:flex;justify-content:center;gap:6px;margin-top:10px;flex-wrap:wrap">
        <x-ui.badge :variant="$patient->sex === 'M' ? 'primary' : 'warning'">
          {{ $patient->sex === 'M' ? '♂ ប្រុស' : '♀ ស្រី' }}
        </x-ui.badge>
        @if(($patient->visits_count ?? $patient->visits->count()) > 0)
        <x-ui.badge variant="primary">
          {{ $patient->visits_count ?? $patient->visits->count() }} Visits
        </x-ui.badge>
        @else
        <x-ui.badge variant="success">New Patient</x-ui.badge>
        @endif
        @if($patient->spid)
        <x-ui.badge variant="warning">SPID</x-ui.badge>
        @endif
        @if($patient->blood_type)
        <x-ui.badge variant="danger">{{ $patient->blood_type }}</x-ui.badge>
        @endif
        @if($patient->status === 'Active')
        <x-ui.badge variant="success" dot>{{ $patient->status }}</x-ui.badge>
        @else
        <x-ui.badge variant="secondary">{{ $patient->status }}</x-ui.badge>
        @endif
      </div>
    </div>

    {{-- Info rows --}}
    <div style="border-top:1px solid #f0f2ff">
      @php
        $rows = [
          ['bi-calendar3',       'ថ្ងៃខែឆ្នាំ / DOB', $ageStr],
          ['bi-telephone-fill',  'ទូរស័ព្ទ / Phone',   $patient->phone ?? '—'],
          ['bi-flag-fill',       'សញ្ជាតិ / Nationality', $patient->nationality ?? '—'],
          ['bi-briefcase-fill',  'មុខរបរ / Occupation', $patient->occupation ?? '—'],
          ['bi-heart-fill',      'អាពាហ៍ / Marital',   $patient->marital_status ?? '—'],
          ['bi-shield-fill',     'SPID',                $patient->spid ?? '—'],
        ];
      @endphp
      @foreach($rows as [$icon, $label, $val])
      <div style="display:flex;align-items:center;gap:10px;padding:9px 16px;border-bottom:1px solid #f8f9ff">
        <i class="bi {{ $icon }}" style="width:16px;text-align:center;color:#aaa;font-size:12px;flex-shrink:0"></i>
        <div style="flex:1;min-width:0">
          <div style="font-size:10px;color:#bbb">{{ $label }}</div>
          <div style="font-size:12.5px;font-weight:{{ $val === '—' ? '400' : '600' }};color:{{ $val === '—' ? '#ccc' : '#222' }}">{{ $val }}</div>
        </div>
      </div>
      @endforeach
    </div>
  </x-ui.card>

  {{-- ── Address ───────────────────────────────────────────────────────────── --}}
  @if($patient->address)
  <x-ui.card title="Address" km="អាសយដ្ឋាន" icon="bi-geo-alt-fill" icon-color="#ff771d" class="mb-3">
    <div style="font-size:12.5px;line-height:1.8;color:#555">
      @php $addr = $patient->address; @endphp
      @if($addr->house_number || $addr->street_number)
        <div><i class="bi bi-house-fill" style="color:#bbb;width:14px"></i>
          ផ្ទះ{{ $addr->house_number ?? '' }} ផ្លូវ{{ $addr->street_number ?? '' }}
        </div>
      @endif
      @if($addr->village_name)  <div><i class="bi bi-map" style="color:#bbb;width:14px"></i> ភូមិ{{ $addr->village_name }}</div> @endif
      @if($addr->commune_name)  <div><i class="bi bi-signpost" style="color:#bbb;width:14px"></i> ឃុំ{{ $addr->commune_name }}</div> @endif
      @if($addr->district_name) <div><i class="bi bi-building" style="color:#bbb;width:14px"></i> ស្រុក{{ $addr->district_name }}</div> @endif
      @if($addr->province_name) <div><i class="bi bi-geo" style="color:#bbb;width:14px"></i> ខេត្ត{{ $addr->province_name }}</div> @endif
      @if(!$addr->province_name && !$addr->district_name && !$addr->village_name)
        <div style="color:#ccc;font-size:11px">— No address recorded —</div>
      @endif
    </div>
  </x-ui.card>
  @endif

  {{-- ── ID Cards ──────────────────────────────────────────────────────────── --}}
  @if($patient->identifications->isNotEmpty())
  <x-ui.card title="ID Cards" km="ប័ណ្ណ" icon="bi-credit-card-fill" icon-color="#9b59b6" :no-padding="true" class="mb-3">
    @foreach($patient->identifications as $idCard)
    <div style="display:flex;align-items:center;gap:10px;padding:10px 16px;border-bottom:1px solid #f8f9ff">
      <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#9b59b6,#9b59b6cc);color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0">
        <i class="bi bi-credit-card"></i>
      </div>
      <div style="flex:1;min-width:0">
        <div style="font-size:12.5px;font-weight:700;color:#012970">{{ $idCard->card_code }}</div>
        <div style="font-size:10.5px;color:#aaa">{{ $idCard->card_type }}</div>
      </div>
    </div>
    @endforeach
  </x-ui.card>
  @endif

  {{-- ── Emergency Contacts ────────────────────────────────────────────────── --}}
  @if($patient->contacts->isNotEmpty())
  <x-ui.card title="Contacts" km="ទំនាក់ទំនង" icon="bi-people-fill" icon-color="#2eca6a" :no-padding="true" class="mb-3">
    @foreach($patient->contacts as $contact)
    <div style="display:flex;align-items:center;gap:10px;padding:10px 16px;border-bottom:1px solid #f8f9ff">
      <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,{{ $contact->is_emergency ? '#e74c3c' : '#2eca6a' }},{{ $contact->is_emergency ? '#c0392b' : '#27ae60' }});color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0">
        <i class="bi bi-{{ $contact->is_emergency ? 'exclamation-triangle-fill' : 'person-fill' }}"></i>
      </div>
      <div style="flex:1;min-width:0">
        <div style="font-size:12.5px;font-weight:700;color:#012970">
          {{ $contact->contact_name }}
          @if($contact->is_emergency)
            <span style="font-size:9px;background:#fce4ec;color:#e74c3c;padding:1px 6px;border-radius:8px;margin-left:4px">EMERGENCY</span>
          @endif
        </div>
        <div style="font-size:10.5px;color:#aaa">
          {{ $contact->relationship ?? '' }}
          @if($contact->contact_phone) · {{ $contact->contact_phone }} @endif
        </div>
      </div>
    </div>
    @endforeach
  </x-ui.card>
  @endif

</div>{{-- /col-lg-4 --}}

{{-- ══════════════════════════════════════════════════════════════════════════
     RIGHT: Tabbed History
     ══════════════════════════════════════════════════════════════════════════ --}}
<div class="col-12 col-lg-8">

  @php
    $opdVisits = $patient->visits->where('visit_type', 'OPD');
    $ipdVisits = $patient->visits->where('visit_type', 'IPD');
  @endphp

  <x-ui.card :no-padding="true">
    {{-- Tab Nav --}}
    <div style="display:flex;align-items:center;justify-content:space-between;border-bottom:2px solid #f0f2ff;padding:0 16px;flex-wrap:wrap;gap:4px">
      <div style="display:flex;gap:0;overflow-x:auto">
        @php
          $tabs = [
            ['id'=>'opd',    'icon'=>'bi-person-fill',       'label'=>'OPD',          'count'=>$opdVisits->count(),                   'color'=>'#4154f1'],
            ['id'=>'ipd',    'icon'=>'bi-bed-fill',          'label'=>'IPD',          'count'=>$ipdVisits->count(),                   'color'=>'#ff771d'],
            ['id'=>'rx',     'icon'=>'bi-capsule-fill',      'label'=>'Prescriptions','count'=>$patient->prescriptions->count(),       'color'=>'#e91e8c'],
            ['id'=>'lab',    'icon'=>'bi-eyedropper',        'label'=>'Lab Results',  'count'=>$patient->laboratories->count(),        'color'=>'#2eca6a'],
            ['id'=>'billing','icon'=>'bi-receipt-cutoff',    'label'=>'Billing',      'count'=>$patient->invoices->count(),            'color'=>'#9b59b6'],
          ];
        @endphp
        @foreach($tabs as $tab)
        <button onclick="switchTab('{{ $tab['id'] }}')" id="tab-btn-{{ $tab['id'] }}"
                style="display:flex;align-items:center;gap:6px;padding:12px 14px;font-size:12px;font-weight:700;border:none;background:none;cursor:pointer;white-space:nowrap;border-bottom:2px solid transparent;margin-bottom:-2px;color:#94a3b8;transition:all .15s"
                class="patient-tab-btn">
          <i class="bi {{ $tab['icon'] }}"></i>
          {{ $tab['label'] }}
          @if($tab['count'] > 0)
          <span style="background:{{ $tab['color'] }}18;color:{{ $tab['color'] }};font-size:10px;font-weight:800;padding:1px 7px;border-radius:10px">{{ $tab['count'] }}</span>
          @endif
        </button>
        @endforeach
      </div>
      <x-ui.button href="{{ url('/workflow/create') }}?patient_code={{ $patient->code }}" variant="primary" size="sm">
        <x-slot:icon><i class="bi bi-plus-lg"></i></x-slot:icon>
        New Visit
      </x-ui.button>
    </div>

    {{-- ── Tab: OPD History ──────────────────────────────────────────────── --}}
    <div id="tab-opd" class="patient-tab-pane">
      @forelse($opdVisits as $visit)
      @php $isActive = is_null($visit->discharged_at); $doneCount = count($visit->done_steps ?? []); @endphp
      <a href="{{ url('/workflow/'.$visit->code) }}" class="visit-row" style="text-decoration:none">
        <div class="v-avatar" style="background:linear-gradient(135deg,#4154f1,#717ff5);border-radius:10px">
          <i class="bi bi-person-fill" style="font-size:15px"></i>
        </div>
        <div class="v-info">
          <div class="v-name" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
            <code style="font-size:12px;color:#4154f1">{{ $visit->code }}</code>
            <x-ui.badge variant="primary">OPD</x-ui.badge>
            @if($isActive)
            <x-ui.badge variant="success" dot>Active</x-ui.badge>
            @else
            <x-ui.badge variant="secondary">Done</x-ui.badge>
            @endif
          </div>
          <div class="v-meta">
            <i class="bi bi-calendar3" style="font-size:10px"></i>
            {{ $visit->admitted_at?->format('d/m/Y H:i') ?? '—' }}
            @if($visit->admission_type) · {{ $visit->admission_type }} @endif
            @if($doneCount > 0) · <span style="color:#2eca6a">{{ $doneCount }} steps done</span> @endif
          </div>
        </div>
        <i class="bi bi-chevron-right" style="color:#ddd;flex-shrink:0"></i>
      </a>
      @empty
      <div style="text-align:center;padding:40px 24px;color:#bbb">
        <div style="font-size:36px;margin-bottom:10px;opacity:.3">🏥</div>
        <div style="font-size:13px;font-weight:600;margin-bottom:12px">No OPD visits yet</div>
        <x-ui.button href="{{ url('/workflow/create') }}?patient_code={{ $patient->code }}" variant="primary" size="sm">
          <x-slot:icon><i class="bi bi-plus-lg"></i></x-slot:icon>
          New OPD Visit
        </x-ui.button>
      </div>
      @endforelse
    </div>

    {{-- ── Tab: IPD Admissions ───────────────────────────────────────────── --}}
    <div id="tab-ipd" class="patient-tab-pane" style="display:none">
      @forelse($ipdVisits as $visit)
      @php $isActive = is_null($visit->discharged_at); @endphp
      <a href="{{ url('/workflow/'.$visit->code) }}" class="visit-row" style="text-decoration:none">
        <div class="v-avatar" style="background:linear-gradient(135deg,#ff771d,#e65a00);border-radius:10px">
          <i class="bi bi-bed-fill" style="font-size:15px"></i>
        </div>
        <div class="v-info">
          <div class="v-name" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
            <code style="font-size:12px;color:#ff771d">{{ $visit->code }}</code>
            <x-ui.badge variant="warning">IPD</x-ui.badge>
            @if($isActive)
            <x-ui.badge variant="success" dot>Active</x-ui.badge>
            @else
            <x-ui.badge variant="secondary">Done</x-ui.badge>
            @endif
          </div>
          <div class="v-meta">
            <i class="bi bi-calendar3" style="font-size:10px"></i>
            {{ $visit->admitted_at?->format('d/m/Y H:i') ?? '—' }}
            @if($visit->discharged_at) · Discharged {{ $visit->discharged_at->format('d/m/Y') }} @endif
          </div>
        </div>
        <i class="bi bi-chevron-right" style="color:#ddd;flex-shrink:0"></i>
      </a>
      @empty
      <div style="text-align:center;padding:40px 24px;color:#bbb">
        <div style="font-size:36px;margin-bottom:10px;opacity:.3">🛏️</div>
        <div style="font-size:13px;font-weight:600">No IPD admissions</div>
      </div>
      @endforelse
    </div>

    {{-- ── Tab: Prescriptions ────────────────────────────────────────────── --}}
    <div id="tab-rx" class="patient-tab-pane" style="display:none">
      @forelse($patient->prescriptions as $rx)
      <a href="{{ route('prescriptions.show', $rx->code) }}" class="visit-row" style="text-decoration:none">
        <div class="v-avatar" style="background:linear-gradient(135deg,#e91e8c,#c2185b);border-radius:10px">
          <i class="bi bi-capsule-fill" style="font-size:15px"></i>
        </div>
        <div class="v-info">
          <div class="v-name" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
            <code style="font-size:12px;color:#e91e8c">{{ $rx->code }}</code>
            @if($rx->dispensed_status === 'dispensed')
              <x-ui.badge variant="success">Dispensed</x-ui.badge>
            @elseif($rx->dispensed_status === 'partial')
              <x-ui.badge variant="warning">Partial</x-ui.badge>
            @else
              <x-ui.badge variant="primary">Pending</x-ui.badge>
            @endif
          </div>
          <div class="v-meta">
            <i class="bi bi-calendar3" style="font-size:10px"></i>
            {{ $rx->created_at?->format('d/m/Y H:i') ?? '—' }}
            @if($rx->visit_code) · {{ $rx->visit_code }} @endif
          </div>
        </div>
        <i class="bi bi-chevron-right" style="color:#ddd;flex-shrink:0"></i>
      </a>
      @empty
      <div style="text-align:center;padding:40px 24px;color:#bbb">
        <div style="font-size:36px;margin-bottom:10px;opacity:.3">💊</div>
        <div style="font-size:13px;font-weight:600">No prescriptions found</div>
      </div>
      @endforelse
    </div>

    {{-- ── Tab: Lab Results ──────────────────────────────────────────────── --}}
    <div id="tab-lab" class="patient-tab-pane" style="display:none">
      @forelse($patient->laboratories as $lab)
      <a href="{{ route('laboratory.show', $lab->code) }}" class="visit-row" style="text-decoration:none">
        <div class="v-avatar" style="background:linear-gradient(135deg,#2eca6a,#16a34a);border-radius:10px">
          <i class="bi bi-eyedropper" style="font-size:15px"></i>
        </div>
        <div class="v-info">
          <div class="v-name" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
            <code style="font-size:12px;color:#2eca6a">{{ $lab->code }}</code>
            @php $labColors = ['pending'=>['#f0f2ff','#4154f1'],'collected'=>['#fff3e8','#ff771d'],'resulted'=>['#e8f8ef','#2eca6a'],'verified'=>['#f0e8ff','#9b59b6']]; $lc = $labColors[$lab->status] ?? ['#f5f5f5','#999']; @endphp
            <span class="badge-s" style="background:{{ $lc[0] }};color:{{ $lc[1] }}">{{ ucfirst($lab->status) }}</span>
          </div>
          <div class="v-meta">
            <i class="bi bi-calendar3" style="font-size:10px"></i>
            {{ $lab->created_at?->format('d/m/Y H:i') ?? '—' }}
            @if($lab->category) · {{ $lab->category }} @endif
          </div>
        </div>
        <i class="bi bi-chevron-right" style="color:#ddd;flex-shrink:0"></i>
      </a>
      @empty
      <div style="text-align:center;padding:40px 24px;color:#bbb">
        <div style="font-size:36px;margin-bottom:10px;opacity:.3">🧪</div>
        <div style="font-size:13px;font-weight:600">No lab results found</div>
      </div>
      @endforelse
    </div>

    {{-- ── Tab: Billing History ──────────────────────────────────────────── --}}
    <div id="tab-billing" class="patient-tab-pane" style="display:none">
      @forelse($patient->invoices as $inv)
      <a href="{{ route('invoices.show', $inv->code) }}" class="visit-row" style="text-decoration:none">
        <div class="v-avatar" style="background:linear-gradient(135deg,#9b59b6,#7c3aed);border-radius:10px">
          <i class="bi bi-receipt-cutoff" style="font-size:15px"></i>
        </div>
        <div class="v-info">
          <div class="v-name" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
            <code style="font-size:12px;color:#9b59b6">{{ $inv->code }}</code>
            @php $invColors = ['draft'=>['#f5f5f5','#999'],'pending'=>['#fff3e8','#ff771d'],'partial'=>['#fff8e1','#f59e0b'],'paid'=>['#e8f8ef','#2eca6a'],'voided'=>['#fce4ec','#e74c3c']]; $ic = $invColors[$inv->status] ?? ['#f5f5f5','#999']; @endphp
            <span class="badge-s" style="background:{{ $ic[0] }};color:{{ $ic[1] }}">{{ ucfirst($inv->status) }}</span>
            <span style="font-size:12px;font-weight:700;color:#012970">${{ number_format($inv->grand_total, 2) }}</span>
          </div>
          <div class="v-meta">
            <i class="bi bi-calendar3" style="font-size:10px"></i>
            {{ $inv->created_at?->format('d/m/Y H:i') ?? '—' }}
            @if($inv->visit_code) · {{ $inv->visit_code }} @endif
          </div>
        </div>
        <i class="bi bi-chevron-right" style="color:#ddd;flex-shrink:0"></i>
      </a>
      @empty
      <div style="text-align:center;padding:40px 24px;color:#bbb">
        <div style="font-size:36px;margin-bottom:10px;opacity:.3">🧾</div>
        <div style="font-size:13px;font-weight:600">No billing records found</div>
      </div>
      @endforelse
    </div>

  </x-ui.card>
</div>{{-- /col-lg-8 --}}

</div>{{-- /row --}}

@endsection

@push('scripts')
<script>
(function () {
    const ACTIVE_COLOR = {
        opd: '#4154f1', ipd: '#ff771d', rx: '#e91e8c', lab: '#2eca6a', billing: '#9b59b6'
    };
    function switchTab(id) {
        document.querySelectorAll('.patient-tab-pane').forEach(p => p.style.display = 'none');
        document.querySelectorAll('.patient-tab-btn').forEach(b => {
            b.style.color = '#94a3b8';
            b.style.borderBottomColor = 'transparent';
        });
        const pane = document.getElementById('tab-' + id);
        const btn  = document.getElementById('tab-btn-' + id);
        if (pane) pane.style.display = 'block';
        if (btn) {
            btn.style.color = ACTIVE_COLOR[id] || '#4154f1';
            btn.style.borderBottomColor = ACTIVE_COLOR[id] || '#4154f1';
        }
        sessionStorage.setItem('patientTab', id);
    }
    window.switchTab = switchTab;
    // Restore last tab or default to 'opd'
    const saved = sessionStorage.getItem('patientTab') || 'opd';
    switchTab(saved);
})();
</script>
@endpush
