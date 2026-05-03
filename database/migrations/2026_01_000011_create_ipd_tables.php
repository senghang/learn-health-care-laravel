<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Inpatient Department (IPD) tables.
 *
 * Includes:
 *   - wards                (hospital wards)
 *   - rooms                (rooms within wards)
 *   - beds                 (individual beds)
 *   - out_in_patient       (encounter record shared by OPD & IPD)
 *   - admissions           (IPD admission header)
 *   - treatments           (daily treatment orders)
 *   - inpatient_medications (IPD medication administration log)
 *   - emergencies          (ER encounter records)
 *   - surgeries            (operative records)
 *   - progress_notes       (daily clinical notes)
 */
return new class extends Migration {

    public function up(): void
    {
        // ── Wards ─────────────────────────────────────────────────────────────
        Schema::create('wards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->string('code', 20)->unique();
            $table->string('name', 120);
            $table->string('name_kh', 120)->nullable();
            $table->string('name_en', 120)->nullable();
            $table->string('type', 30)->default('IPD')
                ->comment('IPD | ICU | ER | OT | OPD');
            $table->unsignedSmallInteger('capacity')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('clinic_id');
        });

        // ── Rooms ─────────────────────────────────────────────────────────────
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ward_id')->constrained()->cascadeOnDelete();
            $table->string('code', 20)->unique();
            $table->string('name', 80);
            $table->string('type', 30)->nullable()
                ->comment('general | private | semi_private | isolation');
            $table->tinyInteger('floor')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('ward_id');
        });

        // ── Beds ──────────────────────────────────────────────────────────────
        Schema::create('beds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ward_id')->constrained()->cascadeOnDelete();
            $table->string('code', 20)->unique();
            $table->string('name', 40);
            $table->string('status', 20)->default('available')
                ->comment('available | occupied | maintenance | reserved');
            $table->string('type', 30)->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('current_visit_code', 30)->nullable();
            $table->string('current_patient_code', 30)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['ward_id', 'status']);
        });

        // ── Out/In Patient Encounter (unified header) ─────────────────────────
        Schema::create('out_in_patient', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 30)->unique();
            $table->string('visit_code', 30);
            $table->string('visit_type', 3)->default('OPD')->comment('OPD | IPD');
            $table->string('name', 200)->nullable()
                ->comment('Ward name for IPD / clinic section for OPD');
            $table->string('service_type', 80)->nullable();
            $table->string('bed', 30)->nullable()->comment('Bed code — IPD only');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->string('encountered_by', 120)->nullable();
            $table->string('title')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('visit_code')->references('code')->on('visits')->cascadeOnDelete();
            $table->index(['clinic_id', 'visit_type']);
            $table->index('visit_code');
        });

        // ── Admissions (IPD entry point) ──────────────────────────────────────
        Schema::create('admissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30)->unique();
            $table->string('patient_code', 30);
            $table->string('visit_code', 30);
            $table->string('encounter_code', 30)->nullable();
            $table->string('admission_type', 40)->nullable();
            $table->string('admission_reason')->nullable();
            $table->text('admission_notes')->nullable();
            $table->foreignId('ward_id')->nullable()->constrained('wards')->nullOnDelete();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->foreignId('bed_id')->nullable()->constrained('beds')->nullOnDelete();
            $table->string('attending_doctor', 120)->nullable();
            $table->string('admitting_doctor', 120)->nullable();
            $table->string('primary_nurse', 120)->nullable();
            $table->dateTime('admitted_at');
            $table->dateTime('discharged_at')->nullable();
            $table->dateTime('expected_discharge_at')->nullable();
            $table->string('discharge_type', 40)->nullable();
            $table->text('discharge_summary')->nullable();
            $table->string('discharge_condition', 40)->nullable();
            $table->string('discharged_by', 120)->nullable();
            $table->string('status', 20)->default('admitted')
                ->comment('admitted | discharged | transferred | deceased');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_code')->references('code')->on('patients')->restrictOnDelete();
            $table->foreign('visit_code')->references('code')->on('visits')->restrictOnDelete();
            $table->index(['clinic_id', 'status']);
            $table->index('patient_code');
        });

        // ── Treatments (daily treatment orders) ───────────────────────────────
        Schema::create('treatments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30)->unique();
            $table->string('admission_code', 30)->index();
            $table->string('visit_code', 30)->nullable();
            $table->string('encounter_code', 30)->nullable();
            $table->string('treatment_type', 60)->nullable()
                ->comment('medication | procedure | therapy | nursing_care');
            $table->string('name', 200);
            $table->text('instructions')->nullable();
            $table->string('frequency', 60)->nullable()
                ->comment('TDS | BD | STAT | PRN | OD');
            $table->string('route', 40)->nullable()
                ->comment('Oral | IV | IM | SC | Topical');
            $table->string('duration', 60)->nullable();
            $table->string('ordered_by', 120)->nullable();
            $table->dateTime('ordered_at')->nullable();
            $table->string('administered_by', 120)->nullable();
            $table->dateTime('administered_at')->nullable();
            $table->string('status', 20)->default('ordered')
                ->comment('ordered | in_progress | completed | cancelled');
            $table->text('notes')->nullable();
            $table->string('cancellation_reason', 255)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['clinic_id', 'status']);
        });

        // ── Inpatient Medications (IPD administration log) ────────────────────
        Schema::create('inpatient_medications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30)->unique();
            $table->string('admission_code', 30)->index();
            $table->string('visit_code', 30)->nullable();
            $table->foreignId('medicine_id')->nullable()->constrained('medicines')->nullOnDelete();
            $table->string('medicine_code', 30)->nullable();
            $table->string('medicine_name', 120);
            $table->string('dosage', 60)->nullable();
            $table->string('route', 40)->nullable()
                ->comment('Oral | IV | IM | SC');
            $table->string('frequency', 60)->nullable()
                ->comment('TDS | BD | QID | STAT');
            $table->decimal('quantity', 10, 2)->default(0);
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->string('status', 20)->default('active')
                ->comment('active | completed | discontinued | held');
            $table->string('prescribed_by', 120)->nullable();
            $table->string('administered_by', 120)->nullable();
            $table->dateTime('administered_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['clinic_id', 'status']);
        });

        // ── Emergencies ───────────────────────────────────────────────────────
        Schema::create('emergencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 30)->unique();
            $table->string('visit_code', 30);
            $table->string('name', 120)->nullable();
            $table->string('service_type', 80)->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->string('encountered_by', 120)->nullable();
            $table->text('emergency_notes')->nullable();
            $table->string('title')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('visit_code')->references('code')->on('visits')->cascadeOnDelete();
            $table->index('visit_code');
        });

        // ── Surgeries ─────────────────────────────────────────────────────────
        Schema::create('surgeries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 30)->unique();
            $table->string('visit_code', 30);
            $table->string('parent_code', 30)->nullable();
            $table->string('theater_name', 120)->nullable();
            $table->string('service_type', 80)->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->text('reason')->nullable();
            $table->string('anesthesia_type', 80)->nullable();
            $table->text('procedure_notes')->nullable();
            $table->json('complications')->nullable();
            $table->json('specimens')->nullable();
            $table->decimal('blood_loss', 8, 2)->nullable()->comment('mL');
            $table->string('surgeon_name', 120)->nullable();
            $table->string('anesthetist_name', 120)->nullable();
            $table->json('assistant_names')->nullable();
            $table->string('title')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('visit_code')->references('code')->on('visits')->cascadeOnDelete();
            $table->index('visit_code');
        });

        // ── Progress Notes ────────────────────────────────────────────────────
        Schema::create('progress_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 30)->unique();
            $table->string('visit_code', 30);
            $table->string('parent_code', 30)->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->string('encountered_by', 120)->nullable();
            $table->string('title')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('visit_code')->references('code')->on('visits')->cascadeOnDelete();
            $table->index('visit_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progress_notes');
        Schema::dropIfExists('surgeries');
        Schema::dropIfExists('emergencies');
        Schema::dropIfExists('inpatient_medications');
        Schema::dropIfExists('treatments');
        Schema::dropIfExists('admissions');
        Schema::dropIfExists('out_in_patient');
        Schema::dropIfExists('beds');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('wards');
    }
};
