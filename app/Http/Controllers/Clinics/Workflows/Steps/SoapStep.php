<?php

namespace App\Http\Controllers\Clinics\Workflows\Steps;

use App\Models\Base\ResolvesEncounter;
use App\Models\SoapModel;
use App\Models\VisitModel;
use Illuminate\Http\Request;

class SoapStep extends AbstractWorkflowStep
{
    use ResolvesEncounter;

    public function id(): string
    {
        return 'soap';
    }

    public function labelKm(): string
    {
        return 'SOAP Notes';
    }

    public function labelEn(): string
    {
        return 'SOAP';
    }

    public function icon(): string
    {
        return '📝';
    }

    public function color(): string
    {
        return '#3498db';
    }

    public function description(): string
    {
        return 'Subjective · Objective · Assessment · Plan';
    }

    public function save(VisitModel $visit, Request $request): void
    {
        $data = $this->validate($request, [
            'subjective' => 'nullable|string',
            'objective' => 'nullable|string',
            'assessment' => 'nullable|string',
            'evaluation' => 'nullable|string',
            'plan' => 'nullable|string',
        ]);

        $encounter = $this->getOrCreateEncounter($visit);

        SoapModel::updateOrCreate(
            ['encounter_code' => $encounter->code],
            array_filter($data, fn($v) => $v !== null && $v !== '')
        );
    }

    public function viewData(VisitModel $visit): array
    {
        $encounter = $this->findEncounter($visit);

        $soap = $encounter
            ? SoapModel::where('encounter_code', $encounter->code)->first()
            : null;

        return compact('soap', 'encounter');
    }
}
