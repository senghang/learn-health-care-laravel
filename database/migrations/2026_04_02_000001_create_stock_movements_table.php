<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('medicine_id');
            $table->string('medicine_code', 30);
            $table->string('medicine_name', 120);
            $table->enum('type', ['in', 'out', 'adjustment', 'expired', 'return']);
            $table->integer('quantity');          // positive = in, negative = out
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->string('reference', 80)->nullable();  // PO no / visit code / etc
            $table->string('supplier', 120)->nullable();
            $table->decimal('unit_cost', 14, 2)->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('batch_no', 60)->nullable();
            $table->text('note')->nullable();
            $table->string('recorded_by', 120)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('medicine_id')->references('id')->on('medicines')->restrictOnDelete();
            $table->index(['clinic_id', 'type']);
            $table->index(['clinic_id', 'medicine_id']);
            $table->index('created_at');
        });

        // Add generic_name column to medicines if not present
        if (!Schema::hasColumn('medicines', 'generic_name')) {
            Schema::table('medicines', function (Blueprint $table) {
                $table->string('generic_name', 120)->nullable()->after('name_en');
            });
        }

        // Add category column to medicines if not present
        if (!Schema::hasColumn('medicines', 'category')) {
            Schema::table('medicines', function (Blueprint $table) {
                $table->string('category', 60)->nullable()->after('generic_name');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
