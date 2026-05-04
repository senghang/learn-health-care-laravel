<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pharmacy and Inventory tables.
 *
 * Includes:
 *   - services               (billable service catalog)
 *   - medicines              (drug catalog with stock tracking)
 *   - suppliers              (medicine/supply vendors)
 *   - pharmacy_dispenses     (OPD prescription dispensing records)
 *   - stock_movements        (all stock in/out/adjustment events)
 *   - stock_balances         (materialized per-medicine stock snapshot)
 *   - inventory_transactions (invoice-linked dispense log)
 */
return new class extends Migration {

    public function up(): void
    {
        // ── Services (billable service catalog) ───────────────────────────────
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30)->unique();
            $table->string('name', 120);
            $table->string('name_kh', 120)->nullable();
            $table->string('name_en', 120)->nullable();
            $table->string('category', 80)->nullable();
            $table->string('service_type', 40)->nullable()
                ->comment('consultation | procedure | lab | imaging | nursing …');
            $table->decimal('price', 14, 2)->default(0);
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->boolean('is_billable')->default(true);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['clinic_id', 'category']);
        });

        // ── Medicines (drug catalog) ───────────────────────────────────────────
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30)->unique();
            $table->string('name', 120);
            $table->string('name_kh', 120)->nullable();
            $table->string('name_en', 120)->nullable();
            $table->string('generic_name', 120)->nullable();
            $table->string('category', 80)->nullable();
            $table->string('form', 60)->nullable()
                ->comment('tablet | capsule | syrup | injection | cream …');
            $table->string('strength', 60)->nullable();
            $table->string('unit', 40)->nullable();
            $table->decimal('price', 14, 2)->default(0);
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('stock_alert')->default(10)
                ->comment('Trigger low-stock notification below this level');
            $table->boolean('low_stock_notified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['clinic_id', 'category']);
            $table->index('name');
        });

        // ── Suppliers ─────────────────────────────────────────────────────────
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30)->unique();
            $table->string('name', 120);
            $table->string('contact_person', 120)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 120)->nullable();
            $table->text('address')->nullable();
            $table->string('tax_id', 40)->nullable();
            $table->string('payment_terms', 60)->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('clinic_id');
        });

        // ── Pharmacy Dispenses (OPD dispensing log) ───────────────────────────
        Schema::create('pharmacy_dispenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30)->unique();
            $table->string('prescription_code', 30)->index();
            $table->foreignId('medicine_id')->constrained('medicines')->restrictOnDelete();
            $table->string('medicine_code', 30)->nullable();
            $table->string('medicine_name', 120);
            $table->string('patient_code', 30)->index();
            $table->string('visit_code', 30)->nullable()->index();
            $table->integer('quantity')->default(0);
            $table->string('status', 20)->default('dispensed');
            $table->string('batch_no', 60)->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('dispensed_by', 120)->nullable();
            $table->timestamp('dispensed_at')->nullable();
            $table->boolean('counseling_done')->default(false);
            $table->text('pharmacist_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['clinic_id', 'dispensed_at']);
        });

        // ── Stock Movements (stock in/out/adjustment ledger) ──────────────────
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained('medicines')->restrictOnDelete();
            $table->string('medicine_code', 30);
            $table->string('medicine_name', 120);
            $table->enum('type', ['in', 'out', 'adjustment', 'expired', 'return'])
                ->comment('in=stock received, out=dispensed, adjustment=manual correction');
            $table->integer('quantity');
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->string('reference', 80)->nullable()
                ->comment('Invoice code / purchase order / prescription code');
            $table->string('supplier', 120)->nullable();
            $table->decimal('unit_cost', 14, 2)->nullable()->default(0);
            $table->date('expiry_date')->nullable();
            $table->string('batch_no', 60)->nullable();
            $table->text('note')->nullable();
            $table->string('recorded_by', 120)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['clinic_id', 'medicine_id']);
            $table->index(['clinic_id', 'type']);
        });

        // ── Stock Balances (materialized snapshot for fast lookups) ───────────
        Schema::create('stock_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained('medicines')->cascadeOnDelete();
            $table->integer('quantity_on_hand')->default(0);
            $table->integer('quantity_reserved')->default(0);
            $table->integer('quantity_available')->default(0)
                ->comment('Synced by StockService: on_hand − reserved');
            $table->unsignedInteger('reorder_level')->default(10);
            $table->decimal('average_cost', 14, 2)->default(0);
            $table->decimal('total_value', 14, 2)->default(0);
            $table->timestamp('last_movement_at')->nullable();
            $table->timestamp('last_counted_at')->nullable();
            $table->timestamps();

            $table->unique(['clinic_id', 'medicine_id']);
            $table->index('clinic_id');
        });

        // ── Inventory Transactions (invoice-linked dispense log) ──────────────
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained('medicines')->restrictOnDelete();
            $table->string('medicine_code', 30);
            $table->string('medicine_name', 120);
            $table->string('visit_code', 30)->nullable()->index();
            $table->string('invoice_code', 30)->nullable()->index();
            $table->string('patient_code', 30)->nullable();
            $table->enum('type', ['dispense', 'return', 'adjustment'])->default('dispense');
            $table->integer('quantity')->comment('Negative = dispensed out');
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->decimal('total_price', 14, 2)->default(0);
            $table->string('note', 255)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['clinic_id', 'created_at']);
            $table->index(['clinic_id', 'medicine_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
        Schema::dropIfExists('stock_balances');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('pharmacy_dispenses');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('medicines');
        Schema::dropIfExists('services');
    }
};
