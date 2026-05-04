<?php

namespace Tests\Unit\Services;

use App\Models\MedicineModel;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private InventoryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginUser();
        $this->service = new InventoryService();
    }

    // ── receiveStock ──────────────────────────────────────────────────────────

    public function test_receive_stock_increases_medicine_stock(): void
    {
        $medicine = MedicineModel::factory()->forClinic($this->clinic)->withStock(50)->create();

        $this->service->receiveStock($medicine, ['quantity' => 30, 'type' => 'in']);

        $this->assertEquals(80, $medicine->fresh()->stock);
    }

    public function test_receive_stock_creates_movement_record(): void
    {
        $medicine = MedicineModel::factory()->forClinic($this->clinic)->withStock(100)->create();

        $movement = $this->service->receiveStock($medicine, [
            'quantity'  => 25,
            'type'      => 'in',
            'supplier'  => 'ABC Pharma',
            'reference' => 'PO-001',
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'medicine_id'  => $medicine->id,
            'type'         => 'in',
            'quantity'     => 25,
            'stock_before' => 100,
            'stock_after'  => 125,
        ]);
        $this->assertEquals(125, $movement->stock_after);
    }

    public function test_receive_stock_throws_on_zero_quantity(): void
    {
        $medicine = MedicineModel::factory()->forClinic($this->clinic)->create();

        $this->expectException(\RuntimeException::class);
        $this->service->receiveStock($medicine, ['quantity' => 0, 'type' => 'in']);
    }

    // ── removeStock ───────────────────────────────────────────────────────────

    public function test_remove_stock_decreases_medicine_stock(): void
    {
        $medicine = MedicineModel::factory()->forClinic($this->clinic)->withStock(100)->create();

        $this->service->removeStock($medicine, ['quantity' => 20, 'type' => 'out']);

        $this->assertEquals(80, $medicine->fresh()->stock);
    }

    public function test_remove_stock_creates_out_movement_record(): void
    {
        $medicine = MedicineModel::factory()->forClinic($this->clinic)->withStock(60)->create();

        $this->service->removeStock($medicine, ['quantity' => 10, 'type' => 'out']);

        $this->assertDatabaseHas('stock_movements', [
            'medicine_id'  => $medicine->id,
            'type'         => 'out',
            'quantity'     => 10,
            'stock_before' => 60,
            'stock_after'  => 50,
        ]);
    }

    public function test_remove_stock_throws_when_insufficient_for_out_type(): void
    {
        $medicine = MedicineModel::factory()->forClinic($this->clinic)->withStock(5)->create();

        $this->expectException(\RuntimeException::class);
        $this->service->removeStock($medicine, ['quantity' => 10, 'type' => 'out']);
    }

    public function test_remove_stock_expired_caps_at_available_stock(): void
    {
        $medicine = MedicineModel::factory()->forClinic($this->clinic)->withStock(3)->create();

        // Requesting to expire 10 but only 3 available — should cap to 3
        $movement = $this->service->removeStock($medicine, ['quantity' => 10, 'type' => 'expired']);

        $this->assertEquals(0, $medicine->fresh()->stock);
        $this->assertEquals(3, $movement->quantity);
    }

    // ── adjustToCount ─────────────────────────────────────────────────────────

    public function test_adjust_to_count_sets_absolute_stock_level(): void
    {
        $medicine = MedicineModel::factory()->forClinic($this->clinic)->withStock(50)->create();

        $this->service->adjustToCount($medicine, 75, 'Physical count', 'Admin');

        $this->assertEquals(75, $medicine->fresh()->stock);
    }

    public function test_adjust_to_count_records_correct_delta_in_movement(): void
    {
        $medicine = MedicineModel::factory()->forClinic($this->clinic)->withStock(40)->create();

        $movement = $this->service->adjustToCount($medicine, 60, 'Recount', 'Admin');

        $this->assertDatabaseHas('stock_movements', [
            'medicine_id'  => $medicine->id,
            'type'         => 'adjustment',
            'stock_before' => 40,
            'stock_after'  => 60,
            'quantity'     => 20,
        ]);
    }

    public function test_adjust_to_count_throws_on_negative_qty(): void
    {
        $medicine = MedicineModel::factory()->forClinic($this->clinic)->create();

        $this->expectException(\RuntimeException::class);
        $this->service->adjustToCount($medicine, -1, '', 'Admin');
    }
}
