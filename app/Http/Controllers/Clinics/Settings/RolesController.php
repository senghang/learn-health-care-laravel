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
            'name' => 'required|string|max:80',
        ]);

        RoleModel::create(['clinic_id' => $this->clinicId, 'name' => $data['name']]);

        return redirect()->route('settings.roles')->with('flash', "Role '{$data['name']}' created.");
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $role = RoleModel::where('clinic_id', $this->clinicId)->findOrFail($id);

        $data = $request->validate(['name' => 'required|string|max:80']);
        $role->update($data);

        return redirect()->route('settings.roles')->with('flash', 'Role updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $role = RoleModel::where('clinic_id', $this->clinicId)->findOrFail($id);

        if ($role->users_count > 0) {
            return back()->with('flash_error', "Cannot delete role with {$role->users_count} user(s) assigned.");
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
}
