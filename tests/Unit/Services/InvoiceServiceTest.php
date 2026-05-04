<?php

namespace Tests\Unit\Services;

use App\Models\InvoiceModel;
use App\Models\PatientModel;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceServiceTest extends TestCase
{
    use RefreshDatabase;

    private InvoiceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginUser();
        $this->service = new InvoiceService();
    }

    private function makePatient(): PatientModel
    {
        return PatientModel::factory()->forClinic($this->clinic)->create();
    }

    private function createInvoiceWithService(PatientModel $patient, float $price = 50000): InvoiceModel
    {
        return $this->service->create([
            'patient_code' => $patient->code,
            'payment_type' => 'Cash',
            'services'     => [
                ['name' => 'Consultation', 'price' => $price, 'qty' => 1],
            ],
        ]);
    }

    // ── create ────────────────────────────────────────────────────────────────

    public function test_create_invoice_sets_pending_status(): void
    {
        $patient = $this->makePatient();
        $invoice = $this->createInvoiceWithService($patient);

        $this->assertEquals('pending', $invoice->status);
    }

    public function test_create_invoice_calculates_total_from_services(): void
    {
        $patient = $this->makePatient();
        $invoice = $this->createInvoiceWithService($patient, 80000);

        $this->assertEquals(80000, $invoice->total);
    }

    public function test_create_invoice_stores_service_line_item(): void
    {
        $patient = $this->makePatient();
        $invoice = $this->createInvoiceWithService($patient, 30000);

        $this->assertDatabaseHas('invoice_services', [
            'invoice_code' => $invoice->code,
            'service_name' => 'Consultation',
            'price'        => 30000,
        ]);
    }

    // ── collectPayment ────────────────────────────────────────────────────────

    public function test_partial_payment_sets_partial_status(): void
    {
        $patient = $this->makePatient();
        $invoice = $this->createInvoiceWithService($patient, 100000);

        $this->service->collectPayment($invoice->code, [
            'amount' => 50000,
            'method' => 'Cash',
        ]);

        $this->assertEquals('partial', $invoice->fresh()->status);
    }

    public function test_full_payment_sets_paid_status(): void
    {
        $patient = $this->makePatient();
        $invoice = $this->createInvoiceWithService($patient, 40000);

        $this->service->collectPayment($invoice->code, [
            'amount' => 40000,
            'method' => 'Cash',
        ]);

        $this->assertEquals('paid', $invoice->fresh()->status);
    }

    public function test_payment_is_capped_at_balance(): void
    {
        $patient = $this->makePatient();
        $invoice = $this->createInvoiceWithService($patient, 20000);

        // Try to overpay
        $payment = $this->service->collectPayment($invoice->code, [
            'amount' => 99999,
            'method' => 'Cash',
        ]);

        // Amount should be capped at the invoice total
        $this->assertEquals(20000, $payment->amount);
        $this->assertEquals('paid', $invoice->fresh()->status);
    }

    public function test_collect_payment_on_void_invoice_throws(): void
    {
        $patient = $this->makePatient();
        $invoice = $this->createInvoiceWithService($patient);
        $this->service->void($invoice->fresh());

        $this->expectException(\RuntimeException::class);
        $this->service->collectPayment($invoice->code, ['amount' => 1000, 'method' => 'Cash']);
    }

    public function test_collect_payment_creates_payment_record(): void
    {
        $patient = $this->makePatient();
        $invoice = $this->createInvoiceWithService($patient, 15000);

        $this->service->collectPayment($invoice->code, [
            'amount' => 15000,
            'method' => 'Bakong',
        ]);

        $this->assertDatabaseHas('payments', [
            'invoice_code' => $invoice->code,
            'amount'       => 15000,
            'method'       => 'Bakong',
        ]);
    }

    // ── void ──────────────────────────────────────────────────────────────────

    public function test_void_pending_invoice_sets_void_status(): void
    {
        $patient = $this->makePatient();
        $invoice = $this->createInvoiceWithService($patient);

        $this->service->void($invoice->fresh());

        $this->assertEquals('void', $invoice->fresh()->status);
    }

    public function test_void_already_voided_invoice_throws(): void
    {
        $patient = $this->makePatient();
        $invoice = $this->createInvoiceWithService($patient);
        $this->service->void($invoice->fresh());

        $this->expectException(\RuntimeException::class);
        $this->service->void($invoice->fresh());
    }

    public function test_void_paid_invoice_throws(): void
    {
        $patient = $this->makePatient();
        $invoice = $this->createInvoiceWithService($patient, 10000);
        $this->service->collectPayment($invoice->code, ['amount' => 10000, 'method' => 'Cash']);

        $this->expectException(\RuntimeException::class);
        $this->service->void($invoice->fresh());
    }

    // ── update ────────────────────────────────────────────────────────────────

    public function test_update_blocked_on_paid_invoice(): void
    {
        $patient = $this->makePatient();
        $invoice = $this->createInvoiceWithService($patient, 5000);
        $this->service->collectPayment($invoice->code, ['amount' => 5000, 'method' => 'Cash']);

        $this->expectException(\RuntimeException::class);
        $this->service->update($invoice->fresh(), ['payment_type' => 'HEF']);
    }
}
