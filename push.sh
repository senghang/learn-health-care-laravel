#!/bin/bash
# MediFlow EMR — deploy script
# Run from inside your Laravel project root: bash push.sh

SOURCE=~/Downloads/outputs
echo "=== MediFlow deploy ==="

# ── Utils ────────────────────────────────────────────────────────────────────
mkdir -p app/Common/Constants app/Common/Utils app/Common/Helpers
cp "$SOURCE/app/Common/Constants/DateFormats.php"       app/Common/Constants/DateFormats.php
cp "$SOURCE/app/Common/Utils/CodeGenerator.php"         app/Common/Utils/CodeGenerator.php
cp "$SOURCE/app/Common/Utils/Currency.php"              app/Common/Utils/Currency.php
cp "$SOURCE/app/Common/Helpers/utils.php"               app/Common/Helpers/utils.php
cp "$SOURCE/app/Common/Helpers/workflow.php"            app/Common/Helpers/workflow.php

# ── Providers / Bootstrap ────────────────────────────────────────────────────
cp "$SOURCE/app/Providers/AppServiceProvider.php"       app/Providers/AppServiceProvider.php
cp "$SOURCE/bootstrap/app.php"                          bootstrap/app.php

# ── Middleware ───────────────────────────────────────────────────────────────
mkdir -p app/Http/Middleware
cp "$SOURCE/app/Http/Middleware/SetLocale.php"          app/Http/Middleware/SetLocale.php

# ── Services ─────────────────────────────────────────────────────────────────
mkdir -p app/Services
cp "$SOURCE/app/Services/PrintService.php"              app/Services/PrintService.php

# ── Controllers ──────────────────────────────────────────────────────────────
mkdir -p app/Http/Controllers/Admin
mkdir -p app/Http/Controllers/Clinics/Beds
mkdir -p app/Http/Controllers/Clinics/Print
mkdir -p app/Http/Controllers/Clinics/Settings
mkdir -p app/Http/Controllers/Clinics/Workflows/Steps

cp "$SOURCE/app/Http/Controllers/Admin/ClinicController.php"                      app/Http/Controllers/Admin/ClinicController.php
cp "$SOURCE/app/Http/Controllers/Clinics/AuthController.php"                      app/Http/Controllers/Clinics/AuthController.php
cp "$SOURCE/app/Http/Controllers/Clinics/PatientController.php"                   app/Http/Controllers/Clinics/PatientController.php
cp "$SOURCE/app/Http/Controllers/Clinics/ReportController.php"                    app/Http/Controllers/Clinics/ReportController.php
cp "$SOURCE/app/Http/Controllers/Clinics/VisitController.php"                     app/Http/Controllers/Clinics/VisitController.php
cp "$SOURCE/app/Http/Controllers/Clinics/Beds/BedController.php"                  app/Http/Controllers/Clinics/Beds/BedController.php
cp "$SOURCE/app/Http/Controllers/Clinics/Print/PrintController.php"               app/Http/Controllers/Clinics/Print/PrintController.php
cp "$SOURCE/app/Http/Controllers/Clinics/Settings/SettingController.php"          app/Http/Controllers/Clinics/Settings/SettingController.php
cp "$SOURCE/app/Http/Controllers/Clinics/Workflows/WorkflowController.php"        app/Http/Controllers/Clinics/Workflows/WorkflowController.php
cp "$SOURCE/app/Http/Controllers/Clinics/Workflows/WorkflowContext.php"           app/Http/Controllers/Clinics/Workflows/WorkflowContext.php
cp "$SOURCE/app/Http/Controllers/Clinics/Workflows/Steps/InvoiceStep.php"         app/Http/Controllers/Clinics/Workflows/Steps/InvoiceStep.php
cp "$SOURCE/app/Http/Controllers/Clinics/Workflows/Steps/PrescriptionStep.php"    app/Http/Controllers/Clinics/Workflows/Steps/PrescriptionStep.php
cp "$SOURCE/app/Http/Controllers/Clinics/Workflows/Steps/ReferralStep.php"        app/Http/Controllers/Clinics/Workflows/Steps/ReferralStep.php
cp "$SOURCE/app/Http/Controllers/Clinics/Workflows/Steps/RegistrationStep.php"    app/Http/Controllers/Clinics/Workflows/Steps/RegistrationStep.php
cp "$SOURCE/app/Http/Controllers/Clinics/Workflows/Steps/VitalsStep.php"          app/Http/Controllers/Clinics/Workflows/Steps/VitalsStep.php

