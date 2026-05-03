<?php

namespace Tests\Feature\Dashboard;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_loads_when_authenticated(): void
    {
        $this->loginUser();

        $response = $this->get($this->clinicUrl('/'));

        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_is_redirected_from_dashboard(): void
    {
        $this->bindClinic();

        $response = $this->get($this->clinicUrl('/'));

        // userauth middleware redirects to /login when not authenticated
        $response->assertRedirect();
        $this->assertStringContainsString('login', $response->headers->get('Location'));
    }
}
