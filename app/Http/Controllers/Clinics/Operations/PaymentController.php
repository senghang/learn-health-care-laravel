<?php

namespace App\Http\Controllers\Clinics\Operations;

use App\Http\Controllers\Controller;
use App\Models\PaymentModel;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $clinicId = currentClinic()->id;

        $query = PaymentModel::with(['invoice', 'patient'])
            ->where('clinic_id', $clinicId)
            ->latest('paid_at');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('invoice_code', 'like', "%{$search}%")
                  ->orWhere('patient_code', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        if ($method = $request->input('method')) {
            $query->where('method', strtoupper($method));
        }

        if ($from = $request->input('from')) {
            $query->whereDate('paid_at', '>=', $from);
        }

        if ($to = $request->input('to')) {
            $query->whereDate('paid_at', '<=', $to);
        }

        $payments = $query->paginate(25)->withQueryString();

        // Stats for today
        $todayTotal = PaymentModel::where('clinic_id', $clinicId)->whereDate('paid_at', today())->sum('amount');
        $todayCount = PaymentModel::where('clinic_id', $clinicId)->whereDate('paid_at', today())->count();
        $monthTotal = PaymentModel::where('clinic_id', $clinicId)->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)->sum('amount');

        return view('clinics.operations.payments', compact(
            'payments', 'todayTotal', 'todayCount', 'monthTotal'
        ));
    }

    public function show(string $code): View
    {
        $clinicId = currentClinic()->id;

        $payment = PaymentModel::with(['invoice.patient', 'invoice.services', 'invoice.payments', 'patient'])
            ->where('clinic_id', $clinicId)
            ->where('code', $code)
            ->firstOrFail();

        return view('clinics.operations.payment-show', compact('payment'));
    }
}
