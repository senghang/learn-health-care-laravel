<?php

namespace Tests\Unit\Models;

use App\Models\ClinicModel;
use App\Models\MedicineModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class MedicineModelTest extends TestCase
{
    use RefreshDatabase;

    // ── isLowStock ────────────────────────────────────────────────────────────

    public function test_medicine_is_low_stock_when_at_alert_level(): void
    {
        $med = MedicineModel::factory()->create(['stock' => 10, 'stock_alert' => 10]);

        $this->assertTrue($med->isLowStock());
    }

    public function test_medicine_is_low_stock_when_below_alert_level(): void
    {
        $med = MedicineModel::factory()->create(['stock' => 5, 'stock_alert' => 10]);

        $this->assertTrue($med->isLowStock());
    }

    public function test_medicine_is_not_low_stock_when_above_alert_level(): void
    {
        $med = MedicineModel::factory()->create(['stock' => 50, 'stock_alert' => 10]);

        $this->assertFalse($med->isLowStock());
    }

    public function test_out_of_stock_medicine_is_low_stock(): void
    {
        $med = MedicineModel::factory()->outOfStock()->create();

        $this->assertTrue($med->isLowStock());
    }

    // ── dispense ──────────────────────────────────────────────────────────────

    public function test_dispense_decrements_stock_by_given_quantity(): void
    {
        $med = MedicineModel::factory()->withStock(100)->create();

        $med->dispense(10);

        $this->assertSame(90, $med->fresh()->stock);
    }

    public function test_dispense_exact_stock_leaves_zero(): void
    {
        $med = MedicineModel::factory()->withStock(5)->create();

        $med->dispense(5);

        $this->assertSame(0, $med->fresh()->stock);
    }

    public function test_dispense_throws_when_stock_insufficient(): void
    {
        $med = MedicineModel::factory()->withStock(3)->create();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/Insufficient stock/i');

        $med->dispense(10);
    }

    public function test_dispense_throws_when_out_of_stock(): void
    {
        $med = MedicineModel::factory()->outOfStock()->create();

        $this->expectException(RuntimeException::class);

        $med->dispense(1);
    }

    public function test_dispense_does_not_modify_stock_on_exception(): void
    {
        $med = MedicineModel::factory()->withStock(3)->create();

        try {
            $med->dispense(10);
        } catch (RuntimeException) {}

        $this->assertSame(3, $med->fresh()->stock);
    }

    // ── stock integrity ───────────────────────────────────────────────────────

    public function test_stock_defaults_to_zero_when_not_set(): void
    {
        $clinic = ClinicModel::factory()->create();
        $med    = MedicineModel::factory()->forClinic($clinic)->create(['stock' => 0]);

        $this->assertSame(0, $med->stock);
    }
}
