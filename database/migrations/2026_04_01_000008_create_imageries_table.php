<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Imagery / radiology module.
 * Mirrors the Laboratories pattern: request header + result rows.
 * Images are stored as JSON arrays of storage paths / URLs.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('imageries', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('patient_code');
            $table->string('visit_code')->nullable();
            $table->string('encounter_code')->nullable();

            // X-ray | Ultrasound | CT | MRI | ECG | Endoscopy
            $table->string('category', 80)->nullable();

            $table->dateTime('requested_at');
            $table->string('requested_by', 120);
            $table->string('title')->nullable();

            $table->dateTime('collected_at')->nullable();
            $table->string('collected_by', 120)->nullable();

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_code')->references('code')->on('patients')->restrictOnDelete();
            $table->foreign('visit_code')->references('code')->on('visits')->nullOnDelete();

            $table->index(['patient_code', 'visit_code']);
            $table->index('encounter_code');
        });

        Schema::create('imagery_results', function (Blueprint $table) {
            $table->id();
            $table->string('request_code');
            $table->string('name', 120)->comment('e.g. Chest X-Ray PA view');
            $table->string('category', 80)->nullable();

            // JSON array of storage paths: ["images/clinic1/img001.jpg", ...]
            $table->json('images')->nullable();

            $table->text('result')->nullable()
                  ->comment('Free-text radiologist findings');
            $table->text('conclusion')->nullable()
                  ->comment('Summary / impression');

            $table->string('value_type', 60)->nullable();

            $table->dateTime('verified_at')->nullable();
            $table->string('verified_by', 120)->nullable();
            $table->dateTime('recorded_at')->nullable();
            $table->string('recorded_by', 120)->nullable();

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('request_code')
                  ->references('code')->on('imageries')
                  ->cascadeOnDelete();

            $table->index('request_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imagery_results');
        Schema::dropIfExists('imageries');
    }
};
