{{--
  Default Prescription Print Template (Khmer)
  Variables available: $rx, $visit, $patient, $locale
  This content is stored in print_templates.content and compiled via Blade::render()
--}}
<div style="font-family:'Noto Sans Khmer','Hanuman',sans-serif;font-size:11pt;color:#000">

  {{-- Header --}}
  <div style="display:flex;justify-content:space-between;align-items:flex-start;border-bottom:2px solid #000;padding-bottom:8px;margin-bottom:10px">
    <div>
      @php $clinic = currentClinic(); @endphp
      @if($clinic->logo)
        <img src="{{ asset('storage/'.$clinic->logo) }}" style="height:48px;margin-bottom:4px;display:block" alt="Logo"/>
      @endif
      <div style="font-size:14pt;font-weight:700">{{ $clinic->name_kh ?? $clinic->name }}</div>
      @if($clinic->address)
        <div style="font-size:9pt;color:#444">{{ $clinic->address }}</div>
      @endif
      @if($clinic->phone)
        <div style="font-size:9pt;color:#444">ទូរស័ព្ទ: {{ $clinic->phone }}</div>
      @endif
    </div>
    <div style="text-align:right">
      <div style="font-size:13pt;font-weight:700;border:1.5px solid #000;padding:4px 12px;display:inline-block">
        វេជ្ជបញ្ជា
      </div>
      <div style="font-size:9pt;margin-top:4px">
        លេខ: <strong>{{ $rx->code }}</strong><br/>
        ថ្ងៃ: {{ df_d($rx->prescribed_at) }}
      </div>
    </div>
  </div>

  {{-- Patient info --}}
  <table style="width:100%;margin-bottom:10px;font-size:10pt">
    <tr>
      <td style="width:50%;padding:2px 0">
        ឈ្មោះអ្នកជំងឺ: <strong>{{ $patient?->surname }}, {{ $patient?->name }}</strong>
      </td>
      <td style="padding:2px 0">
        លេខ: <strong>{{ $patient?->code }}</strong>
      </td>
    </tr>
    <tr>
      <td style="padding:2px 0">
        ភេទ: {{ $patient?->sex === 'M' ? 'ប្រុស' : 'ស្រី' }}
        &nbsp;&nbsp; អាយុ: {{ $patient?->birthdate ? $patient->birthdate->age.'ឆ្នាំ' : '—' }}
      </td>
      <td style="padding:2px 0">
        ការចូលព្យាបាល: {{ $visit?->code }} ({{ $visit?->visit_type }})
      </td>
    </tr>
    @if($patient?->address)
    <tr>
      <td colspan="2" style="padding:2px 0">
        អាសយដ្ឋាន: {{ $patient->address->full_address }}
      </td>
    </tr>
    @endif
  </table>

  {{-- Medications table --}}
  <table style="width:100%;border-collapse:collapse;font-size:10pt;margin-bottom:12px">
    <thead>
      <tr style="background:#f0f0f0">
        <th style="border:1px solid #000;padding:4px 6px;text-align:center;width:30px">#</th>
        <th style="border:1px solid #000;padding:4px 6px;text-align:left">ឈ្មោះថ្នាំ / Medication</th>
        <th style="border:1px solid #000;padding:4px 6px;text-align:center;width:60px">ពេលព្រឹក</th>
        <th style="border:1px solid #000;padding:4px 6px;text-align:center;width:60px">ពេលថ្ងៃ</th>
        <th style="border:1px solid #000;padding:4px 6px;text-align:center;width:60px">ពេលល្ងាច</th>
        <th style="border:1px solid #000;padding:4px 6px;text-align:center;width:60px">ពេលយប់</th>
        <th style="border:1px solid #000;padding:4px 6px;text-align:center;width:50px">ថ្ងៃ</th>
        <th style="border:1px solid #000;padding:4px 6px;text-align:left">ចំណាំ</th>
      </tr>
    </thead>
    <tbody>
      @forelse($rx->medications as $i => $med)
      <tr>
        <td style="border:1px solid #ccc;padding:4px 6px;text-align:center">{{ $i+1 }}</td>
        <td style="border:1px solid #ccc;padding:4px 6px">
          <strong>{{ $med->medicine_name }}</strong>
          @if($med->strength)
            <span style="font-size:9pt;color:#555">({{ $med->strength }})</span>
          @endif
          @if($med->form)
            <span style="font-size:9pt;color:#555">— {{ $med->form }}</span>
          @endif
          @if($med->method)
            <br/><span style="font-size:9pt;color:#444">{{ $med->method }}</span>
          @endif
        </td>
        <td style="border:1px solid #ccc;padding:4px 6px;text-align:center">{{ $med->morning ?: '—' }}</td>
        <td style="border:1px solid #ccc;padding:4px 6px;text-align:center">{{ $med->afternoon ?: '—' }}</td>
        <td style="border:1px solid #ccc;padding:4px 6px;text-align:center">{{ $med->evening ?: '—' }}</td>
        <td style="border:1px solid #ccc;padding:4px 6px;text-align:center">{{ $med->night ?: '—' }}</td>
        <td style="border:1px solid #ccc;padding:4px 6px;text-align:center">{{ $med->days ?: '—' }}</td>
        <td style="border:1px solid #ccc;padding:4px 6px;font-size:9pt;color:#555">{{ $med->note }}</td>
      </tr>
      @empty
      <tr><td colspan="8" style="border:1px solid #ccc;padding:8px;text-align:center;color:#999">គ្មានថ្នាំ</td></tr>
      @endforelse
    </tbody>
  </table>

  {{-- Diagnosis (if loaded) --}}
  @if($visit && $visit->diagnoses->isNotEmpty())
  <div style="margin-bottom:10px;font-size:10pt">
    <strong>រោគវិនិច្ឆ័យ:</strong>
    {{ $visit->diagnoses->where('diagnosis_type','Primary')->first()?->diagnosis_name ?? '—' }}
  </div>
  @endif

  {{-- Signature --}}
  <div style="display:flex;justify-content:space-between;margin-top:20px;font-size:10pt">
    <div style="text-align:center;width:160px">
      <div style="border-top:1px solid #000;padding-top:4px;margin-top:40px">
        ហត្ថលេខាអ្នកជំងឺ
      </div>
    </div>
    <div style="text-align:center;width:160px">
      <div style="border-top:1px solid #000;padding-top:4px;margin-top:40px">
        <div>{{ $rx->prescribed_by }}</div>
        <div style="font-size:9pt;color:#555">វេជ្ជបណ្ឌិត</div>
      </div>
    </div>
  </div>

  {{-- Footer --}}
  <div style="border-top:1px solid #ccc;margin-top:16px;padding-top:6px;font-size:8pt;color:#888;text-align:center">
    {{ $clinic->name }} — {{ df_dt($rx->prescribed_at) }} — MediFlow EMR
  </div>

</div>
