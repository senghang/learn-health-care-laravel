<?php

namespace Tests\Feature\IPD;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admissions_index_loads_when_authenticated(): void
    {
        $this->loginUser();
        $response = $this->get($this->clinicUrl('/admissions'));
        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_redirected_from_admissions(): void
    {
        $this->bindClinic();
        $response = $this->get($this->clinicUrl('/admissions'));
        $response->assertRedirect();
    }
}
