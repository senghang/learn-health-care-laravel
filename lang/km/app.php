<?php

/**
 * Khmer language file (Primary language)
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * IMPORTANT RULES — READ BEFORE EDITING
 * ─────────────────────────────────────────────────────────────────────────────
 *
 * RULE 1: Simple string keys
 *   'patient' => 'អ្នកជំងឺ'           ← flat string
 *   {{ __('app.patient') }}             ← renders: "អ្នកជំងឺ"  ✅
 *
 * RULE 2: Nested keys → access with dot notation
 *   'visit' => ['type_opd' => '...']    ← nested
 *   {{ __('app.visit.type_opd') }}      ← correct access  ✅
 *   {{ __('app.visit') }}               ← WRONG — returns array, htmlspecialchars() crashes  ❌
 *
 * RULE 3: Never use arrays unless you intend to access sub-keys.
 *   If you see: htmlspecialchars(): Argument #1 must be string, array given
 *   → You are printing an array key, not a leaf string.
 *   → Add the sub-key: __('app.patient.title') instead of __('app.patient')
 *
 * ─────────────────────────────────────────────────────────────────────────────
 */

return [

    // ── Common actions ────────────────────────────────────────────────────────
    'save'         => 'រក្សាទុក',
    'update'       => 'កែប្រែ',
    'delete'       => 'លុប',
    'cancel'       => 'បោះបង់',
    'back'         => 'ត្រឡប់',
    'search'       => 'ស្វែងរក',
    'add'          => 'បន្ថែម',
    'edit'         => 'កែ',
    'print'        => 'បោះពុម្ព',
    'export'       => 'នាំចេញ',
    'confirm'      => 'បញ្ជាក់',
    'yes'          => 'បាទ/ចាស',
    'no'           => 'ទេ',
    'loading'      => 'កំពុងផ្ទុក…',
    'all'          => 'ទាំងអស់',
    'none'         => 'គ្មាន',
    'optional'     => 'ស្រេចចិត្ត',
    'required'     => 'ចាំបាច់',
    'actions'      => 'សកម្មភាព',
    'total'        => 'សរុប',
    'active'       => 'សកម្ម',
    'inactive'     => 'អសកម្ម',
    'pending'      => 'រងចាំ',
    'done'         => 'រួចរាល់',
    'skip'         => 'រំលង',
    'clear'        => 'សម្អាត',
    'new'          => 'ថ្មី',

    // ── Common fields ─────────────────────────────────────────────────────────
    'code'         => 'លេខ',
    'name'         => 'ឈ្មោះ',
    'date'         => 'ថ្ងៃខែ',
    'time'         => 'ម៉ោង',
    'type'         => 'ប្រភេទ',
    'status'       => 'ស្ថានភាព',
    'notes'        => 'កំណត់ចំណាំ',
    'phone'        => 'ទូរស័ព្ទ',
    'address'      => 'អាសយដ្ឋាន',
    'gender'       => 'ភេទ',
    'male'         => 'ប្រុស',
    'female'       => 'ស្រី',
    'age'          => 'អាយុ',
    'dob'          => 'ថ្ងៃខែឆ្នាំ',
    'nationality'  => 'សញ្ជាតិ',
    'occupation'   => 'មុខរបរ',

    // ── Navigation ────────────────────────────────────────────────────────────
    // NOTE: 'nav' is a nested array — access as __('app.nav.dashboard')
    'nav' => [
        'dashboard'   => 'ផ្ទាំងគ្រប់គ្រង',
        'patients'    => 'អ្នកជំងឺ',
        'visits'      => 'ការចូលព្យាបាល',
        'workflow'    => 'ប្រតិបត្តិការ',
        'beds'        => 'គ្រែ & បន្ទប់',
        'pharmacy'    => 'ឱសថស្ថាន',
        'laboratory'  => 'មន្ទីរពិសោធន៍',
        'billing'     => 'វិក្កយបត្រ',
        'inventory'   => 'ស្តុក',
        'reports'     => 'របាយការណ៍',
        'settings'    => 'ការកំណត់',
        'logout'      => 'ចាកចេញ',
    ],

    // ── Patients — FLAT strings ───────────────────────────────────────────────
    // Use {{ __('app.patient') }}         → "អ្នកជំងឺ"
    // Use {{ __('app.patients') }}        → "អ្នកជំងឺ" (same, plural context)
    // Use {{ __('app.patient_code') }}    → "លេខអ្នកជំងឺ"
    'patient'          => 'អ្នកជំងឺ',
    'patients'         => 'អ្នកជំងឺ',
    'patient_code'     => 'លេខអ្នកជំងឺ',
    'patient_new'      => 'អ្នកជំងឺថ្មី',
    'patient_surname'  => 'នាមត្រកូល',
    'patient_name'     => 'ឈ្មោះ',
    'patient_sex'      => 'ភេទ',
    'patient_dob'      => 'ថ្ងៃខែឆ្នាំ',
    'patient_age'      => 'អាយុ',
    'patient_phone'    => 'ទូរស័ព្ទ',
    'patient_address'  => 'អាសយដ្ឋាន',
    'patient_id_card'  => 'អត្ដសញ្ញាណប័ណ្ណ',

    // ── Visits — FLAT strings ─────────────────────────────────────────────────
    'visit'            => 'ការចូលព្យាបាល',
    'visits'           => 'ការចូលព្យាបាល',
    'visit_new'        => 'ការចូលព្យាបាលថ្មី',
    'visit_code'       => 'លេខការចូល',
    'visit_type'       => 'ប្រភេទការចូល',
    'visit_opd'        => 'OPD — ការព្យាបាលក្រៅ',
    'visit_ipd'        => 'IPD — ការព្យាបាលក្នុង',
    'visit_admitted'   => 'ថ្ងៃចូល',
    'visit_discharged' => 'ថ្ងៃចេញ',
    'visit_outcome'    => 'លទ្ធផល',
    'visit_active'     => 'កំពុងព្យាបាល',
    'visit_done'       => 'បានរួចរាល់',
    'visit_followup'   => 'ការតាមដាន',

    // ── Workflow steps — FLAT strings ─────────────────────────────────────────
    'step_registration' => 'ការចុះឈ្មោះ',
    'step_triage'       => 'ការពិនិត្យចូល',
    'step_vitals'       => 'សញ្ញានៃជំងឺ',
    'step_history'      => 'ប្រវត្តិ',
    'step_labs'         => 'ពិសោធន៍',
    'step_diagnosis'    => 'រោគវិនិច្ឆ័យ',
    'step_soap'         => 'SOAP Notes',
    'step_prescription' => 'ថ្នាំ',
    'step_referral'     => 'ការបញ្ជូន',
    'step_invoice'      => 'វិក្កយបត្រ',

    // ── Invoices — FLAT strings ───────────────────────────────────────────────
    'invoice'           => 'វិក្កយបត្រ',
    'invoices'          => 'វិក្កយបត្រ',
    'invoice_code'      => 'លេខវិក្កយបត្រ',
    'invoice_date'      => 'ថ្ងៃ',
    'invoice_total'     => 'ប្រាក់សរុប',
    'invoice_paid'      => 'បានទូទាត់',
    'invoice_pending'   => 'រងចាំ',
    'invoice_partial'   => 'ទូទាត់មួយផ្នែក',
    'payment_type'      => 'ប្រភេទទូទាត់',
    'cashier'           => 'អ្នកគិតប្រាក់',

    // ── Prescriptions — FLAT strings ──────────────────────────────────────────
    'prescription'      => 'វេជ្ជបញ្ជា',
    'prescriptions'     => 'វេជ្ជបញ្ជា',
    'medicine'          => 'ថ្នាំ',
    'medicines'         => 'ថ្នាំ',
    'dosage'            => 'កម្រិតថ្នាំ',
    'morning'           => 'ព្រឹក',
    'afternoon'         => 'ថ្ងៃ',
    'evening'           => 'ល្ងាច',
    'night'             => 'យប់',
    'days'              => 'ថ្ងៃ',

    // ── Vitals — FLAT strings ─────────────────────────────────────────────────
    'temperature'       => 'កម្ដៅខ្លួន',
    'heart_rate'        => 'ចង្វាក់បេះដូង',
    'blood_pressure'    => 'សម្ពាធឈាម',
    'oxygen_saturation' => 'អុកស៊ីសែន',
    'blood_glucose'     => 'ជាតិស្ករ',
    'weight'            => 'ទម្ងន់',
    'height'            => 'កម្ពស់',
    'bmi'               => 'BMI',

    // ── Dashboard ─────────────────────────────────────────────────────────────
    'today_visits'      => 'ការចូលថ្ងៃនេះ',
    'active_visits'     => 'ការចូលសកម្ម',
    'inpatients'        => 'អ្នកជំងឺ IPD',
    'this_month'        => 'ខែនេះ',
    'recent_visits'     => 'ការចូលព្យាបាលថ្មីៗ',
    'quick_actions'     => 'សកម្មភាពរហ័ស',
    'live_activity'     => 'សកម្មភាព',
    'last_7_days'       => 'ការចូលព្យាបាល ៧ ថ្ងៃ',

    // ── Status labels ─────────────────────────────────────────────────────────
    'status_active'     => 'សកម្ម',
    'status_inactive'   => 'អសកម្ម',
    'status_pending'    => 'រងចាំ',
    'status_done'       => 'រួចរាល់',
    'status_cancelled'  => 'បានបោះបង់',

    // ── Errors and validation ─────────────────────────────────────────────────
    'error_required'    => 'វាលនេះតម្រូវ',
    'error_not_found'   => 'រកមិនឃើញ',
    'error_unauthorized'=> 'គ្មានសិទ្ធ',
    'success_saved'     => 'រក្សាទុករួចរាល់',
    'success_updated'   => 'បានធ្វើបច្ចុប្បន្នភាព',
    'success_deleted'   => 'បានលុប',
];
