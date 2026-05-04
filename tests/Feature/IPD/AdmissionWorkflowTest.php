<?php

namespace Tests\Feature\IPD;

use App\Models\AdmissionModel;
use App\Models\MedicineModel;
use App\Models\PatientModel;
use App\Models\VisitModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Full IPD admission workflow via HTTP:
 * admit → add treatment → add medication → discharge
 */
class AdmissionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function makeIPDVisit(): VisitModel
    {
        $patient = PatientModel::factory()->forClinic($this->clinic)->create();
        return VisitModel::factory()->forPatient($patient)->ipd()->create();
    }

    // ── admit ─────────────────────────────────────────────────────────────────

    public function test_admit_ipd_visit_via_http(): void
    {
        $this->loginUser();
        $visit = $this->makeIPDVisit();

        $response = $this->post($this->clinicUrl("/admissions/{$visit->code}/admit"), [
            'admission_type'  => 'Medical',
            'attending_doctor' => 'Dr. House',
            'admission_reason' => 'Severe fever',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('admissions', [
            'visit_code' => $visit->code,
            'status'     => AdmissionModel::STATUS_ADMITTED,
        ]);
    }

    public function test_admit_updates_visit_admission_status(): void
    {
        $this->loginUser();
        $visit = $this->makeIPDVisit();

        $this->post($this->clinicUrl("/admissions/{$visit->code}/admit"), [
            'admission_type' => 'Medical',
        ]);

        $this->assertEquals('admitted', $visit->fresh()->admission_status);
    }

    // ── add treatment ─────────────────────────────────────────────────────────

    public function test_add_treatment_via_http(): void
    {
        $this->loginUser();
        $visit     = $this->makeIPDVisit();
        $admission = AdmissionModel::factory()->forVisit($visit, $this->clinic)->admitted()->create();

        $response = $this->post($this->clinicUrl("/admissions/{$admission->code}/treatment"), [
            'treatment_type' => 'Medication',
            'name'           => 'IV Drip 0.9% NaCl',
            'instructions'   => 'Run at 80 ml/hr',
            'frequency'      => 'Continuous',
            'route'          => 'IV',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('treatments', [
            'admission_code' => $admission->code,
            'name'           => 'IV Drip 0.9% NaCl',
        ]);
    }

    // ── add medication ────────────────────────────────────────────────────────

    public function test_add_ipd_medication_via_http_deducts_stock(): void
    {
        $this->loginUser();
        $medicine  = MedicineModel::factory()->forClinic($this->clinic)->withStock(50)->create();
        $visit     = $this->makeIPDVisit();
        $admission = AdmissionModel::factory()->forVisit($visit, $this->clinic)->admitted()->create();

        $response = $this->post($this->clinicUrl("/admissions/{$admission->code}/medication"), [
            'medicine_id' => $medicine->id,
            'quantity'    => 4,
            'dosage'      => '500mg',
            'route'       => 'Oral',
            'frequency'   => 'BID',
        ]);

        $response->assertRedirect();
        $this->assertEquals(46, $medicine->fresh()->stock);
    }

    // ── discharge ─────────────────────────────────────────────────────────────

    public function test_discharge_patient_via_http(): void
    {
        $this->loginUser();
        $visit     = $this->makeIPDVisit();
        $admission = AdmissionModel::factory()->forVisit($visit, $this->clinic)->admitted()->create();

        $response = $this->post($this->clinicUrl("/admissions/{$admission->code}/discharge"), [
            'discharge_type'    => 'Normal',
            'discharge_summary' => 'Patient recovered fully.',
        ]);

        $response->assertRedirect();
        $this->assertEquals(AdmissionModel::STATUS_DISCHARGED, $admission->fresh()->status);
    }

    // ── admissions index ──────────────────────────────────────────────────────

    public function test_admissions_index_shows_active_admissions(): void
    {
        $this->loginUser();
        $visit     = $this->makeIPDVisit();
        AdmissionModel::factory()->forVisit($visit, $this->clinic)->admitted()->create();

        $response = $this->get($this->clinicUrl('/admissions'));
        $response->assertStatus(200);
    }
}
