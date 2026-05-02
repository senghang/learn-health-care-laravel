<?php

namespace App\Http\Controllers\Clinics\Settings;

use App\Http\Controllers\Controller;
use App\Models\EmployeeModel;
use App\Models\RoleModel;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    private int $clinicId;

    public function __construct()
    {
        $this->clinicId = currentClinic()->id;
    }

    public function index(Request $request): View
    {
        $query = User::where('clinic_id', $this->clinicId)
            ->with(['role', 'employee'])
            ->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($roleId = $request->input('role_id')) {
            $query->where('role_id', $roleId);
        }

        if ($request->input('status') !== null && $request->input('status') !== '') {
            $query->where('is_active', (bool) $request->input('status'));
        }

        $users = $query->paginate(20)->withQueryString();
        $roles = RoleModel::where('clinic_id', $this->clinicId)->orderBy('name')->get();

        return view('clinics.settings.users', compact('users', 'roles'));
    }

    public function create(): View
    {
        $roles = RoleModel::where('clinic_id', $this->clinicId)->orderBy('name')->get();

        // Employees not yet linked to any user in this clinic
        $linkedEmployeeIds = User::where('clinic_id', $this->clinicId)
            ->whereNotNull('employee_id')->pluck('employee_id');

        $employees = EmployeeModel::where('clinic_id', $this->clinicId)
            ->whereNotIn('id', $linkedEmployeeIds)
            ->where('status', 'active')
            ->orderBy('surname')->orderBy('name')
            ->get();

        return view('clinics.settings.user-form', compact('roles', 'employees'))->with('user', null);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'email'       => 'required|email|max:150|unique:users,email',
            'phone'       => 'nullable|string|max:30',
            'role_id'     => 'required|exists:roles,id',
            'employee_id' => 'nullable|exists:employees,id',
            'is_active'   => 'boolean',
            'password'    => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        // Ensure role belongs to this clinic
        $role = RoleModel::where('clinic_id', $this->clinicId)->findOrFail($data['role_id']);
        $employeeId = $data['employee_id'] ?? null;
        if ($employeeId) {
            EmployeeModel::where('clinic_id', $this->clinicId)->findOrFail($employeeId);
            $alreadyLinked = User::where('clinic_id', $this->clinicId)
                ->where('employee_id', $employeeId)
                ->exists();
            if ($alreadyLinked) {
                return back()->withInput()->withErrors(['employee_id' => 'Employee is already linked to another user account.']);
            }
        }

        User::create([
            'clinic_id'   => $this->clinicId,
            'role_id'     => $role->id,
            'employee_id' => $employeeId,
            'name'        => $data['name'],
            'email'       => $data['email'],
            'phone'       => $data['phone'] ?? null,
            'password'    => Hash::make($data['password']),
            'is_active'   => $request->boolean('is_active', true),
        ]);

        return redirect()->route('users.index')
            ->with('flash', "User '{$data['email']}' created successfully.");
    }

    public function edit(int $id): View
    {
        $user = User::where('clinic_id', $this->clinicId)->with('role', 'employee')->findOrFail($id);
        $roles = RoleModel::where('clinic_id', $this->clinicId)->orderBy('name')->get();

        // Employees available: unlinked ones + currently assigned one
        $linkedEmployeeIds = User::where('clinic_id', $this->clinicId)
            ->whereNotNull('employee_id')
            ->where('id', '!=', $id)
            ->pluck('employee_id');

        $employees = EmployeeModel::where('clinic_id', $this->clinicId)
            ->where(function ($q) use ($linkedEmployeeIds, $user) {
                $q->whereNotIn('id', $linkedEmployeeIds)
                  ->orWhere('id', $user->employee_id);
            })
            ->orderBy('surname')->orderBy('name')
            ->get();

        return view('clinics.settings.user-form', compact('user', 'roles', 'employees'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $user = User::where('clinic_id', $this->clinicId)->findOrFail($id);

        $rules = [
            'name'        => 'required|string|max:100',
            'email'       => "required|email|max:150|unique:users,email,{$id}",
            'phone'       => 'nullable|string|max:30',
            'role_id'     => 'required|exists:roles,id',
            'employee_id' => 'nullable|exists:employees,id',
            'is_active'   => 'boolean',
            'password'    => ['nullable', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ];

        $data = $request->validate($rules);

        $role = RoleModel::where('clinic_id', $this->clinicId)->findOrFail($data['role_id']);
        $employeeId = $data['employee_id'] ?? null;
        if ($employeeId) {
            EmployeeModel::where('clinic_id', $this->clinicId)->findOrFail($employeeId);
            $alreadyLinked = User::where('clinic_id', $this->clinicId)
                ->where('id', '!=', $id)
                ->where('employee_id', $employeeId)
                ->exists();
            if ($alreadyLinked) {
                return back()->withInput()->withErrors(['employee_id' => 'Employee is already linked to another user account.']);
            }
        }

        $updateData = [
            'role_id'     => $role->id,
            'employee_id' => $employeeId,
            'name'        => $data['name'],
            'email'       => $data['email'],
            'phone'       => $data['phone'] ?? null,
            'is_active'   => $request->boolean('is_active'),
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        return redirect()->route('users.index')
            ->with('flash', "User '{$user->email}' updated.");
    }

    public function destroy(int $id): RedirectResponse
    {
        $user = User::where('clinic_id', $this->clinicId)->findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('flash_error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('flash', "User '{$user->email}' deleted.");
    }
}
