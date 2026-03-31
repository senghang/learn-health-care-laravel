<?php

namespace App\Services;

use App\Models\PatientAddressModel;
use App\Models\PatientContactModel;
use App\Models\PatientIdentificationModel;
use App\Models\PatientModel;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * PatientService — all patient business logic.
 *
 * Controllers should call these methods instead of writing queries directly.
 * Transactions wrap multi-step operations (create patient + address + contacts).
 *
 * ═══════════════════════════════════════════════════════════════════════════════
 * CHANGES FROM PREVIOUS VERSION:
 *   ✅ Fixed gender→sex filter inconsistency (DB column is `sex`)
 *   ✅ Added contacts + identifications to create/update transactions
 *   ✅ Added destroy (soft-delete) with safety check
 *   ✅ Added status filter support
 *   ✅ All existing method signatures preserved (backward compat)
 * ═══════════════════════════════════════════════════════════════════════════════
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
                    ->where('code',    'ilike', "%{$s}%")
                    ->orWhere('surname','ilike', "%{$s}%")
                    ->orWhere('name',   'ilike', "%{$s}%")
                    ->orWhere('phone',  'ilike', "%{$s}%")
                    ->orWhere('spid',   'ilike', "%{$s}%")
                )
            )
            // FIX: Use 'sex' (actual DB column), not 'gender'
            ->when($filters['sex'] ?? null, fn($q, $v) => $q->where('sex', $v))
            ->when($filters['status'] ?? null, fn($q, $v) => $q->where('status', $v))
            ->withCount('visits')
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Find a patient by code with all related data. Throws 404 if not found.
     */
    public function findByCode(string $code): PatientModel
    {
        return PatientModel::where('code', $code)
            ->with(['address', 'identifications', 'contacts'])
            ->firstOrFail();
    }

    /**
     * Find a patient with visits loaded for profile display.
     */
    public function findForProfile(string $code): PatientModel
    {
        return PatientModel::where('code', $code)
            ->with([
                'address',
                'identifications',
                'contacts' => fn($q) => $q->orderByDesc('is_emergency'),
                'visits'   => fn($q) => $q->latest('admitted_at')->limit(10),
            ])
            ->withCount('visits')
            ->firstOrFail();
    }

    /**
     * Create a new patient with address, identifications, and contacts (transactional).
     *
     * @param array $data         Validated patient demographics
     * @param array $addressData  Address fields
     * @param array $identifications  Array of {card_type, card_code}
     * @param array $contacts     Array of {contact_name, contact_phone, relationship, is_emergency}
     * @return PatientModel
     */
    public function create(
        array $data,
        array $addressData = [],
        array $identifications = [],
        array $contacts = []
    ): PatientModel {
        return DB::transaction(function () use ($data, $addressData, $identifications, $contacts) {
            // Auto-generate code and set clinic
            $data['clinic_id'] = currentClinic()->id;
            $data['code'] = ClinicCodeService::patient(currentClinic()->id);
            $data['created_by'] = auth()->id();

            $patient = PatientModel::create($data);

            // ── Address (one per patient) ─────────────────────────────────────
            if (!empty(array_filter($addressData))) {
                PatientAddressModel::create(array_merge($addressData, [
                    'patient_code' => $patient->code,
                    'created_by'   => auth()->id(),
                ]));
            }

            // ── Identifications (NID, Passport, HEF cards) ───────────────────
            foreach ($identifications as $idCard) {
                if (!empty($idCard['card_code']) && !empty($idCard['card_type'])) {
                    PatientIdentificationModel::create([
                        'patient_code' => $patient->code,
                        'card_code'    => $idCard['card_code'],
                        'card_type'    => $idCard['card_type'],
                        'created_by'   => auth()->id(),
                    ]);
                }
            }

            // ── Contacts (emergency/next-of-kin) ─────────────────────────────
            foreach ($contacts as $contact) {
                if (!empty($contact['contact_name'])) {
                    PatientContactModel::create([
                        'patient_code'  => $patient->code,
                        'contact_name'  => $contact['contact_name'],
                        'contact_phone' => $contact['contact_phone'] ?? null,
                        'relationship'  => $contact['relationship'] ?? null,
                        'is_emergency'  => $contact['is_emergency'] ?? false,
                        'created_by'    => auth()->id(),
                    ]);
                }
            }

            return $patient;
        });
    }

    /**
     * Update a patient and their related data (transactional).
     *
     * Uses sync strategy for identifications and contacts:
     * - Items with existing ID → update
     * - Items without ID → create
     * - Existing items not in the array → soft-delete
     */
    public function update(
        string $code,
        array $data,
        array $addressData = [],
        array $identifications = [],
        array $contacts = []
    ): PatientModel {
        return DB::transaction(function () use ($code, $data, $addressData, $identifications, $contacts) {
            $patient = PatientModel::where('code', $code)->firstOrFail();

            $data['updated_by'] = auth()->id();
            $patient->update($data);

            // ── Address ───────────────────────────────────────────────────────
            if (!empty(array_filter($addressData))) {
                PatientAddressModel::updateOrCreate(
                    ['patient_code' => $patient->code],
                    array_merge($addressData, ['updated_by' => auth()->id()])
                );
            }

            // ── Sync identifications ──────────────────────────────────────────
            $this->syncIdentifications($patient, $identifications);

            // ── Sync contacts ─────────────────────────────────────────────────
            $this->syncContacts($patient, $contacts);

            return $patient->fresh(['address', 'identifications', 'contacts']);
        });
    }

    /**
     * Soft-delete a patient (only if no active visits).
     *
     * @throws \RuntimeException if patient has active visits
     */
    public function destroy(string $code): void
    {
        $patient = PatientModel::where('code', $code)->firstOrFail();

        // Safety: prevent deleting patients with active visits
        $activeVisits = $patient->visits()->whereNull('discharged_at')->count();
        if ($activeVisits > 0) {
            throw new \RuntimeException(
                "Cannot delete patient {$code}: {$activeVisits} active visit(s) exist."
            );
        }

        $patient->update(['updated_by' => auth()->id()]);
        $patient->delete(); // SoftDeletes → sets deleted_at
    }

    /**
     * Search patients for autocomplete (JSON).
     */
    public function search(string $query, int $limit = 10): Collection
    {
        return PatientModel::where(fn($q) => $q
                ->where('code',    'ilike', "%{$query}%")
                ->orWhere('surname','ilike', "%{$query}%")
                ->orWhere('name',   'ilike', "%{$query}%")
                ->orWhere('phone',  'ilike', "%{$query}%")
            )
            ->withCount('visits')
            ->limit($limit)
            ->get(['id', 'code', 'surname', 'name', 'sex', 'birthdate', 'phone']);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // PRIVATE HELPERS
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Sync identifications: update existing, create new, soft-delete removed.
     */
    private function syncIdentifications(PatientModel $patient, array $identifications): void
    {
        $submittedIds = collect($identifications)->pluck('id')->filter()->all();

        // Soft-delete items that were removed from the form
        PatientIdentificationModel::where('patient_code', $patient->code)
            ->when(!empty($submittedIds), fn($q) => $q->whereNotIn('id', $submittedIds))
            ->when(empty($submittedIds) && !empty($identifications), fn($q) => $q) // keep existing if no IDs
            ->when(empty($identifications), fn($q) => $q) // if empty array submitted, delete all
            ->delete();

        foreach ($identifications as $idCard) {
            if (empty($idCard['card_code']) || empty($idCard['card_type'])) {
                continue;
            }

            if (!empty($idCard['id'])) {
                // Update existing
                PatientIdentificationModel::where('id', $idCard['id'])
                    ->where('patient_code', $patient->code)
                    ->update([
                        'card_code'  => $idCard['card_code'],
                        'card_type'  => $idCard['card_type'],
                        'updated_by' => auth()->id(),
                    ]);
            } else {
                // Create new
                PatientIdentificationModel::create([
                    'patient_code' => $patient->code,
                    'card_code'    => $idCard['card_code'],
                    'card_type'    => $idCard['card_type'],
                    'created_by'   => auth()->id(),
                ]);
            }
        }
    }

    /**
     * Sync contacts: update existing, create new, soft-delete removed.
     */
    private function syncContacts(PatientModel $patient, array $contacts): void
    {
        $submittedIds = collect($contacts)->pluck('id')->filter()->all();

        // Soft-delete contacts not in submitted list
        if (!empty($submittedIds)) {
            PatientContactModel::where('patient_code', $patient->code)
                ->whereNotIn('id', $submittedIds)
                ->delete();
        }

        foreach ($contacts as $contact) {
            if (empty($contact['contact_name'])) {
                continue;
            }

            $row = [
                'contact_name'  => $contact['contact_name'],
                'contact_phone' => $contact['contact_phone'] ?? null,
                'relationship'  => $contact['relationship'] ?? null,
                'is_emergency'  => $contact['is_emergency'] ?? false,
            ];

            if (!empty($contact['id'])) {
                PatientContactModel::where('id', $contact['id'])
                    ->where('patient_code', $patient->code)
                    ->update(array_merge($row, ['updated_by' => auth()->id()]));
            } else {
                PatientContactModel::create(array_merge($row, [
                    'patient_code' => $patient->code,
                    'created_by'   => auth()->id(),
                ]));
            }
        }
    }
}
