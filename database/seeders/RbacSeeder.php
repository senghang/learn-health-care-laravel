<?php

namespace Database\Seeders;

use App\Models\ClinicModel;
use App\Models\PermissionModel;
use App\Models\RoleModel;
use Illuminate\Database\Seeder;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        $clinics = ClinicModel::all();

        foreach ($clinics as $clinic) {
            $this->seedForClinic($clinic->id);
        }

        $this->command->info("RbacSeeder: seeded roles & permissions for {$clinics->count()} clinic(s).");
    }

    private function seedForClinic(int $clinicId): void
    {
        // 1. Seed permissions
        foreach (PermissionModel::defaultSlugs() as $perm) {
            PermissionModel::firstOrCreate(
                ['clinic_id' => $clinicId, 'slug' => $perm['slug']],
                ['name' => $perm['name'], 'group' => $perm['group']]
            );
        }

        $allPerms = PermissionModel::where('clinic_id', $clinicId)
            ->get()
            ->keyBy('slug');

        // 2. Role definitions
        $roles = [
            'Admin' => [
                'description' => 'Full system access',
                'level' => 100,
                'permissions' => $allPerms->keys()->all(),
            ],

            'Doctor' => [
                'description' => 'Clinical operations + reports',
                'level' => 80,
                'permissions' => [
                    'patients.view', 'patients.create', 'patients.edit',
                    'visits.view', 'visits.create',
                    'workflow.manage',
                    'pharmacy.view',
                    'laboratory.view', 'laboratory.manage', 'laboratory.verify',
                    'imagery.view', 'imagery.manage',
                    'referral.view', 'referral.manage',
                    'invoices.view',
                    'employees.view',
                    'reports.view',
                    'settings.view',
                ],
            ],

            'Nurse' => [
                'description' => 'Clinical workflow + patient management',
                'level' => 60,
                'permissions' => [
                    'patients.view', 'patients.create', 'patients.edit',
                    'visits.view', 'visits.create',
                    'workflow.manage',
                    'pharmacy.view',
                    'laboratory.view', 'laboratory.manage',
                    'imagery.view',
                    'referral.view', 'referral.manage',
                    'invoices.view',
                    'reports.view',
                ],
            ],

            'Pharmacist' => [
                'description' => 'Pharmacy dispensing + inventory management',
                'level' => 50,
                'permissions' => [
                    'patients.view',
                    'visits.view',
                    'pharmacy.view', 'pharmacy.dispense', 'pharmacy.manage',
                    'inventory.view', 'inventory.manage',
                    'reports.view',
                ],
            ],

            'Cashier' => [
                'description' => 'Billing and payment collection',
                'level' => 30,
                'permissions' => [
                    'patients.view',
                    'visits.view',
                    'invoices.view', 'invoices.manage',
                    'payments.manage',
                    'reports.view',
                ],
            ],
        ];

        // 3. Create roles and assign permissions
        foreach ($roles as $roleName => $config) {
            $role = RoleModel::firstOrCreate(
                [
                    'clinic_id' => $clinicId,
                    'name' => $roleName,
                ],
                [
                    'description' => $config['description'],
                    'level' => $config['level'],   // now safe as integer
                    'is_system' => true,
                ]
            );

            // Get permission IDs
            $permIds = collect($config['permissions'])
                ->map(fn($slug) => $allPerms->get($slug)?->id)
                ->filter()
                ->values()
                ->all();

            // Sync permissions (safe to re-run)
            $role->permissions()->syncWithoutDetaching($permIds);
        }
    }
}
