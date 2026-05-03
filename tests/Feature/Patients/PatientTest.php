<?php

namespace Tests\Feature\Patients;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_index_loads_when_authenticated(): void
    {
        $this->loginUser();

        $response = $this->get($this->clinicUrl('/patients'));

        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_is_redirected_from_patient_index(): void
    {
        $this->bindClinic();

        $response = $this->get($this->clinicUrl('/patients'));

        // userauth middleware redirects to /login when not authenticated
        $response->assertRedirect();
        $this->assertStringContainsString('login', $response->headers->get('Location'));
    }

    public function test_patient_create_page_loads_when_authenticated(): void
    {
        $this->loginUser();

        $response = $this->get($this->clinicUrl('/patients/create'));

        $response->assertStatus(200);
    }
}
