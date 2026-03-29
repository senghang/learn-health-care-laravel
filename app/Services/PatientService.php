<?php

namespace App\Services;

use App\Models\PatientAddressModel;
use App\Models\PatientModel;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * PatientService — all patient business logic.
 *
 * Controllers should call these methods instead of writing queries directly.
 * Transactions wrap multi-step operations (create patient + address).
 */
class PatientService
{
    /**
     * List patients with search/filter, scoped to current clinic.
     */
    public function list(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return PatientModel::query()
            ->when($filters['search'] ?? null, fn($q, $s) =>
                $q->where(fn($r) => $r
                    ->where('code',    'like', "%{$s}%")
                    ->orWhere('surname','like', "%{$s}%")
                    ->orWhere('name',   'like', "%{$s}%")
                    ->orWhere('phone',  'like', "%{$s}%")
                    ->orWhere('spid',   'like', "%{$s}%")
                )
            )
            ->when($filters['gender'] ?? null, fn($q, $v) => $q->where('gender', $v))
            ->when($filters['status'] ?? null, fn($q, $v) => $q->where('status', $v))
            ->withCount('visits')
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Find a patient by code. Throws 404 if not found.
     */
    public function findByCode(string $code): PatientModel
    {
        return PatientModel::where('code', $code)
            ->with(['address', 'identifications'])
            ->firstOrFail();
    }

    /**
     * Create a new patient with address (transactional).
     *
     * @param array $data Validated patient data
     * @param array $addressData Validated address data
     * @return PatientModel
     */
    public function create(array $data, array $addressData = []): PatientModel
    {
        return DB::transaction(function () use ($data, $addressData) {
            $data['code'] = ClinicCodeService::patient(currentClinic()->id);

            $patient = PatientModel::create($data);

            if (!empty(array_filter($addressData))) {
                PatientAddressModel::create(array_merge($addressData, [
                    'patient_code' => $patient->code,
                ]));
            }

            return $patient;
        });
    }

    /**
     * Update a patient and their address (transactional).
     */
    public function update(string $code, array $data, array $addressData = []): PatientModel
    {
        return DB::transaction(function () use ($code, $data, $addressData) {
            $patient = $this->findByCode($code);
            $patient->update($data);

            if (!empty(array_filter($addressData))) {
                PatientAddressModel::updateOrCreate(
                    ['patient_code' => $patient->code],
                    $addressData
                );
            }

            return $patient->fresh(['address']);
        });
    }

    /**
     * Search patients for autocomplete (JSON).
     */
    public function search(string $query, int $limit = 10): \Illuminate\Support\Collection
    {
        return PatientModel::where(fn($q) => $q
                ->where('code',    'like', "%{$query}%")
                ->orWhere('surname','like', "%{$query}%")
                ->orWhere('name',   'like', "%{$query}%")
                ->orWhere('phone',  'like', "%{$query}%")
            )
            ->withCount('visits')
            ->limit($limit)
            ->get(['id', 'code', 'surname', 'name', 'gender', 'birthdate', 'phone']);
    }
}
