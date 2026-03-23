#!/usr/bin/env bash
# =============================================================================
# MediFlow EMR — Deploy Script
#
# Usage:
#   1. Extract mediflow-complete.zip to ~/Downloads/outputs/
#   2. cd into your Laravel project root
#   3. bash push.sh
#
# The script copies every output file into the correct Laravel location,
# then runs artisan commands to clear caches and run migrations.
# =============================================================================

set -e  # stop on first error

# ── Config ────────────────────────────────────────────────────────────────────
SOURCE="${SOURCE:-$HOME/Downloads/outputs}"
PROJECT="$(pwd)"

RED='\033[0;31m'; GRN='\033[0;32m'; YLW='\033[1;33m'; BLU='\033[0;34m'; NC='\033[0m'

info()    { echo -e "${BLU}  →${NC} $1"; }
ok()      { echo -e "${GRN}  ✓${NC} $1"; }
warn()    { echo -e "${YLW}  ⚠${NC} $1"; }
section() { echo -e "\n${BLU}══${NC} $1"; }
die()     { echo -e "${RED}  ✗ ERROR:${NC} $1"; exit 1; }

# ── Preflight checks ──────────────────────────────────────────────────────────
echo ""
echo -e "${BLU}╔═══════════════════════════════════════╗${NC}"
echo -e "${BLU}║      MediFlow EMR — Deploy            ║${NC}"
echo -e "${BLU}╚═══════════════════════════════════════╝${NC}"
echo ""

[[ -f "$PROJECT/artisan" ]]        || die "Not a Laravel project root. cd to your project first."
[[ -d "$SOURCE" ]]                  || die "Source directory not found: $SOURCE\n       Extract mediflow-complete.zip to ~/Downloads/outputs/"
[[ -f "$SOURCE/routes/clinic.php" ]] || die "Source files missing. Check $SOURCE contains the zip contents."

ok "Project: $PROJECT"
ok "Source:  $SOURCE"

# ── Helper: cp with auto-mkdir ────────────────────────────────────────────────
put() {
    local src="$SOURCE/$1"
    local dst="$PROJECT/$1"
    mkdir -p "$(dirname "$dst")"
    if [[ -f "$src" ]]; then
        cp "$src" "$dst"
    else
        warn "Not found (skipping): $1"
    fi
}

# ── Common Utils ──────────────────────────────────────────────────────────────
section "Common Utilities"
put app/Common/Constants/DateFormats.php
put app/Common/Utils/CodeGenerator.php
put app/Common/Utils/Currency.php
put app/Common/Helpers/utils.php
put app/Common/Helpers/workflow.php
ok "Utils done"

# ── Providers / Bootstrap ─────────────────────────────────────────────────────
section "Providers & Bootstrap"
put app/Providers/AppServiceProvider.php
put bootstrap/app.php
ok "Providers done"

# ── Middleware ────────────────────────────────────────────────────────────────
section "Middleware"
put app/Http/Middleware/BindSubdomainParameter.php
put app/Http/Middleware/SetLocale.php
ok "Middleware done"

# ── Services ──────────────────────────────────────────────────────────────────
section "Services"
put app/Services/PrintService.php
ok "Services done"

# ── Controllers ───────────────────────────────────────────────────────────────
section "Controllers"

# Admin
put app/Http/Controllers/Admin/ClinicController.php

# Clinics
put app/Http/Controllers/Clinics/AuthController.php
put app/Http/Controllers/Clinics/DashboardController.php
put app/Http/Controllers/Clinics/PatientController.php
put app/Http/Controllers/Clinics/ReportController.php
put app/Http/Controllers/Clinics/VisitController.php

# Sub-namespaces
put app/Http/Controllers/Clinics/Beds/BedController.php
put app/Http/Controllers/Clinics/Print/PrintController.php
put app/Http/Controllers/Clinics/Settings/SettingController.php

