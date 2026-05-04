<?php

namespace Tests\Unit\Services;

use App\Models\AdmissionModel;
use App\Models\MedicineModel;
use App\Models\PatientModel;
use App\Models\VisitModel;
use App\Services\AdmissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdmissionServiceTest extends TestCase
{
    use RefreshDatabase;

    private AdmissionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginUser();
        $this->service = new AdmissionService();
    }

    private function makeIPDVisit(): VisitModel
    {
        $patient = PatientModel::factory()->forClinic($this->clinic)->create();
        return VisitModel::factory()->forPatient($patient)->ipd()->create();
    }

    private function makeOPDVisit(): VisitModel
    {
        $patient = PatientModel::factory()->forClinic($this->clinic)->create();
        return VisitModel::factory()->forPatient($patient)->opd()->create();
    }

    // ── admit ─────────────────────────────────────────────────────────────────

    public function test_admit_ipd_visit_creates_admission_record(): void
    {
        $visit = $this->makeIPDVisit();

        $admission = $this->service->admit([
            'visit_code'      => $visit->code,
            'admission_type'  => 'Medical',
            'attending_doctor' => 'Dr. House',
        ]);

        $this->assertEquals(AdmissionModel::STATUS_ADMITTED, $admission->status);
        $this->assertDatabaseHas('admissions', [
            'visit_code' => $visit->code,
            'status'     => AdmissionModel::STATUS_ADMITTED,
        ]);
    }

    public function test_admit_updates_visit_admission_status(): void
    {
        $visit = $this->makeIPDVisit();

        $this->service->admit([
            'visit_code'     => $visit->code,
            'admission_type' => 'Elective',
        ]);

        $this->assertEquals('admitted', $visit->fresh()->admission_status);
    }

    public function test_admit_throws_for_opd_visit(): void
    {
        $visit = $this->makeOPDVisit();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/OPD|IPD/');

        $this->service->admit([
            'visit_code'     => $visit->code,
            'admission_type' => 'Medical',
        ]);
    }

    public function test_admit_throws_when_visit_already_admitted(): void
    {
        $visit = $this->makeIPDVisit();

        $this->service->admit([
            'visit_code'     => $visit->code,
            'admission_type' => 'Medical',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/already has active admission/');

        $this->service->admit([
            'visit_code'     => $visit->code,
            'admission_type' => 'Medical',
        ]);
    }

    // ── addTreatment ──────────────────────────────────────────────────────────

    public function test_add_treatment_creates_treatment_record(): void
    {
        $visit     = $this->makeIPDVisit();
        $admission = $this->service->admit([
            'visit_code'     => $visit->code,
            'admission_type' => 'Medical',
        ]);

        $this->service->addTreatment($admission->code, [
            'treatment_type' => 'Medication',
            'name'           => 'IV Drip NS 0.9%',
            'instructions'   => 'Run at 80 ml/hr',
            'frequency'      => 'Continuous',
            'route'          => 'IV',
        ]);

        $this->assertDatabaseHas('treatments', [
            'admission_code' => $admission->code,
            'name'           => 'IV Drip NS 0.9%',
            'status'         => 'ordered',
        ]);
    }

    // ── addMedication ─────────────────────────────────────────────────────────

    public function test_add_ipd_medication_deducts_stock(): void
    {
        $medicine  = MedicineModel::factory()->forClinic($this->clinic)->withStock(50)->create();
        $visit     = $this->makeIPDVisit();
        $admission = $this->service->admit([
            'visit_code'     => $visit->code,
            'admission_type' => 'Medical',
        ]);

        $this->service->addMedication($admission->code, [
            'medicine_id' => $medicine->id,
            'quantity'    => 5,
            'dosage'      => '500mg',
            'route'       => 'Oral',
            'frequency'   => 'BID',
        ]);

        $this->assertEquals(45, $medicine->fresh()->stock);
    }

    public function test_add_ipd_medication_throws_when_stock_insufficient(): void
    {
        $medicine  = MedicineModel::factory()->forClinic($this->clinic)->withStock(2)->create();
        $visit     = $this->makeIPDVisit();
        $admission = $this->service->admit([
            'visit_code'     => $visit->code,
            'admission_type' => 'Medical',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/[Ii]nsufficient/');

        $this->service->addMedication($admission->code, [
            'medicine_id' => $medicine->id,
            'quantity'    => 10,
        ]);
    }

    // ── discharge ─────────────────────────────────────────────────────────────

    public function test_discharge_sets_discharged_status(): void
    {
        $visit     = $this->makeIPDVisit();
        $admission = $this->service->admit([
            'visit_code'     => $visit->code,
            'admission_type' => 'Medical',
        ]);

        $discharged = $this->service->discharge($admission->code, [
            'discharge_type'    => 'Normal',
            'discharge_summary' => 'Patient fully recovered.',
        ]);

        $this->assertEquals(AdmissionModel::STATUS_DISCHARGED, $discharged->status);
        $this->assertNotNull($discharged->discharged_at);
    }

    public function test_discharge_updates_parent_visit(): void
    {
        $visit     = $this->makeIPDVisit();
        $admission = $this->service->admit([
            'visit_code'     => $visit->code,
            'admission_type' => 'Medical',
        ]);

        $this->service->discharge($admission->code, ['discharge_type' => 'Normal']);

        $this->assertEquals('discharged', $visit->fresh()->admission_status);
        $this->assertNotNull($visit->fresh()->discharged_at);
    }

    public function test_discharge_throws_when_already_discharged(): void
    {
        $visit     = $this->makeIPDVisit();
        $admission = $this->service->admit([
            'visit_code'     => $visit->code,
            'admission_type' => 'Medical',
        ]);

        $this->service->discharge($admission->code, ['discharge_type' => 'Normal']);

        $this->expectException(\RuntimeException::class);
        $this->service->discharge($admission->code, ['discharge_type' => 'Normal']);
    }
}
