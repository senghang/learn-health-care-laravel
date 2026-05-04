<?php

namespace Tests\Feature\Billing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_index_loads_when_authenticated(): void
    {
        $this->loginUser();
        $response = $this->get($this->clinicUrl('/invoices'));
        $response->assertStatus(200);
    }

    public function test_invoice_create_loads_when_authenticated(): void
    {
        $this->loginUser();
        $response = $this->get($this->clinicUrl('/invoices/create'));
        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_redirected_from_invoices(): void
    {
        $this->bindClinic();
        $response = $this->get($this->clinicUrl('/invoices'));
        $response->assertRedirect();
    }
}
