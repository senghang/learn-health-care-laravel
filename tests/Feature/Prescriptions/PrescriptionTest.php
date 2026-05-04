<?php

namespace Tests\Feature\Prescriptions;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrescriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_prescription_index_loads_when_authenticated(): void
    {
        $this->loginUser();
        $response = $this->get($this->clinicUrl('/prescriptions'));
        $response->assertStatus(200);
    }

    public function test_prescription_create_loads_when_authenticated(): void
    {
        $this->loginUser();
        $response = $this->get($this->clinicUrl('/prescriptions/create'));
        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_redirected_from_prescriptions(): void
    {
        $this->bindClinic();
        $response = $this->get($this->clinicUrl('/prescriptions'));
        $response->assertRedirect();
    }
}
