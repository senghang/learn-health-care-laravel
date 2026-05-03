<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('health_facility_code', 30)->nullable();
            $table->string('patient_code');
            $table->string('surname');
            $table->string('name');
            $table->string('visit_type', 3)->comment('OPD or IPD');
            $table->string('admission_type', 80)->nullable();
            $table->string('discharge_type', 80)->nullable();
            $table->string('visit_outcome', 80)->nullable()->comment('Future required field');
            $table->dateTime('admitted_at')->nullable();
            $table->dateTime('discharged_at')->nullable();
            $table->dateTime('followup_at')->nullable();

            // Workflow progress
            $table->json('done_steps')->nullable();
            $table->json('skipped_steps')->nullable();

            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_code')
                ->references('code')
                ->on('patients')
                ->restrictOnDelete();

            $table->index('patient_code');
            $table->index('admitted_at');
            $table->index('visit_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
