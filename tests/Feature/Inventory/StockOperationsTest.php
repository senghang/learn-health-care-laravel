<?php

namespace Tests\Feature\Inventory;

use App\Models\MedicineModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * HTTP-level stock operations: stock-in, stock-out, adjustment.
 */
class StockOperationsTest extends TestCase
{
    use RefreshDatabase;

    // ── stock-in ──────────────────────────────────────────────────────────────

    public function test_stock_in_increases_medicine_stock(): void
    {
        $this->loginUser();
        $medicine = MedicineModel::factory()->forClinic($this->clinic)->withStock(20)->create();

        $response = $this->post($this->clinicUrl('/inventory/stock-in'), [
            'medicine_id' => $medicine->id,
            'quantity'    => 30,
            'type'        => 'in',
            'supplier'    => 'Test Pharma',
        ]);

        $response->assertRedirect();
        $this->assertEquals(50, $medicine->fresh()->stock);
    }

    public function test_stock_in_creates_stock_movement_record(): void
    {
        $this->loginUser();
        $medicine = MedicineModel::factory()->forClinic($this->clinic)->withStock(10)->create();

        $this->post($this->clinicUrl('/inventory/stock-in'), [
            'medicine_id' => $medicine->id,
            'quantity'    => 15,
            'type'        => 'in',
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'medicine_id' => $medicine->id,
            'type'        => 'in',
            'quantity'    => 15,
        ]);
    }

    public function test_stock_in_validation_requires_minimum_quantity(): void
    {
        $this->loginUser();
        $medicine = MedicineModel::factory()->forClinic($this->clinic)->create();

        $response = $this->post($this->clinicUrl('/inventory/stock-in'), [
            'medicine_id' => $medicine->id,
            'quantity'    => 0,
            'type'        => 'in',
        ]);

        $response->assertSessionHasErrors('quantity');
    }

    // ── stock-out ─────────────────────────────────────────────────────────────

    public function test_stock_out_decreases_medicine_stock(): void
    {
        $this->loginUser();
        $medicine = MedicineModel::factory()->forClinic($this->clinic)->withStock(100)->create();

        $response = $this->post($this->clinicUrl('/inventory/stock-out'), [
            'medicine_id' => $medicine->id,
            'quantity'    => 25,
            'type'        => 'out',
        ]);

        $response->assertRedirect();
        $this->assertEquals(75, $medicine->fresh()->stock);
    }

    public function test_stock_out_fails_on_insufficient_stock(): void
    {
        $this->loginUser();
        $medicine = MedicineModel::factory()->forClinic($this->clinic)->withStock(5)->create();

        $response = $this->post($this->clinicUrl('/inventory/stock-out'), [
            'medicine_id' => $medicine->id,
            'quantity'    => 50,
            'type'        => 'out',
        ]);

        // Should redirect back with validation error
        $response->assertRedirect();
        $this->assertEquals(5, $medicine->fresh()->stock); // stock unchanged
    }

    // ── adjustment ────────────────────────────────────────────────────────────

    public function test_adjustment_sets_exact_stock_quantity(): void
    {
        $this->loginUser();
        $medicine = MedicineModel::factory()->forClinic($this->clinic)->withStock(40)->create();

        $response = $this->post($this->clinicUrl('/inventory/adjustment'), [
            'medicine_id' => $medicine->id,
            'new_qty'     => 55,
            'note'        => 'Physical count corrected',
        ]);

        $response->assertRedirect();
        $this->assertEquals(55, $medicine->fresh()->stock);
    }

    public function test_adjustment_records_movement_with_correct_before_after(): void
    {
        $this->loginUser();
        $medicine = MedicineModel::factory()->forClinic($this->clinic)->withStock(30)->create();

        $this->post($this->clinicUrl('/inventory/adjustment'), [
            'medicine_id' => $medicine->id,
            'new_qty'     => 20,
            'note'        => 'Shrinkage',
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'medicine_id'  => $medicine->id,
            'type'         => 'adjustment',
            'stock_before' => 30,
            'stock_after'  => 20,
        ]);
    }
}
