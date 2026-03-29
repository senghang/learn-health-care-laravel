<?php

namespace App\Services;

use App\Models\LaboratoryModel;
use App\Models\LaboratoryResultModel;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * LaboratoryService — lab order lifecycle.
 *
 * Flow: Request → Collect Sample → Process → Record Results → Verify.
 * Each step is a status transition on the lab order.
 */
class LaboratoryService
{
    /**
     * List lab orders with filters.
     */
    public function list(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return LaboratoryModel::query()
            ->with(['patient', 'visit', 'results'])
            ->when($filters['search'] ?? null, fn($q, $s) => $q->where('code', 'like', "%{$s}%")
                ->orWhereHas('patient', fn($p) => $p->where('surname', 'like', "%{$s}%")
                    ->orWhere('name', 'like', "%{$s}%")
                )
            )
            ->when($filters['status'] ?? null, fn($q, $v) => $q->where('status', $v))
            ->when($filters['urgency'] ?? null, fn($q, $v) => $q->where('urgency', $v))
            ->when($filters['date'] ?? null, fn($q, $v) => $q->whereDate('requested_at', $v))
            ->latest('requested_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Find a lab order by code.
     */
    public function findByCode(string $code): LaboratoryModel
    {
        return LaboratoryModel::where('code', $code)
            ->with(['patient', 'visit', 'results'])
            ->firstOrFail();
    }

    /**
     * Create a lab order with test items (transactional).
     */
    public function createOrder(array $data): LaboratoryModel
    {
        return DB::transaction(function () use ($data) {
            $lab = LaboratoryModel::create([
                'code' => ClinicCodeService::labRequest(currentClinic()->id),
                'patient_code' => $data['patient_code'],
                'visit_code' => $data['visit_code'],
                'encounter_code' => $data['encounter_code'] ?? null,
                'title' => $data['title'] ?? null,
                'status' => 'requested',
                'urgency' => $data['urgency'] ?? 'normal',
                'requested_at' => now(),
                'requested_by' => $data['requested_by'] ?? auth()->user()?->name,
            ]);

            // Create result placeholders for each test
            foreach ($data['tests'] ?? [] as $test) {
                LaboratoryResultModel::create([
                    'request_code' => $lab->code,
                    'name' => $test['name'],
                    'category' => $test['category'] ?? null,
                    'value_type' => $test['value_type'] ?? 'numeric',
                ]);
            }

            return $lab;
        });
    }

    /**
     * Transition: requested → collected (sample taken).
     */
    public function collectSample(string $code, ?string $collectedBy = null): LaboratoryModel
    {
        $lab = LaboratoryModel::where('code', $code)->firstOrFail();

        $lab->update([
            'status' => 'collected',
            'collected_at' => now(),
            'collected_by' => $collectedBy ?? auth()->user()?->name,
        ]);

        return $lab;
    }

    /**
     * Record results for a lab order.
     */
    public function recordResults(string $code, array $results): LaboratoryModel
    {
        return DB::transaction(function () use ($code, $results) {
            $lab = LaboratoryModel::where('code', $code)->firstOrFail();

            foreach ($results as $resultData) {
                if (empty($resultData['id'])) continue;

                LaboratoryResultModel::where('id', $resultData['id'])
                    ->where('request_code', $code)
                    ->update([
                        'value' => $resultData['result'] ?? null,
                        'interpretation' => $resultData['conclusion'] ?? null,
                        'recorded_at' => now(),
                        'recorded_by' => auth()->user()?->name,
                    ]);
            }

            $lab->update(['status' => 'completed']);

            return $lab->fresh(['results']);
        });
    }

    /**
     * Verify lab results (final sign-off).
     */
    public function verify(string $code, ?string $verifiedBy = null): LaboratoryModel
    {
        $lab = LaboratoryModel::where('code', $code)->firstOrFail();

        $lab->results()->whereNull('verified_at')->update([
            'verified_at' => now(),
            'verified_by' => $verifiedBy ?? auth()->user()?->name,
        ]);

        return $lab;
    }

    /**
     * Lab statistics for dashboard.
     */
    public function stats(): array
    {
        return [
            'today_orders' => LaboratoryModel::whereDate('requested_at', today())->count(),
            'pending' => LaboratoryModel::where('status', 'requested')->count(),
            'in_progress' => LaboratoryModel::whereIn('status', ['collected', 'processing'])->count(),
            'completed_today' => LaboratoryModel::where('status', 'completed')->whereDate('updated_at', today())->count(),
        ];
    }
}
