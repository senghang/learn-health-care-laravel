<?php

namespace App\Http\Controllers\Clinics\Workflows\Steps;

use App\Models\Base\ResolvesEncounter;
use App\Models\VisitModel;
use App\Models\VitalSignModel;
use App\Models\VitalSignObservationModel;
use Illuminate\Http\Request;

class VitalsStep extends AbstractWorkflowStep
{
    use ResolvesEncounter;

    public function id(): string
    {
        return 'vitals';
    }

    public function labelKm(): string
    {
        return 'សញ្ញាជំងឺ';
    }

    public function labelEn(): string
    {
        return 'Vital Signs';
    }

    public function icon(): string
    {
        return '❤️';
    }

    public function color(): string
    {
        return '#e74c3c';
    }

    public function description(): string
    {
        return 'T° HR BP SpO₂ Glucose';
    }

    public function save(VisitModel $visit, Request $request): void
    {
        $data = $this->validate($request, [
            'recorded_at' => 'nullable|date',
            'recorded_by' => 'nullable|string|max:120',
            'vitals.temperature' => 'nullable|numeric|between:30,45',
            'vitals.heart_rate' => 'nullable|integer|between:20,300',
            'vitals.respiratory_rate' => 'nullable|integer|between:4,60',
            'vitals.blood_pressure_systolic' => 'nullable|integer|between:50,300',
            'vitals.blood_pressure_diastolic' => 'nullable|integer|between:20,200',
            'vitals.oxygen_saturation' => 'nullable|numeric|between:50,100',
            'vitals.blood_glucose' => 'nullable|numeric|between:1,50',
        ]);

        $vitals = array_filter(
            $data['vitals'] ?? [],
            fn($v) => $v !== null && $v !== ''
        );

        if (empty($vitals)) {
            return; // nothing to save — skip quietly
        }

        $encounter = $this->getOrCreateEncounter($visit);

        $vsCode = 'VS-' . $visit->code . '-' . now()->timestamp;

        $vs = VitalSignModel::create([
            'code' => $vsCode,
            'encounter_code' => $encounter->code,
            'recorded_at' => $data['recorded_at'] ?? now(),
            'recorded_by' => $data['recorded_by'] ?? null,
        ]);

        $units = [
            'temperature' => '°C',
            'heart_rate' => 'bpm',
            'respiratory_rate' => '/min',
            'blood_pressure_systolic' => 'mmHg',
            'blood_pressure_diastolic' => 'mmHg',
            'oxygen_saturation' => '%',
            'blood_glucose' => 'mmol/L',
        ];

        foreach ($vitals as $name => $value) {
            if (!in_array($name, VitalSignObservationModel::ALLOWED_NAMES)) {
                continue; // skip any keys not in the CHECK constraint
            }
            VitalSignObservationModel::create([
                'vital_sign_code' => $vs->code,
                'name' => $name,
                'value' => $value,
                'unit' => $units[$name] ?? null,
            ]);
        }
    }

    public function viewData(VisitModel $visit): array
    {
        $encounter = $this->findEncounter($visit);

        $latestVs = $encounter
            ? VitalSignModel::where('encounter_code', $encounter->code)
                ->with('observations')
                ->latest('recorded_at')
                ->first()
            : null;

        // Build flat ['temperature' => 37.2, ...] from observations collection
        $savedValues = $latestVs
            ? $latestVs->observations->pluck('value', 'name')->all()
            : [];

        $vitalFields = [
            ['key' => 'temperature', 'km' => 'កម្ដៅខ្លួន', 'en' => 'Temperature', 'icon' => '🌡️', 'unit' => '°C', 'placeholder' => '37.0', 'normal' => '36.1–37.2', 'lo' => 36.1, 'hi' => 37.2],
            ['key' => 'heart_rate', 'km' => 'ចង្វាក់បេះដូង', 'en' => 'Heart Rate', 'icon' => '❤️', 'unit' => 'bpm', 'placeholder' => '80', 'normal' => '60–100', 'lo' => 60, 'hi' => 100],
            ['key' => 'respiratory_rate', 'km' => 'ដង្ហើម', 'en' => 'Resp. Rate', 'icon' => '💨', 'unit' => '/min', 'placeholder' => '16', 'normal' => '12–20', 'lo' => 12, 'hi' => 20],
            ['key' => 'blood_pressure_systolic', 'km' => 'សម្ពាធ(S)', 'en' => 'BP Systolic', 'icon' => '🩸', 'unit' => 'mmHg', 'placeholder' => '120', 'normal' => '90–120', 'lo' => 90, 'hi' => 120],
            ['key' => 'blood_pressure_diastolic', 'km' => 'សម្ពាធ(D)', 'en' => 'BP Diastolic', 'icon' => '🩸', 'unit' => 'mmHg', 'placeholder' => '80', 'normal' => '60–80', 'lo' => 60, 'hi' => 80],
            ['key' => 'oxygen_saturation', 'km' => 'អុកស៊ីសែន', 'en' => 'SpO₂', 'icon' => '🫁', 'unit' => '%', 'placeholder' => '98', 'normal' => '95–100', 'lo' => 95, 'hi' => 100],
            ['key' => 'blood_glucose', 'km' => 'ជាតិស្ករ', 'en' => 'Glucose', 'icon' => '🧪', 'unit' => 'mmol/L', 'placeholder' => '5.5', 'normal' => '3.9–6.1', 'lo' => 3.9, 'hi' => 6.1],
        ];

        return compact('latestVs', 'savedValues', 'vitalFields');
    }
}
