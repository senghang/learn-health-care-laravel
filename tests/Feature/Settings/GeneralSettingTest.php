<?php

namespace Tests\Feature\Settings;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeneralSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_general_settings_loads_when_authenticated(): void
    {
        $this->loginUser();
        $response = $this->get($this->clinicUrl('/settings/general'));
        $response->assertStatus(200);
    }

    public function test_services_settings_loads_when_authenticated(): void
    {
        $this->loginUser();
        $response = $this->get($this->clinicUrl('/settings/services'));
        $response->assertStatus(200);
    }

    public function test_medicines_settings_loads_when_authenticated(): void
    {
        $this->loginUser();
        $response = $this->get($this->clinicUrl('/settings/medicines'));
        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_redirected_from_settings(): void
    {
        $this->bindClinic();
        $response = $this->get($this->clinicUrl('/settings/general'));
        $response->assertRedirect();
    }
}
