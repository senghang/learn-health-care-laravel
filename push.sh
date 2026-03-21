#!/bin/bash

# =============================================================================
# Simple file copy script for Mediflow fixes
# Run from inside your Laravel project root:
#   bash push.sh
# =============================================================================

SOURCE=~/Downloads/outputs

echo "Copying files..."

cp "$SOURCE/routes/clinic.php"                                                    routes/clinic.php
cp "$SOURCE/bootstrap/app.php"                                                    bootstrap/app.php

mkdir -p app/Http/Middleware
cp "$SOURCE/app/Http/Middleware/BindSubdomainParameter.php"                       app/Http/Middleware/BindSubdomainParameter.php

# Controllers
cp "$SOURCE/app/Http/Controllers/Admin/ClinicController.php"                     app/Http/Controllers/Admin/ClinicController.php
cp "$SOURCE/app/Http/Controllers/Clinics/AuthController.php"                     app/Http/Controllers/Clinics/AuthController.php
cp "$SOURCE/app/Http/Controllers/Clinics/ReportController.php"                   app/Http/Controllers/Clinics/ReportController.php
cp "$SOURCE/app/Http/Controllers/Clinics/VisitController.php"                    app/Http/Controllers/Clinics/VisitController.php
cp "$SOURCE/app/Http/Controllers/Clinics/Workflows/WorkflowController.php"       app/Http/Controllers/Clinics/Workflows/WorkflowController.php
cp "$SOURCE/app/Http/Controllers/Clinics/Workflows/WorkflowContext.php"          app/Http/Controllers/Clinics/Workflows/WorkflowContext.php
cp "$SOURCE/app/Http/Controllers/Clinics/Workflows/Steps/InvoiceStep.php"        app/Http/Controllers/Clinics/Workflows/Steps/InvoiceStep.php
cp "$SOURCE/app/Http/Controllers/Clinics/Workflows/Steps/PrescriptionStep.php"   app/Http/Controllers/Clinics/Workflows/Steps/PrescriptionStep.php
cp "$SOURCE/app/Http/Controllers/Clinics/Workflows/Steps/ReferralStep.php"       app/Http/Controllers/Clinics/Workflows/Steps/ReferralStep.php
cp "$SOURCE/app/Http/Controllers/Clinics/Workflows/Steps/RegistrationStep.php"   app/Http/Controllers/Clinics/Workflows/Steps/RegistrationStep.php
cp "$SOURCE/app/Http/Controllers/Clinics/Workflows/Steps/VitalsStep.php"         app/Http/Controllers/Clinics/Workflows/Steps/VitalsStep.php

# Models
mkdir -p app/Models/Base app/Common/Helpers
cp "$SOURCE/app/Models/PatientModel.php"                                          app/Models/PatientModel.php
cp "$SOURCE/app/Models/VisitModel.php"                                            app/Models/VisitModel.php
cp "$SOURCE/app/Models/VitalSignModel.php"                                        app/Models/VitalSignModel.php
cp "$SOURCE/app/Models/Base/ClinicScope.php"                                      app/Models/Base/ClinicScope.php

# Helpers
cp "$SOURCE/app/Common/Helpers/workflow.php"                                      app/Common/Helpers/workflow.php

# JS
cp "$SOURCE/resources/js/clinic.js"                                               resources/js/clinic.js

# Layout (new: app + sidebar + header)
mkdir -p resources/views/clinics/layout
cp "$SOURCE/resources/views/clinics/layout/app.blade.php"                         resources/views/clinics/layout/app.blade.php
cp "$SOURCE/resources/views/clinics/layout/sidebar.blade.php"                     resources/views/clinics/layout/sidebar.blade.php
cp "$SOURCE/resources/views/clinics/layout/header.blade.php"                      resources/views/clinics/layout/header.blade.php

# Blade components — step wrapper + form primitives (NEW)
mkdir -p resources/views/components/step resources/views/components/form
cp "$SOURCE/resources/views/components/step/card.blade.php"                       resources/views/components/step/card.blade.php
cp "$SOURCE/resources/views/components/step/note.blade.php"                       resources/views/components/step/note.blade.php
cp "$SOURCE/resources/views/components/form/field.blade.php"                      resources/views/components/form/field.blade.php
cp "$SOURCE/resources/views/components/form/input.blade.php"                      resources/views/components/form/input.blade.php
cp "$SOURCE/resources/views/components/form/section.blade.php"                    resources/views/components/form/section.blade.php
cp "$SOURCE/resources/views/components/form/select.blade.php"                     resources/views/components/form/select.blade.php