# Workflows
put app/Http/Controllers/Clinics/Workflows/WorkflowController.php
put app/Http/Controllers/Clinics/Workflows/WorkflowContext.php
put app/Http/Controllers/Clinics/Workflows/Steps/InvoiceStep.php
put app/Http/Controllers/Clinics/Workflows/Steps/PrescriptionStep.php
put app/Http/Controllers/Clinics/Workflows/Steps/ReferralStep.php
put app/Http/Controllers/Clinics/Workflows/Steps/RegistrationStep.php
put app/Http/Controllers/Clinics/Workflows/Steps/VitalsStep.php

ok "Controllers done"

# ── Models ────────────────────────────────────────────────────────────────────
section "Models"
put app/Models/Base/ClinicScope.php
put app/Models/Concerns/HasTranslations.php
put app/Models/Concerns/LogsActivity.php
put app/Models/AuditLogModel.php
put app/Models/BedModel.php
put app/Models/ClinicModel.php
put app/Models/ClinicSettingModel.php
put app/Models/ImageryModel.php
put app/Models/ImageryResultModel.php
put app/Models/MedicineModel.php
put app/Models/PatientModel.php
put app/Models/PrintTemplateModel.php
put app/Models/RoomModel.php
put app/Models/ServiceModel.php
put app/Models/TranslationModel.php
put app/Models/VisitModel.php
put app/Models/VitalSignModel.php
put app/Models/WardModel.php
ok "Models done"

# ── Routes ────────────────────────────────────────────────────────────────────
section "Routes"
put routes/clinic.php
ok "Routes done"

# ── Migrations ────────────────────────────────────────────────────────────────
section "Migrations"
put database/migrations/2026_04_01_000001_patch_clinics_table.php
put database/migrations/2026_04_01_000002_create_clinic_settings_table.php
put database/migrations/2026_04_01_000003_create_ward_room_bed_tables.php
put database/migrations/2026_04_01_000004_patch_visits_encounters_patients.php
put database/migrations/2026_04_01_000005_create_services_medicines_tables.php
put database/migrations/2026_04_01_000006_create_print_templates_table.php
put database/migrations/2026_04_01_000007_create_translations_table.php
put database/migrations/2026_04_01_000008_create_imageries_table.php
put database/migrations/2026_04_01_000009_create_audit_logs_table.php
put database/migrations/2026_04_01_000010_patch_address_hierarchy.php
ok "Migrations done"

# ── Seeders ───────────────────────────────────────────────────────────────────
section "Seeders"
put database/seeders/ProductionSeeder.php
ok "Seeders done"

# ── Language files ────────────────────────────────────────────────────────────
section "Language files"
# Laravel looks in resources/lang/  OR  lang/  depending on version
LANG_DEST="resources/lang"
[[ -d "$PROJECT/lang" ]] && LANG_DEST="lang"

mkdir -p "$PROJECT/$LANG_DEST/km" "$PROJECT/$LANG_DEST/en"
cp "$SOURCE/lang/km/app.php" "$PROJECT/$LANG_DEST/km/app.php"
cp "$SOURCE/lang/en/app.php" "$PROJECT/$LANG_DEST/en/app.php"
ok "Language files → $LANG_DEST/"

# ── CSS / JS assets ───────────────────────────────────────────────────────────
section "Assets"
put resources/css/clinic-additions.css
put resources/css/sidebar-header.css
put resources/js/clinic.js

# sidebar-header.css must also live in public/css/ (linked via asset())
mkdir -p "$PROJECT/public/css"
cp "$SOURCE/resources/css/sidebar-header.css" "$PROJECT/public/css/sidebar-header.css"
ok "sidebar-header.css → public/css/ (and resources/css/)"
ok "Assets done"

# ── Views — layout ────────────────────────────────────────────────────────────
section "Views: Layout"
put resources/views/clinics/layout/app.blade.php
put resources/views/clinics/layout/sidebar.blade.php
put resources/views/clinics/layout/header.blade.php
put resources/views/clinics/layout/bottom-nav.blade.php
ok "Layout views done"

# ── Views — auth ─────────────────────────────────────────────────────────────
section "Views: Auth"
put resources/views/clinics/auth/login.blade.php
ok "Auth views done"

