<?php

namespace Tests\Feature\Billing;

use App\Models\InvoiceModel;
use App\Models\PatientModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Full invoice lifecycle via HTTP:
 * create → partial pay → full pay → void guard
 */
class InvoiceLifecycleTest extends TestCase
{
    use RefreshDatabase;

    // ── create ────────────────────────────────────────────────────────────────

    public function test_create_invoice_via_http_stores_record(): void
    {
        $user    = $this->loginUser();
        $patient = PatientModel::factory()->forClinic($this->clinic)->create();

        $response = $this->post($this->clinicUrl('/invoices'), [
            'patient_code'   => $patient->code,
            'payment_type'   => 'CASH',
            'invoice_date'   => today()->toDateString(),
            'discount_total' => 0,
            'tax_total'      => 0,
            'services'       => [
                ['name' => 'Consultation', 'price' => 50000, 'qty' => 1],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('invoices', [
            'patient_code' => $patient->code,
            'status'       => 'pending',
        ]);
    }

    public function test_create_invoice_validation_fails_without_patient_code(): void
    {
        $this->loginUser();

        $response = $this->post($this->clinicUrl('/invoices'), [
            'payment_type' => 'Cash',
        ]);

        $response->assertSessionHasErrors('patient_code');
    }

    // ── payment ───────────────────────────────────────────────────────────────

    public function test_collect_payment_via_http_changes_status_to_partial(): void
    {
        $this->loginUser();
        $patient = PatientModel::factory()->forClinic($this->clinic)->create();
        $invoice = InvoiceModel::factory()->pending()->forClinic($this->clinic)->forPatient($patient)->create([
            'total' => 100000,
        ]);

        $response = $this->post($this->clinicUrl("/invoices/{$invoice->code}/payment"), [
            'amount' => 40000,
            'method' => 'CASH',
        ]);

        $response->assertRedirect();
        $this->assertEquals('partial', $invoice->fresh()->status);
    }

    public function test_collect_full_payment_via_http_sets_paid(): void
    {
        $this->loginUser();
        $patient = PatientModel::factory()->forClinic($this->clinic)->create();
        $invoice = InvoiceModel::factory()->pending()->forClinic($this->clinic)->forPatient($patient)->create([
            'total' => 25000,
        ]);

        $this->post($this->clinicUrl("/invoices/{$invoice->code}/payment"), [
            'amount' => 25000,
            'method' => 'CASH',
        ]);

        $this->assertEquals('paid', $invoice->fresh()->status);
    }

    // ── void ──────────────────────────────────────────────────────────────────

    public function test_void_pending_invoice_via_http(): void
    {
        $this->loginUser();
        $patient = PatientModel::factory()->forClinic($this->clinic)->create();
        $invoice = InvoiceModel::factory()->pending()->forClinic($this->clinic)->forPatient($patient)->create();

        $response = $this->post($this->clinicUrl("/invoices/{$invoice->code}/void"));

        $response->assertRedirect();
        $this->assertEquals('void', $invoice->fresh()->status);
    }

    public function test_void_paid_invoice_returns_error_redirect(): void
    {
        $this->loginUser();
        $patient = PatientModel::factory()->forClinic($this->clinic)->create();
        $invoice = InvoiceModel::factory()->paid()->forClinic($this->clinic)->forPatient($patient)->create([
            'total' => 10000,
        ]);

        $response = $this->post($this->clinicUrl("/invoices/{$invoice->code}/void"));

        // Should redirect back with error (not redirect to index)
        $response->assertRedirect();
        $this->assertEquals('paid', $invoice->fresh()->status);
    }

    // ── show ──────────────────────────────────────────────────────────────────

    public function test_invoice_show_loads_correctly(): void
    {
        $this->loginUser();
        $patient = PatientModel::factory()->forClinic($this->clinic)->create();
        $invoice = InvoiceModel::factory()->pending()->forClinic($this->clinic)->forPatient($patient)->create();

        $response = $this->get($this->clinicUrl("/invoices/{$invoice->code}"));
        $response->assertStatus(200);
    }
}