# ── Models ───────────────────────────────────────────────────────────────────
mkdir -p app/Models/Base app/Models/Concerns
cp "$SOURCE/app/Models/Base/ClinicScope.php"            app/Models/Base/ClinicScope.php
cp "$SOURCE/app/Models/Concerns/HasTranslations.php"    app/Models/Concerns/HasTranslations.php
cp "$SOURCE/app/Models/Concerns/LogsActivity.php"       app/Models/Concerns/LogsActivity.php
cp "$SOURCE/app/Models/AuditLogModel.php"               app/Models/AuditLogModel.php
cp "$SOURCE/app/Models/BedModel.php"                    app/Models/BedModel.php
cp "$SOURCE/app/Models/ClinicModel.php"                 app/Models/ClinicModel.php
cp "$SOURCE/app/Models/ClinicSettingModel.php"          app/Models/ClinicSettingModel.php
cp "$SOURCE/app/Models/ImageryModel.php"                app/Models/ImageryModel.php
cp "$SOURCE/app/Models/ImageryResultModel.php"          app/Models/ImageryResultModel.php
cp "$SOURCE/app/Models/MedicineModel.php"               app/Models/MedicineModel.php
cp "$SOURCE/app/Models/PatientModel.php"                app/Models/PatientModel.php
cp "$SOURCE/app/Models/PrintTemplateModel.php"          app/Models/PrintTemplateModel.php
cp "$SOURCE/app/Models/RoomModel.php"                   app/Models/RoomModel.php
cp "$SOURCE/app/Models/ServiceModel.php"                app/Models/ServiceModel.php
cp "$SOURCE/app/Models/TranslationModel.php"            app/Models/TranslationModel.php
cp "$SOURCE/app/Models/VisitModel.php"                  app/Models/VisitModel.php
cp "$SOURCE/app/Models/VitalSignModel.php"              app/Models/VitalSignModel.php
cp "$SOURCE/app/Models/WardModel.php"                   app/Models/WardModel.php

# ── Routes ───────────────────────────────────────────────────────────────────
cp "$SOURCE/routes/clinic.php"                          routes/clinic.php

# ── Migrations ───────────────────────────────────────────────────────────────
cp "$SOURCE/database/migrations/2026_04_01_000001_patch_clinics_table.php"                  database/migrations/
cp "$SOURCE/database/migrations/2026_04_01_000002_create_clinic_settings_table.php"         database/migrations/
cp "$SOURCE/database/migrations/2026_04_01_000003_create_ward_room_bed_tables.php"          database/migrations/
cp "$SOURCE/database/migrations/2026_04_01_000004_patch_visits_encounters_patients.php"     database/migrations/
cp "$SOURCE/database/migrations/2026_04_01_000005_create_services_medicines_tables.php"     database/migrations/
cp "$SOURCE/database/migrations/2026_04_01_000006_create_print_templates_table.php"         database/migrations/
cp "$SOURCE/database/migrations/2026_04_01_000007_create_translations_table.php"            database/migrations/
cp "$SOURCE/database/migrations/2026_04_01_000008_create_imageries_table.php"               database/migrations/
cp "$SOURCE/database/migrations/2026_04_01_000009_create_audit_logs_table.php"              database/migrations/
cp "$SOURCE/database/migrations/2026_04_01_000010_patch_address_hierarchy.php"              database/migrations/

# ── Seeders ──────────────────────────────────────────────────────────────────
cp "$SOURCE/database/seeders/ProductionSeeder.php"      database/seeders/ProductionSeeder.php

# ── Language files ───────────────────────────────────────────────────────────
mkdir -p resources/lang/km resources/lang/en
cp "$SOURCE/lang/km/app.php"                            resources/lang/km/app.php
cp "$SOURCE/lang/en/app.php"                            resources/lang/en/app.php

# ── Views — layout ───────────────────────────────────────────────────────────
mkdir -p resources/views/clinics/layout
cp "$SOURCE/resources/views/clinics/layout/app.blade.php"        resources/views/clinics/layout/app.blade.php
cp "$SOURCE/resources/views/clinics/layout/sidebar.blade.php"    resources/views/clinics/layout/sidebar.blade.php
cp "$SOURCE/resources/views/clinics/layout/header.blade.php"     resources/views/clinics/layout/header.blade.php

# ── Views — patients ─────────────────────────────────────────────────────────
mkdir -p resources/views/clinics/patients
cp "$SOURCE/resources/views/clinics/patients/index.blade.php"    resources/views/clinics/patients/index.blade.php

# ── Views — beds ─────────────────────────────────────────────────────────────
mkdir -p resources/views/clinics/beds
cp "$SOURCE/resources/views/clinics/beds/index.blade.php"        resources/views/clinics/beds/index.blade.php
cp "$SOURCE/resources/views/clinics/beds/beds.blade.php"         resources/views/clinics/beds/beds.blade.php
cp "$SOURCE/resources/views/clinics/beds/ward-form.blade.php"    resources/views/clinics/beds/ward-form.blade.php

# ── Views — print templates ───────────────────────────────────────────────────
mkdir -p resources/views/clinics/print
cp "$SOURCE/resources/views/clinics/print/prescription.blade.php" resources/views/clinics/print/prescription.blade.php
cp "$SOURCE/resources/views/clinics/print/invoice.blade.php"      resources/views/clinics/print/invoice.blade.php