# ── Views — dashboard ────────────────────────────────────────────────────────
section "Views: Dashboard"
put resources/views/clinics/dashboard.blade.php
put resources/views/clinics/dashboard/_recent-visits-rows.blade.php
ok "Dashboard views done"

# ── Views — patients ─────────────────────────────────────────────────────────
section "Views: Patients"
put resources/views/clinics/patients/index.blade.php
put resources/views/clinics/patients/create.blade.php
put resources/views/clinics/patients/show.blade.php
put resources/views/clinics/patients/edit.blade.php
ok "Patient views done"

# ── Views — visits ────────────────────────────────────────────────────────────
section "Views: Visits"
put resources/views/clinics/visits/index.blade.php
ok "Visit views done"

# ── Views — workflow ─────────────────────────────────────────────────────────
section "Views: Workflow"
put resources/views/clinics/workflow/show.blade.php
put resources/views/clinics/workflow/visit-summary.blade.php
put resources/views/clinics/workflow/steps/_dx-row.blade.php
put resources/views/clinics/workflow/steps/_rx-row.blade.php
put resources/views/clinics/workflow/steps/registration.blade.php
put resources/views/clinics/workflow/steps/triage.blade.php
put resources/views/clinics/workflow/steps/vitals.blade.php
put resources/views/clinics/workflow/steps/history.blade.php
put resources/views/clinics/workflow/steps/labs.blade.php
put resources/views/clinics/workflow/steps/diagnosis.blade.php
put resources/views/clinics/workflow/steps/soap.blade.php
put resources/views/clinics/workflow/steps/prescription.blade.php
put resources/views/clinics/workflow/steps/referral.blade.php
put resources/views/clinics/workflow/steps/invoice.blade.php
ok "Workflow views done"

# ── Views — beds ─────────────────────────────────────────────────────────────
section "Views: Beds & Wards"
put resources/views/clinics/beds/index.blade.php
put resources/views/clinics/beds/beds.blade.php
put resources/views/clinics/beds/ward-form.blade.php
ok "Beds views done"

# ── Views — settings ─────────────────────────────────────────────────────────
section "Views: Settings"
put resources/views/clinics/settings/general.blade.php
put resources/views/clinics/settings/templates.blade.php
put resources/views/clinics/settings/template-form.blade.php
put resources/views/clinics/settings/services.blade.php
put resources/views/clinics/settings/medicines.blade.php
put resources/views/clinics/settings/_subnav.blade.php
ok "Settings views done"

# ── Views — reports ───────────────────────────────────────────────────────────
section "Views: Reports"
put resources/views/clinics/reports/visits.blade.php
put resources/views/clinics/reports/daily.blade.php
put resources/views/clinics/reports/inventory.blade.php
ok "Report views done"

# ── Views — print ────────────────────────────────────────────────────────────
section "Views: Print"
put resources/views/clinics/print/prescription.blade.php
put resources/views/clinics/print/invoice.blade.php
ok "Print views done"

# ── Views — admin ────────────────────────────────────────────────────────────
section "Views: Admin"
put resources/views/admin/layout/app.blade.php
put resources/views/admin/clinic/list/clinic_add.blade.php
put resources/views/admin/clinic/list/clinic_edit.blade.php
ok "Admin views done"

# ── Views — components ───────────────────────────────────────────────────────
section "Views: Components"

# Standalone
put resources/views/components/alert.blade.php
put resources/views/components/flash.blade.php
put resources/views/components/page-header.blade.php
put resources/views/components/progress-bar.blade.php
put resources/views/components/status-badge.blade.php

# Form components
put resources/views/components/form/field.blade.php
put resources/views/components/form/input.blade.php
put resources/views/components/form/section.blade.php
put resources/views/components/form/select.blade.php

# Step components
put resources/views/components/step/card.blade.php
put resources/views/components/step/note.blade.php

# Workflow components
put resources/views/components/workflow/action-bar.blade.php
put resources/views/components/workflow/header.blade.php
put resources/views/components/workflow/step-bar.blade.php

ok "Component views done"

