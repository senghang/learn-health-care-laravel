<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Print templates for prescription, invoice, referral letter, discharge summary.
 *
 * The content column stores a Blade-compatible HTML string with placeholders:
 *   {{ $patient->full_name }}  {{ $visit->code }}  {{ $invoice->total }}  etc.
 * The template is rendered server-side and returned as a printable HTML page.
 *
 * type values:
 *   prescription     — medication list with dosing schedule
 *   invoice          — service + medication charges
 *   referral_letter  — outbound referral form
 *   discharge_summary — IPD discharge document
 *   lab_result       — laboratory result sheet
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('print_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();

            $table->string('code', 40)->unique();

            // prescription | invoice | referral_letter | discharge_summary | lab_result
            $table->string('type', 40);

            $table->string('name');                      // human label
            $table->char('locale', 5)->default('km')     // km | en | all
                  ->comment('km | en | all');

            $table->longText('content');                 // Blade HTML template

            $table->boolean('is_default')->default(false)
                  ->comment('Fallback when no locale match found');
            $table->boolean('is_active')->default(true);

            $table->string('paper_size', 10)->default('A5')
                  ->comment('A4 | A5 | Letter');
            $table->string('orientation', 10)->default('portrait')
                  ->comment('portrait | landscape');

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['clinic_id', 'type', 'locale']);
        });

        // ── patch prescriptions and invoices with template FK ─────────────────
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->string('print_template_code', 40)->nullable()->after('title');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('print_template_code', 40)->nullable()->after('cashier');
            // Billing lifecycle: draft | issued | paid | voided
            $table->string('status', 20)->default('draft')->after('print_template_code');
            $table->foreignId('clinic_id')->nullable()->after('id')
                  ->constrained('clinics')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['clinic_id']);
            $table->dropColumn(['print_template_code','status','clinic_id']);
        });
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropColumn('print_template_code');
        });
        Schema::dropIfExists('print_templates');
    }
};
