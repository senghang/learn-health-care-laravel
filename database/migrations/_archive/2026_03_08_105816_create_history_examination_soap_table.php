<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('medical_histories', function (Blueprint $table) {
            $table->id();
            $table->string('patient_code');
            $table->string('visit_code')->nullable();
            $table->string('encounter_code')->nullable();
            $table->string('name', 120)->comment('allergy, chronic_disease, past_surgery …');
            $table->json('value')->nullable()->comment('TEXT[] — list of values');

            // Audit fields
            $this->auditFields($table);

            $table->index(['patient_code', 'name']);
            $table->index(['visit_code', 'encounter_code']);
        });

        Schema::create('physical_examinations', function (Blueprint $table) {
            $table->id();
            $table->string('patient_code');
            $table->string('visit_code')->nullable();
            $table->string('encounter_code')->nullable();
            $table->string('name', 120)->comment('abdomen, chest, skin …');
            $table->text('value')->nullable();
            $table->string('value_type', 60)->nullable();
            $table->string('value_unit', 30)->nullable();

            // Audit fields
            $this->auditFields($table);

            $table->index(['visit_code', 'encounter_code']);
        });

        Schema::create('soaps', function (Blueprint $table) {
            $table->id();
            $table->string('encounter_code');
            $table->text('subjective')->nullable();
            $table->text('objective')->nullable();
            $table->text('assessment')->nullable();
            $table->text('evaluation')->nullable();
            $table->text('plan')->nullable();

            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index('encounter_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soaps');
        Schema::dropIfExists('physical_examinations');
        Schema::dropIfExists('medical_histories');
    }

    /**
     * @param Blueprint $table
     * @return void
     */
    public function auditFields(Blueprint $table): void
    {
        $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        $table->softDeletes();
        $table->timestamps();

        $table->foreign('patient_code')
            ->references('code')->on('patients')
            ->restrictOnDelete();
        $table->foreign('visit_code')
            ->references('code')->on('visits')
            ->nullOnDelete();
    }
};
