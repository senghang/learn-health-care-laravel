<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('laboratories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('patient_code');
            $table->string('visit_code')->nullable();
            $table->string('encounter_code')->nullable();
            $table->dateTime('requested_at');
            $table->string('requested_by', 120);
            $table->string('title')->nullable();
            $table->dateTime('collected_at')->nullable();
            $table->string('collected_by', 120)->nullable();
            $table->timestamps();

            $table->foreign('patient_code')
                ->references('code')->on('patients')
                ->restrictOnDelete();

            $table->foreign('visit_code')
                ->references('code')->on('visits')
                ->nullOnDelete();

            $table->index('patient_code');
            $table->index('visit_code');
            $table->index('encounter_code');

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->softDeletes();
        });

        Schema::create('laboratory_results', function (Blueprint $table) {

            $table->id();

            $table->string('request_code');
            $table->string('name', 120)->comment('Malaria Blood Smear');
            $table->string('category', 80)->nullable();
            $table->text('value')->nullable();
            $table->string('value_type', 60)->nullable();
            $table->string('value_unit', 30)->nullable();
            $table->string('reference_range', 80)->nullable();
            $table->string('interpretation', 80)->nullable();
            $table->dateTime('verified_at')->nullable();
            $table->string('verified_by', 120)->nullable();
            $table->dateTime('recorded_at')->nullable();
            $table->string('recorded_by', 120)->nullable();

            $table->timestamps();

            $table->foreign('request_code')
                ->references('code')
                ->on('laboratories')
                ->cascadeOnDelete();

            $table->index('request_code');

            $table->unique(['request_code', 'name']);

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laboratory_results');
        Schema::dropIfExists('laboratories');
    }
};
