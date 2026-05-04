<?php

namespace Tests\Unit\Services;

use App\Models\MedicineModel;
use App\Models\PatientModel;
use App\Models\PrescriptionMedicationModel;
use App\Models\PrescriptionModel;
use App\Models\VisitModel;
use App\Services\PharmacyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PharmacyServiceTest extends TestCase
{
    use RefreshDatabase;

    private PharmacyService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginUser();
        $this->service = new PharmacyService();
    }

    /**
     * Create a prescription with one medication item linked to $medicine.
     * total_qty = (morning+afternoon+evening+night) * days
     */
    private function prescriptionWithMed(MedicineModel $medicine, int $days = 5, float $dosePerTime = 1.0): PrescriptionModel
    {
        $patient = PatientModel::factory()->forClinic($this->clinic)->create();
        $visit   = VisitModel::factory()->forPatient($patient)->opd()->create();

        $rx = PrescriptionModel::create([
            'clinic_id'        => $this->clinic->id,
            'code'             => 'RX-TEST-' . uniqid(),
            'patient_code'     => $patient->code,
            'visit_code'       => $visit->code,
            'prescribed_at'    => now(),
            'prescribed_by'    => 'Dr. Test',
            'dispensed_status' => 'pending',
            'title'            => 'Test Rx',
        ]);

        PrescriptionMedicationModel::create([
            'prescription_code' => $rx->code,
            'medication_code'   => $medicine->code,   // links to MedicineModel.code
            'medicine_name'     => $medicine->name,
            'morning'           => $dosePerTime,
            'afternoon'         => $dosePerTime,
            'evening'           => 0,
            'night'             => 0,
            'days'              => $days,
            // total_qty = 2 * days
        ]);

        return $rx->load('medications');
    }

    // ── dispense (happy path) ─────────────────────────────────────────────────

    public function test_dispense_deducts_correct_quantity_from_stock(): void
    {
        $medicine = MedicineModel::factory()->forClinic($this->clinic)->withStock(100)->create();
        $rx       = $this->prescriptionWithMed($medicine, days: 5, dosePerTime: 1.0);
        // total_qty = (1+1) * 5 = 10

        $this->service->dispense($rx, 'Pharmacist');

        $this->assertEquals(90, $medicine->fresh()->stock);
    }

    public function test_dispense_creates_inventory_transaction(): void
    {
        $medicine = MedicineModel::factory()->forClinic($this->clinic)->withStock(50)->create();
        $rx       = $this->prescriptionWithMed($medicine, days: 3, dosePerTime: 1.0);
        // total_qty = 6

        $this->service->dispense($rx, 'Pharmacist');

        $this->assertDatabaseHas('inventory_transactions', [
            'medicine_id' => $medicine->id,
            'type'        => 'dispense',
            'quantity'    => -6,
        ]);
    }

    public function test_dispense_creates_dispense_record(): void
    {
        $medicine = MedicineModel::factory()->forClinic($this->clinic)->withStock(50)->create();
        $rx       = $this->prescriptionWithMed($medicine, days: 2, dosePerTime: 1.0);

        $this->service->dispense($rx, 'Pharmacist');

        $this->assertDatabaseHas('pharmacy_dispenses', [
            'prescription_code' => $rx->code,
            'medicine_id'       => $medicine->id,
            'status'            => 'dispensed',
        ]);
    }

    public function test_dispense_marks_prescription_as_dispensed(): void
    {
        $medicine = MedicineModel::factory()->forClinic($this->clinic)->withStock(100)->create();
        $rx       = $this->prescriptionWithMed($medicine);

        $this->service->dispense($rx, 'Pharmacist');

        $this->assertEquals('dispensed', $rx->fresh()->dispensed_status);
    }

    // ── dispense (error cases) ────────────────────────────────────────────────

    public function test_dispense_throws_when_stock_insufficient(): void
    {
        $medicine = MedicineModel::factory()->forClinic($this->clinic)->withStock(3)->create();
        $rx       = $this->prescriptionWithMed($medicine, days: 5, dosePerTime: 1.0);
        // total_qty = 10 > stock = 3

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/[Ii]nsufficient/');

        $this->service->dispense($rx, 'Pharmacist');
    }

    public function test_dispense_throws_when_already_dispensed(): void
    {
        $medicine = MedicineModel::factory()->forClinic($this->clinic)->withStock(100)->create();
        $rx       = $this->prescriptionWithMed($medicine, days: 2);

        $this->service->dispense($rx, 'Pharmacist');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/already.*dispensed/i');

        $this->service->dispense($rx->fresh(), 'Pharmacist');
    }

    // ── idempotency ───────────────────────────────────────────────────────────

    public function test_dispense_does_not_double_deduct_on_retry(): void
    {
        $medicine = MedicineModel::factory()->forClinic($this->clinic)->withStock(100)->create();
        $rx       = $this->prescriptionWithMed($medicine, days: 2, dosePerTime: 1.0);
        // total_qty = 4

        // First dispense — succeeds
        $this->service->dispense($rx, 'Pharmacist');
        $stockAfterFirst = $medicine->fresh()->stock; // 96

        // Second call should throw (already dispensed) — stock must not change further
        try {
            $this->service->dispense($rx->fresh(), 'Pharmacist');
        } catch (\RuntimeException) {
            // expected
        }

        $this->assertEquals($stockAfterFirst, $medicine->fresh()->stock);
    }
}
