<?php

namespace App\Http\Controllers\Clinics;

use App\Http\Controllers\Controller;
use App\Models\VisitModel;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): Factory|View
    {
        // ── KPI stats ────────────────────────────────────────────────────────
        $stats = [
            'today_visits' => VisitModel::whereDate('admitted_at', today())->count(),
            'inpatients' => VisitModel::where('visit_type', 'IPD')
                ->whereNull('discharged_at')
                ->count(),
            'lab_results' => 0,  // TODO: Lab::whereDate('requested_at', today())->count()
            'critical_labs' => 0,  // TODO: Lab::critical()->today()->count()
            'revenue' => '0',// TODO: Invoice::whereDate('invoice_date', today())->sum('total')
        ];

        // ── Recent visits (last 6, with patient eager-loaded) ────────────────
        $recentVisits = VisitModel::with('patient')
            ->latest('admitted_at')
            ->take(6)
            ->get();

        // ── 7-day chart — real counts, no fallback ───────────────────────────
        $dayNames = ['អា', 'ច', 'អ', 'ព', 'ព្រ', 'ស', 'សៅ'];
        $weeklyStats = collect(range(6, 0))->map(function (int $daysAgo) use ($dayNames) {
            $date = now()->subDays($daysAgo);
            return [
                'day' => $dayNames[$date->dayOfWeek],
                'opd' => VisitModel::where('visit_type', 'OPD')
                    ->whereDate('admitted_at', $date)
                    ->count(),
                'ipd' => VisitModel::where('visit_type', 'IPD')
                    ->whereDate('admitted_at', $date)
                    ->count(),
            ];
        })->all();

        return view('clinics.dashboard', compact('stats', 'recentVisits', 'weeklyStats'));
    }
}
