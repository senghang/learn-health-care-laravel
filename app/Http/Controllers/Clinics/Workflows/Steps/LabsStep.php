<?php

namespace App\Http\Controllers\Clinics\Workflows\Steps;

use App\Models\LaboratoryModel;
use App\Models\LaboratoryResultModel;
use App\Models\VisitModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LabsStep extends AbstractWorkflowStep
{
    public function id(): string         { return 'labs'; }
    public function labelKm(): string    { return 'ពិសោធន៍'; }
    public function labelEn(): string    { return 'Laboratory'; }
    public function icon(): string       { return '🔬'; }
    public function color(): string      { return '#ff771d'; }
    public function description(): string { return 'Lab requests & results'; }

    public function save(VisitModel $visit, Request $request): void
    {
        $validated = $request->validate([
            'new_labs'                          => 'nullable|array',
            'new_labs.*.requested_by'           => 'nullable|string|max:120',
            'new_labs.*.requested_at'           => 'nullable|date',
            'new_labs.*.results'                => 'nullable|array',
            'new_labs.*.results.*.name'         => 'required_with:new_labs.*.results|string|max:200',
            'new_labs.*.results.*.category'     => 'nullable|string|max:80',
            'new_labs.*.results.*.value'        => 'nullable|string|max:255',
            'new_labs.*.results.*.interpretation'   => 'nullable|string|max:80',
            'new_labs.*.results.*.reference_range'  => 'nullable|string|max:80',
            'results'                           => 'nullable|array',
            'results.*.id'                      => 'sometimes|integer',
            'results.*.request_code'            => 'sometimes|string',
            'results.*.name'                    => 'required|string|max:200',
            'results.*.value'                   => 'nullable|string|max:255',
            'results.*.interpretation'          => 'nullable|string|max:80',
            'results.*.reference_range'         => 'nullable|string|max:80',
        ]);

        $userName = auth()->user()?->name ?? 'System';

        DB::transaction(function () use ($visit, $validated, $userName) {
            foreach ($validated['new_labs'] ?? [] as $labInput) {
                $resultsInput = array_filter($labInput['results'] ?? [], fn($r) => !empty(trim($r['name'] ?? '')));
                if (empty($resultsInput)) continue;

                $lab = LaboratoryModel::create([
                    'code'         => \App\Services\ClinicCodeService::labRequest(currentClinic()->id),
                    'patient_code' => $visit->patient_code,
                    'visit_code'   => $visit->code,
                    'requested_at' => $labInput['requested_at'] ?? now(),
                    'requested_by' => $labInput['requested_by'] ?? $userName,
                    'title'        => implode(', ', array_column(array_values($resultsInput), 'name')),
                ]);

                foreach ($resultsInput as $res) {
                    LaboratoryResultModel::create([
                        'request_code'    => $lab->code,
                        'name'            => $res['name'],
                        'category'        => $res['category'] ?? null,
                        'value'           => $res['value'] ?? null,
                        'interpretation'  => $res['interpretation'] ?? null,
                        'reference_range' => $res['reference_range'] ?? null,
                        'recorded_at'     => now(),
                        'recorded_by'     => $labInput['requested_by'] ?? $userName,
                    ]);
                }
            }

            // Upsert existing results
            foreach ($validated['results'] ?? [] as $resInput) {
                if (empty($resInput['name']) || empty($resInput['request_code'])) continue;
                LaboratoryResultModel::updateOrCreate(
                    ['request_code' => $resInput['request_code'], 'name' => $resInput['name']],
                    [
                        'value'           => $resInput['value'] ?? null,
                        'interpretation'  => $resInput['interpretation'] ?? null,
                        'reference_range' => $resInput['reference_range'] ?? null,
                        'recorded_at'     => now(),
                        'recorded_by'     => $userName,
                    ]
                );
            }
        });
    }

    public function viewData(VisitModel $visit): array
    {
        $labs = $visit->laboratories()->with('results')->latest('requested_at')->get();
        $hasCritical = $labs->contains(
            fn($lab) => $lab->results->contains(
                fn($r) => in_array(strtolower($r->interpretation ?? ''), ['positive','high','critical'])
            )
        );
        return compact('labs', 'hasCritical');
    }
}
