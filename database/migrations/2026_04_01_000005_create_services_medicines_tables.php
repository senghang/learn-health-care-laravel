<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Master tables for services and medicines.
 * These feed the invoice line-item autocomplete and the prescription drug picker.
 * Each record is clinic-scoped and carries bilingual names.
 */
return new class extends Migration {
    public function up(): void
    {
        // ── Services master ───────────────────────────────────────────────────
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();

            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('name_kh')->nullable();
            $table->string('name_en')->nullable();

            // consultation | laboratory | imaging | procedure | pharmacy | other
            $table->string('category', 60)->nullable();

            $table->decimal('price', 14, 2)->default(0)->comment('Default price in KHR');
            $table->boolean('is_active')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['clinic_id', 'category']);
        });

        // ── Medicines master ──────────────────────────────────────────────────
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();

            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('name_kh')->nullable();
            $table->string('name_en')->nullable();

            $table->string('generic_name')->nullable();
            // Tablet | Capsule | Syrup | Injection | Cream | Drops | Inhaler
            $table->string('form', 60)->nullable();
            $table->string('strength', 60)->nullable()->comment('e.g. 500mg, 250mg/5ml');
            $table->string('unit', 40)->nullable()->comment('e.g. tablet, vial, bottle');

            $table->decimal('price', 14, 2)->default(0)->comment('Price per unit in KHR');
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('stock_alert')->default(10)
                  ->comment('Alert when stock falls below this');
            $table->boolean('is_active')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['clinic_id', 'form']);
            $table->index('generic_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicines');
        Schema::dropIfExists('services');
    }
};
