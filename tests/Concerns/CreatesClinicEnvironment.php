<?php

namespace Tests\Concerns;

use App\Models\ClinicModel;
use App\Models\RoleModel;
use App\Models\User;

/**
 * Shared trait — sets up a clinic, admin role, and authenticated user.
 * Include in any test that touches clinic-scoped models or HTTP routes.
 */
trait CreatesClinicEnvironment
{
    protected ClinicModel $clinic;
    protected RoleModel   $adminRole;
    protected User        $adminUser;

    /**
     * Create a clinic + admin role + admin user, bind currentClinic, and
     * authenticate the user for HTTP tests.
     */
    protected function setUpClinic(): void
    {
        $this->clinic    = ClinicModel::factory()->create();
        $this->adminRole = RoleModel::factory()->for($this->clinic)->create(['name' => 'Admin']);
        $this->adminUser = User::factory()->create([
            'clinic_id' => $this->clinic->id,
            'role_id'   => $this->adminRole->id,
        ]);

        // Bind currentClinic so ClinicScope filters and auto-fills work
        app()->instance('currentClinic', $this->clinic);

        // Authenticate for HTTP tests
        $this->actingAs($this->adminUser);
    }

    /**
     * Bind currentClinic without authenticating (for unit tests).
     */
    protected function bindClinic(ClinicModel $clinic): void
    {
        app()->instance('currentClinic', $clinic);
    }
}
