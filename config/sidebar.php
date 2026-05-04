<?php

/**
 * Sidebar Navigation Configuration
 *
 * Structure:
 *   - Groups:  visual section dividers (key, label, emoji, items[])
 *   - Items:   navigation links with optional children[] for submenu
 *
 * Fields per item:
 *   key        — unique identifier (used for DOM IDs + localStorage state)
 *   label_en   — English label
 *   label_km   — Khmer label
 *   icon       — Bootstrap Icons class (bi-*)
 *   route      — Laravel route name (used as parent URL; parent becomes toggle when children present)
 *   permission — permission slug to check (null = always visible to authenticated users)
 *   active     — array of routeIs() patterns for active highlighting
 *   badge      — badge key resolved by MenuService::badge() (optional)
 *   css        — extra CSS classes on the <a> element (optional)
 *   icon_css   — extra CSS classes on the icon <span> (optional)
 *   children   — array of child items (turns item into a collapsible submenu parent)
 *
 * Child items share the same fields except icon (not shown in sub-items).
 */

 return [

    // ─────────────────────────────────────────────
    // MAIN
    // ─────────────────────────────────────────────
    [
        'key'   => 'main',
        'label' => 'MAIN',
        'emoji' => '📊',

        'items' => [

            [
                'key'        => 'dashboard',
                'label_en'   => 'Dashboard',
                'label_km'   => 'ផ្ទាំងគ្រប់គ្រង',
                'icon'       => 'bi-grid-1x2-fill',
                'route'      => 'dashboard',
                'permission' => null,
                'active'     => ['dashboard'],
            ],

            [
                'key'        => 'patients',
                'label_en'   => 'Patients',
                'label_km'   => 'អ្នកជំងឺ',
                'icon'       => 'bi-people-fill',
                'route'      => 'patients.index',
                'permission' => 'patients.view',
                'active'     => ['patients.*'],

                'children' => [

                    [
                        'label_en' => 'List',
                        'label_km' => 'បញ្ជី',
                        'route'    => 'patients.index',
                        'active'   => [
                            'patients.index',
                            'patients.show',
                            'patients.edit',
                            'patients.update',
                        ],
                    ],

                    [
                        'label_en'   => 'Register',
                        'label_km'   => 'ចុះឈ្មោះ',
                        'route'      => 'patients.create',
                        'permission' => 'patients.create',
                        'active'     => [
                            'patients.create',
                            'patients.store',
                        ],
                    ],
                ],
            ],
        ],
    ],

    // ─────────────────────────────────────────────
    // OPD
    // ─────────────────────────────────────────────
    [
        'key'   => 'opd',
        'label' => 'OPD',
        'emoji' => '🩺',

        'items' => [

            [
                'key'        => 'opd_nav',
                'label_en'   => 'OPD',
                'label_km'   => 'ផ្នែកអ្នកជំងឺក្រៅ',
                'icon'       => 'bi-activity',
                'route'      => 'visits.index',
                'permission' => 'visits.view',
                'active'     => [
                    'visits.*',
                    'workflow.*',
                    'prescriptions.*',
                ],

                'children' => [

                    [
                        'label_en'   => 'Visits',
                        'label_km'   => 'ការចូលព្យាបាល',
                        'route'      => 'visits.index',
                        'permission' => 'visits.view',
                        'active'     => ['visits.*'],
                        'badge'      => 'visits_today',
                    ],

                    [
                        'label_en'   => 'Triage',
                        'label_km'   => 'ការចូលព្យាបាលថ្មី',
                        'route'      => 'workflow.create',
                        'permission' => 'visits.create',
                        'active'     => ['workflow.create', 'workflow.store'],
                    ],

                    [
                        'label_en'   => 'Consultations',
                        'label_km'   => 'ពិគ្រោះព្យាបាល',
                        'route'      => 'workflow.index',
                        'permission' => 'workflow.manage',
                        'active'     => [
                            'workflow.index',
                            'workflow.show',
                            'workflow.step',
                        ],
                    ],

                    [
                        'label_en'   => 'Prescriptions',
                        'label_km'   => 'វេជ្ជបញ្ជា',
                        'route'      => 'prescriptions.index',
                        'permission' => 'pharmacy.view',
                        'active'     => ['prescriptions.*'],
                    ],
                ],
            ],
        ],
    ],

    // ─────────────────────────────────────────────
    // LAB & IMAGING
    // ─────────────────────────────────────────────
    [
        'key'   => 'lab',
        'label' => 'LAB & IMAGING',
        'emoji' => '🔬',

        'items' => [

            [
                'key'        => 'lab_nav',
                'label_en'   => 'Lab & Imaging',
                'label_km'   => 'មន្ទីរពិសោធន៍ & រូបភាព',
                'icon'       => 'bi-droplet-fill',
                'route'      => 'laboratory.index',
                'permission' => 'laboratory.view',
                'active'     => ['laboratory.*', 'imagery.*'],
                'badge'      => 'pending_labs',

                'children' => [

                    [
                        'label_en'   => 'Lab Orders',
                        'label_km'   => 'បញ្ជាសាំអ្នក',
                        'route'      => 'laboratory.create',
                        'permission' => 'laboratory.manage',
                        'active'     => ['laboratory.create'],
                    ],

                    [
                        'label_en'   => 'Lab Results',
                        'label_km'   => 'លទ្ធផលមន្ទីរ',
                        'route'      => 'laboratory.index',
                        'permission' => 'laboratory.view',
                        'active'     => ['laboratory.index', 'laboratory.show'],
                    ],

                    [
                        'label_en'   => 'Imaging Orders',
                        'label_km'   => 'បញ្ជារូបភាព',
                        'route'      => 'imagery.create',
                        'permission' => 'imagery.manage',
                        'active'     => ['imagery.create'],
                    ],

                    [
                        'label_en'   => 'Imaging Results',
                        'label_km'   => 'លទ្ធផលរូបភាព',
                        'route'      => 'imagery.index',
                        'permission' => 'imagery.view',
                        'active'     => ['imagery.index', 'imagery.show'],
                    ],
                ],
            ],
        ],
    ],

    // ─────────────────────────────────────────────
    // BILLING
    // ─────────────────────────────────────────────
    [
        'key'   => 'billing',
        'label' => 'BILLING',
        'emoji' => '💰',

        'items' => [

            [
                'key'        => 'billing_nav',
                'label_en'   => 'Billing',
                'label_km'   => 'វិក្កយបត្រ & ប្រាក់',
                'icon'       => 'bi-cash-coin',
                'route'      => 'invoices.index',
                'permission' => 'invoices.view',
                'active'     => ['invoices.*', 'payments.*'],
                'badge'      => 'pending_invoices',

                'children' => [

                    [
                        'label_en'   => 'Invoices',
                        'label_km'   => 'វិក្កយបត្រ',
                        'route'      => 'invoices.index',
                        'permission' => 'invoices.view',
                        'active'     => ['invoices.*'],
                    ],

                    [
                        'label_en'   => 'Payments',
                        'label_km'   => 'ការបង់ប្រាក់',
                        'route'      => 'payments.index',
                        'permission' => 'payments.manage',
                        'active'     => ['payments.*'],
                    ],
                ],
            ],
        ],
    ],

    // ─────────────────────────────────────────────
    // PHARMACY
    // ─────────────────────────────────────────────
    [
        'key'   => 'pharmacy',
        'label' => 'PHARMACY',
        'emoji' => '💊',

        'items' => [

            [
                'key'        => 'pharmacy_nav',
                'label_en'   => 'Pharmacy',
                'label_km'   => 'ឱសថស្ថាន',
                'icon'       => 'bi-capsule',
                'route'      => 'pharmacy.index',
                'permission' => 'pharmacy.view',
                'active'     => [
                    'pharmacy.*',
                    'inventory.products',
                    'inventory.product.*',
                ],
                'badge' => 'pending_rx',

                'children' => [

                    [
                        'label_en'   => 'Medicines',
                        'label_km'   => 'ថ្នាំ / ផលិតផល',
                        'route'      => 'inventory.products',
                        'permission' => 'inventory.view',
                        'active'     => [
                            'inventory.products',
                            'inventory.product.*',
                        ],
                    ],

                    [
                        'label_en'   => 'Dispensing',
                        'label_km'   => 'ការចែកថ្នាំ',
                        'route'      => 'pharmacy.index',
                        'permission' => 'pharmacy.dispense',
                        'active'     => ['pharmacy.*'],
                    ],
                ],
            ],
        ],
    ],

    // ─────────────────────────────────────────────
    // INVENTORY
    // ─────────────────────────────────────────────
    [
        'key'   => 'inventory',
        'label' => 'INVENTORY',
        'emoji' => '📦',

        'items' => [

            [
                'key'        => 'inventory_nav',
                'label_en'   => 'Inventory',
                'label_km'   => 'ស្តុក / ឃ្លាំង',
                'icon'       => 'bi-box-seam-fill',
                'route'      => 'inventory.movements',
                'permission' => 'inventory.view',
                'active'     => [
                    'inventory.movements',
                    'inventory.report',
                    'inventory.adjustment',
                    'inventory.stock-in',
                    'inventory.stock-out',
                ],

                'children' => [

                    [
                        'label_en'   => 'Stock Movements',
                        'label_km'   => 'ចលនាស្តុក',
                        'route'      => 'inventory.movements',
                        'permission' => 'inventory.view',
                        'active'     => [
                            'inventory.movements',
                            'inventory.stock-in',
                            'inventory.stock-out',
                            'inventory.adjustment',
                        ],
                    ],

                    [
                        'label_en'   => 'Stock Balance',
                        'label_km'   => 'សមតុល្យស្តុក',
                        'route'      => 'inventory.report',
                        'permission' => 'inventory.view',
                        'active'     => ['inventory.report'],
                    ],
                ],
            ],
        ],
    ],

    // ─────────────────────────────────────────────
    // IPD
    // ─────────────────────────────────────────────
    [
        'key'   => 'ipd',
        'label' => 'IPD',
        'emoji' => '🏥',

        'items' => [

            [
                'key'        => 'ipd_nav',
                'label_en'   => 'IPD',
                'label_km'   => 'អ្នកជំងឺក្នុង',
                'icon'       => 'bi-door-open-fill',
                'route'      => 'admissions.index',
                'permission' => 'visits.create',
                'active'     => ['admissions.*', 'discharge.*', 'beds.*'],
                'badge'      => 'active_ipd',

                'children' => [

                    [
                        'label_en'   => 'Admissions',
                        'label_km'   => 'ការចូលសម្រាក',
                        'route'      => 'admissions.index',
                        'permission' => 'visits.create',
                        'active'     => ['admissions.*'],
                    ],

                    [
                        'label_en'   => 'Wards',
                        'label_km'   => 'វ៉ត / បន្ទប់',
                        'route'      => 'beds.index',
                        'permission' => 'inventory.view',
                        'active'     => ['beds.ward.create', 'beds.room.*'],
                    ],

                    [
                        'label_en'   => 'Beds',
                        'label_km'   => 'គ្រែ',
                        'route'      => 'beds.index',
                        'permission' => 'inventory.view',
                        'active'     => ['beds.index', 'beds.ward', 'beds.bed.*', 'beds.available'],
                    ],

                    [
                        'label_en'   => 'Treatments',
                        'label_km'   => 'ការព្យាបាល',
                        'route'      => 'admissions.index',
                        'permission' => 'visits.create',
                        'active'     => ['admissions.treatment', 'admissions.medication'],
                    ],
                ],
            ],
        ],
    ],

    // ─────────────────────────────────────────────
    // EMPLOYEE
    // ─────────────────────────────────────────────
    [
        'key'   => 'employee',
        'label' => 'EMPLOYEE',
        'emoji' => '👨‍💼',

        'items' => [

            [
                'key'        => 'employee_nav',
                'label_en'   => 'Employee',
                'label_km'   => 'បុគ្គលិក',
                'icon'       => 'bi-person-badge-fill',
                'route'      => 'employees.index',
                'permission' => 'employees.view',
                'active'     => ['employees.*', 'users.*'],

                'children' => [

                    [
                        'label_en'   => 'Employees',
                        'label_km'   => 'បុគ្គលិក',
                        'route'      => 'employees.index',
                        'permission' => 'employees.view',
                        'active'     => ['employees.*'],
                    ],

                    [
                        'label_en'   => 'Users',
                        'label_km'   => 'អ្នកប្រើប្រាស់',
                        'route'      => 'users.index',
                        'permission' => 'settings.manage',
                        'active'     => ['users.*'],
                    ],
                ],
            ],
        ],
    ],

    // ─────────────────────────────────────────────
    // SYSTEM
    // ─────────────────────────────────────────────
    [
        'key'   => 'system',
        'label' => 'SYSTEM',
        'emoji' => '⚙️',

        'items' => [

            [
                'key'        => 'roles_nav',
                'label_en'   => 'Roles & Permissions',
                'label_km'   => 'តួនាទី & សិទ្ធិ',
                'icon'       => 'bi-shield-lock-fill',
                'route'      => 'settings.roles',
                'permission' => 'settings.manage',
                'active'     => [
                    'settings.roles',
                    'settings.roles.permissions',
                    'settings.roles.seed-permissions',
                ],

                'children' => [

                    [
                        'label_en'   => 'Roles',
                        'label_km'   => 'តួនាទី',
                        'route'      => 'settings.roles',
                        'permission' => 'settings.manage',
                        'active'     => [
                            'settings.roles',
                            'settings.roles.update',
                            'settings.roles.destroy',
                        ],
                    ],

                    [
                        'label_en'   => 'Permissions',
                        'label_km'   => 'សិទ្ធិ',
                        'route'      => 'settings.roles',
                        'permission' => 'settings.manage',
                        'active'     => [
                            'settings.roles.permissions',
                            'settings.roles.seed-permissions',
                        ],
                    ],
                ],
            ],

            [
                'key'        => 'settings',
                'label_en'   => 'Settings',
                'label_km'   => 'ការកំណត់',
                'icon'       => 'bi-gear-fill',
                'route'      => 'settings.general',
                'permission' => 'settings.view',
                'active'     => [
                    'settings.general',
                    'settings.services',
                    'settings.medicines',
                    'settings.store-settings',
                    'settings.store-settings.*',
                ],
            ],
        ],
    ],
];
