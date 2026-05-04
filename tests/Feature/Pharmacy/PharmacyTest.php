<?php

namespace Tests\Feature\Pharmacy;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PharmacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_pharmacy_index_loads_when_authenticated_with_permission(): void
    {
        $this->loginAdmin();
        $response = $this->get($this->clinicUrl('/pharmacy'));
        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_redirected_from_pharmacy(): void
    {
        $this->bindClinic();
        $response = $this->get($this->clinicUrl('/pharmacy'));
        $response->assertRedirect();
    }
}
