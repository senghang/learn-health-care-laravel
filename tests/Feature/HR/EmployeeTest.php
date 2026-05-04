<?php

namespace Tests\Feature\HR;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_index_loads_when_authenticated_with_permission(): void
    {
        $this->loginAdmin();
        $response = $this->get($this->clinicUrl('/employees'));
        $response->assertStatus(200);
    }

    public function test_employee_create_loads_when_authenticated_with_permission(): void
    {
        $this->loginAdmin();
        $response = $this->get($this->clinicUrl('/employees/create'));
        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_redirected_from_employees(): void
    {
        $this->bindClinic();
        $response = $this->get($this->clinicUrl('/employees'));
        $response->assertRedirect();
    }
}
