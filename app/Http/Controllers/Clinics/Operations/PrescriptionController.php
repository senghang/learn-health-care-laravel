<?php

namespace App\Http\Controllers\Clinics\Operations;

use App\Http\Controllers\Controller;
use App\Models\PrescriptionModel;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrescriptionController extends Controller
{
    private int $clinicId;

    public function __construct()
    {
        $this->clinicId = currentClinic()->id;
    }

    public function index(Request $request): View
    {
        $prescriptions = PrescriptionModel::whereHas('patient', fn($q) =>
                $q->where('clinic_id', $this->clinicId)
            )
            ->with(['patient', 'visit', 'medications'])
            ->when($request->filled('search'), fn($q) =>
                $q->whereHas('patient', fn($p) =>
                    $p->where('surname', 'like', "%{$request->search}%")
                      ->orWhere('name',    'like', "%{$request->search}%")
                      ->orWhere('code',    'like', "%{$request->search}%")
                )
                ->orWhere('code', 'like', "%{$request->search}%")
            )
            ->when($request->filled('date'), fn($q) =>
                $q->whereDate('prescribed_at', $request->date)
            )
            ->when($request->filled('doctor'), fn($q) =>
                $q->where('prescribed_by', 'like', "%{$request->doctor}%")
            )
            ->latest('prescribed_at')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'today'   => PrescriptionModel::whereHas('patient', fn($q) => $q->where('clinic_id', $this->clinicId))
                            ->whereDate('prescribed_at', today())->count(),
            'total'   => PrescriptionModel::whereHas('patient', fn($q) => $q->where('clinic_id', $this->clinicId))->count(),
            'doctors' => PrescriptionModel::whereHas('patient', fn($q) => $q->where('clinic_id', $this->clinicId))
                            ->whereNotNull('prescribed_by')->distinct()->count('prescribed_by'),
        ];

        return view('clinics.operations.prescriptions', compact('prescriptions', 'stats'));
    }

    public function show(string $code): View
    {
        $prescription = PrescriptionModel::whereHas('patient', fn($q) =>
                $q->where('clinic_id', $this->clinicId)
            )
            ->where('code', $code)
            ->with(['patient', 'visit', 'medications'])
            ->firstOrFail();

        return view('clinics.operations.prescription-show', compact('prescription'));
    }
}
