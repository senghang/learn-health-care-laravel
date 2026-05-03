<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('patient_code');
            $table->string('visit_code')->nullable();
            $table->string('encounter_code')->nullable();
            $table->dateTime('prescribed_at')->nullable();
            $table->string('prescribed_by', 120)->nullable();
            $table->string('title')->nullable();

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_code')
                ->references('code')->on('patients')
                ->restrictOnDelete();
            $table->foreign('visit_code')
                ->references('code')->on('visits')
                ->nullOnDelete();

            $table->index(['visit_code', 'encounter_code']);
        });

        Schema::create('prescription_medications', function (Blueprint $table) {
            $table->id();
            $table->string('prescription_code');
            $table->string('medication_code', 60)->nullable()
                ->comment('Drug master code');
            $table->string('medicine_name', 200);
            $table->string('strength', 60)->nullable()
                ->comment('e.g. 500mg');
            $table->string('form', 60)->nullable()
                ->comment('Tablet, Capsule, Syrup …');
            $table->string('method', 80)->nullable()
                ->comment('Oral, IV, IM …');
            $table->string('unit', 40)->nullable();

            $table->decimal('morning', 4, 2)->nullable();
            $table->decimal('afternoon', 4, 2)->nullable();
            $table->decimal('evening', 4, 2)->nullable();
            $table->decimal('night', 4, 2)->nullable();

            $table->unsignedSmallInteger('days')->nullable();
            $table->string('interval', 40)->nullable()
                ->comment('e.g. every 8 hours — use when time-of-day not applicable');
            $table->text('note')->nullable();

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('prescription_code')
                ->references('code')->on('prescriptions')
                ->cascadeOnDelete();
            $table->index('prescription_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescription_medications');
        Schema::dropIfExists('prescriptions');
    }
};
