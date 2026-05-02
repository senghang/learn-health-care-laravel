<?php

namespace Tests\Unit\Models;

use App\Models\InvoiceModel;
use App\Models\InvoiceMedicationModel;
use App\Models\InvoiceServiceModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceModelTest extends TestCase
{
    use RefreshDatabase;

    // ── balance ───────────────────────────────────────────────────────────────

    public function test_balance_equals_total_when_no_payments(): void
    {
        $invoice = InvoiceModel::factory()->create(['total' => 50000, 'status' => 'pending']);

        $this->assertEqualsWithDelta(50000.0, $invoice->balance, 0.01);
    }

    public function test_balance_decreases_with_payments(): void
    {
        $invoice = InvoiceModel::factory()->create(['total' => 100000, 'status' => 'pending']);
        $invoice->payments()->create([
            'code'         => 'PAY20260425TEST01',
            'patient_code' => $invoice->patient_code,
            'clinic_id'    => $invoice->clinic_id,
            'amount'       => 40000,
            'method'       => 'CASH',
            'paid_at'      => now(),
        ]);

        $invoice->refresh();
        $this->assertEqualsWithDelta(60000.0, $invoice->balance, 0.01);
    }

    public function test_balance_is_zero_when_fully_paid(): void
    {
        $invoice = InvoiceModel::factory()->create(['total' => 25000, 'status' => 'paid']);
        $invoice->payments()->create([
            'code'         => 'PAY20260425TEST02',
            'patient_code' => $invoice->patient_code,
            'clinic_id'    => $invoice->clinic_id,
            'amount'       => 25000,
            'method'       => 'CASH',
            'paid_at'      => now(),
        ]);

        $invoice->refresh();
        $this->assertEqualsWithDelta(0.0, $invoice->balance, 0.01);
    }

    // ── isPaid ────────────────────────────────────────────────────────────────

    public function test_is_paid_returns_true_for_paid_status(): void
    {
        $invoice = InvoiceModel::factory()->paid()->create();

        $this->assertTrue($invoice->isPaid());
    }

    public function test_is_paid_returns_false_for_pending_status(): void
    {
        $invoice = InvoiceModel::factory()->pending()->create();

        $this->assertFalse($invoice->isPaid());
    }

    // ── recalculateTotal ──────────────────────────────────────────────────────

    public function test_recalculate_total_sums_services_and_medications(): void
    {
        $invoice = InvoiceModel::factory()->create([
            'subtotal'       => 0,
            'discount_total' => 0,
            'tax_total'      => 0,
            'total'          => 0,
        ]);

        // Add two service lines
        $invoice->services()->create([
            'service_name' => 'Consultation',
            'price'        => 20000,
            'qty'          => 1,
        ]);
        $invoice->services()->create([
            'service_name' => 'Lab test',
            'price'        => 15000,
            'qty'          => 1,
        ]);

        // Add one medication line
        $invoice->medications()->create([
            'medicine_name' => 'Paracetamol',
            'price'         => 500,
            'quantity'      => 10,
        ]);

        $invoice->recalculateTotal();
        $invoice->refresh();

        // 20000 + 15000 + (500 * 10) = 40000
        $this->assertEqualsWithDelta(40000.0, $invoice->subtotal, 0.01);
        $this->assertEqualsWithDelta(40000.0, $invoice->total, 0.01);
    }

    public function test_recalculate_total_applies_discount(): void
    {
        $invoice = InvoiceModel::factory()->create([
            'subtotal'       => 0,
            'discount_total' => 5000,
            'tax_total'      => 0,
            'total'          => 0,
        ]);
        $invoice->services()->create([
            'service_name' => 'Consultation',
            'price'        => 30000,
            'qty'          => 1,
        ]);

        $invoice->recalculateTotal();
        $invoice->refresh();

        // 30000 subtotal - 5000 discount = 25000 total
        $this->assertEqualsWithDelta(30000.0, $invoice->subtotal, 0.01);
        $this->assertEqualsWithDelta(25000.0, $invoice->total, 0.01);
    }

    public function test_recalculate_total_applies_tax(): void
    {
        $invoice = InvoiceModel::factory()->create([
            'subtotal'       => 0,
            'discount_total' => 0,
            'tax_total'      => 1000,
            'total'          => 0,
        ]);
        $invoice->services()->create([
            'service_name' => 'X-Ray',
            'price'        => 20000,
            'qty'          => 1,
        ]);

        $invoice->recalculateTotal();
        $invoice->refresh();

        // 20000 + 1000 tax = 21000 total
        $this->assertEqualsWithDelta(21000.0, $invoice->total, 0.01);
    }

    public function test_recalculate_total_is_zero_with_no_lines(): void
    {
        $invoice = InvoiceModel::factory()->create([
            'subtotal'       => 99999,
            'discount_total' => 0,
            'tax_total'      => 0,
            'total'          => 99999,
        ]);

        $invoice->recalculateTotal();
        $invoice->refresh();

        $this->assertEqualsWithDelta(0.0, $invoice->subtotal, 0.01);
        $this->assertEqualsWithDelta(0.0, $invoice->total, 0.01);
    }
}
