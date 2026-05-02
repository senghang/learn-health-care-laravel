<?php

namespace Tests\Feature\Inventory;

use App\Models\MedicineModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesClinicEnvironment;
use Tests\TestCase;

class StockManagementTest extends TestCase
{
    use RefreshDatabase, CreatesClinicEnvironment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpClinic();
    }

    // ── Products page ─────────────────────────────────────────────────────────

    public function test_inventory_products_page_loads(): void
    {
        MedicineModel::factory()->forClinic($this->clinic)->count(3)->create();

        $response = $this->get('/inventory/products');

        $response->assertStatus(200);
    }

    public function test_inventory_only_shows_own_clinic_medicines(): void
    {
        MedicineModel::factory()->forClinic($this->clinic)->create(['name' => 'OwnMed']);
        MedicineModel::factory()->create(['name' => 'OtherMed']); // different clinic

        $response = $this->get('/inventory/products');

        $response->assertStatus(200);
        $response->assertSee('OwnMed');
        $response->assertDontSee('OtherMed');
    }

    // ── Stock-in ──────────────────────────────────────────────────────────────

    public function test_can_stock_in_medicine(): void
    {
        $medicine = MedicineModel::factory()->forClinic($this->clinic)->withStock(10)->create();

        $response = $this->post('/inventory/stock-in', [
            'medicine_id' => $medicine->id,
            'quantity'    => 50,
            'note'        => 'Monthly restock',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertSame(60, $medicine->fresh()->stock);
    }

    public function test_stock_in_requires_medicine_and_positive_quantity(): void
    {
        $response = $this->post('/inventory/stock-in', [
            'quantity' => -5,
        ]);

        $response->assertSessionHasErrors(['medicine_id', 'quantity']);
    }

    // ── Stock-out ─────────────────────────────────────────────────────────────

    public function test_can_stock_out_medicine(): void
    {
        $medicine = MedicineModel::factory()->forClinic($this->clinic)->withStock(100)->create();

        $response = $this->post('/inventory/stock-out', [
            'medicine_id' => $medicine->id,
            'quantity'    => 20,
            'note'        => 'Expired / damaged',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertSame(80, $medicine->fresh()->stock);
    }

    public function test_stock_out_fails_with_insufficient_stock(): void
    {
        $medicine = MedicineModel::factory()->forClinic($this->clinic)->withStock(5)->create();

        $response = $this->post('/inventory/stock-out', [
            'medicine_id' => $medicine->id,
            'quantity'    => 20,
        ]);

        $response->assertRedirect();
        // Stock unchanged
        $this->assertSame(5, $medicine->fresh()->stock);
    }

    // ── Medicine ledger ───────────────────────────────────────────────────────

    public function test_medicine_ledger_page_loads(): void
    {
        $medicine = MedicineModel::factory()->forClinic($this->clinic)->create();

        $response = $this->get("/inventory/medicine/{$medicine->id}/ledger");

        $response->assertStatus(200);
    }

    // ── Adjustments ───────────────────────────────────────────────────────────

    public function test_adjustment_page_loads(): void
    {
        $response = $this->get('/inventory/adjustment');

        $response->assertStatus(200);
    }

    // ── Low stock detection ───────────────────────────────────────────────────

    public function test_low_stock_medicine_is_flagged_in_inventory(): void
    {
        MedicineModel::factory()->forClinic($this->clinic)->lowStock()->create(['name' => 'LowMed']);
        MedicineModel::factory()->forClinic($this->clinic)->withStock(200)->create(['name' => 'FullMed']);

        $lowStock = MedicineModel::where('clinic_id', $this->clinic->id)
            ->get()
            ->filter(fn($m) => $m->isLowStock());

        $this->assertCount(1, $lowStock);
        $this->assertSame('LowMed', $lowStock->first()->name);
    }
}