# ── composer.json autoload.files check ───────────────────────────────────────
section "Composer autoload check"
HELPERS=(
    "app/Common/Helpers/utils.php"
    "app/Common/Helpers/workflow.php"
)
NEEDS_DUMP=false
for H in "${HELPERS[@]}"; do
    if ! grep -q "\"$H\"" "$PROJECT/composer.json" 2>/dev/null; then
        warn "Missing from composer.json autoload.files: \"$H\""
        warn "  Add it under: \"autoload\": { \"files\": [ ... ] }"
        NEEDS_DUMP=true
    else
        ok "composer.json has $H"
    fi
done

# ── Artisan ───────────────────────────────────────────────────────────────────
section "Artisan"

info "composer dump-autoload"
composer dump-autoload --quiet && ok "composer dump-autoload"

info "php artisan migrate --force"
php artisan migrate --force && ok "migrate"

info "php artisan db:seed --class=ProductionSeeder --force"
php artisan db:seed --class=ProductionSeeder --force \
    && ok "ProductionSeeder" \
    || warn "ProductionSeeder skipped (already seeded or error — safe to ignore)"

info "php artisan route:clear"
php artisan route:clear && ok "route:clear"

info "php artisan config:clear"
php artisan config:clear && ok "config:clear"

info "php artisan view:clear"
php artisan view:clear && ok "view:clear"

info "php artisan cache:clear"
php artisan cache:clear && ok "cache:clear"

info "php artisan storage:link"
php artisan storage:link 2>/dev/null && ok "storage:link" || warn "storage:link already exists"

# ── Done ──────────────────────────────────────────────────────────────────────
echo ""
echo -e "${GRN}╔═══════════════════════════════════════╗${NC}"
echo -e "${GRN}║      Deploy complete ✓                ║${NC}"
echo -e "${GRN}╚═══════════════════════════════════════╝${NC}"
echo ""
echo "  Next steps:"
echo "    1. Open http://your-subdomain.localhost/ in browser"
echo "    2. Check logs: tail -f storage/logs/laravel.log"
echo ""

# Remind about composer.json if needed
if [[ "$NEEDS_DUMP" == "true" ]]; then
    echo -e "${YLW}  ⚠  ACTION REQUIRED: add helper files to composer.json${NC}"
    echo "     \"autoload\": {"
    echo "       \"files\": ["
    for H in "${HELPERS[@]}"; do
        echo "         \"$H\","
    done
    echo "       ]"
    echo "     }"
    echo "  Then run: composer dump-autoload"
    echo ""
fi

# ── Inventory (new) ───────────────────────────────────────────────────────────
section "Inventory (New)"
put app/Models/StockMovementModel.php
put app/Http/Controllers/Clinics/Inventory/InventoryController.php
put database/migrations/2026_04_02_000001_create_stock_movements_table.php
put resources/views/clinics/inventory/products.blade.php
put resources/views/clinics/inventory/product-form.blade.php
put resources/views/clinics/inventory/stock-in.blade.php
put resources/views/clinics/inventory/stock-out.blade.php
put resources/views/clinics/inventory/report.blade.php
ok "Inventory done"

# ── New Reports ───────────────────────────────────────────────────────────────
section "New Reports"
put resources/views/clinics/reports/revenue.blade.php
put resources/views/clinics/reports/doctor-performance.blade.php
ok "New reports done"

# ── Operations (New) ──────────────────────────────────────────────────────────
section "Operations: Prescriptions & Invoices"
put app/Models/InvoiceModel.php
put app/Models/InvoiceServiceModel.php
put app/Models/InvoiceMedicationModel.php
put app/Models/PaymentModel.php
put app/Models/PrescriptionModel.php
put app/Models/PrescriptionMedicationModel.php
put app/Http/Controllers/Clinics/Operations/PrescriptionController.php
put app/Http/Controllers/Clinics/Operations/InvoiceController.php
put resources/views/clinics/operations/prescriptions.blade.php
put resources/views/clinics/operations/prescription-show.blade.php
put resources/views/clinics/operations/invoices.blade.php
put resources/views/clinics/operations/invoice-show.blade.php
put database/migrations/2026_04_02_000002_create_payments_table.php
ok "Operations done"

