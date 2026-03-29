<?php

namespace App\Services;

use App\Models\EmployeeModel;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * EmployeeService — HR business logic.
 */
class EmployeeService
{
    public function list(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return EmployeeModel::query()
            ->with('department')
            ->when($filters['search'] ?? null, fn($q, $s) =>
                $q->where(fn($r) => $r
                    ->where('code',    'like', "%{$s}%")
                    ->orWhere('surname','like', "%{$s}%")
                    ->orWhere('name',   'like', "%{$s}%")
                    ->orWhere('phone',  'like', "%{$s}%")
                )
            )
            ->when($filters['type'] ?? null,       fn($q, $v) => $q->where('employee_type', $v))
            ->when($filters['department'] ?? null, fn($q, $v) => $q->where('department_id', $v))
            ->when($filters['status'] ?? null,     fn($q, $v) => $q->where('status', $v))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findById(int $id): EmployeeModel
    {
        return EmployeeModel::with('department', 'user')->findOrFail($id);
    }

    public function create(array $data): EmployeeModel
    {
        $data['code'] = ClinicCodeService::next(currentClinic()->id, 'EMP');
        return EmployeeModel::create($data);
    }

    public function update(int $id, array $data): EmployeeModel
    {
        $employee = EmployeeModel::findOrFail($id);
        $employee->update($data);
        return $employee->fresh('department');
    }

    public function delete(int $id): void
    {
        EmployeeModel::findOrFail($id)->delete();
    }

    /**
     * Get doctors for dropdown/autocomplete.
     */
    public function doctors(): \Illuminate\Support\Collection
    {
        return EmployeeModel::where('employee_type', 'doctor')
            ->where('status', 'active')
            ->orderBy('surname')
            ->get(['id', 'code', 'surname', 'name', 'specialization']);
    }
}
