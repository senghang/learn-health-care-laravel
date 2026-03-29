<?php

namespace App\Models;

use App\Models\Base\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PermissionModel extends Model
{
    use Auditable;

    protected $table = 'permissions';

    protected $fillable = ['clinic_id', 'name', 'slug', 'group'];

    /**
     * Default permission set for a new clinic — now includes pharmacy, lab, imagery, referral, employees.
     */
    public static function defaultSlugs(): array
    {
        return [
            // patients
            ['group' => 'patients', 'slug' => 'patients.view', 'name' => 'View Patients'],
            ['group' => 'patients', 'slug' => 'patients.create', 'name' => 'Create Patients'],
            ['group' => 'patients', 'slug' => 'patients.edit', 'name' => 'Edit Patients'],
            ['group' => 'patients', 'slug' => 'patients.delete', 'name' => 'Delete Patients'],
            // visits
            ['group' => 'visits', 'slug' => 'visits.view', 'name' => 'View Visits'],
            ['group' => 'visits', 'slug' => 'visits.create', 'name' => 'Create Visits'],
            // workflow
            ['group' => 'workflow', 'slug' => 'workflow.manage', 'name' => 'Manage Workflow'],
            // pharmacy (NEW)
            ['group' => 'pharmacy', 'slug' => 'pharmacy.view', 'name' => 'View Pharmacy'],
            ['group' => 'pharmacy', 'slug' => 'pharmacy.dispense', 'name' => 'Dispense Medication'],
            ['group' => 'pharmacy', 'slug' => 'pharmacy.manage', 'name' => 'Manage Pharmacy'],
            // laboratory (NEW)
            ['group' => 'laboratory', 'slug' => 'laboratory.view', 'name' => 'View Lab'],
            ['group' => 'laboratory', 'slug' => 'laboratory.manage', 'name' => 'Manage Lab'],
            ['group' => 'laboratory', 'slug' => 'laboratory.verify', 'name' => 'Verify Lab Results'],
            // imagery (NEW)
            ['group' => 'imagery', 'slug' => 'imagery.view', 'name' => 'View Imagery'],
            ['group' => 'imagery', 'slug' => 'imagery.manage', 'name' => 'Manage Imagery'],
            // referral (NEW)
            ['group' => 'referral', 'slug' => 'referral.view', 'name' => 'View Referrals'],
            ['group' => 'referral', 'slug' => 'referral.manage', 'name' => 'Manage Referrals'],
            // inventory
            ['group' => 'inventory', 'slug' => 'inventory.view', 'name' => 'View Inventory'],
            ['group' => 'inventory', 'slug' => 'inventory.manage', 'name' => 'Manage Inventory'],
            // billing
            ['group' => 'billing', 'slug' => 'invoices.view', 'name' => 'View Invoices'],
            ['group' => 'billing', 'slug' => 'invoices.manage', 'name' => 'Manage Invoices'],
            ['group' => 'billing', 'slug' => 'payments.manage', 'name' => 'Collect Payments'],
            // employees (NEW)
            ['group' => 'employees', 'slug' => 'employees.view', 'name' => 'View Employees'],
            ['group' => 'employees', 'slug' => 'employees.manage', 'name' => 'Manage Employees'],
            // reports
            ['group' => 'reports', 'slug' => 'reports.view', 'name' => 'View Reports'],
            // settings
            ['group' => 'settings', 'slug' => 'settings.view', 'name' => 'View Settings'],
            ['group' => 'settings', 'slug' => 'settings.manage', 'name' => 'Manage Settings'],
        ];
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(ClinicModel::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(RoleModel::class, 'role_permission', 'permission_id', 'role_id');
    }
}
