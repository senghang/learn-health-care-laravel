<?php

namespace Tests\Feature\Settings;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verify that CheckPermission middleware correctly enforces 403 / 200
 * on permission-gated routes.
 */
class PermissionEnforcementTest extends TestCase
{
    use RefreshDatabase;

    // ── blocked without permission ────────────────────────────────────────────

    public function test_laboratory_index_returns_403_without_permission(): void
    {
        $this->loginUser(); // no permissions

        $response = $this->get($this->clinicUrl('/laboratory'));
        $response->assertStatus(403);
    }

    public function test_imagery_index_returns_403_without_permission(): void
    {
        $this->loginUser();

        $response = $this->get($this->clinicUrl('/imagery'));
        $response->assertStatus(403);
    }

    public function test_employees_index_returns_403_without_permission(): void
    {
        $this->loginUser();

        $response = $this->get($this->clinicUrl('/employees'));
        $response->assertStatus(403);
    }

    public function test_payments_index_returns_403_without_permission(): void
    {
        $this->loginUser();

        $response = $this->get($this->clinicUrl('/payments'));
        $response->assertStatus(403);
    }

    // ── allowed with permission ───────────────────────────────────────────────

    public function test_employees_index_returns_200_with_permission(): void
    {
        $this->loginAdmin(); // full permissions

        $response = $this->get($this->clinicUrl('/employees'));
        $response->assertStatus(200);
    }

    public function test_laboratory_index_returns_200_with_permission(): void
    {
        $this->loginAdmin();

        $response = $this->get($this->clinicUrl('/laboratory'));
        $response->assertStatus(200);
    }

    public function test_imagery_index_returns_200_with_permission(): void
    {
        $this->loginAdmin();

        $response = $this->get($this->clinicUrl('/imagery'));
        $response->assertStatus(200);
    }

    // ── unauthenticated redirect ──────────────────────────────────────────────

    public function test_unauthenticated_user_redirected_from_permission_gated_route(): void
    {
        $this->bindClinic(); // no login

        $response = $this->get($this->clinicUrl('/employees'));
        $response->assertRedirect();
    }
}
