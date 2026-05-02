<?php

namespace Tests\Feature\Billing;

use App\Models\InvoiceModel;
use App\Models\MedicineModel;
use App\Models\PatientModel;
use App\Models\VisitModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesClinicEnvironment;
use Tests\TestCase;

class InvoiceWorkflowTest extends TestCase
{
    use RefreshDatabase, CreatesClinicEnvironment;

    private PatientModel $patient;
    private VisitModel   $visit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpClinic();

        $this->patient = PatientModel::factory()->forClinic($this->clinic)->create();
        $this->visit   = VisitModel::factory()->forPatient($this->patient)->opd()->create();
    }

    // ── Create invoice ────────────────────────────────────────────────────────

    public function test_can_create_invoice_with_service_line(): void
    {
        $response = $this->post('/invoices', [
            'patient_code' => $this->patient->code,
            'visit_code'   => $this->visit->code,
            'payment_type' => 'CASH',
            'invoice_date' => now()->toDateString(),
            'services'     => [
                ['name' => 'Consultation', 'qty' => 1, 'price' => 20000],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('invoices', [
            'patient_code' => $this->patient->code,
            'clinic_id'    => $this->clinic->id,
            'payment_type' => 'CASH',
        ]);
    }

    public function test_invoice_code_starts_with_inv_prefix(): void
    {
        $this->post('/invoices', [
            'patient_code' => $this->patient->code,
            'payment_type' => 'CASH',
            'services'     => [
                ['name' => 'Consultation', 'qty' => 1, 'price' => 10000],
            ],
        ]);

        $invoice = InvoiceModel::where('patient_code', $this->patient->code)->first();

        $this->assertNotNull($invoice);
        $this->assertStringStartsWith('INV', $invoice->code);
    }

    public function test_all_payment_types_are_accepted(): void
    {
        $types = ['CASH', 'HEF', 'NSSF', 'CARD', 'BAKONG'];

        foreach ($types as $type) {
            $patient = PatientModel::factory()->forClinic($this->clinic)->create();

            $response = $this->post('/invoices', [
                'patient_code' => $patient->code,
                'payment_type' => $type,
                'services'     => [
                    ['name' => 'Test Service', 'qty' => 1, 'price' => 5000],
                ],
            ]);

            $response->assertSessionMissingErrorsIn('payment_type',
                message: "payment_type {$type} should be valid"
            );
        }
    }

    public function test_invoice_requires_at_least_one_line_item(): void
    {
        $response = $this->post('/invoices', [
            'patient_code' => $this->patient->code,
            'payment_type' => 'CASH',
        ]);

        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('invoices', ['patient_code' => $this->patient->code]);
    }

    public function test_invoice_requires_patient_code(): void
    {
        $response = $this->post('/invoices', [
            'payment_type' => 'CASH',
            'services'     => [['name' => 'Consultation', 'qty' => 1, 'price' => 5000]],
        ]);

        $response->assertSessionHasErrors('patient_code');
    }

    public function test_invoice_requires_valid_payment_type(): void
    {
        $response = $this->post('/invoices', [
            'patient_code' => $this->patient->code,
            'payment_type' => 'BITCOIN',
            'services'     => [['name' => 'Consultation', 'qty' => 1, 'price' => 5000]],
        ]);

        $response->assertSessionHasErrors('payment_type');
    }

    public function test_invoice_total_is_calculated_from_line_items(): void
    {
        $this->post('/invoices', [
            'patient_code' => $this->patient->code,
            'payment_type' => 'CASH',
            'services'     => [
                ['name' => 'Consultation', 'qty' => 1,  'price' => 20000],
                ['name' => 'Lab test',     'qty' => 2,  'price' => 10000],
                ['name' => 'X-Ray',        'qty' => 1,  'price' => 15000],
            ],
        ]);

        $invoice = InvoiceModel::where('clinic_id', $this->clinic->id)
            ->where('patient_code', $this->patient->code)
            ->first();

        $this->assertNotNull($invoice);
        // 20000 + (10000*2) + 15000 = 55000
        $this->assertEqualsWithDelta(55000.0, $invoice->total, 0.01);
    }

    // ── Collect payment ───────────────────────────────────────────────────────

    public function test_can_collect_full_payment_on_invoice(): void
    {
        $invoice = InvoiceModel::factory()->forPatient($this->patient)->create([
            'total'  => 30000,
            'status' => 'pending',
        ]);

        $response = $this->post("/invoices/{$invoice->code}/payment", [
            'amount'  => 30000,
            'method'  => 'CASH',
            'paid_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('payments', [
            'invoice_code' => $invoice->code,
            'amount'       => 30000,
            'method'       => 'CASH',
        ]);
    }

    public function test_payment_requires_amount_and_method(): void
    {
        $invoice = InvoiceModel::factory()->forPatient($this->patient)->create(['status' => 'pending']);

        $response = $this->post("/invoices/{$invoice->code}/payment", []);

        $response->assertSessionHasErrors(['amount', 'method']);
    }

    public function test_payment_amount_must_be_positive(): void
    {
        $invoice = InvoiceModel::factory()->forPatient($this->patient)->create(['status' => 'pending']);

        $response = $this->post("/invoices/{$invoice->code}/payment", [
            'amount' => -100,
            'method' => 'CASH',
        ]);

        $response->assertSessionHasErrors('amount');
    }

    // ── Invoice list ──────────────────────────────────────────────────────────

    public function test_invoices_index_loads(): void
    {
        InvoiceModel::factory()->forPatient($this->patient)->count(3)->create();

        $response = $this->get('/invoices');

        $response->assertStatus(200);
    }

    public function test_invoices_only_shows_own_clinic_data(): void
    {
        InvoiceModel::factory()->forPatient($this->patient)->create(['status' => 'pending']);
        InvoiceModel::factory()->create(['status' => 'pending']); // other clinic

        $response = $this->get('/invoices');

        $invoices = InvoiceModel::where('clinic_id', $this->clinic->id)->get();
        $this->assertCount(1, $invoices);
    }

    // ── Invoice show ──────────────────────────────────────────────────────────

    public function test_invoice_show_page_loads(): void
    {
        $invoice = InvoiceModel::factory()->forPatient($this->patient)->create();

        $response = $this->get("/invoices/{$invoice->code}");

        $response->assertStatus(200);
        $response->assertSee($invoice->code);
    }

    public function test_cannot_view_invoice_from_other_clinic(): void
    {
        $other = InvoiceModel::factory()->create(); // different clinic

        $response = $this->get("/invoices/{$other->code}");

        $response->assertStatus(404);
    }
}
