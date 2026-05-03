You are a senior Laravel + PostgreSQL + Healthcare ERP architect.

Project Context:
This is a multi-tenant clinic/hospital management system with modules:

- Dashboard
- Patient Management
- OPD
- IPD
- Lab & Imaging
- Pharmacy
- Billing
- Inventory
- Reports
- Activity Logs
- Settings

Tech stack:
- Laravel
- PostgreSQL
- Blade + Tailwind
- Multi-tenant architecture
- Role/Permission system
- Activity logging

Your task:
Refactor and fix the system while preserving existing business workflow.

-----------------------------------
CRITICAL FIXES
-----------------------------------

1. Fix database errors:
- Resolve:
  "Undefined column: day does not exist"
- Find all queries using invalid `day` column.
- Replace with:
    DATE(created_at)
    OR proper visit_date field
- Ensure PostgreSQL compatibility.

Example:
Replace:
->select('day')

With:
->selectRaw('DATE(created_at) as day')

-----------------------------------

2. Fix patient address schema mismatch:
Resolve:
"province_name column does not exist"

Audit:
- migrations
- models
- controllers
- form requests
- blade forms
- seeders

Ensure patient_addresses uses:

- province_id
- district_id
- commune_id
- village_id
- street

Remove all references to:
- province_name
- district_name
- commune_name
- village_name

Add proper relationships:
- province()
- district()
- commune()
- village()

Display names through relationships.

-----------------------------------

3. Sidebar architecture refactor

Move:
- Medicines
- Services

OUT of Settings menu.

New sidebar structure:

Dashboard

Patients

OPD
- Visits
- Triage
- Consultations

IPD
- Admissions
- Beds
- Discharge

Lab & Imaging
- Orders
- Results

Pharmacy
- Medicines
- Dispensing
- Stock

Billing
- Invoices
- Payments
- Services

Inventory
- Purchases
- Stock Movements

Reports

Activity Logs

Settings
- Users
- Roles
- Clinic Config

Update:
- sidebar blade
- menu service
- permissions mapping
- route checks
- active states

-----------------------------------

4. Fix Activity Logs module

Verify:
- sidebar route exists
- controller exists
- permission exists
- activity tracking works

Track events:
- patient registration
- visit creation
- consultation
- prescription
- invoice creation
- payment
- stock movement
- admission/discharge

Ensure activity list loads without errors.

-----------------------------------

5. Convert all billing calculations to USD

Standardize system currency:

USD ($)

Update:
- invoice calculations
- receipts
- pharmacy billing
- service billing
- dashboard revenue cards

Create helper:

money($amount)

Format:
$100.00

Ensure decimal precision:
decimal(12,2)

-----------------------------------

6. End-to-End Testing

Audit all workflows and fix failures:

Patient flow:
Register → Edit → Address → History

OPD flow:
Register → Visit → Triage → Consultation → Prescription → Billing → Payment

IPD flow:
Admission → Bed → Treatment → Billing → Discharge

Lab flow:
Order → Result → Review

Pharmacy flow:
Prescription → Dispense → Inventory deduction

Inventory flow:
Purchase → Stock in/out → Alerts

-----------------------------------

7. Create automated tests

Add feature tests for:

- PatientRegistrationTest
- OPDWorkflowTest
- IPDWorkflowTest
- BillingWorkflowTest
- PharmacyInventoryTest
- ActivityLogTest

Ensure tests pass.

-----------------------------------

8. Code quality improvements

Reduce duplicate code:
- extract services
- use repositories where needed
- reusable components
- reusable form partials

Improve:
- readability
- maintainability
- scalability

-----------------------------------

9. Migration safety

DO NOT create unnecessary migrations.

Only create migrations when schema truly requires updates.

Prefer:
- fixing code references
- cleaning models
- fixing queries

-----------------------------------

10. Final validation checklist

Run:

php artisan optimize:clear
php artisan migrate
php artisan db:seed
php artisan test

Validate:
- no SQL errors
- no broken routes
- no sidebar issues
- no billing issues
- no workflow regressions

-----------------------------------

EXPECTED OUTPUT
-----------------------------------

Provide:

1. files changed
2. exact fixes made
3. schema issues fixed
4. test coverage added
5. remaining risks
6. refactor summary

IMPORTANT:
Do not break existing healthcare workflow.
Maintain tenant isolation.
Keep code clean, reusable, and production-ready.