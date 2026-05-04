<?php

namespace Tests\Feature\Reports;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_visits_report_loads_when_authenticated(): void
    {
        $this->loginUser();
        $response = $this->get($this->clinicUrl('/reports/visits'));
        $response->assertStatus(200);
    }

    public function test_inventory_report_loads_when_authenticated(): void
    {
        $this->loginUser();
        $response = $this->get($this->clinicUrl('/reports/inventory'));
        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_redirected_from_reports(): void
    {
        $this->bindClinic();
        $response = $this->get($this->clinicUrl('/reports/visits'));
        $response->assertRedirect();
    }
}
