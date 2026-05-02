<?php

namespace Tests\Unit\Services;

use App\Models\AdmissionModel;
use App\Models\BedModel;
use App\Models\ClinicModel;
use App\Models\MedicineModel;
use App\Models\PatientModel;
use App\Models\RoomModel;
use App\Models\VisitModel;
use App\Models\WardModel;
use App\Services\AdmissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class AdmissionServiceTest extends TestCase
{
    use RefreshDatabase;

    private AdmissionService $service;
    private ClinicModel      $clinic;
    private PatientModel     $patient;
    private VisitModel       $ipdVisit;
    private WardModel        $ward;
    private RoomModel        $room;
    private BedModel         $bed;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service  = app(AdmissionService::class);
        $this->clinic   = ClinicModel::factory()->create();

        app()->instance('currentClinic', $this->clinic);

        $this->patient  = PatientModel::factory()->forClinic($this->clinic)->create();
        $this->ipdVisit = VisitModel::factory()->forPatient($this->patient)->ipd()->create();

        $this->ward = WardModel::factory()->forClinic($this->clinic)->create();
        $this->room = RoomModel::factory()->forWard($this->ward)->create();
        $this->bed  = BedModel::factory()->available()->inWard($this->ward, $this->room)->create();
    }

    // ── admit() ───────────────────────────────────────────────────────────────

    public function test_admit_creates_admission_and_marks_bed_occupied(): void
    {
        $admission = $this->service->admit([
            'visit_code'     => $this->ipdVisit->code,
            'bed_id'         => $this->bed->id,
            'ward_id'        => $this->ward->id,
            'admission_type' => 'Medical',
            'admitted_at'    => now()->toDateTimeString(),
        ]);

        $this->assertSame('admitted', $admission->status);
        $this->assertSame($this->bed->id, $admission->bed_id);
        $this->assertSame($this->ipdVisit->code, $admission->visit_code);

        // Bed occupied
        $this->assertSame('occupied', $this->bed->fresh()->status);

        // Visit updated
        $this->assertSame('admitted', $this->ipdVisit->fresh()->admission_status);
    }

    public function test_admit_throws_when_visit_is_opd(): void
    {
        $opdVisit = VisitModel::factory()->forPatient($this->patient)->opd()->create();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/OPD/i');

        $this->service->admit(['visit_code' => $opdVisit->code]);
    }

    public function test_admit_throws_when_visit_already_has_active_admission(): void
    {
        // First admission
        $this->service->admit(['visit_code' => $this->ipdVisit->code, 'admitted_at' => now()]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/already has active admission/i');

        // Second attempt
        $this->service->admit(['visit_code' => $this->ipdVisit->code]);
    }

    public function test_admit_throws_when_bed_is_not_available(): void
    {
        $this->bed->assignTo('OTHER-VISIT', 'OTHER-PATIENT');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/not available/i');

        $this->service->admit([
            'visit_code' => $this->ipdVisit->code,
            'bed_id'     => $this->bed->id,
        ]);
    }

    public function test_admit_creates_encounter_record(): void
    {
        $this->service->admit([
            'visit_code'  => $this->ipdVisit->code,
            'admitted_at' => now()->toDateTimeString(),
        ]);

        $this->assertDatabaseHas('out_in_patients', [
            'visit_code'  => $this->ipdVisit->code,
            'visit_type'  => 'IPD',
            'status'      => 'active',
        ]);
    }

    // ── addMedication() ───────────────────────────────────────────────────────

    public function test_add_medication_deducts_stock_atomically(): void
    {
        $admission = AdmissionModel::factory()
            ->forVisit($this->ipdVisit, $this->clinic)
            ->admitted()
            ->create();

        $medicine = MedicineModel::factory()->forClinic($this->clinic)->withStock(100)->create();

        $this->service->addMedication($admission->code, [
            'medicine_id' => $medicine->id,
            'quantity'    => 10,
            'start_date'  => now()->toDateString(),
        ]);

        $this->assertSame(90, $medicine->fresh()->stock);
    }

    public function test_add_medication_throws_when_stock_insufficient(): void
    {
        $admission = AdmissionModel::factory()
            ->forVisit($this->ipdVisit, $this->clinic)
            ->admitted()
            ->create();

        $medicine = MedicineModel::factory()->forClinic($this->clinic)->withStock(2)->create();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Insufficient stock/i');

        $this->service->addMedication($admission->code, [
            'medicine_id' => $medicine->id,
            'quantity'    => 10,
        ]);
    }

    public function test_add_medication_creates_inventory_transaction(): void
    {
        $admission = AdmissionModel::factory()
            ->forVisit($this->ipdVisit, $this->clinic)
            ->admitted()
            ->create();

        $medicine = MedicineModel::factory()->forClinic($this->clinic)->withStock(50)->create();

        $this->service->addMedication($admission->code, [
            'medicine_id' => $medicine->id,
            'quantity'    => 5,
        ]);

        $this->assertDatabaseHas('inventory_transactions', [
            'clinic_id'   => $this->clinic->id,
            'medicine_id' => $medicine->id,
            'type'        => 'dispense',
            'quantity'    => -5,
        ]);
    }

    public function test_add_medication_throws_when_admission_not_active(): void
    {
        $admission = AdmissionModel::factory()
            ->forVisit($this->ipdVisit, $this->clinic)
            ->discharged()
            ->create();

        $medicine = MedicineModel::factory()->forClinic($this->clinic)->withStock(50)->create();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/not active/i');

        $this->service->addMedication($admission->code, [
            'medicine_id' => $medicine->id,
            'quantity'    => 5,
        ]);
    }

    // ── discharge() ──────────────────────────────────────────────────────────

    public function test_discharge_updates_all_linked_records(): void
    {
        $this->bed->assignTo($this->ipdVisit->code, $this->patient->code);

        $admission = AdmissionModel::factory()
            ->forVisit($this->ipdVisit, $this->clinic)
            ->admitted()
            ->withBed($this->bed)
            ->create();

        $this->service->addTreatment($admission->code, [
            'treatment_type' => 'therapy',
            'name'           => 'Physiotherapy',
        ]);

        $this->service->discharge($admission->code, [
            'discharge_type'      => 'Normal',
            'discharge_condition' => 'Recovered',
            'discharged_at'       => now()->toDateTimeString(),
        ]);

        // Admission discharged
        $this->assertSame('discharged', $admission->fresh()->status);

        // Bed released
        $this->assertSame('cleaning', $this->bed->fresh()->status);

        // Treatment completed
        $this->assertDatabaseHas('treatments', [
            'admission_code' => $admission->code,
            'status'         => 'completed',
        ]);

        // Visit updated
        $this->assertSame('discharged', $this->ipdVisit->fresh()->admission_status);
    }

    public function test_discharge_throws_when_already_discharged(): void
    {
        $admission = AdmissionModel::factory()
            ->forVisit($this->ipdVisit, $this->clinic)
            ->discharged()
            ->create();

        $this->expectException(RuntimeException::class);

        $this->service->discharge($admission->code, ['discharge_type' => 'Normal']);
    }

    // ── transferBed() ────────────────────────────────────────────────────────

    public function test_transfer_bed_swaps_beds_correctly(): void
    {
        $this->bed->assignTo($this->ipdVisit->code, $this->patient->code);

        $admission = AdmissionModel::factory()
            ->forVisit($this->ipdVisit, $this->clinic)
            ->admitted()
            ->withBed($this->bed)
            ->create();

        $newBed = BedModel::factory()->available()->inWard($this->ward, $this->room)->create();

        $this->service->transferBed($admission->code, $newBed->id);

        $this->assertSame('cleaning', $this->bed->fresh()->status);
        $this->assertSame('occupied', $newBed->fresh()->status);
        $this->assertSame($newBed->id, $admission->fresh()->bed_id);
    }

    public function test_transfer_bed_throws_when_new_bed_is_occupied(): void
    {
        $this->bed->assignTo($this->ipdVisit->code, $this->patient->code);

        $admission = AdmissionModel::factory()
            ->forVisit($this->ipdVisit, $this->clinic)
            ->admitted()
            ->withBed($this->bed)
            ->create();

        $occupiedBed = BedModel::factory()->occupied()->inWard($this->ward, $this->room)->create();

        $this->expectException(RuntimeException::class);

        $this->service->transferBed($admission->code, $occupiedBed->id);
    }
}
