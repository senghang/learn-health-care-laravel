<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PermissionModel extends Model
{
    protected $table = 'permissions';

    protected $fillable = ['clinic_id', 'name', 'slug', 'group'];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(ClinicModel::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            RoleModel::class,
            'role_permission',
            'permission_id',
            'role_id'
        );
    }

    /**
     * Default permission set for a new clinic.
     * Run once when clinic is created.
     */
    public static function defaultSlugs(): array
    {
        return [
            // patients
            ['group' => 'patients',   'slug' => 'patients.view',       'name' => 'View Patients'],
            ['group' => 'patients',   'slug' => 'patients.create',      'name' => 'Create Patients'],
            ['group' => 'patients',   'slug' => 'patients.edit',        'name' => 'Edit Patients'],
            // visits
            ['group' => 'visits',     'slug' => 'visits.view',          'name' => 'View Visits'],
            ['group' => 'visits',     'slug' => 'visits.create',        'name' => 'Create Visits'],
            // workflow
            ['group' => 'workflow',   'slug' => 'workflow.manage',      'name' => 'Manage Workflow'],
            // inventory
            ['group' => 'inventory',  'slug' => 'inventory.view',       'name' => 'View Inventory'],
            ['group' => 'inventory',  'slug' => 'inventory.manage',     'name' => 'Manage Inventory'],
            // billing
            ['group' => 'billing',    'slug' => 'invoices.view',        'name' => 'View Invoices'],
            ['group' => 'billing',    'slug' => 'invoices.manage',      'name' => 'Manage Invoices'],
            ['group' => 'billing',    'slug' => 'payments.manage',      'name' => 'Collect Payments'],
            // reports
            ['group' => 'reports',    'slug' => 'reports.view',         'name' => 'View Reports'],
            // settings
            ['group' => 'settings',   'slug' => 'settings.view',        'name' => 'View Settings'],
            ['group' => 'settings',   'slug' => 'settings.manage',      'name' => 'Manage Settings'],
        ];
    }
}
