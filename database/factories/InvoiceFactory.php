<?php

namespace Database\Factories;

use App\Models\ClinicModel;
use App\Models\InvoiceModel;
use App\Models\PatientModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = InvoiceModel::class;

    public function definition(): array
    {
        $clinic  = ClinicModel::factory()->create();
        $patient = PatientModel::factory()->forClinic($clinic)->create();
        $total   = $this->faker->randomFloat(2, 10000, 500000);

        return [
            'clinic_id'      => $clinic->id,
            'code'           => 'INV' . now()->format('Ymd') . $this->faker->unique()->numerify('####'),
            'patient_code'   => $patient->code,
            'payment_type'   => $this->faker->randomElement(['CASH', 'HEF', 'NSSF', 'CARD', 'BAKONG']),
            'invoice_date'   => now()->toDateString(),
            'subtotal'       => $total,
            'discount_total' => 0,
            'tax_total'      => 0,
            'total'          => $total,
            'status'         => 'pending',
            'cashier'        => $this->faker->name(),
        ];
    }

    public function paid(): static
    {
        return $this->state(['status' => 'paid']);
    }

    public function pending(): static
    {
        return $this->state(['status' => 'pending']);
    }

    public function forClinic(ClinicModel $clinic): static
    {
        return $this->state(['clinic_id' => $clinic->id]);
    }

    public function forPatient(PatientModel $patient): static
    {
        return $this->state([
            'clinic_id'    => $patient->clinic_id,
            'patient_code' => $patient->code,
        ]);
    }
}
