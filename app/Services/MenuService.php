<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * MenuService — Dynamic role-based sidebar menu.
 *
 * Menu structure matches the ERD diagram:
 *   OPERATIONS → Patient-centric OPD workflow
 *   IPD        → Inpatient admission/discharge cycle
 *   PHARMACY   → Dispensing + inventory management
 *   BILLING    → Invoice/payment/revenue tracking
 *   HR         → Employee + performance reporting
 *   SYSTEM     → Settings, RBAC, localization
 */
class MenuService
{
    public function build(): array
    {
        $user = Auth::user();
        $menu = $this->definition();

        foreach ($menu as &$group) {
            foreach ($group['items'] as &$item) {
                $item['visible'] = $this->canSee($user, $item['permission'] ?? null);
                $item['url'] = $this->resolveUrl($item);
                $item['active'] = $this->isActive($item);
            }
        }

        return $menu;
    }

    private function definition(): array
    {
        return [
            // ── 🧾 OPERATIONS ─────────────────────────────────────────────────
            [
                'key' => 'operations', 'label' => 'OPERATIONS', 'emoji' => '🧾',
                'items' => [
                    $this->item('dashboard',     'dashboard',           'bi-grid-1x2-fill',            'ផ្ទាំងគ្រប់គ្រង',          'Dashboard',           null),
                    $this->item('new_visit',     'workflow.create',     'bi-plus-circle-fill',         'ការចូលព្យាបាលថ្មី',       'New Visit',           'visits.create',     'sb-item--new-visit', 'sb-item-icon--accent'),
                    $this->item('patients',      'patients.index',      'bi-people-fill',              'អ្នកជំងឺ',                'Patients',            'patients.view',     '', '', null, ['patients.*']),
                    $this->item('visits',        'visits.index',        'bi-hospital-fill',            'ការចូលព្យាបាល',           'Visits',              'visits.view',       '', '', 'visits_today', ['visits.*']),
                    $this->item('consultation',  'workflow.index',      'bi-clipboard2-pulse-fill',    'ពិគ្រោះព្យាបាល',           'Consultation',        'workflow.manage',   '', '', null, ['workflow.index', 'workflow.show', 'workflow.step']),
                    $this->item('lab_imaging',   'laboratory.index',    'bi-droplet-fill',             'មន្ទីរពិសោធន៍ & រូបភាព',  'Lab & Imaging',       'laboratory.view',   '', '', 'pending_labs', ['laboratory.*', 'imagery.*']),
                    $this->item('prescriptions', 'prescriptions.index', 'bi-file-earmark-medical-fill','វេជ្ជបញ្ជា',               'Prescription',        'pharmacy.view',     '', '', null, ['prescriptions.*']),
                    $this->item('services',      'settings.services',   'bi-heart-pulse-fill',         'សេវា',                    'Services',            'settings.view'),
                    $this->item('referrals',     'referrals.index',     'bi-arrow-left-right',         'បញ្ជូន',                  'Referrals',           'referral.view',     '', '', null, ['referrals.*']),
                ],
            ],

            // ── 🏥 IPD ────────────────────────────────────────────────────────
            [
                'key' => 'ipd', 'label' => 'IPD', 'emoji' => '🏥',
                'items' => [
                    $this->item('admissions', 'admissions.index', 'bi-door-open-fill',    'ការចូលសម្រាក',  'Admissions',     'visits.create',    '', '', 'active_ipd', ['admissions.*']),
                    $this->item('beds',       'beds.index',       'bi-grid-3x3-gap-fill', 'គ្រែ',          'Bed Management', 'inventory.view',   '', '', null, ['beds.ward', 'beds.bed.*']),
                    $this->item('wards',      'beds.index',       'bi-building-fill',     'វ៉ត / បន្ទប់',  'Ward / Room',    'inventory.manage', '', '', null, ['beds.index', 'beds.ward.create', 'beds.room.*']),
                    $this->item('discharge',  'discharge.index',  'bi-box-arrow-right',   'ចាកចេញ',       'Discharge',      'visits.create',    '', '', null, ['discharge.*']),
                ],
            ],

            // ── 💊 PHARMACY & INVENTORY ───────────────────────────────────────
            [
                'key' => 'pharmacy_inventory', 'label' => 'PHARMACY & INVENTORY', 'emoji' => '💊',
                'items' => [
                    $this->item('pharmacy',      'pharmacy.index',      'bi-capsule',              'ឱសថស្ថាន',          'Pharmacy (Dispense)', 'pharmacy.dispense', '', '', 'pending_rx', ['pharmacy.*']),
                    $this->item('products',      'inventory.products',  'bi-box-seam-fill',        'ថ្នាំ / ផលិតផល',     'Products / Medicines','inventory.view',    '', '', null, ['inventory.products', 'inventory.product.*']),
                    $this->item('stock_in',      'inventory.stock-in',  'bi-box-arrow-in-down',    'ស្តុកចូល',           'Stock In',            'inventory.manage',  '', 'sb-item-icon--green'),
                    $this->item('stock_out',     'inventory.stock-out', 'bi-box-arrow-up',         'ស្តុកចេញ',           'Stock Out',           'inventory.manage',  '', 'sb-item-icon--red'),
                    $this->item('inv_report',    'inventory.report',    'bi-clipboard-data-fill',  'របាយការណ៍ស្តុក',     'Inventory Report',    'inventory.view'),
                ],
            ],

            // ── 💰 BILLING ────────────────────────────────────────────────────
            [
                'key' => 'billing', 'label' => 'BILLING', 'emoji' => '💰',
                'items' => [
                    $this->item('invoices', 'invoices.index',  'bi-receipt-cutoff',  'វិក្កយបត្រ',   'Invoices', 'invoices.view',  '', '', 'pending_invoices', ['invoices.*']),
                    $this->item('payments', 'payments.index',  'bi-cash-stack',      'ការបង់ប្រាក់', 'Payments', 'payments.manage', '', '', null, ['payments.*']),
                    $this->item('revenue',  'reports.revenue', 'bi-graph-up-arrow',  'ប្រាក់ចំណូល',  'Revenue',  'reports.view'),
                ],
            ],

            // ── 👨‍💼 HR & REPORTS ──────────────────────────────────────────────
            [
                'key' => 'hr_reports', 'label' => 'HR & REPORTS', 'emoji' => '👨‍💼',
                'items' => [
                    $this->item('employees',  'employees.index',            'bi-person-badge-fill',   'បុគ្គលិក',             'Employees',          'employees.view', '', '', null, ['employees.*']),
                    $this->item('reports',    'reports.visits',             'bi-bar-chart-line-fill', 'របាយការណ៍',            'Reports',            'reports.view',   '', '', null, ['reports.visits', 'reports.daily']),
                    $this->item('dr_perf',    'reports.doctor-performance', 'bi-award-fill',          'សមត្ថភាពវេជ្ជបណ្ឌិត', 'Doctor Performance', 'reports.view'),
                ],
            ],

            // ── ⚙️ SYSTEM ────────────────────────────────────────────────────
            [
                'key' => 'system', 'label' => 'SYSTEM', 'emoji' => '⚙️',
                'items' => [
                    $this->item('general_settings', 'settings.general',        'bi-gear-fill',        'ការកំណត់ទូទៅ',      'General Settings',          'settings.view'),
                    $this->item('system_settings',  'settings.store-settings', 'bi-sliders2',         'ការកំណត់ប្រព័ន្ធ',   'System Settings (Key/Value)','settings.manage', '', '', null, ['settings.store-settings*']),
                    $this->item('localization',      'settings.templates',      'bi-translate',        'ភាសា',              'Localization (EN/KM)',       'settings.view'),
                    $this->item('roles',             'settings.roles',          'bi-shield-lock-fill', 'តួនាទី & សិទ្ធិ',    'Roles & Permissions',       'settings.manage', '', '', null, ['settings.roles*']),
                    $this->item('users',             'users.index',             'bi-person-gear',      'អ្នកប្រើប្រាស់',     'Users',                     'settings.manage', '', '', null, ['users.*']),
                ],
            ],
        ];
    }

