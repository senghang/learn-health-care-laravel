<?php

namespace Tests\Feature\Patients;

use App\Models\PatientModel;
use App\Models\VisitModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Patient CRUD via HTTP including business rule: cannot delete with active visit.
 */
class PatientCrudTest extends TestCase
{
    use RefreshDatabase;

    // ── create ────────────────────────────────────────────────────────────────

    public function test_create_patient_stores_record(): void
    {
        $this->loginUser();

        $response = $this->post($this->clinicUrl('/patients'), [
            'surname'   => 'Sok',
            'name'      => 'Dara',
            'sex'       => 'M',
            'birthdate' => '1985-06-15',
            'phone'     => '012345678',
            'status'    => 'Active',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('patients', [
            'surname' => 'Sok',
            'name'    => 'Dara',
            'sex'     => 'M',
        ]);
    }

    public function test_create_patient_validation_fails_without_name(): void
    {
        $this->loginUser();

        $response = $this->post($this->clinicUrl('/patients'), [
            'sex'       => 'M',
            'birthdate' => '1985-01-01',
        ]);

        $response->assertSessionHasErrors();
    }

    // ── update ────────────────────────────────────────────────────────────────

    public function test_update_patient_persists_changes(): void
    {
        $this->loginUser();
        $patient = PatientModel::factory()->forClinic($this->clinic)->create([
            'phone' => '011111111',
        ]);

        $response = $this->patch($this->clinicUrl("/patients/{$patient->code}"), [
            'surname'   => $patient->surname,
            'name'      => $patient->name,
            'sex'       => $patient->sex,
            'birthdate' => $patient->birthdate->toDateString(),
            'phone'     => '099999999',
            'status'    => 'Active',
        ]);

        $response->assertRedirect();
        $this->assertEquals('099999999', $patient->fresh()->phone);
    }

    // ── destroy ───────────────────────────────────────────────────────────────

    public function test_delete_patient_soft_deletes(): void
    {
        $this->loginUser();
        $patient = PatientModel::factory()->forClinic($this->clinic)->create();

        $response = $this->delete($this->clinicUrl("/patients/{$patient->code}"));

        $response->assertRedirect();
        $this->assertSoftDeleted('patients', ['code' => $patient->code]);
    }

    public function test_delete_patient_with_active_visit_fails(): void
    {
        $this->loginUser();
        $patient = PatientModel::factory()->forClinic($this->clinic)->create();

        // Create an active (not discharged) visit
        VisitModel::factory()->forPatient($patient)->opd()->create([
            'discharged_at' => null,
        ]);

        $response = $this->delete($this->clinicUrl("/patients/{$patient->code}"));

        // Should redirect back with error, patient not deleted
        $response->assertRedirect();
        $this->assertDatabaseHas('patients', ['code' => $patient->code]);
        $this->assertNull(PatientModel::where('code', $patient->code)->first()->deleted_at);
    }

    // ── show ──────────────────────────────────────────────────────────────────

    public function test_patient_profile_page_loads(): void
    {
        $this->loginUser();
        $patient = PatientModel::factory()->forClinic($this->clinic)->create();

        $response = $this->get($this->clinicUrl("/patients/{$patient->code}"));
        $response->assertStatus(200);
    }
}
