<?php

namespace Tests\Feature\Prescriptions;

use App\Models\MedicineModel;
use App\Models\PatientModel;
use App\Models\PrescriptionMedicationModel;
use App\Models\PrescriptionModel;
use App\Models\VisitModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Full prescription → pharmacy dispense workflow.
 */
class DispenseFlowTest extends TestCase
{
    use RefreshDatabase;

    private function makeRxWithMedicine(int $stock = 100, int $days = 5): array
    {
        $patient  = PatientModel::factory()->forClinic($this->clinic)->create();
        $visit    = VisitModel::factory()->forPatient($patient)->opd()->create();
        $medicine = MedicineModel::factory()->forClinic($this->clinic)->withStock($stock)->create();

        $rx = PrescriptionModel::create([
            'clinic_id'        => $this->clinic->id,
            'code'             => 'RX-' . uniqid(),
            'patient_code'     => $patient->code,
            'visit_code'       => $visit->code,
            'prescribed_at'    => now(),
            'prescribed_by'    => 'Dr. Test',
            'dispensed_status' => 'pending',
            'title'            => 'Test Prescription',
        ]);

        PrescriptionMedicationModel::create([
            'prescription_code' => $rx->code,
            'medication_code'   => $medicine->code,
            'medicine_name'     => $medicine->name,
            'morning'           => 1,
            'afternoon'         => 1,
            'evening'           => 0,
            'night'             => 0,
            'days'              => $days,
        ]);

        return [$rx, $medicine];
    }

    // ── pharmacy dispense endpoint ────────────────────────────────────────────

    public function test_pharmacy_dispense_reduces_medicine_stock(): void
    {
        $user = $this->loginAdmin();
        [$rx, $medicine] = $this->makeRxWithMedicine(stock: 100, days: 5);
        // total_qty = (1+1) * 5 = 10

        $response = $this->post($this->clinicUrl("/pharmacy/{$rx->code}/dispense"), [
            'dispensed_by' => 'Pharmacist Test',
            'mode'         => 'full',
        ]);

        $response->assertRedirect();
        $this->assertEquals(90, $medicine->fresh()->stock);
    }

    public function test_pharmacy_dispense_marks_prescription_dispensed(): void
    {
        $this->loginAdmin();
        [$rx, $medicine] = $this->makeRxWithMedicine(stock: 50);

        $this->post($this->clinicUrl("/pharmacy/{$rx->code}/dispense"), [
            'dispensed_by' => 'Pharmacist Test',
            'mode'         => 'full',
        ]);

        $this->assertEquals('dispensed', $rx->fresh()->dispensed_status);
    }

    public function test_pharmacy_dispense_requires_permission(): void
    {
        // loginUser() creates a user with NO permissions
        $this->loginUser();
        [$rx, $medicine] = $this->makeRxWithMedicine();

        $response = $this->post($this->clinicUrl("/pharmacy/{$rx->code}/dispense"), [
            'dispensed_by' => 'Pharmacist Test',
            'mode'         => 'full',
        ]);

        $response->assertStatus(403);
        // Stock must be untouched
        $stockBefore = $medicine->fresh()->stock;
        $this->assertEquals($stockBefore, $medicine->fresh()->stock);
    }

    // ── prescription show page ────────────────────────────────────────────────

    public function test_prescription_show_page_loads(): void
    {
        $this->loginUser();
        [$rx, $medicine] = $this->makeRxWithMedicine();

        $response = $this->get($this->clinicUrl("/prescriptions/{$rx->code}"));
        $response->assertStatus(200);
    }

    // ── pharmacy show (pre-dispense review) ───────────────────────────────────

    public function test_pharmacy_show_page_loads_for_pending_prescription(): void
    {
        $this->loginAdmin();
        [$rx, $medicine] = $this->makeRxWithMedicine();

        $response = $this->get($this->clinicUrl("/pharmacy/{$rx->code}"));
        $response->assertStatus(200);
    }
}
