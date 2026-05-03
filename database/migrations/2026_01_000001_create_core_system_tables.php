<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Core system tables.
 *
 * Includes:
 *   - clinics            (multi-tenant root)
 *   - clinic_settings    (per-clinic key/value config)
 *   - clinic_code_sequences (atomic auto-code generation)
 *   - audit_logs         (immutable event trail)
 *   - print_templates    (Blade HTML print layouts)
 *   - translations       (polymorphic bilingual fields)
 *   - store_settings     (dropdown/lookup value catalog)
 */
return new class extends Migration {

    public function up(): void
    {
        // ── Clinics ───────────────────────────────────────────────────────────
        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('name_kh')->nullable();
            $table->string('name_en')->nullable();
            $table->string('tagline')->nullable();
            $table->string('logo')->nullable();
            $table->string('subdomain')->unique()->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 120)->nullable();
            $table->text('address')->nullable();
            $table->string('owner_name', 120)->nullable();
            $table->string('owner_number', 30)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('support', 120)->nullable();
            $table->string('plan', 30)->default('standard');
            $table->unsignedSmallInteger('max_users')->default(10);
            $table->char('default_locale', 5)->default('en');
            $table->string('currency', 10)->default('USD')
                ->comment('ISO 4217 — USD | KHR | EUR …');
            $table->boolean('is_active')->default(true);
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
        });

        // ── Clinic Settings ───────────────────────────────────────────────────
        Schema::create('clinic_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->string('key', 100);
            $table->text('value')->nullable();
            $table->char('locale', 5)->nullable()->comment('km | en | null = global');
            $table->timestamps();

            $table->unique(['clinic_id', 'key', 'locale']);
            $table->index('clinic_id');
        });

        // ── Clinic Code Sequences ─────────────────────────────────────────────
        Schema::create('clinic_code_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->string('prefix', 10);
            $table->date('seq_date');
            $table->unsignedInteger('last_seq')->default(0);
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['clinic_id', 'prefix', 'seq_date']);
        });

        // ── Audit Logs ────────────────────────────────────────────────────────
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('model', 80)->comment('e.g. App\\Models\\PatientModel');
            $table->string('model_id', 40)->nullable();
            $table->string('event', 20)->comment('created | updated | deleted | restored');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['clinic_id', 'created_at']);
            $table->index(['model', 'model_id']);
            $table->index('user_id');
        });

        // ── Print Templates ───────────────────────────────────────────────────
        Schema::create('print_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->string('code', 40)->unique();
            $table->string('type', 40)
                ->comment('prescription | invoice | referral_letter | discharge_summary | lab_result');
            $table->string('name');
            $table->char('locale', 5)->default('en')->comment('km | en | all');
            $table->longText('content')->comment('Blade-compatible HTML with {{ $patient->name }} placeholders');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('paper_size', 10)->default('A5')->comment('A4 | A5 | Letter');
            $table->string('orientation', 10)->default('portrait')->comment('portrait | landscape');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['clinic_id', 'type', 'locale']);
        });

        // ── Translations (polymorphic bilingual fields) ───────────────────────
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('model')->comment('Fully-qualified class, e.g. App\\Models\\WardModel');
            $table->unsignedBigInteger('model_id');
            $table->char('locale', 5)->comment('km | en');
            $table->string('field', 80)->comment('e.g. name | description');
            $table->text('value');
            $table->timestamps();

            $table->unique(['model', 'model_id', 'locale', 'field'], 'translations_unique');
            $table->index(['model', 'model_id']);
            $table->index('locale');
        });

        // ── Store Settings (dropdown/lookup catalog) ──────────────────────────
        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('group', 60)->index()
                ->comment('admission_type | discharge_type | payment_method …');
            $table->string('key', 80);
            $table->string('label_en', 120)->nullable();
            $table->string('label_km', 120)->nullable();
            $table->string('value', 255)->nullable();
            $table->string('color', 20)->nullable();
            $table->string('icon', 40)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['clinic_id', 'group', 'key'], 'store_settings_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_settings');
        Schema::dropIfExists('translations');
        Schema::dropIfExists('print_templates');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('clinic_code_sequences');
        Schema::dropIfExists('clinic_settings');
        Schema::dropIfExists('clinics');
    }
};
