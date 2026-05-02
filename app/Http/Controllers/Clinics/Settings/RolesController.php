<?php

namespace App\Http\Controllers\Clinics\Settings;

use App\Http\Controllers\Controller;
use App\Models\PermissionModel;
use App\Models\RoleModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RolesController extends Controller
{
    private int $clinicId;

    public function __construct()
    {
        $this->clinicId = currentClinic()->id;
    }

    public function index(): View
    {
        $roles = RoleModel::where('clinic_id', $this->clinicId)
            ->withCount('users')
            ->with('permissions')
            ->orderBy('name')
            ->get();

        $permissions = PermissionModel::where('clinic_id', $this->clinicId)
            ->orderBy('group')->orderBy('name')->get()
            ->groupBy('group');

        // Seed default permissions if none exist yet
        if ($permissions->isEmpty()) {
            foreach (PermissionModel::defaultSlugs() as $perm) {
                PermissionModel::firstOrCreate(
                    ['clinic_id' => $this->clinicId, 'slug' => $perm['slug']],
                    ['name' => $perm['name'], 'group' => $perm['group']]
                );
            }
            $permissions = PermissionModel::where('clinic_id', $this->clinicId)
                ->orderBy('group')->orderBy('name')->get()->groupBy('group');
        }

        return view('clinics.settings.roles', compact('roles', 'permissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:80',
            'description' => 'nullable|string|max:255',
            'level'       => 'nullable|integer|min:0|max:100',
        ]);

        RoleModel::create(array_merge($data, ['clinic_id' => $this->clinicId]));

        return redirect()->route('settings.roles')->with('flash', "Role '{$data['name']}' created.");
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $role = RoleModel::where('clinic_id', $this->clinicId)->findOrFail($id);

        if ($role->is_system) {
            return back()->with('flash_error', "System role '{$role->name}' cannot be modified.");
        }

        $data = $request->validate([
            'name'        => 'required|string|max:80',
            'description' => 'nullable|string|max:255',
            'level'       => 'nullable|integer|min:0|max:100',
        ]);
        $role->update($data);

        return redirect()->route('settings.roles')->with('flash', 'Role updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $role = RoleModel::where('clinic_id', $this->clinicId)
            ->withCount('users')
            ->findOrFail($id);

        if ($role->is_system) {
            return back()->with('flash_error', "System role '{$role->name}' cannot be deleted.");
        }

        if ($role->users_count > 0) {
            return back()->with('flash_error', "Cannot delete role '{$role->name}' — {$role->users_count} user(s) assigned.");
        }

        $role->delete();
        return redirect()->route('settings.roles')->with('flash', 'Role deleted.');
    }

    /** Save permission matrix for a single role */
    public function syncPermissions(Request $request, int $id): RedirectResponse
    {
        $role = RoleModel::where('clinic_id', $this->clinicId)->findOrFail($id);

        $permissionIds = PermissionModel::where('clinic_id', $this->clinicId)
            ->whereIn('slug', $request->input('permissions', []))
            ->pluck('id');

        $role->permissions()->sync($permissionIds);

        return redirect()->route('settings.roles')
            ->with('flash', "Permissions for '{$role->name}' updated.");
    }

    /** Sync all default permissions — adds missing ones, leaves existing untouched */
    public function seedPermissions(): RedirectResponse
    {
        $seeded = 0;
        foreach (PermissionModel::defaultSlugs() as $perm) {
            $created = PermissionModel::firstOrCreate(
                ['clinic_id' => $this->clinicId, 'slug' => $perm['slug']],
                ['name' => $perm['name'], 'group' => $perm['group']]
            );
            if ($created->wasRecentlyCreated) {
                $seeded++;
            }
        }

        $msg = $seeded > 0
            ? "{$seeded} new permission(s) added."
            : 'All permissions are already up to date.';

        return redirect()->route('settings.roles')->with('flash', $msg);
    }
}