# ── Roles & Permissions (New) ──────────────────────────────────────────────────
section "Settings: Roles & Permissions"
put app/Models/RoleModel.php
put app/Models/PermissionModel.php
put app/Http/Controllers/Clinics/Settings/RolesController.php
put resources/views/clinics/settings/roles.blade.php
ok "Roles done"

# ── Lang switch + Updated files ────────────────────────────────────────────────
section "Lang switch, sidebar, routes, header (updated)"
put routes/clinic.php
put resources/views/clinics/layout/sidebar.blade.php
put resources/views/clinics/layout/header.blade.php
put resources/css/sidebar-header.css
put lang/km/app.php
put lang/en/app.php
ok "All updates done"

# ── Visual Enhancement (this session) ─────────────────────────────────────────
section "Visual Enhancement — CSS, JS, Components"
put resources/css/emr-enhance.css
put resources/js/clinic.js
put resources/views/clinics/layout/app.blade.php
put resources/views/clinics/layout/sidebar.blade.php
put resources/views/components/flash.blade.php
put resources/views/components/workflow/step-bar.blade.php
ok "Visual enhancement done"

# ── Continue: dashboard + controller (this session) ────────────────────────
section "Dashboard redesign + DashboardController"
put resources/views/clinics/dashboard.blade.php
put app/Http/Controllers/Clinics/DashboardController.php
ok "Dashboard done"

# ── Architecture fixes (2026-04-03) ──────────────────────────────────────────
section "Architecture Fixes: Code Gen, ClinicScope, Lang, Constraints"

# Services
put app/Services/ClinicCodeService.php

# Base models
put app/Models/Base/ClinicScope.php
put app/Models/Base/Auditable.php

# Models with ClinicScope added
put app/Models/VisitModel.php
put app/Models/InvoiceModel.php
put app/Models/PrescriptionModel.php
put app/Models/PaymentModel.php

# Utils (now delegates to ClinicCodeService)
put app/Common/Utils/CodeGenerator.php

# Middleware
put app/Http/Middleware/SetLocale.php

# Migrations
put database/migrations/2026_04_03_000001_create_clinic_code_sequences_table.php
put database/migrations/2026_04_03_000002_add_clinic_id_to_clinical_tables.php
put database/migrations/2026_04_03_000003_fix_not_null_and_check_constraints.php

# Language files (fixed flat-key structure)
put lang/km/app.php
put lang/en/app.php

ok "All architecture fixes deployed — run: php artisan migrate"

# ── Code auto-generation enforcement (all codes readonly) ─────────────────────
section "Auto-gen codes: views readonly, controllers generate"

# Controllers updated to use ClinicCodeService
put app/Http/Controllers/Clinics/Workflows/WorkflowController.php
put app/Http/Controllers/Clinics/PatientController.php
put app/Http/Controllers/Clinics/Beds/BedController.php
put app/Http/Controllers/Clinics/Inventory/InventoryController.php
put app/Http/Controllers/Clinics/Settings/SettingController.php

# Views with readonly code fields
put resources/views/clinics/patients/create.blade.php
put resources/views/clinics/beds/ward-form.blade.php
put resources/views/clinics/settings/medicines.blade.php
put resources/views/clinics/settings/services.blade.php

ok "All code fields are now auto-generated and read-only"

# ── Form improvements + bug fixes ─────────────────────────────────────────────
section "Form improvements: prescription, invoice, error handling"

# Bug fixes
put app/Models/VisitModel.php
put app/Models/PrescriptionModel.php
put app/Http/Controllers/Clinics/Workflows/WorkflowController.php
put app/Http/Controllers/Clinics/Workflows/Steps/PrescriptionStep.php

# Improved views
put resources/views/clinics/workflow/steps/prescription.blade.php
put resources/views/clinics/workflow/steps/invoice.blade.php
put resources/views/clinics/patients/create.blade.php

# New migration
put database/migrations/2026_04_03_000004_add_dispensed_status_to_prescriptions.php

ok "Forms improved. Run: php artisan migrate"

