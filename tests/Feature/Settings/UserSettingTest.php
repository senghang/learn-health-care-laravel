<?php

namespace Tests\Feature\Settings;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_index_loads_when_authenticated_with_permission(): void
    {
        $this->loginAdmin();
        $response = $this->get($this->clinicUrl('/users'));
        $response->assertStatus(200);
    }

    public function test_users_create_loads_when_authenticated_with_permission(): void
    {
        $this->loginAdmin();
        $response = $this->get($this->clinicUrl('/users/create'));
        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_redirected_from_users(): void
    {
        $this->bindClinic();
        $response = $this->get($this->clinicUrl('/users'));
        $response->assertRedirect();
    }
}
