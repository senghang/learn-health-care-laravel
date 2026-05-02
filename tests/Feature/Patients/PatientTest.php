<?php

namespace Tests\Feature\Patients;

use App\Models\PatientModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesClinicEnvironment;
use Tests\TestCase;

class PatientTest extends TestCase
{
    use RefreshDatabase, CreatesClinicEnvironment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpClinic();
    }

    // ── List ──────────────────────────────────────────────────────────────────

    public function test_patient_list_page_loads(): void
    {
        PatientModel::factory()->forClinic($this->clinic)->count(3)->create();

        $response = $this->get('/patients');

        $response->assertStatus(200);
    }

    public function test_patient_list_only_shows_own_clinic_patients(): void
    {
        // Our clinic's patient
        PatientModel::factory()->forClinic($this->clinic)->create(['surname' => 'OwnClinic']);

        // Another clinic's patient (different clinic)
        PatientModel::factory()->create(['surname' => 'OtherClinic']);

        $response = $this->get('/patients');

        $response->assertStatus(200);
        $response->assertSee('OwnClinic');
        $response->assertDontSee('OtherClinic');
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function test_patient_create_page_loads(): void
    {
        $response = $this->get('/patients/create');

        $response->assertStatus(200);
    }

    public function test_can_create_a_patient_with_valid_data(): void
    {
        $response = $this->post('/patients', [
            'surname'   => 'Dara',
            'name'      => 'Chan',
            'sex'       => 'M',
            'birthdate' => '1990-05-15',
            'phone'     => '012345678',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('patients', [
            'clinic_id' => $this->clinic->id,
            'surname'   => 'Dara',
            'name'      => 'Chan',
            'sex'       => 'M',
        ]);
    }

    public function test_patient_code_is_generated_on_create(): void
    {
        $this->post('/patients', [
            'surname' => 'Test',
            'name'    => 'Patient',
            'sex'     => 'F',
        ]);

        $patient = PatientModel::where('clinic_id', $this->clinic->id)
            ->where('surname', 'Test')
            ->first();

        $this->assertNotNull($patient);
        $this->assertNotEmpty($patient->code);
        $this->assertStringStartsWith('PT', $patient->code);
    }

    public function test_create_patient_requires_surname(): void
    {
        $response = $this->post('/patients', [
            'name' => 'Chan',
            'sex'  => 'M',
        ]);

        $response->assertSessionHasErrors('surname');
    }

    public function test_create_patient_requires_name(): void
    {
        $response = $this->post('/patients', [
            'surname' => 'Dara',
            'sex'     => 'M',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_create_patient_requires_sex(): void
    {
        $response = $this->post('/patients', [
            'surname' => 'Dara',
            'name'    => 'Chan',
        ]);

        $response->assertSessionHasErrors('sex');
    }

    public function test_sex_must_be_m_or_f(): void
    {
        $response = $this->post('/patients', [
            'surname' => 'Dara',
            'name'    => 'Chan',
            'sex'     => 'X',
        ]);

        $response->assertSessionHasErrors('sex');
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function test_patient_show_page_loads(): void
    {
        $patient = PatientModel::factory()->forClinic($this->clinic)->create();

        $response = $this->get("/patients/{$patient->code}");

        $response->assertStatus(200);
        $response->assertSee($patient->surname);
    }

    public function test_show_patient_from_other_clinic_returns_404(): void
    {
        $other = PatientModel::factory()->create(); // different clinic

        $response = $this->get("/patients/{$other->code}");

        $response->assertStatus(404);
    }
}
