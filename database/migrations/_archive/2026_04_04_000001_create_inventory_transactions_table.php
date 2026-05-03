<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Inventory transaction log.
 * Every stock deduction (invoice confirm) writes here.
 * stock_movements already handles manual stock-in/out.
 * This table links dispensing to specific invoices.
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('inventory_transactions')) {
            Schema::create('inventory_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
                $table->foreignId('medicine_id')->constrained('medicines')->restrictOnDelete();
                $table->string('medicine_code', 30);
                $table->string('medicine_name', 120);
                $table->string('visit_code', 30)->nullable()->index();
                $table->string('invoice_code', 30)->nullable()->index();
                $table->string('patient_code', 30)->nullable();
                $table->enum('type', ['dispense','return','adjustment'])->default('dispense');
                $table->integer('quantity');          // negative = dispensed out
                $table->integer('stock_before');
                $table->integer('stock_after');
                $table->decimal('unit_price', 14, 2)->default(0);
                $table->decimal('total_price', 14, 2)->default(0);
                $table->string('note', 255)->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->index(['clinic_id', 'created_at']);
                $table->index(['clinic_id', 'medicine_id']);
            });
        }

        // Add low_stock_notified flag to medicines if missing
        if (Schema::hasTable('medicines') && !Schema::hasColumn('medicines', 'low_stock_notified')) {
            Schema::table('medicines', function (Blueprint $table) {
                $table->boolean('low_stock_notified')->default(false)->after('stock_alert');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
