<?php

namespace App\Http\Controllers\Clinics\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeRequest;
use App\Models\DepartmentModel;
use App\Services\EmployeeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * EmployeeController — thin controller.
 * ALL business logic lives in EmployeeService.
 */
class EmployeeController extends Controller
{
    public function __construct(
        private readonly EmployeeService $employeeService
    ) {}

    public function index(Request $request): View
    {
        $employees = $this->employeeService->list($request->all());
        $departments = DepartmentModel::where('is_active', true)->orderBy('name')->get();

        return view('clinics.hr.employees', compact('employees', 'departments'));
    }

    public function create(): View
    {
        $departments = DepartmentModel::where('is_active', true)->orderBy('name')->get();

        return view('clinics.hr.employee-form', [
            'employee'    => null,
            'departments' => $departments,
        ]);
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $employee = $this->employeeService->create($request->validated());

        return redirect()->route('employees.index')
            ->with('flash', "Employee {$employee->code} created.");
    }

    public function show(int $id): View
    {
        $employee = $this->employeeService->findById($id);

        return view('clinics.hr.employee-show', compact('employee'));
    }

    public function edit(int $id): View
    {
        $employee = $this->employeeService->findById($id);
        $departments = DepartmentModel::where('is_active', true)->orderBy('name')->get();

        return view('clinics.hr.employee-form', compact('employee', 'departments'));
    }

    public function update(StoreEmployeeRequest $request, int $id): RedirectResponse
    {
        $employee = $this->employeeService->update($id, $request->validated());

        return redirect()->route('employees.show', $id)
            ->with('flash', "Employee {$employee->code} updated.");
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->employeeService->delete($id);

        return redirect()->route('employees.index')
            ->with('flash', 'Employee deleted.');
    }
}
