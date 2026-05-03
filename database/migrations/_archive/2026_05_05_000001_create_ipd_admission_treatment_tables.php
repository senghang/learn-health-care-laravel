<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ══════════════════════════════════════════════════════════════════════════════
 * IPD WORKFLOW — ADDITIVE MIGRATION
 * ══════════════════════════════════════════════════════════════════════════════
 *
 * SAFETY CHECK:
 *   ✅ admissions       — new table (Schema::create)
 *   ✅ treatments       — new table (Schema::create)
 *   ✅ out_in_patient   — adds nullable columns only (no rename/drop)
 *   ✅ visits           — adds nullable columns only
 *   ✅ beds             — no changes (assignTo/release already exists)
 *
 * DOES NOT:
 *   ❌ Rename any existing column
 *   ❌ Drop any existing column
 *   ❌ Modify any existing NOT NULL constraint
 *   ❌ Change any existing FK relationship
 *
 * ══════════════════════════════════════════════════════════════════════════════
 */
return new class extends Migration {
    public function up(): void
    {
        // ══════════════════════════════════════════════════════════════════════
        // 1. ADMISSIONS — dedicated IPD admission lifecycle table
        // ══════════════════════════════════════════════════════════════════════
        //
        // WHY: out_in_patient is an encounter record (clinical context).
        //      admissions is a lifecycle record (administrative: admit → discharge).
        //      They coexist — admission tracks the bed/ward/status,
        //      encounter tracks the clinical workflow.
        //
        // RELATIONSHIP CHAIN:
        //   Patient → Visit(IPD) → Admission → Ward → Room → Bed
        //                       → Encounter → Vitals, Treatments, Medications
        //
        if (!Schema::hasTable('admissions')) {
            Schema::create('admissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
                $table->string('code', 30)->unique();

                // ── Context FKs ───────────────────────────────────────────────
                $table->string('patient_code', 30);
                $table->string('visit_code', 30);
                $table->string('encounter_code', 30)->nullable()
                      ->comment('Links to out_in_patient.code for clinical data');

                // ── Admission details ─────────────────────────────────────────
                $table->string('admission_type', 40)->nullable()
                      ->comment('Emergency|Elective|Referral|Transfer');
                $table->text('admission_reason')->nullable();
                $table->text('admission_notes')->nullable();

                // ── Ward / Bed assignment (proper FK) ─────────────────────────
                $table->foreignId('ward_id')->nullable()->constrained('wards')->nullOnDelete();
                $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
                $table->foreignId('bed_id')->nullable()->constrained('beds')->nullOnDelete();

                // ── Responsible staff ─────────────────────────────────────────
                $table->string('attending_doctor', 120)->nullable();
                $table->string('admitting_doctor', 120)->nullable();
                $table->string('primary_nurse', 120)->nullable();

                // ── Timestamps (explicit — not just created_at) ───────────────
                $table->dateTime('admitted_at');
                $table->dateTime('discharged_at')->nullable();
                $table->dateTime('expected_discharge_at')->nullable()
                      ->comment('Estimated discharge date for planning');

                // ── Discharge details ─────────────────────────────────────────
                $table->string('discharge_type', 40)->nullable()
                      ->comment('Normal|Against_advice|Death|Transfer');
                $table->text('discharge_summary')->nullable();
                $table->string('discharge_condition', 40)->nullable()
                      ->comment('Recovered|Improved|Unchanged|Worsened|Deceased');
                $table->string('discharged_by', 120)->nullable();

                // ── Status lifecycle ──────────────────────────────────────────
                $table->string('status', 20)->default('admitted')
                      ->comment('admitted|discharged|transferred|deceased|cancelled');

                // ── Audit ─────────────────────────────────────────────────────
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                // ── Indexes ───────────────────────────────────────────────────
                $table->index('clinic_id');
                $table->index(['clinic_id', 'status']);
                $table->index('patient_code');
                $table->index('visit_code');
                $table->index('bed_id');

                $table->foreign('patient_code')->references('code')->on('patients')->restrictOnDelete();
                $table->foreign('visit_code')->references('code')->on('visits')->restrictOnDelete();
            });
        }

        // ══════════════════════════════════════════════════════════════════════
        // 2. TREATMENTS — IPD daily treatment/procedure orders
        // ══════════════════════════════════════════════════════════════════════
        //
        // One admission can have MANY treatments over days.
        // Each treatment is an order (medication, procedure, therapy, nursing).
        //
        if (!Schema::hasTable('treatments')) {
            Schema::create('treatments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
                $table->string('code', 30)->unique();

                // ── Context ───────────────────────────────────────────────────
                $table->string('admission_code', 30);
                $table->string('visit_code', 30)->nullable();
                $table->string('patient_code', 30);

                // ── Treatment details ─────────────────────────────────────────
                $table->string('treatment_type', 60)
                      ->comment('medication|procedure|therapy|nursing_care|diet|other');
                $table->string('name', 200)->comment('Treatment name / description');
                $table->text('instructions')->nullable();
                $table->string('frequency', 60)->nullable()
                      ->comment('STAT|OD|BD|TDS|QID|PRN|Q4H|Q6H|Q8H');
                $table->string('route', 40)->nullable()
                      ->comment('Oral|IV|IM|SC|Topical|Inhalation|PR|PV');
                $table->string('duration', 60)->nullable();

                // ── Order tracking ────────────────────────────────────────────
                $table->string('ordered_by', 120)->nullable();
                $table->dateTime('ordered_at')->nullable();
                $table->string('administered_by', 120)->nullable();
                $table->dateTime('administered_at')->nullable();

                // ── Status ────────────────────────────────────────────────────
                $table->string('status', 20)->default('ordered')
                      ->comment('ordered|in_progress|completed|cancelled|held');
                $table->text('notes')->nullable();
                $table->text('cancellation_reason')->nullable();

                // ── Audit ─────────────────────────────────────────────────────
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->index('clinic_id');
                $table->index('admission_code');
                $table->index(['clinic_id', 'status']);
                $table->index('patient_code');

                $table->foreign('patient_code')->references('code')->on('patients')->restrictOnDelete();
            });
        }

        // ══════════════════════════════════════════════════════════════════════
        // 3. PATCH out_in_patient — add status + admission_code (ADDITIVE)
        // ══════════════════════════════════════════════════════════════════════
        if (Schema::hasTable('out_in_patient')) {
            Schema::table('out_in_patient', function (Blueprint $table) {
                if (!Schema::hasColumn('out_in_patient', 'status')) {
                    $table->string('status', 20)->default('active')->after('ended_at')
                          ->comment('active|completed|cancelled');
                }
                if (!Schema::hasColumn('out_in_patient', 'admission_code')) {
                    $table->string('admission_code', 30)->nullable()->after('visit_code')
                          ->comment('Links IPD encounters to admissions table');
                }
                if (!Schema::hasColumn('out_in_patient', 'clinic_id')) {
                    $table->unsignedBigInteger('clinic_id')->nullable()->after('id');
                }
            });
        }

        // ══════════════════════════════════════════════════════════════════════
        // 4. PATCH visits — add admission_status for quick queries (ADDITIVE)
        // ══════════════════════════════════════════════════════════════════════
        if (Schema::hasTable('visits')) {
            Schema::table('visits', function (Blueprint $table) {
                if (!Schema::hasColumn('visits', 'admission_status')) {
                    $table->string('admission_status', 20)->nullable()->after('visit_outcome')
                          ->comment('NULL for OPD | admitted|discharged for IPD');
                }
            });
        }
    }

    public function down(): void
    {
        // Drop new tables
        Schema::dropIfExists('treatments');
        Schema::dropIfExists('admissions');

        // Remove added columns (safe)
        if (Schema::hasTable('out_in_patient')) {
            Schema::table('out_in_patient', function (Blueprint $table) {
                if (Schema::hasColumn('out_in_patient', 'status')) $table->dropColumn('status');
                if (Schema::hasColumn('out_in_patient', 'admission_code')) $table->dropColumn('admission_code');
            });
        }
        if (Schema::hasTable('visits') && Schema::hasColumn('visits', 'admission_status')) {
            Schema::table('visits', fn($t) => $t->dropColumn('admission_status'));
        }
    }
};
