<?php

/**
 * ══════════════════════════════════════════════════════════════════════════════
 * PERMISSION ↔ MENU MAPPING
 * ══════════════════════════════════════════════════════════════════════════════
 *
 * This file documents the complete mapping between:
 *   - Menu items (sidebar)
 *   - Permission slugs (stored in `permissions` table)
 *   - Route names (registered in routes/clinic.php)
 *   - Icons (Bootstrap Icons)
 *
 * ┌─────────────────────────┬────────────────────────┬──────────────────────────────┬─────────────────────────────┐
 * │ Menu Item               │ Permission Slug        │ Route Name                   │ Icon                        │
 * ├─────────────────────────┼────────────────────────┼──────────────────────────────┼─────────────────────────────┤
 * │                         │                        │                              │                             │
 * │ 🧾 OPERATIONS           │                        │                              │                             │
 * │ ├─ Dashboard            │ (none — all users)     │ dashboard                    │ bi-grid-1x2-fill            │
 * │ ├─ New Visit            │ visits.create          │ workflow.create               │ bi-plus-circle-fill         │
 * │ ├─ Patients             │ patients.view          │ patients.index               │ bi-people-fill              │
 * │ ├─ Visits               │ visits.view            │ visits.index                 │ bi-hospital-fill            │
 * │ ├─ Consultation         │ workflow.manage        │ workflow.index               │ bi-clipboard2-pulse-fill    │
 * │ ├─ Lab & Imaging        │ laboratory.view        │ laboratory.index             │ bi-droplet-fill             │
 * │ ├─ Prescription         │ pharmacy.view          │ prescriptions.index          │ bi-file-earmark-medical-fill│
 * │ ├─ Services             │ settings.view          │ settings.services            │ bi-heart-pulse-fill         │
 * │ └─ Referrals            │ referral.view          │ referrals.index              │ bi-arrow-left-right         │
 * │                         │                        │                              │                             │
 * │ 🏥 IPD                  │                        │                              │                             │
 * │ ├─ Admissions           │ visits.create          │ admissions.index             │ bi-door-open-fill           │
 * │ ├─ Bed Management       │ inventory.view         │ beds.index                   │ bi-grid-3x3-gap-fill        │
 * │ ├─ Ward / Room          │ inventory.manage       │ beds.index                   │ bi-building-fill            │
 * │ └─ Discharge            │ visits.create          │ discharge.index              │ bi-box-arrow-right          │
 * │                         │                        │                              │                             │
 * │ 💊 PHARMACY & INVENTORY │                        │                              │                             │
 * │ ├─ Pharmacy (Dispense)  │ pharmacy.dispense      │ pharmacy.index               │ bi-capsule                  │
 * │ ├─ Products / Medicines │ inventory.view         │ inventory.products           │ bi-box-seam-fill            │
 * │ ├─ Stock In             │ inventory.manage       │ inventory.stock-in           │ bi-box-arrow-in-down        │
 * │ ├─ Stock Out            │ inventory.manage       │ inventory.stock-out          │ bi-box-arrow-up             │
 * │ └─ Inventory Report     │ inventory.view         │ inventory.report             │ bi-clipboard-data-fill      │
 * │                         │                        │                              │                             │
 * │ 💰 BILLING              │                        │                              │                             │
 * │ ├─ Invoices             │ invoices.view          │ invoices.index               │ bi-receipt-cutoff           │
 * │ ├─ Payments             │ payments.manage        │ payments.index               │ bi-cash-stack               │
 * │ └─ Revenue              │ reports.view           │ reports.revenue              │ bi-graph-up-arrow           │
 * │                         │                        │                              │                             │
 * │ 👨‍💼 HR & REPORTS        │                        │                              │                             │
 * │ ├─ Employees            │ employees.view         │ employees.index              │ bi-person-badge-fill        │
 * │ ├─ Reports              │ reports.view           │ reports.visits               │ bi-bar-chart-line-fill      │
 * │ └─ Doctor Performance   │ reports.view           │ reports.doctor-performance   │ bi-award-fill               │
 * │                         │                        │                              │                             │
 * │ ⚙️ SYSTEM               │                        │                              │                             │
 * │ ├─ General Settings     │ settings.view          │ settings.general             │ bi-gear-fill                │
 * │ ├─ System Settings      │ settings.manage        │ settings.store-settings      │ bi-sliders2                 │
 * │ ├─ Localization         │ settings.view          │ settings.templates           │ bi-translate                │
 * │ ├─ Roles & Permissions  │ settings.manage        │ settings.roles               │ bi-shield-lock-fill         │
 * │ └─ Users                │ settings.manage        │ users.index                  │ bi-person-gear              │
 * └─────────────────────────┴────────────────────────┴──────────────────────────────┴─────────────────────────────┘
 *
 * COMPLETE PERMISSION SLUGS (28 total):
 *
 * patients:    patients.view, patients.create, patients.edit, patients.delete
 * visits:      visits.view, visits.create
 * workflow:    workflow.manage
 * pharmacy:    pharmacy.view, pharmacy.dispense, pharmacy.manage
 * laboratory:  laboratory.view, laboratory.manage, laboratory.verify
 * imagery:     imagery.view, imagery.manage
 * referral:    referral.view, referral.manage
 * inventory:   inventory.view, inventory.manage
 * billing:     invoices.view, invoices.manage, payments.manage
 * employees:   employees.view, employees.manage
 * reports:     reports.view
 * settings:    settings.view, settings.manage
 */

return [
    'version' => '1.0',
    'total_permissions' => 28,
    'total_menu_items' => 27,
    'total_groups' => 6,
];
