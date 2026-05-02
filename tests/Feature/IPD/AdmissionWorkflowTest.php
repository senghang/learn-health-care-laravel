<?php

namespace Tests\Feature\IPD;

use App\Models\AdmissionModel;
use App\Models\BedModel;
use App\Models\MedicineModel;
use App\Models\PatientModel;
use App\Models\RoomModel;
use App\Models\VisitModel;
use App\Models\WardModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesClinicEnvironment;
use Tests\TestCase;

class AdmissionWorkflowTest extends TestCase
{
    use RefreshDatabase, CreatesClinicEnvironment;

    private PatientModel $patient;
    private VisitModel   $ipdVisit;
    private WardModel    $ward;
    private RoomModel    $room;
    private BedModel     $bed;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpClinic();

        $this->patient  = PatientModel::factory()->forClinic($this->clinic)->create();
        $this->ipdVisit = VisitModel::factory()->forPatient($this->patient)->ipd()->create();

        $this->ward = WardModel::factory()->forClinic($this->clinic)->create();
        $this->room = RoomModel::factory()->forWard($this->ward)->create();
        $this->bed  = BedModel::factory()->available()->inWard($this->ward, $this->room)->create();
    }

    // ── Admit patient ─────────────────────────────────────────────────────────

    public function test_ipd_visit_can_be_admitted_with_a_bed(): void
    {
        $response = $this->post("/admissions/{$this->ipdVisit->code}/admit", [
            'bed_id'           => $this->bed->id,
            'ward_id'          => $this->ward->id,
            'admission_type'   => 'Medical',
            'attending_doctor' => 'Dr. Test',
            'admitted_at'      => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        // Admission created
        $this->assertDatabaseHas('admissions', [
            'clinic_id'    => $this->clinic->id,
            'visit_code'   => $this->ipdVisit->code,
            'patient_code' => $this->patient->code,
            'status'       => 'admitted',
            'bed_id'       => $this->bed->id,
        ]);

        // Bed marked occupied
        $this->assertDatabaseHas('beds', [
            'id'                   => $this->bed->id,
            'status'               => 'occupied',
            'current_visit_code'   => $this->ipdVisit->code,
            'current_patient_code' => $this->patient->code,
        ]);

        // Visit admission_status updated
        $this->assertDatabaseHas('visits', [
            'code'             => $this->ipdVisit->code,
            'admission_status' => 'admitted',
        ]);
    }

    public function test_admit_without_bed_creates_admission_without_bed(): void
    {
        $response = $this->post("/admissions/{$this->ipdVisit->code}/admit", [
            'admission_type' => 'Emergency',
            'admitted_at'    => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('admissions', [
            'visit_code' => $this->ipdVisit->code,
            'bed_id'     => null,
            'status'     => 'admitted',
        ]);
    }

    public function test_opd_visit_cannot_be_admitted(): void
    {
        $opdVisit = VisitModel::factory()->forPatient($this->patient)->opd()->create();

        $response = $this->post("/admissions/{$opdVisit->code}/admit", [
            'admitted_at' => now()->format('Y-m-d H:i:s'),
        ]);

        // Should redirect back with an error
        $response->assertRedirect();
        $this->assertDatabaseMissing('admissions', ['visit_code' => $opdVisit->code]);
    }

    public function test_cannot_admit_a_visit_that_is_already_admitted(): void
    {
        // First admission
        $this->post("/admissions/{$this->ipdVisit->code}/admit", [
            'admitted_at' => now()->format('Y-m-d H:i:s'),
        ]);

        // Second attempt
        $bed2 = BedModel::factory()->available()->inWard($this->ward, $this->room)->create();
        $response = $this->post("/admissions/{$this->ipdVisit->code}/admit", [
            'bed_id'      => $bed2->id,
            'admitted_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect();
        $this->assertCount(1, AdmissionModel::where('visit_code', $this->ipdVisit->code)->get());
    }

    public function test_cannot_admit_with_occupied_bed(): void
    {
        $occupiedBed = BedModel::factory()->occupied()->inWard($this->ward, $this->room)->create();

        $response = $this->post("/admissions/{$this->ipdVisit->code}/admit", [
            'bed_id'      => $occupiedBed->id,
            'admitted_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('admissions', ['visit_code' => $this->ipdVisit->code]);
    }

    // ── Add treatment ─────────────────────────────────────────────────────────

    public function test_can_add_treatment_to_active_admission(): void
    {
        $admission = AdmissionModel::factory()->forVisit($this->ipdVisit, $this->clinic)->admitted()->create();

        $response = $this->post("/admissions/{$admission->code}/treatment", [
            'treatment_type' => 'procedure',
            'name'           => 'IV Fluid therapy',
            'frequency'      => 'Q8H',
            'route'          => 'IV',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('treatments', [
            'admission_code' => $admission->code,
            'name'           => 'IV Fluid therapy',
            'status'         => 'ordered',
            'treatment_type' => 'procedure',
        ]);
    }

    public function test_add_treatment_requires_type_and_name(): void
    {
        $admission = AdmissionModel::factory()->forVisit($this->ipdVisit, $this->clinic)->admitted()->create();

        $response = $this->post("/admissions/{$admission->code}/treatment", []);

        $response->assertSessionHasErrors(['treatment_type', 'name']);
    }

    public function test_cannot_add_treatment_to_discharged_admission(): void
    {
        $admission = AdmissionModel::factory()->forVisit($this->ipdVisit, $this->clinic)->discharged()->create();

        $response = $this->post("/admissions/{$admission->code}/treatment", [
            'treatment_type' => 'procedure',
            'name'           => 'Should fail',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('treatments', [
            'admission_code' => $admission->code,
            'name'           => 'Should fail',
        ]);
    }

    // ── Add medication ────────────────────────────────────────────────────────

    public function test_can_add_medication_and_stock_is_deducted_immediately(): void
    {
        $medicine  = MedicineModel::factory()->forClinic($this->clinic)->withStock(50)->create();
        $admission = AdmissionModel::factory()->forVisit($this->ipdVisit, $this->clinic)->admitted()->create();

        $response = $this->post("/admissions/{$admission->code}/medication", [
            'medicine_id' => $medicine->id,
            'quantity'    => 5,
            'dosage'      => '500mg',
            'route'       => 'Oral',
            'frequency'   => 'BD',
            'start_date'  => now()->toDateString(),
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        // Medication order created
        $this->assertDatabaseHas('inpatient_medications', [
            'admission_code' => $admission->code,
            'medicine_id'    => $medicine->id,
            'quantity'       => 5,
            'status'         => 'active',
        ]);

        // Stock deducted at order time
        $this->assertSame(45, $medicine->fresh()->stock);
    }

    public function test_adding_medication_fails_with_insufficient_stock(): void
    {
        $medicine  = MedicineModel::factory()->forClinic($this->clinic)->withStock(3)->create();
        $admission = AdmissionModel::factory()->forVisit($this->ipdVisit, $this->clinic)->admitted()->create();

        $response = $this->post("/admissions/{$admission->code}/medication", [
            'medicine_id' => $medicine->id,
            'quantity'    => 10,
        ]);

        $response->assertRedirect();
        // Stock NOT changed
        $this->assertSame(3, $medicine->fresh()->stock);
        $this->assertDatabaseMissing('inpatient_medications', [
            'admission_code' => $admission->code,
            'medicine_id'    => $medicine->id,
        ]);
    }

    public function test_add_medication_requires_medicine_id(): void
    {
        $admission = AdmissionModel::factory()->forVisit($this->ipdVisit, $this->clinic)->admitted()->create();

        $response = $this->post("/admissions/{$admission->code}/medication", [
            'quantity' => 5,
        ]);

        $response->assertSessionHasErrors('medicine_id');
    }

    // ── Transfer bed ──────────────────────────────────────────────────────────

    public function test_can_transfer_patient_to_new_bed(): void
    {
        $admission = AdmissionModel::factory()
            ->forVisit($this->ipdVisit, $this->clinic)
            ->admitted()
            ->withBed($this->bed)
            ->create();

        // Manually mark the first bed occupied
        $this->bed->assignTo($this->ipdVisit->code, $this->patient->code);

        $newBed = BedModel::factory()->available()->inWard($this->ward, $this->room)->create();

        $response = $this->patch("/admissions/{$admission->code}/transfer-bed", [
            'bed_id' => $newBed->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        // Old bed released to cleaning
        $this->assertSame('cleaning', $this->bed->fresh()->status);

        // New bed occupied
        $this->assertSame('occupied', $newBed->fresh()->status);

        // Admission updated
        $this->assertDatabaseHas('admissions', [
            'code'   => $admission->code,
            'bed_id' => $newBed->id,
        ]);
    }

    public function test_cannot_transfer_to_occupied_bed(): void
    {
        $admission = AdmissionModel::factory()
            ->forVisit($this->ipdVisit, $this->clinic)
            ->admitted()
            ->withBed($this->bed)
            ->create();

        $occupiedBed = BedModel::factory()->occupied()->inWard($this->ward, $this->room)->create();

        $response = $this->patch("/admissions/{$admission->code}/transfer-bed", [
            'bed_id' => $occupiedBed->id,
        ]);

        $response->assertRedirect();
        // Admission bed unchanged
        $this->assertDatabaseHas('admissions', [
            'code'   => $admission->code,
            'bed_id' => $this->bed->id,
        ]);
    }

    // ── Discharge ─────────────────────────────────────────────────────────────

    public function test_can_discharge_admitted_patient(): void
    {
        $this->bed->assignTo($this->ipdVisit->code, $this->patient->code);
        $admission = AdmissionModel::factory()
            ->forVisit($this->ipdVisit, $this->clinic)
            ->admitted()
            ->withBed($this->bed)
            ->create();

        $response = $this->post("/admissions/{$admission->code}/discharge", [
            'discharge_type'      => 'Normal',
            'discharge_condition' => 'Recovered',
            'discharged_at'       => now()->format('Y-m-d H:i:s'),
            'discharge_summary'   => 'Patient recovered fully.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        // Admission discharged
        $this->assertDatabaseHas('admissions', [
            'code'   => $admission->code,
            'status' => 'discharged',
        ]);

        // Bed released to cleaning
        $this->assertSame('cleaning', $this->bed->fresh()->status);

        // Visit admission_status updated
        $this->assertDatabaseHas('visits', [
            'code'             => $this->ipdVisit->code,
            'admission_status' => 'discharged',
        ]);
    }

    public function test_discharge_marks_active_treatments_completed(): void
    {
        $this->bed->assignTo($this->ipdVisit->code, $this->patient->code);
        $admission = AdmissionModel::factory()
            ->forVisit($this->ipdVisit, $this->clinic)
            ->admitted()
            ->withBed($this->bed)
            ->create();

        // Add a treatment
        $this->post("/admissions/{$admission->code}/treatment", [
            'treatment_type' => 'nursing_care',
            'name'           => 'Wound dressing',
        ]);

        // Discharge
        $this->post("/admissions/{$admission->code}/discharge", [
            'discharge_type' => 'Normal',
            'discharged_at'  => now()->format('Y-m-d H:i:s'),
        ]);

        $this->assertDatabaseHas('treatments', [
            'admission_code' => $admission->code,
            'name'           => 'Wound dressing',
            'status'         => 'completed',
        ]);
    }

    public function test_discharge_requires_discharge_type(): void
    {
        $admission = AdmissionModel::factory()
            ->forVisit($this->ipdVisit, $this->clinic)
            ->admitted()
            ->create();

        $response = $this->post("/admissions/{$admission->code}/discharge", [
            'discharged_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertSessionHasErrors('discharge_type');
        // Admission still admitted
        $this->assertSame('admitted', $admission->fresh()->status);
    }

    // ── Admissions list ───────────────────────────────────────────────────────

    public function test_admissions_index_loads(): void
    {
        AdmissionModel::factory()->forVisit($this->ipdVisit, $this->clinic)->admitted()->create();

        $response = $this->get('/admissions');

        $response->assertStatus(200);
    }

    public function test_admissions_show_loads(): void
    {
        $admission = AdmissionModel::factory()->forVisit($this->ipdVisit, $this->clinic)->admitted()->create();

        $response = $this->get("/admissions/{$admission->code}");

        $response->assertStatus(200);
        $response->assertSee($admission->code);
    }
}
