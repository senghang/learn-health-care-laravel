<?php

namespace Tests\Feature\Settings;

use App\Models\PermissionModel;
use App\Models\RoleModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Role + permission management CRUD.
 */
class RolePermissionCrudTest extends TestCase
{
    use RefreshDatabase;

    // ── create role ───────────────────────────────────────────────────────────

    public function test_create_role_stores_record(): void
    {
        $this->loginAdmin();

        $response = $this->post($this->clinicUrl('/settings/roles'), [
            'name'        => 'Pharmacist',
            'description' => 'Dispenses medications',
            'level'       => 2,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('roles', [
            'name'      => 'Pharmacist',
            'clinic_id' => $this->clinic->id,
        ]);
    }

    public function test_create_role_validation_requires_name(): void
    {
        $this->loginAdmin();

        $response = $this->post($this->clinicUrl('/settings/roles'), [
            'description' => 'No name',
        ]);

        $response->assertSessionHasErrors('name');
    }

    // ── assign permissions ────────────────────────────────────────────────────

    public function test_assign_permissions_to_role(): void
    {
        $this->loginAdmin();

        // Permissions are already seeded by loginAdmin() — just look them up
        $role = RoleModel::factory()->create(['clinic_id' => $this->clinic->id]);
        $p1   = PermissionModel::where('clinic_id', $this->clinic->id)->where('slug', 'patients.view')->first();
        $p2   = PermissionModel::where('clinic_id', $this->clinic->id)->where('slug', 'patients.create')->first();

        $response = $this->post($this->clinicUrl("/settings/roles/{$role->id}/permissions"), [
            'permissions' => [$p1->slug, $p2->slug],
        ]);

        $response->assertRedirect();
        $this->assertTrue($role->fresh()->load('permissions')->hasPermission('patients.view'));
        $this->assertTrue($role->fresh()->load('permissions')->hasPermission('patients.create'));
    }

    // ── update role ───────────────────────────────────────────────────────────

    public function test_update_role_name(): void
    {
        $this->loginAdmin();
        $role = RoleModel::factory()->create(['clinic_id' => $this->clinic->id, 'name' => 'Old Name']);

        $response = $this->patch($this->clinicUrl("/settings/roles/{$role->id}"), [
            'name'  => 'New Name',
            'level' => 3,
        ]);

        $response->assertRedirect();
        $this->assertEquals('New Name', $role->fresh()->name);
    }

    // ── delete role ───────────────────────────────────────────────────────────

    public function test_delete_non_system_role(): void
    {
        $this->loginAdmin();
        $role = RoleModel::factory()->create([
            'clinic_id' => $this->clinic->id,
            'is_system'  => false,
        ]);

        $response = $this->delete($this->clinicUrl("/settings/roles/{$role->id}"));

        $response->assertRedirect();
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_delete_system_role_is_blocked(): void
    {
        $this->loginAdmin();
        $role = RoleModel::factory()->create([
            'clinic_id' => $this->clinic->id,
            'is_system'  => true,
        ]);

        $response = $this->delete($this->clinicUrl("/settings/roles/{$role->id}"));

        // Should NOT delete — redirect with error
        $response->assertRedirect();
        $this->assertDatabaseHas('roles', ['id' => $role->id]);
    }

    // ── seed permissions ──────────────────────────────────────────────────────

    public function test_seed_permissions_creates_all_default_slugs(): void
    {
        $this->loginAdmin();

        $this->post($this->clinicUrl('/settings/roles/seed-permissions'));

        $expected = count(PermissionModel::defaultSlugs());
        $actual   = PermissionModel::where('clinic_id', $this->clinic->id)->count();

        // Already seeded by loginAdmin() — seeding again should not duplicate
        $this->assertEquals($expected, $actual);
    }
}
