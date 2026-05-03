<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Billing and payment tables.
 *
 * Includes:
 *   - invoices           (invoice header with lifecycle status)
 *   - invoice_services   (service line items)
 *   - invoice_medications (medication line items)
 *   - payments           (payment collection records)
 *
 * Default currency: USD ($) — configurable per clinic via clinic_settings.
 */
return new class extends Migration {

    public function up(): void
    {
        // ── Invoices ──────────────────────────────────────────────────────────
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30)->unique();
            $table->string('patient_code', 30);
            $table->string('visit_code', 30)->nullable();
            $table->string('encounter_code', 30)->nullable();
            $table->string('status', 20)->default('draft')
                ->comment('draft | issued | paid | voided');
            $table->string('payment_type', 10)->nullable()
                ->comment('CASH | CARD | INSURANCE | MIXED');
            $table->date('invoice_date')->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount_total', 14, 2)->default(0);
            $table->decimal('tax_total', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->string('currency', 10)->default('USD')
                ->comment('ISO 4217 — default USD ($)');
            $table->string('cashier', 120)->nullable();
            $table->string('insurance_provider', 120)->nullable();
            $table->string('claim_number', 60)->nullable();
            $table->string('print_template_code', 40)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['clinic_id', 'status']);
            $table->index('patient_code');
        });

        // ── Invoice Services (service line items) ─────────────────────────────
        Schema::create('invoice_services', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_code', 30);
            $table->string('service_code', 30)->nullable();
            $table->string('service_name', 200);
            $table->string('service_category', 80)->nullable();
            $table->decimal('price', 14, 2)->default(0);
            $table->decimal('payment', 14, 2)->default(0);
            $table->decimal('paid', 14, 2)->default(0);
            $table->string('discount_type', 20)->nullable()
                ->comment('percentage | fixed');
            $table->decimal('discount', 14, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('invoice_code');
        });

        // ── Invoice Medications (medication line items) ───────────────────────
        Schema::create('invoice_medications', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_code', 30);
            $table->unsignedBigInteger('medicine_id')->nullable();
            $table->string('medicine_code', 30)->nullable();
            $table->string('medicine_name', 200);
            $table->decimal('quantity', 10, 2)->default(0);
            $table->decimal('price', 14, 2)->default(0);
            $table->decimal('payment', 14, 2)->default(0);
            $table->decimal('paid', 14, 2)->default(0);
            $table->string('discount_type', 20)->nullable();
            $table->decimal('discount', 14, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('invoice_code');
        });

        // ── Payments ──────────────────────────────────────────────────────────
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30)->unique();
            $table->string('invoice_code', 30);
            $table->string('patient_code', 30);
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('method', 20)->default('CASH')
                ->comment('CASH | CARD | TRANSFER | INSURANCE');
            $table->string('reference', 80)->nullable()
                ->comment('Card / transfer reference number');
            $table->text('note')->nullable();
            $table->string('collected_by', 120)->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['clinic_id', 'invoice_code']);
            $table->index('patient_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_medications');
        Schema::dropIfExists('invoice_services');
        Schema::dropIfExists('invoices');
    }
};