# ── Views — settings ─────────────────────────────────────────────────────────
mkdir -p resources/views/clinics/settings
cp "$SOURCE/resources/views/clinics/settings/general.blade.php"         resources/views/clinics/settings/general.blade.php
cp "$SOURCE/resources/views/clinics/settings/templates.blade.php"       resources/views/clinics/settings/templates.blade.php
cp "$SOURCE/resources/views/clinics/settings/template-form.blade.php"   resources/views/clinics/settings/template-form.blade.php
cp "$SOURCE/resources/views/clinics/settings/services.blade.php"        resources/views/clinics/settings/services.blade.php
cp "$SOURCE/resources/views/clinics/settings/medicines.blade.php"       resources/views/clinics/settings/medicines.blade.php
cp "$SOURCE/resources/views/clinics/settings/_subnav.blade.php"         resources/views/clinics/settings/_subnav.blade.php

# ── Views — reports ───────────────────────────────────────────────────────────
mkdir -p resources/views/clinics/reports
cp "$SOURCE/resources/views/clinics/reports/visits.blade.php"    resources/views/clinics/reports/visits.blade.php
cp "$SOURCE/resources/views/clinics/reports/daily.blade.php"     resources/views/clinics/reports/daily.blade.php

# ── Views — workflow ─────────────────────────────────────────────────────────
cp "$SOURCE/resources/views/clinics/workflow/show.blade.php"             resources/views/clinics/workflow/show.blade.php
cp "$SOURCE/resources/views/clinics/workflow/visit-summary.blade.php"    resources/views/clinics/workflow/visit-summary.blade.php

mkdir -p resources/views/clinics/workflow/steps
for step in _rx-row _dx-row registration triage vitals history labs diagnosis soap prescription referral invoice; do
  cp "$SOURCE/resources/views/clinics/workflow/steps/${step}.blade.php" \
     "resources/views/clinics/workflow/steps/${step}.blade.php"
done

# ── Views — visits + dashboard ───────────────────────────────────────────────
cp "$SOURCE/resources/views/clinics/visits/index.blade.php"                      resources/views/clinics/visits/index.blade.php
mkdir -p resources/views/clinics/dashboard
cp "$SOURCE/resources/views/clinics/dashboard/_recent-visits-rows.blade.php"     resources/views/clinics/dashboard/_recent-visits-rows.blade.php

# ── Views — components ───────────────────────────────────────────────────────
mkdir -p resources/views/components/step resources/views/components/form resources/views/components/workflow
cp "$SOURCE/resources/views/components/step/card.blade.php"           resources/views/components/step/card.blade.php
cp "$SOURCE/resources/views/components/step/note.blade.php"           resources/views/components/step/note.blade.php
cp "$SOURCE/resources/views/components/form/field.blade.php"          resources/views/components/form/field.blade.php
cp "$SOURCE/resources/views/components/form/input.blade.php"          resources/views/components/form/input.blade.php
cp "$SOURCE/resources/views/components/form/section.blade.php"        resources/views/components/form/section.blade.php
cp "$SOURCE/resources/views/components/form/select.blade.php"         resources/views/components/form/select.blade.php
cp "$SOURCE/resources/views/components/alert.blade.php"               resources/views/components/alert.blade.php
cp "$SOURCE/resources/views/components/flash.blade.php"               resources/views/components/flash.blade.php
cp "$SOURCE/resources/views/components/page-header.blade.php"         resources/views/components/page-header.blade.php
cp "$SOURCE/resources/views/components/progress-bar.blade.php"        resources/views/components/progress-bar.blade.php
cp "$SOURCE/resources/views/components/status-badge.blade.php"        resources/views/components/status-badge.blade.php
cp "$SOURCE/resources/views/components/workflow/action-bar.blade.php" resources/views/components/workflow/action-bar.blade.php

# ── Views — admin ────────────────────────────────────────────────────────────
mkdir -p resources/views/admin/clinic/list
cp "$SOURCE/resources/views/admin/clinic/list/clinic_add.blade.php"   resources/views/admin/clinic/list/clinic_add.blade.php
cp "$SOURCE/resources/views/admin/clinic/list/clinic_edit.blade.php"  resources/views/admin/clinic/list/clinic_edit.blade.php

# ── JS / CSS ──────────────────────────────────────────────────────────────────
cp "$SOURCE/resources/js/clinic.js"   resources/js/clinic.js

echo ""
echo "=== All files copied ==="

# ── composer.json autoload ────────────────────────────────────────────────────
HELPERS=(
    "app/Common/Helpers/helpers.php"
    "app/Common/Helpers/workflow.php"
    "app/Common/Helpers/utils.php"
)
for HELPER in "${HELPERS[@]}"; do
    if ! grep -q "\"$HELPER\"" composer.json 2>/dev/null; then
        echo "  MANUAL: add \"$HELPER\" to composer.json autoload.files"
    fi
done

# ── Artisan ───────────────────────────────────────────────────────────────────
composer dump-autoload --quiet && echo "  composer dump-autoload OK"
php artisan migrate --force           && echo "  migrate OK"
php artisan db:seed --class=ProductionSeeder --force && echo "  ProductionSeeder OK"
php artisan route:clear               && echo "  route:clear OK"
php artisan config:clear              && echo "  config:clear OK"
php artisan view:clear                && echo "  view:clear OK"
php artisan storage:link 2>/dev/null  && echo "  storage:link OK"

echo ""
echo "=== Deploy complete ==="
echo "    Visit: http://your-subdomain.localhost/"
