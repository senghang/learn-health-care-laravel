{{--
  Default Invoice Print Template (Khmer)
  Variables: $invoice, $visit, $patient, $locale
--}}
<div style="font-family:'Noto Sans Khmer','Hanuman',sans-serif;font-size:11pt;color:#000">

  {{-- Header --}}
  @php $clinic = currentClinic(); @endphp
  <div style="display:flex;justify-content:space-between;align-items:flex-start;border-bottom:2px solid #000;padding-bottom:8px;margin-bottom:10px">
    <div>
      @if($clinic->logo)
        <img src="{{ asset('storage/'.$clinic->logo) }}" style="height:48px;margin-bottom:4px;display:block"/>
      @endif
      <div style="font-size:14pt;font-weight:700">{{ $clinic->name_kh ?? $clinic->name }}</div>
      @if($clinic->address)<div style="font-size:9pt;color:#444">{{ $clinic->address }}</div>@endif
      @if($clinic->phone)<div style="font-size:9pt;color:#444">Tel: {{ $clinic->phone }}</div>@endif
    </div>
    <div style="text-align:right">
      <div style="font-size:14pt;font-weight:700;border:1.5px solid #000;padding:4px 12px;display:inline-block">
        វិក្កយបត្រ / INVOICE
      </div>
      <div style="font-size:9pt;margin-top:4px">
        លេខ: <strong>{{ $invoice->code }}</strong><br/>
        ថ្ងៃ: {{ df_d($invoice->invoice_date) }}<br/>
        ប្រភេទ: <strong>{{ $invoice->payment_type }}</strong>
      </div>
    </div>
  </div>

  {{-- Patient + Visit --}}
  <table style="width:100%;font-size:10pt;margin-bottom:10px">
    <tr>
      <td style="width:55%;padding:2px 0">
        ឈ្មោះ: <strong>{{ $patient?->surname }}, {{ $patient?->name }}</strong>
      </td>
      <td style="padding:2px 0">លេខ PT: <strong>{{ $patient?->code }}</strong></td>
    </tr>
    <tr>
      <td style="padding:2px 0">
        ភេទ: {{ $patient?->sex === 'M' ? 'ប្រុស' : 'ស្រី' }}
        &nbsp; អាយុ: {{ $patient?->birthdate ? $patient->birthdate->age.'y' : '—' }}
      </td>
      <td style="padding:2px 0">
        ការចូល: {{ $visit?->code }} ({{ $visit?->visit_type }})
        @if($visit?->admitted_at) — {{ df_d($visit->admitted_at) }} @endif
      </td>
    </tr>
    @if($invoice->cashier)
    <tr><td colspan="2" style="padding:2px 0">អ្នកគិតប្រាក់: {{ $invoice->cashier }}</td></tr>
    @endif
  </table>

  {{-- Services --}}
  @if($invoice->services->isNotEmpty())
  <div style="font-weight:700;font-size:10pt;margin-bottom:4px">សេវា / Services</div>
  <table style="width:100%;border-collapse:collapse;font-size:10pt;margin-bottom:8px">
    <thead>
      <tr style="background:#f0f0f0">
        <th style="border:1px solid #000;padding:3px 6px;text-align:left">សេវា</th>
        <th style="border:1px solid #000;padding:3px 6px;width:80px">ប្រភេទ</th>
        <th style="border:1px solid #000;padding:3px 6px;width:90px;text-align:right">តម្លៃ (KHR)</th>
        <th style="border:1px solid #000;padding:3px 6px;width:70px;text-align:center">ស្ថានភាព</th>
      </tr>
    </thead>
    <tbody>
      @foreach($invoice->services as $svc)
      <tr>
        <td style="border:1px solid #ccc;padding:3px 6px">{{ $svc->service_name }}</td>
        <td style="border:1px solid #ccc;padding:3px 6px;font-size:9pt;color:#555">{{ $svc->service_category }}</td>
        <td style="border:1px solid #ccc;padding:3px 6px;text-align:right">{{ khr_fmt($svc->price) }}</td>
        <td style="border:1px solid #ccc;padding:3px 6px;text-align:center">
          {{ $svc->paid ? 'បានបង់' : 'មិនទាន់' }}
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
  @endif

  {{-- Medications --}}
  @if($invoice->medications->isNotEmpty())
  <div style="font-weight:700;font-size:10pt;margin-bottom:4px">ថ្នាំ / Medications</div>
  <table style="width:100%;border-collapse:collapse;font-size:10pt;margin-bottom:8px">
    <thead>
      <tr style="background:#f0f0f0">
        <th style="border:1px solid #000;padding:3px 6px;text-align:left">ថ្នាំ</th>
        <th style="border:1px solid #000;padding:3px 6px;width:60px;text-align:center">ចំនួន</th>
        <th style="border:1px solid #000;padding:3px 6px;width:90px;text-align:right">តម្លៃ (KHR)</th>
        <th style="border:1px solid #000;padding:3px 6px;width:70px;text-align:center">ស្ថានភាព</th>
      </tr>
    </thead>
    <tbody>
      @foreach($invoice->medications as $med)
      <tr>
        <td style="border:1px solid #ccc;padding:3px 6px">{{ $med->medicine_name }}</td>
        <td style="border:1px solid #ccc;padding:3px 6px;text-align:center">{{ $med->quantity }}</td>
        <td style="border:1px solid #ccc;padding:3px 6px;text-align:right">{{ khr_fmt($med->payment) }}</td>
        <td style="border:1px solid #ccc;padding:3px 6px;text-align:center">
          {{ $med->paid ? 'បានបង់' : 'មិនទាន់' }}
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
  @endif

  {{-- Total --}}
  <table style="width:100%;font-size:11pt;margin-bottom:16px">
    <tr>
      <td style="text-align:right;padding:3px 0">
        <strong>ប្រាក់សរុប / Grand Total:</strong>
      </td>
      <td style="text-align:right;padding:3px 12px;font-size:14pt;font-weight:700;width:140px;border-top:2px solid #000">
        {{ khr($invoice->total) }}
      </td>
    </tr>
  </table>

  {{-- Signatures --}}
  <div style="display:flex;justify-content:space-between;margin-top:16px;font-size:10pt">
    <div style="text-align:center;width:140px">
      <div style="border-top:1px solid #000;padding-top:4px;margin-top:40px">ហត្ថលេខាអ្នកជំងឺ</div>
    </div>
    <div style="text-align:center;width:140px">
      <div style="border-top:1px solid #000;padding-top:4px;margin-top:40px">
        <div>{{ $invoice->cashier ?? '—' }}</div>
        <div style="font-size:9pt;color:#555">អ្នកគិតប្រាក់</div>
      </div>
    </div>
  </div>

  <div style="border-top:1px solid #ccc;margin-top:14px;padding-top:5px;font-size:8pt;color:#888;text-align:center">
    {{ $clinic->name }} — {{ df_dt($invoice->invoice_date) }} — MediFlow EMR
  </div>
</div>
