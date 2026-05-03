<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * OPD visit and clinical documentation tables.
 *
 * Includes:
 *   - visits                  (root encounter record for OPD & IPD)
 *   - triages                 (nursing assessment + triage level)
 *   - vital_signs             (vital sign groups per encounter)
 *   - vital_sign_observations (individual measurements)
 *   - medical_histories       (past medical / surgical / family / social)
 *   - physical_examinations   (system-by-system findings)
 *   - soaps                   (SOAP notes)
 *   - diagnoses               (ICD-10 coded diagnoses)
 *   - referrals               (inbound / outbound referral letters)
 */
return new class extends Migration {

    public function up(): void
    {
        // ── Visits ────────────────────────────────────────────────────────────
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30)->unique();
            $table->string('health_facility_code', 30)->nullable();
            $table->string('patient_code', 30);
            $table->string('surname', 80)->nullable();
            $table->string('name', 80)->nullable();
            $table->string('visit_type', 3)->default('OPD')->comment('OPD | IPD');
            $table->string('admission_type', 60)->nullable();
            $table->string('discharge_type', 60)->nullable();
            $table->string('visit_outcome', 60)->nullable();
            $table->string('priority', 20)->nullable()
                ->comment('normal | urgent | emergency');
            $table->string('reason_for_visit')->nullable();
            $table->text('clinical_summary')->nullable();
            $table->string('attending_doctor', 120)->nullable();
            $table->unsignedBigInteger('ward_id')->nullable();
            $table->dateTime('admitted_at')->nullable();
            $table->dateTime('discharged_at')->nullable();
            $table->date('followup_at')->nullable();
            $table->json('done_steps')->nullable();
            $table->json('skipped_steps')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_code')->references('code')->on('patients')->restrictOnDelete();
            $table->index('clinic_id');
            $table->index('patient_code');
            $table->index('visit_type');
        });

        // ── Triages ───────────────────────────────────────────────────────────
        Schema::create('triages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 30)->unique();
            $table->string('patient_code', 30);
            $table->string('visit_code', 30);
            $table->string('encounter_code', 30)->nullable();
            $table->text('chief_complaint')->nullable();
            $table->decimal('height', 5, 1)->nullable()->comment('cm');
            $table->decimal('weight', 5, 2)->nullable()->comment('kg');
            $table->decimal('bmi', 4, 1)->nullable();
            $table->string('triage_level', 20)->nullable()
                ->comment('immediate | urgent | less_urgent | non_urgent');
            $table->dateTime('recorded_at')->nullable();
            $table->string('recorded_by', 120)->nullable();
            $table->string('title')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['clinic_id', 'visit_code']);
        });

        // ── Vital Signs (header per encounter) ───────────────────────────────
        Schema::create('vital_signs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 30)->unique();
            $table->string('patient_code', 30)->nullable();
            $table->string('visit_code', 30)->nullable();
            $table->string('encounter_code', 30)->nullable();
            $table->dateTime('recorded_at')->nullable();
            $table->string('recorded_by', 120)->nullable();
            $table->string('title')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['clinic_id', 'visit_code']);
        });

        // ── Vital Sign Observations (individual measurements) ─────────────────
        Schema::create('vital_sign_observations', function (Blueprint $table) {
            $table->id();
            $table->string('vital_sign_code', 30);
            $table->string('name', 80)->comment('BP | HR | Temp | SpO2 …');
            $table->string('value', 40)->nullable();
            $table->string('unit', 20)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('vital_sign_code');
        });

        // ── Medical Histories ─────────────────────────────────────────────────
        Schema::create('medical_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->nullable()->constrained()->nullOnDelete();
            $table->string('patient_code', 30);
            $table->string('visit_code', 30)->nullable();
            $table->string('encounter_code', 30)->nullable();
            $table->string('name', 120)->comment('allergy | past_medical | family | surgical …');
            $table->json('value')->nullable();
            $table->timestamps();

            $table->index(['clinic_id', 'patient_code']);
        });

        // ── Physical Examinations ─────────────────────────────────────────────
        Schema::create('physical_examinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->nullable()->constrained()->nullOnDelete();
            $table->string('patient_code', 30);
            $table->string('visit_code', 30)->nullable();
            $table->string('encounter_code', 30)->nullable();
            $table->string('name', 120)->comment('Body system or region');
            $table->string('value', 255)->nullable();
            $table->string('value_type', 40)->nullable();
            $table->string('value_unit', 40)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['clinic_id', 'visit_code']);
        });

        // ── SOAP Notes ────────────────────────────────────────────────────────
        Schema::create('soaps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->nullable()->constrained()->nullOnDelete();
            $table->string('encounter_code', 30);
            $table->string('visit_code', 30)->nullable();
            $table->text('subjective')->nullable();
            $table->text('objective')->nullable();
            $table->text('assessment')->nullable();
            $table->text('evaluation')->nullable();
            $table->text('plan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['clinic_id', 'encounter_code']);
        });

        // ── Diagnoses ─────────────────────────────────────────────────────────
        Schema::create('diagnoses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->nullable()->constrained()->nullOnDelete();
            $table->string('patient_code', 30);
            $table->string('visit_code', 30);
            $table->string('encounter_code', 30)->nullable();
            $table->string('diagnosis_type', 20)->nullable()
                ->comment('primary | secondary | differential');
            $table->string('diagnosis_code', 20)->nullable()->comment('ICD-10 code');
            $table->string('diagnosis_name', 200)->nullable();
            $table->text('diagnosis_description')->nullable();
            $table->dateTime('diagnosed_at')->nullable();
            $table->string('diagnosed_by', 120)->nullable();
            $table->string('title')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['clinic_id', 'visit_code']);
        });

        // ── Referrals ─────────────────────────────────────────────────────────
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 30)->unique();
            $table->string('visit_code', 30);
            $table->string('encounter_code', 30)->nullable();
            $table->string('direction', 4)->comment('FROM | TO');
            $table->string('referral_status', 30)->nullable()
                ->comment('pending | accepted | rejected | completed');
            $table->string('referral_number', 60)->nullable();
            $table->string('facility_name', 120)->nullable();
            $table->string('facility_code', 30)->nullable();
            $table->string('facility_phone', 30)->nullable();
            $table->string('transportation', 60)->nullable();
            $table->text('reason')->nullable();
            $table->text('diagnosis_summary')->nullable();
            $table->text('clinical_notes')->nullable();
            $table->boolean('has_called')->default(false);
            $table->boolean('follow_up_required')->default(false);
            $table->date('follow_up_date')->nullable();
            $table->string('caretaker_name', 120)->nullable();
            $table->string('caretaker_phone', 30)->nullable();
            $table->string('referred_by', 120)->nullable();
            $table->string('referred_by_phone', 30)->nullable();
            $table->dateTime('referred_at')->nullable();
            $table->string('received_by', 120)->nullable();
            $table->string('received_by_phone', 30)->nullable();
            $table->dateTime('received_at')->nullable();
            $table->text('medications')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['clinic_id', 'visit_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
        Schema::dropIfExists('diagnoses');
        Schema::dropIfExists('soaps');
        Schema::dropIfExists('physical_examinations');
        Schema::dropIfExists('medical_histories');
        Schema::dropIfExists('vital_sign_observations');
        Schema::dropIfExists('vital_signs');
        Schema::dropIfExists('triages');
        Schema::dropIfExists('visits');
    }
};
