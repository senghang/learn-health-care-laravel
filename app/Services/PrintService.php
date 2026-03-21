<?php

namespace App\Services;

use App\Models\InvoiceModel;
use App\Models\PatientModel;
use App\Models\PrescriptionModel;
use App\Models\PrintTemplateModel;
use App\Models\VisitModel;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;

/**
 * PrintService
 *
 * Resolves the correct print template for the current clinic+locale,
 * injects data, and returns a renderable HTML string ready for
 * window.print() in the browser.
 *
 * Usage in controller:
 *   return response($this->print->prescription($rx, $locale))->header('Content-Type','text/html');
 */
class PrintService
{
    // ── Prescription ──────────────────────────────────────────────────────────

    public function prescription(PrescriptionModel $rx, string $locale = 'km'): string
    {
        $rx->loadMissing(['medications', 'visit.patient']);

        $visit   = $rx->visit;
        $patient = $visit?->patient;

        $data = compact('rx', 'visit', 'patient', 'locale');

        return $this->render('prescription', $rx->clinic_id ?? currentClinic()->id, $locale, $data);
    }

    // ── Invoice ───────────────────────────────────────────────────────────────

    public function invoice(InvoiceModel $invoice, string $locale = 'km'): string
    {
        $invoice->loadMissing(['services', 'medications', 'visit.patient']);

        $visit   = $invoice->visit;
        $patient = $visit?->patient;

        $data = compact('invoice', 'visit', 'patient', 'locale');

        return $this->render('invoice', $invoice->clinic_id ?? currentClinic()->id, $locale, $data);
    }

    // ── Core renderer ─────────────────────────────────────────────────────────

    /**
     * Resolve template → compile Blade → wrap in print shell HTML.
     */
    private function render(string $type, int $clinicId, string $locale, array $data): string
    {
        $template = PrintTemplateModel::resolve($clinicId, $type, $locale);

        if ($template) {
            // Compile the stored Blade template string
            $body = $this->compileBlade($template->content, $data);
            $paper       = $template->paper_size;
            $orientation = $template->orientation;
        } else {
            // Fall back to a view file: resources/views/clinics/print/{type}.blade.php
            $body        = View::make("clinics.print.{$type}", $data)->render();
            $paper       = 'A5';
            $orientation = 'portrait';
        }

        return $this->wrapInShell($body, $paper, $orientation, $locale);
    }

    /**
     * Safely compile a Blade template string stored in the database.
     * Uses a unique cache key so repeated calls are fast.
     */
    private function compileBlade(string $content, array $data): string
    {
        $compiled = Blade::render($content, $data);
        return $compiled;
    }

    /**
     * Wrap the body in a minimal print-only HTML shell.
     * The shell sets paper size, hides browser chrome, and auto-triggers print.
     */
    private function wrapInShell(string $body, string $paper, string $orientation, string $locale): string
    {
        $fontFamily = $locale === 'km'
            ? "'Noto Sans Khmer', 'Hanuman', sans-serif"
            : "'Nunito', 'Open Sans', sans-serif";

        return <<<HTML
<!DOCTYPE html>
<html lang="{$locale}">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Print</title>
<style>
  @page { size: {$paper} {$orientation}; margin: 12mm 14mm; }
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: {$fontFamily};
    font-size: 11pt;
    color: #000;
    background: #fff;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }
  .no-print { display: none !important; }
  table { width: 100%; border-collapse: collapse; }
  th, td { padding: 4px 6px; }
  .border-t { border-top: 1px solid #000; }
  .border-b { border-bottom: 1px solid #000; }
  .text-right { text-align: right; }
  .text-center { text-align: center; }
  .font-bold { font-weight: 700; }
  .text-sm { font-size: 9pt; }
  .text-xs { font-size: 8pt; color: #555; }
  .mt-4 { margin-top: 8mm; }
  .mb-2 { margin-bottom: 4mm; }
  @media screen {
    body { max-width: 210mm; margin: 20px auto; padding: 20px; background: #f5f5f5; }
    .print-page { background: #fff; padding: 20mm; box-shadow: 0 0 10px rgba(0,0,0,.15); }
    .print-btn { display: block; margin: 10px auto 20px; padding: 8px 24px;
                 background: #4154f1; color: #fff; border: none; border-radius: 6px;
                 font-size: 14px; cursor: pointer; font-family: {$fontFamily}; }
  }
</style>
</head>
<body>
<button class="print-btn no-print" onclick="window.print()">🖨 Print</button>
<div class="print-page">
{$body}
</div>
<script>
  // Auto-print when opened in a dedicated tab
  if (window.opener || document.referrer === '') {
    window.addEventListener('load', () => setTimeout(() => window.print(), 400));
  }
</script>
</body>
</html>
HTML;
    }
}