# Blade components
cp "$SOURCE/resources/views/components/alert.blade.php"                           resources/views/components/alert.blade.php
cp "$SOURCE/resources/views/components/flash.blade.php"                           resources/views/components/flash.blade.php
cp "$SOURCE/resources/views/components/page-header.blade.php"                     resources/views/components/page-header.blade.php
cp "$SOURCE/resources/views/components/progress-bar.blade.php"                    resources/views/components/progress-bar.blade.php
cp "$SOURCE/resources/views/components/status-badge.blade.php"                    resources/views/components/status-badge.blade.php
cp "$SOURCE/resources/views/components/form/input.blade.php"                      resources/views/components/form/input.blade.php
cp "$SOURCE/resources/views/components/form/select.blade.php"                     resources/views/components/form/select.blade.php
cp "$SOURCE/resources/views/components/form/section.blade.php"                    resources/views/components/form/section.blade.php
cp "$SOURCE/resources/views/components/workflow/action-bar.blade.php"             resources/views/components/workflow/action-bar.blade.php

# Reports (new)
mkdir -p resources/views/clinics/reports
cp "$SOURCE/resources/views/clinics/reports/visits.blade.php"                     resources/views/clinics/reports/visits.blade.php
cp "$SOURCE/resources/views/clinics/reports/daily.blade.php"                      resources/views/clinics/reports/daily.blade.php

# Admin views
cp "$SOURCE/resources/views/admin/clinic/list/clinic_add.blade.php"               resources/views/admin/clinic/list/clinic_add.blade.php
cp "$SOURCE/resources/views/admin/clinic/list/clinic_edit.blade.php"              resources/views/admin/clinic/list/clinic_edit.blade.php

# Workflow shell + sidebar
cp "$SOURCE/resources/views/clinics/workflow/show.blade.php"                      resources/views/clinics/workflow/show.blade.php
cp "$SOURCE/resources/views/clinics/workflow/visit-summary.blade.php"             resources/views/clinics/workflow/visit-summary.blade.php

# Visits & dashboard
cp "$SOURCE/resources/views/clinics/visits/index.blade.php"                       resources/views/clinics/visits/index.blade.php
mkdir -p resources/views/clinics/dashboard
cp "$SOURCE/resources/views/clinics/dashboard/_recent-visits-rows.blade.php"      resources/views/clinics/dashboard/_recent-visits-rows.blade.php

# All 10 workflow step blades + _rx-row partial
cp "$SOURCE/resources/views/clinics/workflow/steps/_rx-row.blade.php"             resources/views/clinics/workflow/steps/_rx-row.blade.php
cp "$SOURCE/resources/views/clinics/workflow/steps/_dx-row.blade.php"             resources/views/clinics/workflow/steps/_dx-row.blade.php
cp "$SOURCE/resources/views/clinics/workflow/steps/registration.blade.php"        resources/views/clinics/workflow/steps/registration.blade.php
cp "$SOURCE/resources/views/clinics/workflow/steps/triage.blade.php"              resources/views/clinics/workflow/steps/triage.blade.php
cp "$SOURCE/resources/views/clinics/workflow/steps/vitals.blade.php"              resources/views/clinics/workflow/steps/vitals.blade.php
cp "$SOURCE/resources/views/clinics/workflow/steps/history.blade.php"             resources/views/clinics/workflow/steps/history.blade.php
cp "$SOURCE/resources/views/clinics/workflow/steps/labs.blade.php"                resources/views/clinics/workflow/steps/labs.blade.php
cp "$SOURCE/resources/views/clinics/workflow/steps/diagnosis.blade.php"           resources/views/clinics/workflow/steps/diagnosis.blade.php
cp "$SOURCE/resources/views/clinics/workflow/steps/soap.blade.php"                resources/views/clinics/workflow/steps/soap.blade.php
cp "$SOURCE/resources/views/clinics/workflow/steps/prescription.blade.php"        resources/views/clinics/workflow/steps/prescription.blade.php
cp "$SOURCE/resources/views/clinics/workflow/steps/referral.blade.php"            resources/views/clinics/workflow/steps/referral.blade.php
cp "$SOURCE/resources/views/clinics/workflow/steps/invoice.blade.php"             resources/views/clinics/workflow/steps/invoice.blade.php

echo "All files copied."

# Register workflow helper in composer.json autoload if not already there
if ! grep -q '"workflow.php"' composer.json 2>/dev/null; then
  if grep -q 'helpers.php' composer.json 2>/dev/null; then
    sed -i 's|"app/Common/Helpers/helpers\.php"|"app/Common/Helpers/helpers.php",\n            "app/Common/Helpers/workflow.php"|' composer.json
    echo "Added workflow.php to composer.json autoload."
  else
    echo "MANUAL: add \"app/Common/Helpers/workflow.php\" to composer.json autoload.files"
  fi
fi

composer dump-autoload --quiet
php artisan route:clear
php artisan config:clear
php artisan view:clear

echo "Done."
