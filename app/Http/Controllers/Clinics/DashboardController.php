<?php

namespace App\Http\Controllers\Clinics;

use App\Http\Controllers\Controller;
use App\Models\MedicineModel;
use App\Models\VisitModel;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): Factory|View
    {
        $clinicId = currentClinic()->id;

        // ── KPI Stats ────────────────────────────────────────────────────────
        $stats = [
            'today_visits' => VisitModel::whereHas('patient', fn($q) => $q->where('clinic_id', $clinicId))
                ->whereDate('admitted_at', today())
                ->count(),

            'inpatients' => VisitModel::whereHas('patient', fn($q) => $q->where('clinic_id', $clinicId))
                ->where('visit_type', 'IPD')
                ->whereNull('discharged_at')
                ->count(),

            'active_total' => VisitModel::whereHas('patient', fn($q) => $q->where('clinic_id', $clinicId))
                ->whereNull('discharged_at')
                ->count(),

            'pending_invoices' => \App\Models\InvoiceModel::whereHas('patient', fn($q) => $q->where('clinic_id', $clinicId))
                ->whereIn('status', ['pending', 'partial'])->count(),

            'this_month' => VisitModel::whereHas('patient', fn($q) => $q->where('clinic_id', $clinicId))
                ->whereMonth('admitted_at', now()->month)
                ->whereYear('admitted_at', now()->year)
                ->count(),

            'low_stock' => MedicineModel::where('clinic_id', $clinicId)
                ->whereColumn('stock', '<=', 'stock_alert')
                ->count(),

            'out_stock' => MedicineModel::where('clinic_id', $clinicId)
                ->where('stock', '<=', 0)
                ->count(),
        ];

        // ── Recent visits (last 10, with patient eager-loaded) ───────────────
        $recentVisits = VisitModel::whereHas('patient', fn($q) => $q->where('clinic_id', $clinicId))
            ->with('patient')
            ->latest('admitted_at')
            ->take(10)
            ->get();

        // ── 7-day chart ──────────────────────────────────────────────────────
        $dayNames    = ['អា', 'ច', 'អ', 'ព', 'ព្រ', 'ស', 'សៅ'];
        $weeklyStats = collect(range(6, 0))->map(function (int $daysAgo) use ($clinicId, $dayNames) {
            $date = now()->subDays($daysAgo);

            $base = VisitModel::whereHas('patient', fn($q) => $q->where('clinic_id', $clinicId))
                ->whereDate('admitted_at', $date);

            return [
                'day' => $dayNames[$date->dayOfWeek],
                'date' => $date->format('d/m'),
                'opd'  => (clone $base)->where('visit_type', 'OPD')->count(),
                'ipd'  => (clone $base)->where('visit_type', 'IPD')->count(),
            ];
        })->all();

        return view('clinics.dashboard', compact('stats', 'recentVisits', 'weeklyStats'));
    }
}
