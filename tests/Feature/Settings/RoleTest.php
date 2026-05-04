<?php

namespace Tests\Feature\Settings;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_roles_page_loads_when_authenticated(): void
    {
        $this->loginUser();
        $response = $this->get($this->clinicUrl('/settings/roles'));
        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_redirected_from_roles(): void
    {
        $this->bindClinic();
        $response = $this->get($this->clinicUrl('/settings/roles'));
        $response->assertRedirect();
    }
}
