#!/bin/bash

# =============================================================================
# Run this from inside the extracted mediflow-fixes folder:
#   cd /c/Users/YourName/Downloads/mediflow-fixes
#   bash push.sh
# =============================================================================

# ── EDIT THESE TWO PATHS ─────────────────────────────────────────────────────
PROJECT="/d/learn/learn-health-care-laravel"   # ← your Laravel root
SOURCE="$(cd "$(dirname "$0")" && pwd)"               # auto: folder this script is in
# ─────────────────────────────────────────────────────────────────────────────

echo "Project : $PROJECT"
echo "Source  : $SOURCE"
echo ""

cd "$PROJECT" || { echo "ERROR: project not found at $PROJECT"; exit 1; }

# 1. Switch to dev branch
git checkout dev
git pull origin dev

# 2. Copy all files
echo ""
echo "--- Copying files ---"

cp "$SOURCE/routes/clinic.php"                                                                        routes/clinic.php
cp "$SOURCE/bootstrap/app.php"                                                                        bootstrap/app.php
cp "$SOURCE/app/Http/Middleware/BindSubdomainParameter.php"                                           app/Http/Middleware/BindSubdomainParameter.php
cp "$SOURCE/app/Http/Controllers/Clinics/AuthController.php"                                         app/Http/Controllers/Clinics/AuthController.php
cp "$SOURCE/app/Http/Controllers/Clinics/VisitController.php"                                        app/Http/Controllers/Clinics/VisitController.php
cp "$SOURCE/app/Http/Controllers/Clinics/Workflows/WorkflowController.php"                           app/Http/Controllers/Clinics/Workflows/WorkflowController.php
cp "$SOURCE/app/Http/Controllers/Clinics/Workflows/WorkflowContext.php"                              app/Http/Controllers/Clinics/Workflows/WorkflowContext.php
cp "$SOURCE/app/Http/Controllers/Clinics/Workflows/Steps/RegistrationStep.php"                      app/Http/Controllers/Clinics/Workflows/Steps/RegistrationStep.php
cp "$SOURCE/app/Http/Controllers/Clinics/Workflows/Steps/VitalsStep.php"                            app/Http/Controllers/Clinics/Workflows/Steps/VitalsStep.php
cp "$SOURCE/app/Http/Controllers/Admin/ClinicController.php"                                         app/Http/Controllers/Admin/ClinicController.php
cp "$SOURCE/app/Models/PatientModel.php"                                                             app/Models/PatientModel.php
cp "$SOURCE/app/Models/VisitModel.php"                                                               app/Models/VisitModel.php
cp "$SOURCE/app/Models/VitalSignModel.php"                                                           app/Models/VitalSignModel.php

mkdir -p app/Models/Base
cp "$SOURCE/app/Models/Base/ClinicScope.php"                                                         app/Models/Base/ClinicScope.php

mkdir -p app/Common/Helpers
cp "$SOURCE/app/Common/Helpers/workflow.php"                                                         app/Common/Helpers/workflow.php

cp "$SOURCE/resources/js/clinic.js"                                                                  resources/js/clinic.js
cp "$SOURCE/resources/views/components/alert.blade.php"                                              resources/views/components/alert.blade.php
cp "$SOURCE/resources/views/components/flash.blade.php"                                              resources/views/components/flash.blade.php
cp "$SOURCE/resources/views/components/page-header.blade.php"                                        resources/views/components/page-header.blade.php
cp "$SOURCE/resources/views/components/progress-bar.blade.php"                                       resources/views/components/progress-bar.blade.php
cp "$SOURCE/resources/views/components/status-badge.blade.php"                                       resources/views/components/status-badge.blade.php
cp "$SOURCE/resources/views/components/form/input.blade.php"                                         resources/views/components/form/input.blade.php
cp "$SOURCE/resources/views/components/form/select.blade.php"                                        resources/views/components/form/select.blade.php
cp "$SOURCE/resources/views/components/form/section.blade.php"                                       resources/views/components/form/section.blade.php
cp "$SOURCE/resources/views/components/workflow/action-bar.blade.php"                                resources/views/components/workflow/action-bar.blade.php
cp "$SOURCE/resources/views/admin/clinic/list/clinic_add.blade.php"                                  resources/views/admin/clinic/list/clinic_add.blade.php
cp "$SOURCE/resources/views/admin/clinic/list/clinic_edit.blade.php"                                 resources/views/admin/clinic/list/clinic_edit.blade.php
cp "$SOURCE/resources/views/clinics/workflow/show.blade.php"                                         resources/views/clinics/workflow/show.blade.php
cp "$SOURCE/resources/views/clinics/workflow/visit-summary.blade.php"                                resources/views/clinics/workflow/visit-summary.blade.php
cp "$SOURCE/resources/views/clinics/workflow/steps/registration.blade.php"                           resources/views/clinics/workflow/steps/registration.blade.php
cp "$SOURCE/resources/views/clinics/visits/index.blade.php"                                          resources/views/clinics/visits/index.blade.php

mkdir -p resources/views/clinics/dashboard
cp "$SOURCE/resources/views/clinics/dashboard/_recent-visits-rows.blade.php"                         resources/views/clinics/dashboard/_recent-visits-rows.blade.php

echo "All files copied."

# 3. Register workflow helper in composer.json if not already there
if ! grep -q "workflow.php" composer.json; then
  sed -i 's|"app/Common/Helpers/helpers\.php"|"app/Common/Helpers/helpers.php",\n            "app/Common/Helpers/workflow.php"|' composer.json
  echo "Added workflow.php to composer.json autoload."
fi

# 4. Clear caches
echo ""
echo "--- Clearing caches ---"
composer dump-autoload -q
php artisan route:clear
php artisan config:clear
php artisan view:clear

# 5. Commit and push
echo ""
echo "--- Git commit and push ---"
git add -A
git commit -m "fix: clinic delivery fixes and reusable components

- bootstrap/app.php: add BindSubdomainParameter middleware
- routes/clinic.php: remove hardcoded dtc.localhost, add logout route
- WorkflowContext: add missing stepIdx to toViewData
- WorkflowController: race-safe visit code, named route params
- RegistrationStep: fix sex->gender mapping, fix ward field
- VitalsStep: pass visit_code to VitalSignModel::create
- AuthController: fix logout redirect, add session regeneration
- VisitController: scope patient search to clinic_id
- PatientModel: add ClinicScope, getSexAttribute accessor
- VisitModel: add getGivenNameAttribute accessor
- VitalSignModel: add visit_code to fillable
- ClinicController: store logo on public disk consistently
- clinic_add/edit.blade: add name=note to textarea
- form/input: fix required prop, add id/readonly/hint
- form/select: add id, required, error display
- form/section: add explicit border-left style
- flash.blade: handle all session key patterns
- clinic.js: querySelectorAll for flash, sidebar persistence
- New components: x-alert, x-page-header, x-status-badge,
  x-progress-bar, ClinicScope trait, wf_*_url helpers"

git push origin dev

echo ""
echo "Done! Visit: http://dtc.localhost:8000"
