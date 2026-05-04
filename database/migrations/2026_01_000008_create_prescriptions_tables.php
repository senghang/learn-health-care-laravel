<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Prescription tables.
 *
 * Includes:
 *   - prescriptions           (prescription header)
 *   - prescription_medications (prescribed drug lines)
 */
return new class extends Migration {

    public function up(): void
    {
        // ── Prescriptions ─────────────────────────────────────────────────────
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30)->unique();
            $table->string('patient_code', 30);
            $table->string('visit_code', 30)->nullable();
            $table->string('encounter_code', 30)->nullable();
            $table->dateTime('prescribed_at')->nullable();
            $table->string('prescribed_by', 120)->nullable();
            $table->string('dispensed_status', 20)->nullable()
                ->comment('pending | partial | dispensed | cancelled');
            $table->string('dispensed_by', 120)->nullable();
            $table->string('print_template_code', 40)->nullable();
            $table->string('title')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['clinic_id', 'visit_code']);
            $table->index('patient_code');
        });

        // ── Prescription Medications (line items) ─────────────────────────────
        Schema::create('prescription_medications', function (Blueprint $table) {
            $table->id();
            $table->string('prescription_code', 30);
            $table->string('medication_code', 30)->nullable()
                ->comment('FK to medicines.code');
            $table->string('medicine_name', 200);
            $table->string('strength', 60)->nullable();
            $table->string('form', 60)->nullable()->comment('tablet | capsule | syrup …');
            $table->string('method', 120)->nullable()->comment('Route of administration');
            $table->string('unit', 40)->nullable();
            $table->decimal('morning', 4, 2)->default(0);
            $table->decimal('afternoon', 4, 2)->default(0);
            $table->decimal('evening', 4, 2)->default(0);
            $table->decimal('night', 4, 2)->default(0);
            $table->unsignedSmallInteger('days')->default(0);
            $table->string('interval', 60)->nullable();
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('prescription_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescription_medications');
        Schema::dropIfExists('prescriptions');
    }
};
