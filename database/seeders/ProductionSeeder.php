<?php

namespace Database\Seeders;

use App\Models\ClinicSettingModel;
use App\Models\PrintTemplateModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * ProductionSeeder
 *
 * Seeds every clinic with:
 *   1. Default clinic settings (locale, timezone, currency)
 *   2. Default print templates for prescription and invoice (KH + EN)
 *
 * Run after ClinicSeeder + UserTableSeeder so created_by can reference user id=1.
 */
class ProductionSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $clinics = DB::table('clinics')->get();

        foreach ($clinics as $clinic) {
            // Resolve the admin user for this clinic (created_by audit field)
            $adminUserId = DB::table('users')
                ->where('clinic_id', $clinic->id)
                ->orderBy('id')
                ->value('id');

            $this->seedSettings($clinic->id);
            $this->seedPrintTemplates($clinic->id, $clinic->name, $adminUserId);
        }

        $this->command->info('ProductionSeeder: seeded ' . $clinics->count() . ' clinic(s).');
    }

    // ── Settings ──────────────────────────────────────────────────────────────

    private function seedSettings(int $clinicId): void
    {
        $defaults = [
            ['key' => 'default_locale',    'value' => 'km'],
            ['key' => 'timezone',          'value' => 'Asia/Phnom_Penh'],
            ['key' => 'currency',          'value' => 'USD'],
            ['key' => 'date_format',       'value' => 'd/m/Y'],
            ['key' => 'invoice.footer',    'value' => 'សូមអរគុណ! / Thank you!'],
            ['key' => 'prescription.note', 'value' => 'ប្រើតាមវេជ្ជបញ្ជា / Take as prescribed'],
        ];

        foreach ($defaults as $d) {
            ClinicSettingModel::firstOrCreate(
                ['clinic_id' => $clinicId, 'key' => $d['key'], 'locale' => null],
                ['value'     => $d['value']]
            );
        }
    }

    // ── Print templates ───────────────────────────────────────────────────────

    private function seedPrintTemplates(int $clinicId, string $clinicName, ?int $createdBy = null): void
    {
        $templates = [
            // ── Prescription KH ──────────────────────────────────────────────
            [
                'code'        => "TPL-{$clinicId}-RX-KH",
                'type'        => 'prescription',
                'name'        => 'វេជ្ជបញ្ជា (ភាសាខ្មែរ)',
                'locale'      => 'km',
                'is_default'  => true,
                'paper_size'  => 'A5',
                'orientation' => 'portrait',
                'content'     => file_get_contents(
                    resource_path('views/clinics/print/prescription.blade.php')
                ),
            ],
            // ── Prescription EN ──────────────────────────────────────────────
            [
                'code'        => "TPL-{$clinicId}-RX-EN",
                'type'        => 'prescription',
                'name'        => 'Prescription (English)',
                'locale'      => 'en',
                'is_default'  => false,
                'paper_size'  => 'A5',
                'orientation' => 'portrait',
                'content'     => $this->prescriptionEnTemplate(),
            ],
            // ── Invoice KH ───────────────────────────────────────────────────
            [
                'code'        => "TPL-{$clinicId}-INV-KH",
                'type'        => 'invoice',
                'name'        => 'វិក្កយបត្រ (ភាសាខ្មែរ)',
                'locale'      => 'km',
                'is_default'  => true,
                'paper_size'  => 'A5',
                'orientation' => 'portrait',
                'content'     => file_get_contents(
                    resource_path('views/clinics/print/invoice.blade.php')
                ),
            ],
            // ── Invoice EN ───────────────────────────────────────────────────
            [
                'code'        => "TPL-{$clinicId}-INV-EN",
                'type'        => 'invoice',
                'name'        => 'Invoice (English)',
                'locale'      => 'en',
                'is_default'  => false,
                'paper_size'  => 'A5',
                'orientation' => 'portrait',
                'content'     => $this->invoiceEnTemplate(),
            ],
        ];

        foreach ($templates as $t) {
            PrintTemplateModel::firstOrCreate(
                ['code' => $t['code']],
                array_merge($t, [
                    'clinic_id'  => $clinicId,
                    'is_active'  => true,
                    'created_by' => $createdBy,
                    'updated_by' => $createdBy,
                ])
            );
        }
    }

    // ── English template stubs ────────────────────────────────────────────────

    private function prescriptionEnTemplate(): string
    {
        return <<<'BLADE'
<div style="font-family:'Nunito','Open Sans',sans-serif;font-size:11pt;color:#000">
  @php $clinic = currentClinic(); @endphp
  <div style="display:flex;justify-content:space-between;border-bottom:2px solid #000;padding-bottom:8px;margin-bottom:10px">
    <div>
      @if($clinic->logo)<img src="{{ asset('storage/'.$clinic->logo) }}" style="height:44px;display:block;margin-bottom:4px"/>@endif
      <div style="font-size:13pt;font-weight:700">{{ $clinic->name_en ?? $clinic->name }}</div>
      @if($clinic->phone)<div style="font-size:9pt;color:#444">Tel: {{ $clinic->phone }}</div>@endif
    </div>
    <div style="text-align:right">
      <div style="font-size:13pt;font-weight:700;border:1.5px solid #000;padding:4px 12px;display:inline-block">PRESCRIPTION</div>
      <div style="font-size:9pt;margin-top:4px">No: <strong>{{ $rx->code }}</strong><br/>Date: {{ df_d($rx->prescribed_at) }}</div>
    </div>
  </div>
  <div style="margin-bottom:10px;font-size:10pt">
    <strong>Patient:</strong> {{ $patient?->surname }}, {{ $patient?->name }} &nbsp;|&nbsp;
    <strong>Code:</strong> {{ $patient?->code }} &nbsp;|&nbsp;
    <strong>Sex:</strong> {{ $patient?->sex }} &nbsp;|&nbsp;
    <strong>Age:</strong> {{ $patient?->birthdate ? $patient->birthdate->age.'y' : '—' }}
  </div>
  <table style="width:100%;border-collapse:collapse;font-size:10pt;margin-bottom:12px">
    <thead><tr style="background:#f0f0f0">
      <th style="border:1px solid #000;padding:3px 6px">#</th>
      <th style="border:1px solid #000;padding:3px 6px;text-align:left">Medication</th>
      <th style="border:1px solid #000;padding:3px 6px">Morn</th>
      <th style="border:1px solid #000;padding:3px 6px">Noon</th>
      <th style="border:1px solid #000;padding:3px 6px">Eve</th>
      <th style="border:1px solid #000;padding:3px 6px">Night</th>
      <th style="border:1px solid #000;padding:3px 6px">Days</th>
      <th style="border:1px solid #000;padding:3px 6px;text-align:left">Note</th>
    </tr></thead>
    <tbody>
      @foreach($rx->medications as $i => $med)
      <tr>
        <td style="border:1px solid #ccc;padding:3px 6px;text-align:center">{{ $i+1 }}</td>
        <td style="border:1px solid #ccc;padding:3px 6px"><strong>{{ $med->medicine_name }}</strong> {{ $med->strength }} {{ $med->form }}</td>
        <td style="border:1px solid #ccc;padding:3px 6px;text-align:center">{{ $med->morning ?: '—' }}</td>
        <td style="border:1px solid #ccc;padding:3px 6px;text-align:center">{{ $med->afternoon ?: '—' }}</td>
        <td style="border:1px solid #ccc;padding:3px 6px;text-align:center">{{ $med->evening ?: '—' }}</td>
        <td style="border:1px solid #ccc;padding:3px 6px;text-align:center">{{ $med->night ?: '—' }}</td>
        <td style="border:1px solid #ccc;padding:3px 6px;text-align:center">{{ $med->days ?: '—' }}</td>
        <td style="border:1px solid #ccc;padding:3px 6px;font-size:9pt;color:#555">{{ $med->note }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
  <div style="display:flex;justify-content:space-between;margin-top:20px;font-size:10pt">
    <div style="text-align:center;width:140px"><div style="border-top:1px solid #000;padding-top:4px;margin-top:40px">Patient Signature</div></div>
    <div style="text-align:center;width:140px"><div style="border-top:1px solid #000;padding-top:4px;margin-top:40px">{{ $rx->prescribed_by }}<br/><small style="color:#555">Physician</small></div></div>
  </div>
  <div style="border-top:1px solid #ccc;margin-top:14px;padding-top:5px;font-size:8pt;color:#888;text-align:center">{{ $clinic->name }} — {{ df_dt($rx->prescribed_at) }} — MediFlow EMR</div>
</div>
BLADE;
    }

    private function invoiceEnTemplate(): string
    {
        return <<<'BLADE'
<div style="font-family:'Nunito','Open Sans',sans-serif;font-size:11pt;color:#000">
  @php $clinic = currentClinic(); @endphp
  <div style="display:flex;justify-content:space-between;border-bottom:2px solid #000;padding-bottom:8px;margin-bottom:10px">
    <div>
      @if($clinic->logo)<img src="{{ asset('storage/'.$clinic->logo) }}" style="height:44px;display:block;margin-bottom:4px"/>@endif
      <div style="font-size:13pt;font-weight:700">{{ $clinic->name_en ?? $clinic->name }}</div>
      @if($clinic->phone)<div style="font-size:9pt;color:#444">Tel: {{ $clinic->phone }}</div>@endif
    </div>
    <div style="text-align:right">
      <div style="font-size:13pt;font-weight:700;border:1.5px solid #000;padding:4px 12px;display:inline-block">INVOICE</div>
      <div style="font-size:9pt;margin-top:4px">No: <strong>{{ $invoice->code }}</strong><br/>Date: {{ df_d($invoice->invoice_date) }}<br/>Type: {{ $invoice->payment_type }}</div>
    </div>
  </div>
  <div style="margin-bottom:10px;font-size:10pt">
    <strong>Patient:</strong> {{ $patient?->surname }}, {{ $patient?->name }} &nbsp;|&nbsp; Code: {{ $patient?->code }}<br/>
    Visit: {{ $visit?->code }} ({{ $visit?->visit_type }}) @if($visit?->admitted_at)— {{ df_d($visit->admitted_at) }}@endif
    @if($invoice->cashier) &nbsp;|&nbsp; Cashier: {{ $invoice->cashier }}@endif
  </div>
  @if($invoice->services->isNotEmpty())
  <div style="font-weight:700;margin-bottom:4px">Services</div>
  <table style="width:100%;border-collapse:collapse;font-size:10pt;margin-bottom:8px">
    <thead><tr style="background:#f0f0f0">
      <th style="border:1px solid #000;padding:3px 6px;text-align:left">Service</th>
      <th style="border:1px solid #000;padding:3px 6px;width:80px">Category</th>
      <th style="border:1px solid #000;padding:3px 6px;width:90px;text-align:right">Price (KHR)</th>
      <th style="border:1px solid #000;padding:3px 6px;width:60px">Status</th>
    </tr></thead>
    <tbody>
      @foreach($invoice->services as $svc)
      <tr>
        <td style="border:1px solid #ccc;padding:3px 6px">{{ $svc->service_name }}</td>
        <td style="border:1px solid #ccc;padding:3px 6px;font-size:9pt">{{ $svc->service_category }}</td>
        <td style="border:1px solid #ccc;padding:3px 6px;text-align:right">{{ khr_fmt($svc->price) }}</td>
        <td style="border:1px solid #ccc;padding:3px 6px;text-align:center">{{ $svc->paid ? 'Paid' : 'Due' }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
  @endif
  <table style="width:100%;font-size:11pt">
    <tr>
      <td style="text-align:right;padding:3px 0"><strong>Grand Total:</strong></td>
      <td style="text-align:right;padding:3px 12px;font-size:14pt;font-weight:700;width:140px;border-top:2px solid #000">{{ khr($invoice->total) }}</td>
    </tr>
  </table>
  <div style="border-top:1px solid #ccc;margin-top:14px;padding-top:5px;font-size:8pt;color:#888;text-align:center">{{ $clinic->name }} — {{ df_dt($invoice->invoice_date) }} — MediFlow EMR</div>
</div>
BLADE;
    }
}
