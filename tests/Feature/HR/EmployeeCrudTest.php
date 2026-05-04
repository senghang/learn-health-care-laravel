<?php

namespace Tests\Feature\HR;

use App\Models\EmployeeModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Employee CRUD via HTTP (permission-gated).
 */
class EmployeeCrudTest extends TestCase
{
    use RefreshDatabase;

    // ── create / store ────────────────────────────────────────────────────────

    public function test_create_employee_stores_record(): void
    {
        $this->loginAdmin();

        $response = $this->post($this->clinicUrl('/employees'), [
            'surname'       => 'Chan',
            'name'          => 'Sokha',
            'gender'        => 'M',
            'birthdate'     => '1990-01-15',
            'phone'         => '0123456789',
            'employee_type' => 'Doctor',
            'hire_date'     => today()->toDateString(),
            'status'        => 'active',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('employees', [
            'surname'       => 'Chan',
            'name'          => 'Sokha',
            'employee_type' => 'Doctor',
        ]);
    }

    public function test_create_employee_validation_fails_without_required_fields(): void
    {
        $this->loginAdmin();

        $response = $this->post($this->clinicUrl('/employees'), []);

        $response->assertSessionHasErrors();
    }

    public function test_create_employee_denied_without_permission(): void
    {
        $this->loginUser(); // no permissions

        $response = $this->post($this->clinicUrl('/employees'), [
            'surname'       => 'Test',
            'name'          => 'User',
            'gender'        => 'F',
            'employee_type' => 'Nurse',
            'hire_date'     => today()->toDateString(),
            'status'        => 'Active',
        ]);

        $response->assertStatus(403);
    }

    // ── edit / update ─────────────────────────────────────────────────────────

    public function test_update_employee_persists_changes(): void
    {
        $this->loginAdmin();
        $employee = EmployeeModel::factory()->forClinic($this->clinic)->create([
            'specialization' => 'General',
        ]);

        $response = $this->patch($this->clinicUrl("/employees/{$employee->id}"), [
            'surname'       => $employee->surname,
            'name'          => $employee->name,
            'gender'        => $employee->gender,
            'birthdate'     => $employee->birthdate->toDateString(),
            'phone'         => $employee->phone,
            'employee_type' => $employee->employee_type,
            'hire_date'     => $employee->hire_date->toDateString(),
            'status'        => 'active',
            'specialization' => 'Cardiology',
        ]);

        $response->assertRedirect();
        $this->assertEquals('Cardiology', $employee->fresh()->specialization);
    }

    // ── show ──────────────────────────────────────────────────────────────────

    public function test_employee_show_page_loads(): void
    {
        $this->loginAdmin();
        $employee = EmployeeModel::factory()->forClinic($this->clinic)->create();

        $response = $this->get($this->clinicUrl("/employees/{$employee->id}"));
        $response->assertStatus(200);
    }
}
