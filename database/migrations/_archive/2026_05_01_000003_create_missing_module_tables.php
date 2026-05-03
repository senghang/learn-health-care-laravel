<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ADDITIVE ONLY — creates 4 missing tables:
 *   1. user_roles       — many-to-many pivot (extends existing users.role_id)
 *   2. dispenses        — pharmacy dispensing log per prescription
 *   3. inpatient_medications — IPD medication administration records
 *   4. stock_balances   — denormalized current stock snapshot per medicine
 *
 * NO existing tables are modified.
 */
return new class extends Migration {
    public function up(): void
    {
        // ══════════════════════════════════════════════════════════════════════
        // 1. user_roles — many-to-many pivot for RBAC
        // ══════════════════════════════════════════════════════════════════════
        // Note: existing system uses users.role_id (single role).
        // This pivot enables FUTURE multi-role support without breaking
        // the current single-role flow. Both can coexist.
        if (!Schema::hasTable('user_roles')) {
            Schema::create('user_roles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('role_id')->constrained()->cascadeOnDelete();
                $table->timestamp('assigned_at')->useCurrent();
                $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();

                $table->unique(['user_id', 'role_id']);
                $table->index('role_id');
            });
        }

        // ══════════════════════════════════════════════════════════════════════
        // 2. dispenses — pharmacy dispensing log
        // ══════════════════════════════════════════════════════════════════════
        // Each row = one medicine dispensed for one prescription.
        // Links to: prescription, medicine, patient, visit.
        // Triggers stock deduction via StockService.
        if (!Schema::hasTable('dispenses')) {
            Schema::create('dispenses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
                $table->string('code', 30)->unique();

                // What was dispensed
                $table->string('prescription_code', 30)->index();
                $table->foreignId('medicine_id')->constrained('medicines')->restrictOnDelete();
                $table->string('medicine_name', 120)->comment('Snapshot at dispense time');
                $table->integer('quantity');
                $table->string('batch_no', 60)->nullable();
                $table->date('expiry_date')->nullable();

                // Who / when
                $table->string('patient_code', 30)->index();
                $table->string('visit_code', 30)->nullable()->index();
                $table->string('dispensed_by', 120)->nullable();
                $table->timestamp('dispensed_at')->nullable();

                // Pharmacy workflow
                $table->string('status', 20)->default('dispensed')
                      ->comment('dispensed|returned|cancelled');
                $table->boolean('counseling_done')->default(false);
                $table->text('pharmacist_notes')->nullable();

                // Audit
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['clinic_id', 'dispensed_at']);
                $table->index(['clinic_id', 'prescription_code']);

                $table->foreign('prescription_code')->references('code')->on('prescriptions')->restrictOnDelete();
                $table->foreign('patient_code')->references('code')->on('patients')->restrictOnDelete();
            });
        }

        // ══════════════════════════════════════════════════════════════════════
        // 3. inpatient_medications — IPD medication administration
        // ══════════════════════════════════════════════════════════════════════
        // Tracks every medication dose given to an admitted patient.
        // Different from prescription_medications (OPD one-time dispensing)
        // and from dispenses (pharmacy fulfillment tracking).
        if (!Schema::hasTable('inpatient_medications')) {
            Schema::create('inpatient_medications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
                $table->string('code', 30)->unique();

                // Context
                $table->string('visit_code', 30)->index();
                $table->string('patient_code', 30)->index();
                $table->string('admission_code', 30)->nullable()->index()
                      ->comment('FK to admissions table if exists');

                // Medication
                $table->foreignId('medicine_id')->constrained('medicines')->restrictOnDelete();
                $table->string('medicine_name', 120);
                $table->string('dosage', 60)->nullable()->comment('e.g. 500mg');
                $table->string('route', 40)->nullable()->comment('Oral|IV|IM|SC|Topical');
                $table->string('frequency', 60)->nullable()->comment('e.g. TDS, BD, QID, STAT');
                $table->decimal('quantity', 10, 2)->default(1);

                // Schedule
                $table->dateTime('start_date')->nullable();
                $table->dateTime('end_date')->nullable();
                $table->string('status', 20)->default('active')
                      ->comment('active|completed|discontinued|held');

                // Administration
                $table->string('prescribed_by', 120)->nullable();
                $table->string('administered_by', 120)->nullable();
                $table->dateTime('administered_at')->nullable();
                $table->text('notes')->nullable();

                // Audit
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['clinic_id', 'status']);
                $table->index(['clinic_id', 'visit_code']);

                $table->foreign('visit_code')->references('code')->on('visits')->restrictOnDelete();
                $table->foreign('patient_code')->references('code')->on('patients')->restrictOnDelete();
            });
        }

        // ══════════════════════════════════════════════════════════════════════
        // 4. stock_balances — denormalized current stock per medicine per clinic
        // ══════════════════════════════════════════════════════════════════════
        // Provides O(1) stock lookups without aggregating stock_movements.
        // Updated by StockService on every movement.
        // medicines.stock column is the source of truth; this table adds
        // audit trail and per-batch tracking for FIFO/FEFO.
        if (!Schema::hasTable('stock_balances')) {
            Schema::create('stock_balances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
                $table->foreignId('medicine_id')->constrained('medicines')->cascadeOnDelete();

                $table->integer('quantity_on_hand')->default(0);
                $table->integer('quantity_reserved')->default(0)->comment('Allocated but not yet dispensed');
                $table->integer('quantity_available')->storedAs('quantity_on_hand - quantity_reserved');
                $table->integer('reorder_level')->default(0);
                $table->decimal('average_cost', 14, 2)->default(0)->comment('Weighted average unit cost');
                $table->decimal('total_value', 14, 2)->default(0)->comment('quantity_on_hand * average_cost');

                $table->timestamp('last_movement_at')->nullable();
                $table->timestamp('last_counted_at')->nullable()->comment('Last physical count date');
                $table->timestamps();

                $table->unique(['clinic_id', 'medicine_id']);
                $table->index('clinic_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_balances');
        Schema::dropIfExists('inpatient_medications');
        Schema::dropIfExists('dispenses');
        Schema::dropIfExists('user_roles');
    }
};
