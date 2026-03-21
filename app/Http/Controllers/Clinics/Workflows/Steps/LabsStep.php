<?php

namespace App\Http\Controllers\Clinics\Workflows\Steps;

use App\Models\LaboratoryModel;
use App\Models\LaboratoryResultModel;
use App\Models\VisitModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LabsStep extends AbstractWorkflowStep
{
    public function id(): string
    {
        return 'labs';
    }

    public function labelKm(): string
    {
        return 'ពិសោធន៍';
    }

    public function labelEn(): string
    {
        return 'Laboratory';
    }

    public function icon(): string
    {
        return '🔬';
    }

    public function color(): string
    {
        return '#ff771d';
    }

    public function description(): string
    {
        return 'សំណើ & លទ្ធផលមន្ទីរពិសោធន៍';
    }

    public function save(VisitModel $visit, Request $request): void
    {
        $validated = $request->validate([
            // New lab requests (multiple rows from dynamic form)
            'new_labs' => 'nullable|array',
            'new_labs.*.requested_by' => 'nullable|string|max:120',
            'new_labs.*.requested_at' => 'nullable|date',
            'new_labs.*.results' => 'nullable|array',
            'new_labs.*.results.*.name' => 'required_with:new_labs.*.results|string|max:200',
            'new_labs.*.results.*.category' => 'nullable|string|max:80',
            'new_labs.*.results.*.value' => 'nullable|string|max:255',
            'new_labs.*.results.*.interpretation' => 'nullable|string|max:80',
            'new_labs.*.results.*.reference_range' => 'nullable|string|max:80',

            // Updates / upsert for existing results (indexed by result ID or by name+request)
            'results' => 'nullable|array',
            'results.*.id' => 'sometimes|integer|exists:laboratory_results,id',
            'results.*.request_code' => 'sometimes|required_without:results.*.id|string',
            'results.*.name' => 'required|string|max:200',
            'results.*.category' => 'nullable|string|max:80',
            'results.*.value' => 'nullable|string|max:255',
            'results.*.interpretation' => 'nullable|string|max:80',
            'results.*.reference_range' => 'nullable|string|max:80',
        ]);

        $userName = auth()->user()?->name ?? 'System';

        DB::transaction(function () use ($visit, $validated, $userName) {

            // ── 1. Create NEW lab requests + initial results ─────────────────────
            foreach ($validated['new_labs'] ?? [] as $labInput) {
                $resultsInput = $labInput['results'] ?? [];

                // Filter valid results (must have name)
                $validResults = array_filter($resultsInput, fn($r) => !empty(trim($r['name'] ?? '')));

                if (empty($validResults)) {
                    continue;
                }

                $lab = LaboratoryModel::create([
                    'code' => 'EL-' . $visit->code . '-' . now()->format('YmdHisv') . '-' . substr(uniqid(), -6),
                    'patient_code' => $visit->patient_code,
                    'visit_code' => $visit->code,
                    'requested_at' => $labInput['requested_at'] ?? now(),
                    'requested_by' => $labInput['requested_by'] ?? $userName,
                    'title' => implode(', ', array_column($validResults, 'name')),
                    // 'status'    => 'pending',  // add if your model has this
                ]);

                foreach ($validResults as $res) {
                    LaboratoryResultModel::create([
                        'request_code' => $lab->code,
                        'name' => $res['name'],
                        'category' => $res['category'] ?? null,
                        'value' => $res['value'] ?? null,
                        'interpretation' => $res['interpretation'] ?? null,
                        'reference_range' => $res['reference_range'] ?? null,
                        'recorded_at' => now(),
                        'recorded_by' => $labInput['requested_by'] ?? $userName,
                    ]);
                }
            }

            // ── 2. Upsert EXISTING / submitted results ────────────────────────────
            $resultsToUpsert = [];
            foreach ($validated['results'] ?? [] as $resInput) {
                $key = [
                    'request_code' => $resInput['request_code'] ?? null,
                    'name' => $resInput['name'],
                ];

                // Skip if missing critical unique keys
                if (empty($key['name']) || (empty($resInput['id']) && empty($key['request_code']))) {
                    continue;
                }

                $resultsToUpsert[] = [
                    'id' => $resInput['id'] ?? null,  // used only for reference, not upsert key
                    'request_code' => $key['request_code'],
                    'name' => $key['name'],
                    'category' => $resInput['category'] ?? null,
                    'value' => $resInput['value'] ?? null,
                    'interpretation' => $resInput['interpretation'] ?? null,
                    'reference_range' => $resInput['reference_range'] ?? null,
                    'recorded_at' => now(),
                    'recorded_by' => $userName,
                ];
            }

            if (!empty($resultsToUpsert)) {
                // Ensure table has unique index on (request_code, name)
                LaboratoryResultModel::upsert(
                    $resultsToUpsert,
                    ['request_code', 'name'],  // must match unique constraint/index
                    [
                        'category',
                        'value',
                        'interpretation',
                        'reference_range',
                        'recorded_at',
                        'recorded_by',
                    ]
                );
            }
        });
    }

    public function viewData(VisitModel $visit): array
    {
        $labs = $visit->laboratories()
            ->with('results')
            ->latest('requested_at')
            ->get();

        $hasCritical = $labs->contains(
            fn($lab) => $lab->results->contains(
                fn($r) => in_array(strtolower($r->interpretation ?? ''), ['positive', 'high', 'critical'])
            )
        );

        return compact('labs', 'hasCritical');
    }
}
