<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('triages', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('patient_code');
            $table->string('visit_code')->nullable();
            $table->string('encounter_code')->nullable();
            $table->text('chief_complaint')->nullable();
            $table->decimal('height', 5, 1)->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $this->extracted($table);

            $table->foreign('patient_code')->references('code')->on('patients')->restrictOnDelete();
            $table->foreign('visit_code')->references('code')->on('visits')->nullOnDelete();

            $table->index(['patient_code', 'visit_code', 'encounter_code']);
        });

        Schema::create('vital_signs', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('patient_code');
            $table->string('visit_code')->nullable();
            $table->string('encounter_code')->nullable();

            $this->extracted($table);

            $table->foreign('patient_code')->references('code')->on('patients')->restrictOnDelete();
            $table->foreign('visit_code')->references('code')->on('visits')->nullOnDelete();

            $table->index('encounter_code');
        });

        Schema::create('vital_sign_observations', function (Blueprint $table) {
            $table->id();
            $table->string('vital_sign_code');
            $table->string('name', 60);
            $table->decimal('value', 8, 2)->nullable();
            $table->string('unit', 30)->nullable();

            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('vital_sign_code')
                ->references('code')->on('vital_signs')
                ->cascadeOnDelete();

            $table->index(['vital_sign_code', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vital_sign_observations');
        Schema::dropIfExists('vital_signs');
        Schema::dropIfExists('triages');
    }

    /**
     * @param Blueprint $table
     * @return void
     */
    public function extracted(Blueprint $table): void
    {
        $table->dateTime('recorded_at')->nullable();
        $table->string('recorded_by', 120)->nullable();
        $table->string('title')->nullable();

        // Audit fields
        $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamps();
        $table->softDeletes();
    }
};