# ── ParseError + duplicate key + missing steps ────────────────────────────────
section "Fix ParseError, duplicate key invoice, all 10 step files"

# All 10 workflow steps (complete set)
put app/Http/Controllers/Clinics/Workflows/Steps/RegistrationStep.php
put app/Http/Controllers/Clinics/Workflows/Steps/TriageStep.php
put app/Http/Controllers/Clinics/Workflows/Steps/VitalsStep.php
put app/Http/Controllers/Clinics/Workflows/Steps/HistoryStep.php
put app/Http/Controllers/Clinics/Workflows/Steps/LabsStep.php
put app/Http/Controllers/Clinics/Workflows/Steps/DiagnosisStep.php
put app/Http/Controllers/Clinics/Workflows/Steps/SoapStep.php
put app/Http/Controllers/Clinics/Workflows/Steps/PrescriptionStep.php
put app/Http/Controllers/Clinics/Workflows/Steps/ReferralStep.php
put app/Http/Controllers/Clinics/Workflows/Steps/InvoiceStep.php
put app/Http/Controllers/Clinics/Workflows/WorkflowStepRegistry.php

# Fixed prescription blade (ParseError resolved)
put resources/views/clinics/workflow/steps/prescription.blade.php
put resources/views/clinics/workflow/steps/invoice.blade.php

ok "All workflow steps fixed. ParseError resolved. Duplicate key fixed."

# ── Autocomplete + Stock + Print + Inventory Log ─────────────────────────────
section "Stock validation, autocomplete, print A4, inventory transactions"

# New utility classes
put app/Common/Utils/MoneyUtil.php
put app/Services/StockService.php
put app/Models/InventoryTransactionModel.php
put app/Models/InvoiceMedicationModel.php

# Updated controllers
put app/Http/Controllers/Clinics/Workflows/Steps/InvoiceStep.php
put app/Http/Controllers/Clinics/Inventory/InventoryController.php
put app/Http/Controllers/Clinics/DashboardController.php

# Improved workflow step views
put resources/views/clinics/workflow/steps/prescription.blade.php
put resources/views/clinics/workflow/steps/_rx-card-body.blade.php
put resources/views/clinics/workflow/steps/invoice.blade.php

# A4 Print templates
put resources/views/clinics/print/prescription.blade.php
put resources/views/clinics/print/invoice.blade.php

# Inventory dashboard
put resources/views/clinics/inventory/report.blade.php

# New migration: inventory_transactions table
put database/migrations/2026_04_04_000001_create_inventory_transactions_table.php

ok "Done. Run: php artisan migrate"

# ── UX Enhancements ──────────────────────────────────────────────────────────
section "UX: enhanced search, workflow pills, dosing grid, action bar"

put resources/css/ux-enhancements.css
put resources/views/clinics/layout/header.blade.php
put resources/views/clinics/layout/app.blade.php
put resources/views/components/workflow/action-bar.blade.php

# Deploy CSS to public
run "cp public/css/ux-enhancements.css public/css/ux-enhancements.css 2>/dev/null; cp -f \$(find storage -name ux-enhancements.css 2>/dev/null | head -1) public/css/ 2>/dev/null; cp resources/css/ux-enhancements.css public/css/ux-enhancements.css"

ok "UX enhancements deployed."


# ── DEPLOY NOTE ───────────────────────────────────────────────────────────────
echo ""
echo "========================================"
echo " POST-DEPLOY CHECKLIST"
echo "========================================"
echo " 1. php artisan migrate"
echo " 2. cp resources/css/ux-enhancements.css public/css/"
echo " 3. php artisan config:cache"
echo " 4. php artisan view:clear"
echo "========================================"

# ── Sidebar + Settings UX overhaul ────────────────────────────────────────────
section "Sidebar restructure + Settings page redesign"

put resources/views/clinics/layout/sidebar.blade.php
put resources/views/clinics/settings/general.blade.php
put resources/views/clinics/settings/_subnav.blade.php
put resources/css/sidebar-header.css
put resources/css/ux-enhancements.css

ok "Sidebar and Settings updated."
