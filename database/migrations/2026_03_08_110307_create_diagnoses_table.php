<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('diagnoses', function (Blueprint $table) {
            $table->id();
            $table->string('patient_code');
            $table->string('visit_code')->nullable();
            $table->string('encounter_code')->nullable();
            $table->string('diagnosis_type', 20)
                ->comment('In | Out | Primary | Secondary');
            $table->string('diagnosis_code', 20)->nullable()
                ->comment('ICD-10 code e.g. B50.0');
            $table->string('diagnosis_name', 200)->nullable();
            $table->text('diagnosis_description')->nullable();
            $table->dateTime('diagnosed_at')->nullable();
            $table->string('diagnosed_by', 120)->nullable();
            $table->string('title')->nullable();
            $table->timestamps();

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->softDeletes();

            $table->foreign('patient_code')
                ->references('code')->on('patients')
                ->restrictOnDelete();
            $table->foreign('visit_code')
                ->references('code')->on('visits')
                ->nullOnDelete();

            $table->index(['visit_code', 'diagnosis_type']);
            $table->index('diagnosis_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diagnoses');
    }
};
