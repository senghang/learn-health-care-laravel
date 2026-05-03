<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Laboratory and Medical Imaging tables.
 *
 * Includes:
 *   - laboratories        (lab request header)
 *   - laboratory_results  (individual test results)
 *   - imageries           (imaging request header)
 *   - imagery_results     (radiology / ultrasound findings)
 *   - lab_test_catalogs   (reusable test catalog with reference ranges)
 */
return new class extends Migration {

    public function up(): void
    {
        // ── Laboratories (request headers) ────────────────────────────────────
        Schema::create('laboratories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30)->unique();
            $table->string('patient_code', 30);
            $table->string('visit_code', 30)->nullable();
            $table->string('encounter_code', 30)->nullable();
            $table->string('status', 20)->default('requested')
                ->comment('requested | collected | processing | completed | cancelled');
            $table->string('urgency', 20)->default('normal')
                ->comment('normal | urgent | stat');
            $table->string('category', 80)->nullable()
                ->comment('hematology | biochemistry | microbiology | serology …');
            $table->dateTime('requested_at')->nullable();
            $table->string('requested_by', 120)->nullable();
            $table->string('title')->nullable();
            $table->dateTime('collected_at')->nullable();
            $table->string('collected_by', 120)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['clinic_id', 'status']);
            $table->index('patient_code');
        });

        // ── Laboratory Results ────────────────────────────────────────────────
        Schema::create('laboratory_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->nullable()->constrained()->nullOnDelete();
            $table->string('request_code', 30);
            $table->string('name', 120);
            $table->string('category', 60)->nullable();
            $table->string('value', 80)->nullable();
            $table->string('value_type', 40)->nullable();
            $table->string('value_unit', 40)->nullable();
            $table->string('reference_range', 80)->nullable();
            $table->string('interpretation', 60)->nullable()
                ->comment('normal | high | low | critical');
            $table->string('flag', 10)->nullable()
                ->comment('H | L | HH | LL | A (abnormal)');
            $table->dateTime('verified_at')->nullable();
            $table->string('verified_by', 120)->nullable();
            $table->dateTime('recorded_at')->nullable();
            $table->string('recorded_by', 120)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['clinic_id', 'request_code']);
        });

        // ── Imageries (request headers) ───────────────────────────────────────
        Schema::create('imageries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30)->unique();
            $table->string('patient_code', 30);
            $table->string('visit_code', 30)->nullable();
            $table->string('encounter_code', 30)->nullable();
            $table->string('status', 20)->default('requested')
                ->comment('requested | in_progress | completed | cancelled');
            $table->string('urgency', 20)->default('normal')
                ->comment('normal | urgent | stat');
            $table->string('category', 80)->nullable()
                ->comment('x-ray | ultrasound | ct_scan | mri | endoscopy …');
            $table->dateTime('requested_at')->nullable();
            $table->string('requested_by', 120)->nullable();
            $table->string('title')->nullable();
            $table->dateTime('collected_at')->nullable();
            $table->string('collected_by', 120)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['clinic_id', 'status']);
            $table->index('patient_code');
        });

        // ── Imagery Results ───────────────────────────────────────────────────
        Schema::create('imagery_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->nullable()->constrained()->nullOnDelete();
            $table->string('request_code', 30);
            $table->string('name', 120);
            $table->string('category', 60)->nullable();
            $table->json('images')->nullable()->comment('Array of file paths');
            $table->text('result')->nullable();
            $table->text('conclusion')->nullable();
            $table->string('value_type', 40)->nullable();
            $table->dateTime('verified_at')->nullable();
            $table->string('verified_by', 120)->nullable();
            $table->dateTime('recorded_at')->nullable();
            $table->string('recorded_by', 120)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['clinic_id', 'request_code']);
        });

        // ── Lab Test Catalogs ─────────────────────────────────────────────────
        Schema::create('lab_test_catalogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30)->unique();
            $table->string('name', 120);
            $table->string('name_kh', 120)->nullable();
            $table->string('category', 60)->nullable();
            $table->string('sample_type', 60)->nullable()
                ->comment('blood | urine | stool | swab …');
            $table->string('unit', 40)->nullable();
            $table->decimal('normal_range_min', 10, 2)->nullable();
            $table->decimal('normal_range_max', 10, 2)->nullable();
            $table->string('normal_range_text', 120)->nullable();
            $table->decimal('price', 14, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['clinic_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_test_catalogs');
        Schema::dropIfExists('imagery_results');
        Schema::dropIfExists('imageries');
        Schema::dropIfExists('laboratory_results');
        Schema::dropIfExists('laboratories');
    }
};
