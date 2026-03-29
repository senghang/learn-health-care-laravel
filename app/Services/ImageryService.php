<?php

namespace App\Services;

use App\Models\ImageryModel;
use App\Models\ImageryResultModel;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * ImageryService — imaging/radiology order lifecycle.
 *
 * Flow: Request → Perform → Record Result → Verify.
 * Mirrors LaboratoryService pattern for consistency.
 */
class ImageryService
{
    public function list(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return ImageryModel::query()
            ->with(['patient', 'visit', 'results'])
            ->when($filters['search'] ?? null, fn($q, $s) =>
                $q->where('code', 'like', "%{$s}%")
                  ->orWhereHas('patient', fn($p) =>
                      $p->where('surname', 'like', "%{$s}%")
                        ->orWhere('name', 'like', "%{$s}%")
                  )
            )
            ->when($filters['status'] ?? null,   fn($q, $v) => $q->where('status', $v))
            ->when($filters['category'] ?? null, fn($q, $v) => $q->where('category', $v))
            ->when($filters['urgency'] ?? null,  fn($q, $v) => $q->where('urgency', $v))
            ->when($filters['date'] ?? null,     fn($q, $v) => $q->whereDate('requested_at', $v))
            ->latest('requested_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findByCode(string $code): ImageryModel
    {
        return ImageryModel::where('code', $code)
            ->with(['patient', 'visit', 'results'])
            ->firstOrFail();
    }

    /**
     * Create an imaging order (transactional).
     */
    public function createOrder(array $data): ImageryModel
    {
        return DB::transaction(function () use ($data) {
            $imagery = ImageryModel::create([
                'code'           => ClinicCodeService::next(currentClinic()->id, 'IMG'),
                'patient_code'   => $data['patient_code'],
                'visit_code'     => $data['visit_code'],
                'encounter_code' => $data['encounter_code'] ?? null,
                'category'       => $data['category'],
                'title'          => $data['title'] ?? null,
                'status'         => 'requested',
                'urgency'        => $data['urgency'] ?? 'normal',
                'requested_at'   => now(),
                'requested_by'   => $data['requested_by'] ?? auth()->user()?->name,
            ]);

            // Create empty result placeholder
            ImageryResultModel::create([
                'request_code' => $imagery->code,
                'name'         => $data['title'] ?? $data['category'],
                'category'     => $data['category'],
            ]);

            return $imagery;
        });
    }

    /**
     * Record imaging results with optional image paths.
     */
    public function recordResult(string $code, array $resultData): ImageryModel
    {
        return DB::transaction(function () use ($code, $resultData) {
            $imagery = ImageryModel::where('code', $code)->firstOrFail();

            // Update existing result or create new one
            $result = $imagery->results()->first();

            if ($result) {
                $result->update([
                    'result'      => $resultData['result'] ?? null,
                    'conclusion'  => $resultData['conclusion'] ?? null,
                    'images'      => $resultData['images'] ?? $result->images,
                    'recorded_at' => now(),
                    'recorded_by' => auth()->user()?->name,
                ]);
            }

            $imagery->update([
                'status'       => 'completed',
                'collected_at' => now(),
                'collected_by' => auth()->user()?->name,
            ]);

            return $imagery->fresh(['results']);
        });
    }

    public function verify(string $code, ?string $verifiedBy = null): ImageryModel
    {
        $imagery = ImageryModel::where('code', $code)->firstOrFail();

        $imagery->results()->whereNull('verified_at')->update([
            'verified_at' => now(),
            'verified_by' => $verifiedBy ?? auth()->user()?->name,
        ]);

        return $imagery;
    }

    public function stats(): array
    {
        return [
            'today_orders'    => ImageryModel::whereDate('requested_at', today())->count(),
            'pending'         => ImageryModel::where('status', 'requested')->count(),
            'completed_today' => ImageryModel::where('status', 'completed')->whereDate('updated_at', today())->count(),
        ];
    }
}
