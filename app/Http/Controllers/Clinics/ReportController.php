<?php

namespace App\Http\Controllers\Clinics;

use App\Common\Constants\DateFormats;
use App\Common\Utils\Currency;
use App\Http\Controllers\Controller;
use App\Models\InvoiceModel;
use App\Models\MedicineModel;
use App\Models\VisitModel;
use Carbon\Carbon;
use DB;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReportController extends Controller
{
    // ── Visit Report ─────────────────────────────────────────────────────────

    public function visits(Request $request): View|Response
    {
        $dateFrom = $request->date('date_from') ?? now()->startOfMonth();
        $dateTo = $request->date('date_to') ?? now()->endOfDay();
        $visitType = $request->get('visit_type');
        $status = $request->get('status');
        $paymentType = $request->get('payment_type');
        $search = $request->get('search');
        $sort = $request->get('sort', 'admitted_at');
        $dir = $request->get('dir', 'desc');
        $perPage = (int)$request->get('per_page', 20);

        $query = VisitModel::query()
            ->with(['invoices'])
            ->whereBetween('admitted_at', [$dateFrom->startOfDay(), $dateTo->endOfDay()])
            ->when($visitType, fn($q) => $q->where('visit_type', $visitType))
            ->when($status === 'active', fn($q) => $q->whereNull('discharged_at'))
            ->when($status === 'done', fn($q) => $q->whereNotNull('discharged_at'))
            ->when($search, fn($q) => $q
                ->where('surname', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('patient_code', 'like', "%{$search}%")
            )
            ->when(in_array($sort, ['admitted_at', 'surname', 'visit_type']),
                fn($q) => $q->orderBy($sort, $dir === 'asc' ? 'asc' : 'desc')
            );

        // Payment type filter — join invoices
        if ($paymentType) {
            $query->whereHas('invoices', fn($q) => $q->where('payment_type', $paymentType));
        }

        // CSV export
        if ($request->get('export') === 'csv') {
            return $this->exportCsv($query->get());
        }

        $visits = $query->paginate(max(1, min($perPage, 200)))->withQueryString();

        // ── Totals (full range, not just current page) ──────────────────────
        $allQuery = VisitModel::query()
            ->whereBetween('admitted_at', [$dateFrom->startOfDay(), $dateTo->endOfDay()])
            ->when($visitType, fn($q) => $q->where('visit_type', $visitType))
            ->when($status === 'active', fn($q) => $q->whereNull('discharged_at'))
            ->when($status === 'done', fn($q) => $q->whereNotNull('discharged_at'))
            ->when($search, fn($q) => $q
                ->where('surname', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('patient_code', 'like', "%{$search}%")
            );

        if ($paymentType) {
            $allQuery->whereHas('invoices', fn($q) => $q->where('payment_type', $paymentType));
        }

        $allVisits = $allQuery->withCount([])->get(['id', 'visit_type', 'discharged_at', 'admitted_at']);

        $revenue = InvoiceModel::whereHas('visit', function ($q) use ($dateFrom, $dateTo, $visitType, $paymentType) {
            $q->whereBetween('admitted_at', [$dateFrom->startOfDay(), $dateTo->endOfDay()])
                ->when($visitType, fn($q2) => $q2->where('visit_type', $visitType));
        })
            ->when($paymentType, fn($q) => $q->where('payment_type', $paymentType))
            ->sum('total');

        $totalStats = [
            'total' => $allVisits->count(),
            'opd' => $allVisits->where('visit_type', 'OPD')->count(),
            'ipd' => $allVisits->where('visit_type', 'IPD')->count(),
            'active' => $allVisits->whereNull('discharged_at')->count(),
            'done' => $allVisits->whereNotNull('discharged_at')->count(),
            'revenue' => Currency::format($revenue),
        ];

        // ── Daily chart ──────────────────────────────────────────────────────
        $days = $dateFrom->copy()->startOfDay()->diffInDays($dateTo->copy()->endOfDay());
        $days = min($days, 60); // cap at 60 bars

        $dailyChart = collect();
        for ($i = 0; $i <= $days; $i++) {
            $day = $dateFrom->copy()->addDays($i);
            $dailyChart->push([
                'label' => $day->format($days <= 7 ? 'D' : ($days <= 31 ? 'd' : 'M/d')),
                'opd' => $allVisits->where('visit_type', 'OPD')
                    ->filter(fn($v) => Carbon::parse($v->admitted_at)->isSameDay($day))->count(),
                'ipd' => $allVisits->where('visit_type', 'IPD')
                    ->filter(fn($v) => Carbon::parse($v->admitted_at)->isSameDay($day))->count(),
            ]);
        }

        return view('clinics.reports.visits', compact(
            'visits', 'totalStats', 'dailyChart'
        ));
    }

    // ── Daily Summary ─────────────────────────────────────────────────────────

    private function exportCsv($visits): Response
    {
        $filename = 'visits-report-' . now()->format(DateFormats::EXPORT_DATE) . '.csv';

        $rows = [];
        $rows[] = implode(',', [
            'Date', 'Time', 'Visit Code', 'Patient Code',
            'Surname', 'Given Name', 'Type', 'Admission',
            'Status', 'Discharged At', 'Steps Done', 'Payment Type', 'Total (KHR)',
        ]);

        foreach ($visits as $v) {
            $inv = $v->invoices->first();
            $rows[] = implode(',', array_map(
                fn($cell) => '"' . str_replace('"', '""', $cell ?? '') . '"',
                [
                    $v->admitted_at?->format(DateFormats::EXPORT_DATE),
                    $v->admitted_at?->format(DateFormats::DISPLAY_TIME),
                    $v->code,
                    $v->patient_code,
                    $v->surname,
                    $v->name,
                    $v->visit_type,
                    $v->admission_type ?? '',
                    is_null($v->discharged_at) ? 'Active' : 'Done',
                    $v->discharged_at?->format(DateFormats::EXPORT_DATETIME) ?? '',
                    count($v->done_steps ?? []),
                    $inv?->payment_type ?? '',
                    $inv?->total ?? 0,
                ]
            ));
        }

        return response(implode("\n", $rows), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    // ── Inventory Report ──────────────────────────────────────────────────────

    public function daily(Request $request): View
    {
        $date = $request->date('date') ?? today();

        $allVisits = VisitModel::query()
            ->with(['invoices'])
            ->whereDate('admitted_at', $date)
            ->orderBy('admitted_at')
            ->get();

        $opdVisits = $allVisits->where('visit_type', 'OPD')->values();
        $ipdVisits = $allVisits->where('visit_type', 'IPD')->values();

        $summary = [
            'total' => $allVisits->count(),
            'opd' => $opdVisits->count(),
            'ipd' => $ipdVisits->count(),
            'active' => $allVisits->whereNull('discharged_at')->count(),
            'done' => $allVisits->whereNotNull('discharged_at')->count(),
        ];

        return view('clinics.reports.daily', compact('opdVisits', 'ipdVisits', 'summary'));
    }


    // ── Revenue Report ────────────────────────────────────────────────────────

    public function inventory(Request $request): View|Response
    {
        $clinicId = currentClinic()->id;

        $medicines = MedicineModel::where('clinic_id', $clinicId)
            ->when($request->filled('search'), fn($q) => $q->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('name_kh', 'like', '%' . $request->search . '%')
                ->orWhere('generic_name', 'like', '%' . $request->search . '%')
                ->orWhere('code', 'like', '%' . $request->search . '%')
            )
            ->when($request->filter === 'low', fn($q) => $q->whereColumn('stock', '<=', 'stock_alert')->where('stock', '>', 0))
            ->when($request->filter === 'out', fn($q) => $q->where('stock', 0))
            ->when($request->filter === 'ok', fn($q) => $q->whereColumn('stock', '>', 'stock_alert'))
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        // CSV export
        if ($request->get('export') === 'csv') {
            $all = MedicineModel::where('clinic_id', $clinicId)->orderBy('name')->get();
            $rows = [implode(',', ['Code', 'Name', 'Generic', 'Form', 'Strength', 'Unit', 'Stock', 'Alert', 'Price', 'Value'])];
            foreach ($all as $m) {
                $rows[] = implode(',', array_map(
                    fn($v) => '"' . str_replace('"', '""', $v ?? '') . '"',
                    [$m->code, $m->name, $m->generic_name, $m->form, $m->strength, $m->unit,
                        $m->stock, $m->stock_alert, $m->price, ($m->stock * $m->price)]
                ));
            }
            return response(implode("\n", $rows), 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="inventory-' . now()->format('Y-m-d') . '.csv"',
            ]);
        }

        return view('clinics.reports.inventory', compact('medicines'));
    }

    // ── Doctor Performance ────────────────────────────────────────────────────

    public function revenue(Request $request): View|Response
    {
        $clinicId = currentClinic()->id;
        $dateFrom = $request->date('date_from') ?? now()->startOfMonth();
        $dateTo = $request->date('date_to') ?? now()->endOfDay();

        $invoices = InvoiceModel::whereHas('visit', fn($q) => $q->whereHas('patient', fn($p) => $p->where('clinic_id', $clinicId))
        )
            ->whereBetween('invoice_date', [$dateFrom->startOfDay(), $dateTo->endOfDay()])
            ->when($request->payment_type, fn($q) => $q->where('payment_type', $request->payment_type))
            ->with('visit')
            ->latest('invoice_date')
            ->paginate(30)
            ->withQueryString();

        $all = InvoiceModel::whereHas('visit', fn($q) => $q->whereHas('patient', fn($p) => $p->where('clinic_id', $clinicId))
        )
            ->whereBetween('invoice_date', [$dateFrom->startOfDay(), $dateTo->endOfDay()])
            ->get(['total', 'payment_type']);

        $stats = [
            'total_revenue' => $all->sum('total'),
            'count' => $all->count(),
            'hef' => $all->where('payment_type', 'HEF')->sum('total'),
            'nssf' => $all->where('payment_type', 'NSSF')->sum('total'),
            'cash' => $all->where('payment_type', 'CASH')->sum('total'),
        ];

        $days = min(30, $dateFrom->copy()->diffInDays($dateTo) + 1);
        $dailyRevenue = collect(range(0, $days - 1))->map(function ($i) use ($dateFrom, $clinicId) {
            $day = $dateFrom->copy()->addDays($i);
            $rev = InvoiceModel::whereHas('visit', fn($q) => $q->whereHas('patient', fn($p) => $p->where('clinic_id', $clinicId))
            )
                ->whereDate('invoice_date', $day)->sum('total');
            return ['label' => $day->format('d/m'), 'revenue' => (int)$rev];
        });

        if ($request->export === 'csv') {
            $rows = [implode(',', ['Date', 'Invoice', 'Visit', 'Payment Type', 'Total KHR'])];
            InvoiceModel::whereHas('visit', fn($q) => $q->whereHas('patient', fn($p) => $p->where('clinic_id', $clinicId))
            )
                ->whereBetween('invoice_date', [$dateFrom->startOfDay(), $dateTo->endOfDay()])
                ->orderBy('invoice_date')->each(function ($inv) use (&$rows) {
                    $rows[] = implode(',', array_map(
                        fn($v) => '"' . str_replace('"', '""', $v ?? '') . '"',
                        [$inv->invoice_date?->format('Y-m-d'), $inv->code, $inv->visit_code, $inv->payment_type, $inv->total]
                    ));
                });
            return response(implode("\n", $rows), 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="revenue-' . now()->format('Y-m-d') . '.csv"',
            ]);
        }

        return view('clinics.reports.revenue', compact('invoices', 'stats', 'dailyRevenue'));
    }

    public function doctorPerformance(Request $request): View
    {
        $clinicId = currentClinic()->id;
        $dateFrom = $request->date('date_from') ?? now()->startOfMonth();
        $dateTo = $request->date('date_to') ?? now()->endOfDay();

        $doctors = DB::table('diagnoses')
            ->join('visits', 'diagnoses.visit_code', '=', 'visits.code')
            ->join('patients', 'visits.patient_code', '=', 'patients.code')
            ->where('patients.clinic_id', $clinicId)
            ->whereBetween('diagnoses.created_at', [$dateFrom->startOfDay(), $dateTo->endOfDay()])
            ->whereNotNull('diagnoses.diagnosed_by')
            ->whereNull('diagnoses.deleted_at')
            ->groupBy('diagnoses.diagnosed_by')
            ->selectRaw('diagnoses.diagnosed_by as doctor, COUNT(DISTINCT diagnoses.visit_code) as visits, COUNT(diagnoses.id) as diagnoses')
            ->orderByDesc('visits')->get();

        $prescribers = DB::table('prescriptions')
            ->join('visits', 'prescriptions.visit_code', '=', 'visits.code')
            ->join('patients', 'visits.patient_code', '=', 'patients.code')
            ->where('patients.clinic_id', $clinicId)
            ->whereBetween('prescriptions.created_at', [
                $dateFrom->copy()->startOfDay(),
                $dateTo->copy()->endOfDay()
            ])
            ->whereNotNull('prescriptions.prescribed_by')
            ->whereNull('prescriptions.deleted_at')
            ->groupBy('prescriptions.prescribed_by')
            ->selectRaw('prescriptions.prescribed_by as doctor, COUNT(prescriptions.id) as prescriptions')
            ->pluck('prescriptions', 'doctor');

        $performance = $doctors->map(fn($r) => [
            'doctor' => $r->doctor,
            'visits' => $r->visits,
            'diagnoses' => $r->diagnoses,
            'prescriptions' => $prescribers[$r->doctor] ?? 0,
        ]);

        return view('clinics.reports.doctor-performance', compact('performance', 'dateFrom', 'dateTo'));
    }
}
