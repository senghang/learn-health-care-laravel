<?php

namespace Tests\Feature\Inventory;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_products_loads_when_authenticated(): void
    {
        $this->loginUser();
        $response = $this->get($this->clinicUrl('/inventory/products'));
        $response->assertStatus(200);
    }

    public function test_inventory_movements_loads_when_authenticated(): void
    {
        $this->loginUser();
        $response = $this->get($this->clinicUrl('/inventory/movements'));
        $response->assertStatus(200);
    }

    public function test_inventory_stock_in_loads_when_authenticated(): void
    {
        $this->loginUser();
        $response = $this->get($this->clinicUrl('/inventory/stock-in'));
        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_redirected_from_inventory(): void
    {
        $this->bindClinic();
        $response = $this->get($this->clinicUrl('/inventory/products'));
        $response->assertRedirect();
    }
}
