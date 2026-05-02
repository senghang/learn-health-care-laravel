<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

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
                $hasChildren = !empty($item['children']);

                // Normalize label keys (config uses label_en/label_km)
                $item['en']       = $item['label_en'] ?? ($item['en'] ?? '');
                $item['km']       = $item['label_km'] ?? ($item['km'] ?? '');
                $item['css']      = $item['css'] ?? '';
                $item['icon_css'] = $item['icon_css'] ?? '';
                $item['badge']    = $item['badge'] ?? null;
                // Parent entries with children are toggles; they can stay visible without a direct route.
                $item['visible']  = $this->canSee($user, $item['permission'] ?? null)
                    && ($hasChildren || $this->routeExists($item));
                $item['url']      = $this->resolveUrl($item);
                $item['active']   = $this->isActive($item);

                // Process children (submenu items)
                if ($hasChildren) {
                    $hasVisibleChild = false;
                    $hasActiveChild  = false;

                    foreach ($item['children'] as &$child) {
                        $child['en']      = $child['label_en'] ?? ($child['en'] ?? '');
                        $child['km']      = $child['label_km'] ?? ($child['km'] ?? '');
                        $child['badge']   = $child['badge'] ?? null;
                        $child['visible'] = $this->canSee($user, $child['permission'] ?? null)
                            && $this->routeExists($child);
                        $child['url']     = $this->resolveUrl($child);
                        $child['active']  = $this->isActive($child);

                        if ($child['visible']) $hasVisibleChild = true;
                        if ($child['active'])  $hasActiveChild  = true;
                    }

                    // Promote active state to parent if any child is active
                    if ($hasActiveChild) $item['active'] = true;

                    // Hide parent if it has children but none are visible
                    if (!$hasVisibleChild) $item['visible'] = false;
                }
            }
        }

        $this->dedupeVisibleRoutes($menu);

        return $menu;
    }

    /**
     * Remove duplicate visible routes in sidebar items.
     *
     * Rules:
     * - Parent items (with children) are not deduped by their own route because they are toggles.
     * - Visible leaf items are deduped globally by route name.
     * - Child items can opt out by setting ['allow_duplicate_route' => true] in config.
     */
    private function dedupeVisibleRoutes(array &$menu): void
    {
        $seen = [];

        foreach ($menu as &$group) {
            if (empty($group['items']) || !is_array($group['items'])) {
                continue;
            }

            foreach ($group['items'] as &$item) {
                if (($item['visible'] ?? false) !== true) {
                    continue;
                }

                $hasChildren = !empty($item['children']) && is_array($item['children']);

                if ($hasChildren) {
                    foreach ($item['children'] as &$child) {
                        if (($child['visible'] ?? false) !== true) {
                            continue;
                        }

                        $route = $child['route'] ?? null;
                        $allowDuplicate = (bool)($child['allow_duplicate_route'] ?? false);
                        if (!$route || $allowDuplicate) {
                            continue;
                        }

                        if (isset($seen[$route])) {
                            $child['visible'] = false;
                            continue;
                        }

                        $seen[$route] = true;
                    }

                    // Hide empty parents after child dedupe.
                    $hasVisibleChild = collect($item['children'])->contains(fn($c) => ($c['visible'] ?? false) === true);
                    if (!$hasVisibleChild) {
                        $item['visible'] = false;
                    }
                    continue;
                }

                $route = $item['route'] ?? null;
                $allowDuplicate = (bool)($item['allow_duplicate_route'] ?? false);
                if (!$route || $allowDuplicate) {
                    continue;
                }

                if (isset($seen[$route])) {
                    $item['visible'] = false;
                    continue;
                }

                $seen[$route] = true;
            }
        }
    }

    /**
     * Menu definition — loaded from config/sidebar.php.
     * Falls back to a minimal built-in definition if the config is missing.
     */
    private function definition(): array
    {
        $config = config('sidebar');

        if (!empty($config)) {
            return $config;
        }

        // ── Legacy built-in fallback (kept for safety) ────────────────────────
        return [
            [
                'key' => 'operations', 'label' => 'OPERATIONS', 'emoji' => '🧾',
                'items' => [
                    $this->item('dashboard',     'dashboard',           'bi-grid-1x2-fill',            'ផ្ទាំងគ្រប់គ្រង',   'Dashboard',      null),
                    $this->item('new_visit',     'workflow.create',     'bi-plus-circle-fill',         'ការចូលព្យាបាលថ្មី', 'New Visit',      'visits.create', 'sb-item--new-visit', 'sb-item-icon--accent'),
                    $this->item('patients',      'patients.index',      'bi-people-fill',              'អ្នកជំងឺ',          'Patients',       'patients.view', '', '', null, ['patients.*']),
                    $this->item('visits',        'visits.index',        'bi-hospital-fill',            'ការចូលព្យាបាល',     'Visits',         'visits.view',   '', '', 'visits_today', ['visits.*']),
                    $this->item('prescriptions', 'prescriptions.index', 'bi-file-earmark-medical-fill','វេជ្ជបញ្ជា',        'Prescriptions',  'pharmacy.view', '', '', null, ['prescriptions.*']),
                ],
            ],
            [
                'key' => 'billing', 'label' => 'BILLING', 'emoji' => '💰',
                'items' => [
                    $this->item('invoices', 'invoices.index', 'bi-receipt-cutoff', 'វិក្កយបត្រ',   'Invoices', 'invoices.view',   '', '', 'pending_invoices', ['invoices.*']),
                    $this->item('payments', 'payments.index', 'bi-cash-stack',     'ការបង់ប្រាក់', 'Payments', 'payments.manage', '', '', null, ['payments.*']),
                ],
            ],
            [
                'key' => 'system', 'label' => 'SYSTEM', 'emoji' => '⚙️',
                'items' => [
                    $this->item('settings', 'settings.general', 'bi-gear-fill',        'ការកំណត់',       'Settings',          'settings.view'),
                    $this->item('roles',    'settings.roles',   'bi-shield-lock-fill', 'តួនាទី & សិទ្ធិ', 'Roles & Permissions','settings.manage', '', '', null, ['settings.roles*']),
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
        if (!$this->routeExists($item)) {
            return '#';
        }

        try { return route($item['route']); } catch (\Throwable) { return '#'; }
    }

    private function isActive(array $item): bool
    {
        // Config uses 'active'; legacy definition uses 'active_routes'
        $patterns = $item['active'] ?? $item['active_routes'] ?? [$item['route'] ?? ''];
        foreach ((array) $patterns as $pattern) {
            if (request()->routeIs($pattern)) return true;
        }
        return false;
    }

    private function routeExists(array $item): bool
    {
        $route = $item['route'] ?? null;
        if (!$route) {
            return false;
        }

        return Route::has($route);
    }

    /**
     * Dynamic badge values — cached 60s.
     */
    public function badge(string $key): ?string
    {
        if (!app()->has('currentClinic')) return null;
        $cid = currentClinic()->id;

        return match ($key) {
            'visits_today'     => $this->cached("menu_visits_{$cid}",     fn() => \App\Models\VisitModel::whereHas('patient', fn($q) => $q->where('clinic_id', $cid))->whereDate('admitted_at', today())->count()),
            'pending_invoices' => $this->cached("menu_inv_{$cid}",        fn() => \App\Models\InvoiceModel::where('clinic_id', $cid)->whereIn('status', ['pending','partial'])->count()),
            'active_ipd'       => $this->cached("menu_ipd_{$cid}",        fn() => \App\Models\VisitModel::whereHas('patient', fn($q) => $q->where('clinic_id', $cid))->where('visit_type','IPD')->whereNull('discharged_at')->count()),
            'pending_labs'     => $this->cached("menu_labs_{$cid}",       fn() => \App\Models\LaboratoryModel::where('clinic_id', $cid)->where('status','requested')->count()),
            'pending_rx'       => $this->cached("menu_rx_{$cid}",         fn() => \App\Models\PrescriptionModel::where('clinic_id', $cid)->where(fn($q) => $q->whereNull('dispensed_status')->orWhere('dispensed_status','pending'))->count()),
            default            => null,
        };
    }

    private function cached(string $key, \Closure $fn): string
    {
        $val = Cache::remember($key, 60, $fn);
        return $val > 0 ? (string) $val : '0';
    }
}
