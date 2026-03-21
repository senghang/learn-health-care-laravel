<?php

namespace App\Http\Controllers\Clinics\Workflows\Steps;

use App\Common\Utils\CodeGenerator;
use App\Models\ReferralModel;
use App\Models\VisitModel;
use Illuminate\Http\Request;

class ReferralStep extends AbstractWorkflowStep
{
    public function id(): string          { return 'referral'; }
    public function labelKm(): string     { return 'បញ្ជូន'; }
    public function labelEn(): string     { return 'Referral'; }
    public function icon(): string        { return '➡️'; }
    public function color(): string       { return '#ff9800'; }
    public function description(): string { return 'ការបញ្ជូនចូល (FROM) / ចេញ (TO)'; }

    public function save(VisitModel $visit, Request $request): void
    {
        $data = $this->validate($request, [
            'direction'       => 'required|in:FROM,TO',
            'referral_number' => 'nullable|string|max:80',
            'transportation'  => 'nullable|string|max:80',
            'reason'          => 'nullable|string',
            'has_called'      => 'nullable|boolean',
            'caretaker_name'  => 'nullable|string|max:120',
            'caretaker_phone' => 'nullable|string|max:30',
            'referred_by'     => 'nullable|string|max:120',
            'referred_at'     => 'nullable|date',
            'received_by'     => 'nullable|string|max:120',
            'medications'     => 'nullable|string',
        ]);

        ReferralModel::create(array_merge($data, [
            'code'       => CodeGenerator::referral($visit->code),
            'visit_code' => $visit->code,
        ]));
    }

    public function viewData(VisitModel $visit): array
    {
        return [
            'referrals' => $visit->referrals()->latest()->get(),
        ];
    }
}
