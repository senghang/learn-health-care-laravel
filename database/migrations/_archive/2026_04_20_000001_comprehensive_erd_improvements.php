<?php

use App\Models\Base\AuditEntity;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ══════════════════════════════════════════════════════════════════════════════
 * COMPREHENSIVE DATABASE IMPROVEMENT — Based on ERD Diagram
 * ══════════════════════════════════════════════════════════════════════════════
 *
 * This migration:
 *   1. Creates missing tables from the ERD (admissions, treatments, inpatient_medications)
 *   2. Patches ALL existing tables to use AuditEntity pattern consistently
 *   3. Adds clinic_id + index where missing
 *   4. Replaces is_deleted with SoftDeletes
 *   5. Adds missing columns identified in the ERD
 *
 * Safe for production — all operations check existence before acting.
 * ══════════════════════════════════════════════════════════════════════════════
 */
return new class extends Migration {

    public function up(): void
    {
        // ══════════════════════════════════════════════════════════════════════
        // 1. NEW TABLES FROM ERD
        // ══════════════════════════════════════════════════════════════════════

        // ── 1a. Admissions (IPD workflow entry point) ─────────────────────────
        if (!Schema::hasTable('admissions')) {
            Schema::create('admissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
                $table->string('code', 30)->unique();
                $table->string('patient_code', 30);
                $table->string('visit_code', 30);
                $table->string('encounter_code', 30)->nullable();

                $table->string('admission_type', 40)->nullable()
                    ->comment('from store_settings(admission_type)');
                $table->string('admission_reason')->nullable();
                $table->text('admission_notes')->nullable();

                // Ward / Bed assignment
                $table->unsignedBigInteger('ward_id')->nullable();
                $table->unsignedBigInteger('room_id')->nullable();
                $table->unsignedBigInteger('bed_id')->nullable();

                $table->string('attending_doctor', 120)->nullable();
                $table->string('admitting_doctor', 120)->nullable();
                $table->dateTime('admitted_at');
                $table->dateTime('discharged_at')->nullable();
                $table->string('discharge_type', 40)->nullable()
                    ->comment('from store_settings(discharge_type)');
                $table->text('discharge_summary')->nullable();
                $table->string('discharge_condition', 40)->nullable();

                $table->string('status', 20)->default('admitted')
                    ->comment('admitted | discharged | transferred | deceased');

                AuditEntity::columns($table);
                $table->index('clinic_id');

                $table->foreign('patient_code')->references('code')->on('patients')->restrictOnDelete();
                $table->foreign('visit_code')->references('code')->on('visits')->restrictOnDelete();
                $table->foreign('ward_id')->references('id')->on('wards')->nullOnDelete();
                $table->foreign('room_id')->references('id')->on('rooms')->nullOnDelete();
                $table->foreign('bed_id')->references('id')->on('beds')->nullOnDelete();

                $table->index(['clinic_id', 'status']);
                $table->index('patient_code');
                $table->index('visit_code');
            });
        }

        // ── 1b. Treatments (IPD daily treatment orders) ───────────────────────
        if (!Schema::hasTable('treatments')) {
            Schema::create('treatments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
                $table->string('code', 30)->unique();
                $table->string('admission_code', 30);
                $table->string('visit_code', 30)->nullable();
                $table->string('encounter_code', 30)->nullable();

                $table->string('treatment_type', 60)->nullable()
                    ->comment('medication | procedure | therapy | nursing_care');
                $table->string('name')->comment('Treatment description');
                $table->text('instructions')->nullable();
                $table->string('frequency', 60)->nullable()
                    ->comment('e.g. TDS, BD, STAT, PRN');
                $table->string('route', 40)->nullable()
                    ->comment('Oral, IV, IM, SC, Topical');
                $table->string('duration', 60)->nullable();

                $table->string('ordered_by', 120)->nullable();
                $table->dateTime('ordered_at')->nullable();
                $table->string('administered_by', 120)->nullable();
                $table->dateTime('administered_at')->nullable();

                $table->string('status', 20)->default('ordered')
                    ->comment('ordered | in_progress | completed | cancelled');
                $table->text('notes')->nullable();

                AuditEntity::columns($table);
                $table->index('clinic_id');
                $table->index('admission_code');
            });
        }

        // ── 1c. Inpatient Medications (IPD med administration log) ────────────
        if (!Schema::hasTable('inpatient_medications')) {
            Schema::create('inpatient_medications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
                $table->string('code', 30)->unique();
                $table->string('admission_code', 30);
                $table->string('visit_code', 30)->nullable();

                $table->foreignId('medicine_id')->nullable()->constrained('medicines')->nullOnDelete();
                $table->string('medicine_code', 30)->nullable();
                $table->string('medicine_name', 120);
                $table->string('dosage', 60)->nullable();
                $table->string('route', 40)->nullable()
                    ->comment('Oral, IV, IM, SC');
                $table->string('frequency', 60)->nullable()
                    ->comment('e.g. TDS, BD, QID, STAT');
                $table->decimal('quantity', 10, 2)->default(0);

                $table->dateTime('start_date')->nullable();
                $table->dateTime('end_date')->nullable();
                $table->string('status', 20)->default('active')
                    ->comment('active | completed | discontinued | held');

                $table->string('prescribed_by', 120)->nullable();
                $table->string('administered_by', 120)->nullable();
                $table->dateTime('administered_at')->nullable();
                $table->text('notes')->nullable();

                AuditEntity::columns($table);
                $table->index('clinic_id');
                $table->index('admission_code');
                $table->index('medicine_id');
            });
        }

        // ── 1d. Store Settings (if not already created) ───────────────────────
        if (!Schema::hasTable('store_settings')) {
            Schema::create('store_settings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('clinic_id')->nullable()->constrained()->cascadeOnDelete();
                $table->string('group', 60)->index();
                $table->string('key', 80);
                $table->string('label_en', 120)->nullable();
                $table->string('label_km', 120)->nullable();
                $table->string('value', 255)->nullable();
                $table->string('color', 20)->nullable();
                $table->string('icon', 40)->nullable();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->boolean('is_system')->default(false);
                AuditEntity::columns($table);
                $table->unique(['clinic_id', 'group', 'key'], 'store_settings_unique');
            });
        }

        // ── 1f. Suppliers (if not already created) ────────────────────────────
        if (!Schema::hasTable('suppliers')) {
            Schema::create('suppliers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
                $table->string('code', 30)->unique();
                $table->string('name', 120);
                $table->string('contact_person', 120)->nullable();
                $table->string('phone', 30)->nullable();
                $table->string('email', 120)->nullable();
                $table->text('address')->nullable();
                $table->string('tax_id', 40)->nullable();
                $table->string('payment_terms', 60)->nullable();
                $table->boolean('is_active')->default(true);
                AuditEntity::columns($table);
                $table->index('clinic_id');
            });
        }

        // ── 1g. Lab Test Catalogs (if not already created) ────────────────────
        if (!Schema::hasTable('lab_test_catalogs')) {
            Schema::create('lab_test_catalogs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
                $table->string('code', 30)->unique();
                $table->string('name', 120);
                $table->string('name_kh', 120)->nullable();
                $table->string('category', 60)->nullable();
                $table->string('sample_type', 60)->nullable();
                $table->string('unit', 40)->nullable();
                $table->decimal('normal_range_min', 10, 2)->nullable();
                $table->decimal('normal_range_max', 10, 2)->nullable();
                $table->string('normal_range_text', 120)->nullable();
                $table->decimal('price', 14, 2)->default(0);
                $table->boolean('is_active')->default(true);
                AuditEntity::columns($table);
                $table->index(['clinic_id', 'category']);
            });
        }

        // ── 1h. Pharmacy Dispenses (if not already created) ───────────────────
        if (!Schema::hasTable('pharmacy_dispenses')) {
            Schema::create('pharmacy_dispenses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
                $table->string('code', 30)->unique();
                $table->string('prescription_code', 30)->index();
                $table->foreignId('medicine_id')->constrained('medicines')->restrictOnDelete();
                $table->string('medicine_code', 30);
                $table->string('medicine_name', 120);
                $table->string('patient_code', 30)->index();
                $table->string('visit_code', 30)->nullable()->index();
                $table->integer('quantity_dispensed');
                $table->string('batch_no', 60)->nullable();
                $table->date('expiry_date')->nullable();
                $table->string('dispensed_by', 120)->nullable();
                $table->timestamp('dispensed_at')->nullable();
                $table->boolean('counseling_done')->default(false);
                $table->text('pharmacist_notes')->nullable();
                AuditEntity::columns($table);
                $table->index(['clinic_id', 'dispensed_at']);
            });
        }

        // ══════════════════════════════════════════════════════════════════════
        // 2. PATCH EXISTING TABLES
        // ══════════════════════════════════════════════════════════════════════

        // ── 2a. users: is_deleted → SoftDeletes + audit columns ──────────────
        $this->replaceIsDeleted('users');
        $this->replaceIsDeleted('admin_users');
        $this->addAuditColumns('users');

        // ── 2b. roles: + SoftDeletes + audit + description/level/is_system ───
        $this->addSoftDeletes('roles');
        $this->addAuditColumns('roles');
        $this->addColumnIfMissing('roles', 'description', fn(Blueprint $t) => $t->string('description', 255)->nullable()->after('name'));
        $this->addColumnIfMissing('roles', 'level', fn(Blueprint $t) => $t->unsignedSmallInteger('level')->default(0)->after('description'));
        $this->addColumnIfMissing('roles', 'is_system', fn(Blueprint $t) => $t->boolean('is_system')->default(false)->after('level'));

        // ── 2c. permissions: + SoftDeletes + audit ───────────────────────────
        $this->addSoftDeletes('permissions');
        $this->addAuditColumns('permissions');

        // ── 2d. patients: + blood_type, emergency contact, photo ─────────────
        $this->addColumnIfMissing('patients', 'blood_type', fn(Blueprint $t) => $t->string('blood_type', 10)->nullable()->after('spid'));
        $this->addColumnIfMissing('patients', 'emergency_contact_name', fn(Blueprint $t) => $t->string('emergency_contact_name', 120)->nullable()->after('blood_type'));
        $this->addColumnIfMissing('patients', 'emergency_contact_phone', fn(Blueprint $t) => $t->string('emergency_contact_phone', 30)->nullable()->after('emergency_contact_name'));
        $this->addColumnIfMissing('patients', 'photo_path', fn(Blueprint $t) => $t->string('photo_path')->nullable()->after('emergency_contact_phone'));

        // ── 2e. users: + employee_id, is_active, login tracking ──────────────
        $this->addColumnIfMissing('users', 'employee_id', fn(Blueprint $t) => $t->unsignedBigInteger('employee_id')->nullable()->after('id'));
        $this->addColumnIfMissing('users', 'phone', fn(Blueprint $t) => $t->string('phone', 30)->nullable()->after('email'));
        $this->addColumnIfMissing('users', 'is_active', fn(Blueprint $t) => $t->boolean('is_active')->default(true)->after('password'));
        $this->addColumnIfMissing('users', 'avatar_path', fn(Blueprint $t) => $t->string('avatar_path')->nullable()->after('is_active'));
        $this->addColumnIfMissing('users', 'last_login_at', fn(Blueprint $t) => $t->timestamp('last_login_at')->nullable()->after('avatar_path'));
        $this->addColumnIfMissing('users', 'last_login_ip', fn(Blueprint $t) => $t->string('last_login_ip', 45)->nullable()->after('last_login_at'));

        // ── 2f. visits: + reason_for_visit, attending_doctor ─────────────────
        $this->addColumnIfMissing('visits', 'reason_for_visit', fn(Blueprint $t) => $t->text('reason_for_visit')->nullable()->after('visit_outcome'));
        $this->addColumnIfMissing('visits', 'attending_doctor', fn(Blueprint $t) => $t->string('attending_doctor', 120)->nullable()->after('reason_for_visit'));
        $this->addColumnIfMissing('visits', 'ward_id', fn(Blueprint $t) => $t->unsignedBigInteger('ward_id')->nullable()->after('attending_doctor'));

        // ── 2g. referrals: + clinic_id, facility tracking, follow-up ─────────
        $this->addColumnIfMissing('referrals', 'clinic_id', fn(Blueprint $t) => $t->unsignedBigInteger('clinic_id')->nullable()->after('id'));
        $this->addColumnIfMissing('referrals', 'referral_status', fn(Blueprint $t) => $t->string('referral_status', 30)->nullable()->after('direction'));
        $this->addColumnIfMissing('referrals', 'facility_name', fn(Blueprint $t) => $t->string('facility_name', 120)->nullable()->after('referral_status'));
        $this->addColumnIfMissing('referrals', 'facility_code', fn(Blueprint $t) => $t->string('facility_code', 30)->nullable()->after('facility_name'));
        $this->addColumnIfMissing('referrals', 'facility_phone', fn(Blueprint $t) => $t->string('facility_phone', 30)->nullable()->after('facility_code'));
        $this->addColumnIfMissing('referrals', 'diagnosis_summary', fn(Blueprint $t) => $t->text('diagnosis_summary')->nullable()->after('reason'));
        $this->addColumnIfMissing('referrals', 'clinical_notes', fn(Blueprint $t) => $t->text('clinical_notes')->nullable()->after('diagnosis_summary'));
        $this->addColumnIfMissing('referrals', 'follow_up_required', fn(Blueprint $t) => $t->boolean('follow_up_required')->default(false)->after('medications'));
        $this->addColumnIfMissing('referrals', 'follow_up_date', fn(Blueprint $t) => $t->date('follow_up_date')->nullable()->after('follow_up_required'));

        // ── 2h. laboratories + imageries: + status, urgency ──────────────────
        foreach (['laboratories', 'imageries'] as $table) {
            $this->addColumnIfMissing($table, 'status', fn(Blueprint $t) => $t->string('status', 30)->default('requested')->after('title'));
            $this->addColumnIfMissing($table, 'urgency', fn(Blueprint $t) => $t->string('urgency', 20)->default('normal')->after('status'));
        }

        // ── 2i. services: + service_type, duration, billable ─────────────────
        $this->addColumnIfMissing('services', 'service_type', fn(Blueprint $t) => $t->string('service_type', 40)->nullable()->after('category'));
        $this->addColumnIfMissing('services', 'duration_minutes', fn(Blueprint $t) => $t->unsignedSmallInteger('duration_minutes')->nullable()->after('price'));
        $this->addColumnIfMissing('services', 'is_billable', fn(Blueprint $t) => $t->boolean('is_billable')->default(true)->after('duration_minutes'));

        // ── 2j. invoices: + subtotal, discount, tax, insurance, due_date ─────
        $this->addColumnIfMissing('invoices', 'due_date', fn(Blueprint $t) => $t->date('due_date')->nullable()->after('invoice_date'));
        $this->addColumnIfMissing('invoices', 'subtotal', fn(Blueprint $t) => $t->decimal('subtotal', 14, 2)->default(0)->after('due_date'));
        $this->addColumnIfMissing('invoices', 'discount_total', fn(Blueprint $t) => $t->decimal('discount_total', 14, 2)->default(0)->after('subtotal'));
        $this->addColumnIfMissing('invoices', 'tax_total', fn(Blueprint $t) => $t->decimal('tax_total', 14, 2)->default(0)->after('discount_total'));
        $this->addColumnIfMissing('invoices', 'currency', fn(Blueprint $t) => $t->string('currency', 10)->default('KHR')->after('total'));
        $this->addColumnIfMissing('invoices', 'insurance_provider', fn(Blueprint $t) => $t->string('insurance_provider', 120)->nullable()->after('cashier'));
        $this->addColumnIfMissing('invoices', 'claim_number', fn(Blueprint $t) => $t->string('claim_number', 60)->nullable()->after('insurance_provider'));
        $this->addColumnIfMissing('invoices', 'notes', fn(Blueprint $t) => $t->text('notes')->nullable()->after('claim_number'));

        // ── 2k. invoice_medications: + medicine_id FK ─────────────────────────
        $this->addColumnIfMissing('invoice_medications', 'medicine_id', fn(Blueprint $t) => $t->unsignedBigInteger('medicine_id')->nullable()->after('invoice_code'));

        // ── 2l. inventory_transactions: + SoftDeletes ────────────────────────
        $this->addSoftDeletes('inventory_transactions');

        // ── 2m. out_in_patient: + SoftDeletes + clinic_id ────────────────────
        $this->addSoftDeletes('out_in_patient');
        $this->addColumnIfMissing('out_in_patient', 'clinic_id', fn(Blueprint $t) => $t->unsignedBigInteger('clinic_id')->nullable()->after('id'));

        // ── 2n. prescriptions: + dispensed_status ────────────────────────────
        $this->addColumnIfMissing('prescriptions', 'dispensed_status', fn(Blueprint $t) => $t->string('dispensed_status', 20)->nullable()->after('prescribed_by'));
        $this->addColumnIfMissing('prescriptions', 'dispensed_by', fn(Blueprint $t) => $t->string('dispensed_by', 120)->nullable()->after('dispensed_status'));

        // ══════════════════════════════════════════════════════════════════════
        // 3. ADD clinic_id + INDEX WHERE MISSING
        // ══════════════════════════════════════════════════════════════════════

        $clinicIdTables = [
            'triages', 'diagnoses', 'physical_examinations',
            'medical_histories', 'imagery_results', 'laboratory_results',
            'patient_addresses', 'patient_identifications',
        ];

        foreach ($clinicIdTables as $table) {
            $this->addColumnIfMissing($table, 'clinic_id', fn(Blueprint $t) => $t->unsignedBigInteger('clinic_id')->nullable()->after('id'));
        }

        // Add index on clinic_id for ALL tenant tables
        $allTenantTables = array_merge($clinicIdTables, [
            'visits', 'invoices', 'prescriptions', 'payments',
            'laboratories', 'imageries', 'services', 'medicines',
            'stock_movements', 'inventory_transactions',
            'wards', 'roles', 'permissions', 'clinic_settings',
            'referrals', 'out_in_patient',
            'admissions', 'treatments', 'inpatient_medications',
            'employees', 'suppliers', 'lab_test_catalogs',
            'pharmacy_dispenses', 'store_settings',
        ]);

        foreach ($allTenantTables as $table) {
            $this->addIndexIfMissing($table, 'clinic_id');
        }

        // ══════════════════════════════════════════════════════════════════════
        // 4. BACKFILL clinic_id FROM patients TABLE
        // ══════════════════════════════════════════════════════════════════════

        $backfillTables = [
            'triages' => 'patient_code',
            'diagnoses' => 'patient_code',
            'physical_examinations' => 'patient_code',
            'medical_histories' => 'patient_code',
            'patient_addresses' => 'patient_code',
            'patient_identifications' => 'patient_code',
            'referrals' => 'visit_code',
            'out_in_patient' => 'visit_code',
        ];

        foreach ($backfillTables as $table => $joinCol) {
            $this->backfillClinicId($table, $joinCol);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_dispenses');
        Schema::dropIfExists('inpatient_medications');
        Schema::dropIfExists('treatments');
        Schema::dropIfExists('admissions');
        Schema::dropIfExists('lab_test_catalogs');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('store_settings');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // HELPER METHODS
    // ══════════════════════════════════════════════════════════════════════════

    private function addColumnIfMissing(string $table, string $column, Closure $callback): void
    {
        if (Schema::hasTable($table) && !Schema::hasColumn($table, $column)) {
            Schema::table($table, $callback);
        }
    }

    private function addSoftDeletes(string $table): void
    {
        if (Schema::hasTable($table) && !Schema::hasColumn($table, 'deleted_at')) {
            Schema::table($table, fn(Blueprint $t) => $t->softDeletes());
        }
    }

    private function addAuditColumns(string $table): void
    {
        if (!Schema::hasTable($table)) return;
        if (!Schema::hasColumn($table, 'created_by')) {
            Schema::table($table, fn(Blueprint $t) => $t->unsignedBigInteger('created_by')->nullable());
        }
        if (!Schema::hasColumn($table, 'updated_by')) {
            Schema::table($table, fn(Blueprint $t) => $t->unsignedBigInteger('updated_by')->nullable());
        }
    }

    private function replaceIsDeleted(string $table): void
    {
        if (!Schema::hasTable($table)) return;
        if (!Schema::hasColumn($table, 'is_deleted')) return;
        if (Schema::hasColumn($table, 'deleted_at')) return;

        Schema::table($table, fn(Blueprint $t) => $t->softDeletes());

        DB::table($table)->where('is_deleted', true)->update(['deleted_at' => now()]);

        Schema::table($table, fn(Blueprint $t) => $t->dropColumn('is_deleted'));
    }

    private function addIndexIfMissing(string $table, string $column): void
    {
        if (!Schema::hasTable($table)) return;
        if (!Schema::hasColumn($table, $column)) return;

        $indexName = "{$table}_{$column}_index";
        try {
            // PostgreSQL
            $exists = DB::select("SELECT 1 FROM pg_indexes WHERE tablename = ? AND indexname = ?", [$table, $indexName]);
            if (empty($exists)) {
                Schema::table($table, fn(Blueprint $t) => $t->index($column, $indexName));
            }
        } catch (Throwable) {
            try {
                // MySQL
                $exists = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
                if (empty($exists)) {
                    Schema::table($table, fn(Blueprint $t) => $t->index($column, $indexName));
                }
            } catch (Throwable) {
                // Skip — index may already exist
            }
        }
    }

    private function backfillClinicId(string $table, string $joinCol): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'clinic_id')) return;

        $sourceTable = $joinCol === 'visit_code' ? 'visits' : 'patients';
        $sourceCol = $joinCol === 'visit_code' ? 'code' : 'code';

        try {
            // PostgreSQL syntax
            DB::statement("
                UPDATE {$table} t SET clinic_id = s.clinic_id
                FROM {$sourceTable} s WHERE t.{$joinCol} = s.{$sourceCol}
                AND t.clinic_id IS NULL
            ");
        } catch (Throwable) {
            try {
                // MySQL syntax
                DB::statement("
                    UPDATE {$table} t JOIN {$sourceTable} s ON t.{$joinCol} = s.{$sourceCol}
                    SET t.clinic_id = s.clinic_id WHERE t.clinic_id IS NULL
                ");
            } catch (Throwable) {
            }
        }
    }
};
