<?php

/**
 * English language file
 * Must mirror the structure of lang/km/app.php exactly.
 */

return [

    // ── Common actions ────────────────────────────────────────────────────────
    'save'         => 'Save',
    'update'       => 'Update',
    'delete'       => 'Delete',
    'cancel'       => 'Cancel',
    'back'         => 'Back',
    'search'       => 'Search',
    'add'          => 'Add',
    'edit'         => 'Edit',
    'print'        => 'Print',
    'export'       => 'Export',
    'confirm'      => 'Confirm',
    'yes'          => 'Yes',
    'no'           => 'No',
    'loading'      => 'Loading…',
    'all'          => 'All',
    'none'         => 'None',
    'optional'     => 'Optional',
    'required'     => 'Required',
    'actions'      => 'Actions',
    'total'        => 'Total',
    'active'       => 'Active',
    'inactive'     => 'Inactive',
    'pending'      => 'Pending',
    'done'         => 'Done',
    'skip'         => 'Skip',
    'clear'        => 'Clear',
    'new'          => 'New',

    // ── Common fields ─────────────────────────────────────────────────────────
    'code'         => 'Code',
    'name'         => 'Name',
    'date'         => 'Date',
    'time'         => 'Time',
    'type'         => 'Type',
    'status'       => 'Status',
    'notes'        => 'Notes',
    'phone'        => 'Phone',
    'address'      => 'Address',
    'gender'       => 'Gender',
    'male'         => 'Male',
    'female'       => 'Female',
    'age'          => 'Age',
    'dob'          => 'Date of Birth',
    'nationality'  => 'Nationality',
    'occupation'   => 'Occupation',

    // ── Navigation ────────────────────────────────────────────────────────────
    'nav' => [
        'dashboard'   => 'Dashboard',
        'patients'    => 'Patients',
        'visits'      => 'Visits',
        'workflow'    => 'Clinical Workflow',
        'beds'        => 'Beds & Wards',
        'pharmacy'    => 'Pharmacy',
        'laboratory'  => 'Laboratory',
        'billing'     => 'Billing',
        'inventory'   => 'Inventory',
        'reports'     => 'Reports',
        'settings'    => 'Settings',
        'logout'      => 'Logout',
    ],

    // ── Patients ──────────────────────────────────────────────────────────────
    'patient'          => 'Patient',
    'patients'         => 'Patients',
    'patient_code'     => 'Patient Code',
    'patient_new'      => 'New Patient',
    'patient_surname'  => 'Surname',
    'patient_name'     => 'Given Name',
    'patient_sex'      => 'Sex',
    'patient_dob'      => 'Date of Birth',
    'patient_age'      => 'Age',
    'patient_phone'    => 'Phone',
    'patient_address'  => 'Address',
    'patient_id_card'  => 'ID Card',

    // ── Visits ────────────────────────────────────────────────────────────────
    'visit'            => 'Visit',
    'visits'           => 'Visits',
    'visit_new'        => 'New Visit',
    'visit_code'       => 'Visit Code',
    'visit_type'       => 'Visit Type',
    'visit_opd'        => 'OPD — Outpatient',
    'visit_ipd'        => 'IPD — Inpatient',
    'visit_admitted'   => 'Admitted At',
    'visit_discharged' => 'Discharged At',
    'visit_outcome'    => 'Outcome',
    'visit_active'     => 'Active',
    'visit_done'       => 'Completed',
    'visit_followup'   => 'Follow-up',

    // ── Workflow steps ────────────────────────────────────────────────────────
    'step_registration' => 'Registration',
    'step_triage'       => 'Triage',
    'step_vitals'       => 'Vital Signs',
    'step_history'      => 'Medical History',
    'step_labs'         => 'Laboratory',
    'step_diagnosis'    => 'Diagnosis',
    'step_soap'         => 'SOAP Notes',
    'step_prescription' => 'Prescription',
    'step_referral'     => 'Referral',
    'step_invoice'      => 'Invoice',

    // ── Invoices ──────────────────────────────────────────────────────────────
    'invoice'           => 'Invoice',
    'invoices'          => 'Invoices',
    'invoice_code'      => 'Invoice Code',
    'invoice_date'      => 'Invoice Date',
    'invoice_total'     => 'Total',
    'invoice_paid'      => 'Paid',
    'invoice_pending'   => 'Pending',
    'invoice_partial'   => 'Partial',
    'payment_type'      => 'Payment Type',
    'cashier'           => 'Cashier',

    // ── Prescriptions ─────────────────────────────────────────────────────────
    'prescription'      => 'Prescription',
    'prescriptions'     => 'Prescriptions',
    'medicine'          => 'Medicine',
    'medicines'         => 'Medicines',
    'dosage'            => 'Dosage',
    'morning'           => 'Morning',
    'afternoon'         => 'Afternoon',
    'evening'           => 'Evening',
    'night'             => 'Night',
    'days'              => 'Days',

    // ── Vitals ────────────────────────────────────────────────────────────────
    'temperature'       => 'Temperature',
    'heart_rate'        => 'Heart Rate',
    'blood_pressure'    => 'Blood Pressure',
    'oxygen_saturation' => 'SpO₂',
    'blood_glucose'     => 'Blood Glucose',
    'weight'            => 'Weight',
    'height'            => 'Height',
    'bmi'               => 'BMI',

    // ── Dashboard ─────────────────────────────────────────────────────────────
    'today_visits'      => "Today's Visits",
    'active_visits'     => 'Active Visits',
    'inpatients'        => 'Inpatients (IPD)',
    'this_month'        => 'This Month',
    'recent_visits'     => 'Recent Visits',
    'quick_actions'     => 'Quick Actions',
    'live_activity'     => 'Live Activity',
    'last_7_days'       => 'Last 7 Days',

    // ── Status labels ─────────────────────────────────────────────────────────
    'status_active'     => 'Active',
    'status_inactive'   => 'Inactive',
    'status_pending'    => 'Pending',
    'status_done'       => 'Done',
    'status_cancelled'  => 'Cancelled',

    // ── Errors and validation ─────────────────────────────────────────────────
    'error_required'    => 'This field is required',
    'error_not_found'   => 'Record not found',
    'error_unauthorized'=> 'Unauthorized',
    'success_saved'     => 'Saved successfully',
    'success_updated'   => 'Updated successfully',
    'success_deleted'   => 'Deleted successfully',
];