    /**
     * Build a menu item array.
     */
    private function item(
        string $key, string $route, string $icon, string $km, string $en,
        ?string $permission = null, string $css = '', string $iconCss = '',
        ?string $badge = null, ?array $activeRoutes = null
    ): array {
        return [
            'key'           => $key,
            'route'         => $route,
            'icon'          => $icon,
            'km'            => $km,
            'en'            => $en,
            'permission'    => $permission,
            'css'           => $css,
            'icon_css'      => $iconCss,
            'badge'         => $badge,
            'active_routes' => $activeRoutes ?? [$route],
        ];
    }

    private function canSee($user, ?string $permission): bool
    {
        if ($permission === null) return true;
        if (!$user) return false;
        return $user->hasPermission($permission);
    }

    private function resolveUrl(array $item): string
    {
        try { return route($item['route']); } catch (\Throwable) { return '#'; }
    }

    private function isActive(array $item): bool
    {
        foreach ($item['active_routes'] as $pattern) {
            if (request()->routeIs($pattern)) return true;
        }
        return false;
    }

    /**
     * Dynamic badge values — cached 60s.
     */
    public function badge(string $key): ?string
    {
        if (!app()->has('currentClinic')) return null;
        $cid = currentClinic()->id;

        return match ($key) {
            'visits_today'     => $this->cached("menu_visits_{$cid}",     fn() => \App\Models\VisitModel::whereDate('admitted_at', today())->count()),
            'pending_invoices' => $this->cached("menu_inv_{$cid}",        fn() => \App\Models\InvoiceModel::whereIn('status', ['pending','partial'])->count()),
            'active_ipd'       => $this->cached("menu_ipd_{$cid}",        fn() => \App\Models\VisitModel::where('visit_type','IPD')->whereNull('discharged_at')->count()),
            'pending_labs'     => $this->cached("menu_labs_{$cid}",        fn() => \App\Models\LaboratoryModel::where('status','requested')->count()),
            'pending_rx'       => $this->cached("menu_rx_{$cid}",         fn() => \App\Models\PrescriptionModel::whereNull('dispensed_status')->orWhere('dispensed_status','pending')->count()),
            default            => null,
        };
    }

    private function cached(string $key, \Closure $fn): string
    {
        $val = Cache::remember($key, 60, $fn);
        return $val > 0 ? (string) $val : '0';
    }
}
