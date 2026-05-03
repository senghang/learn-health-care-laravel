<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Departments — organizational units within a clinic.
 * Referenced by employees.department_id and optionally by wards.
 * ADDITIVE: new table, no existing tables modified.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30)->unique();
            $table->string('name', 120);
            $table->string('name_kh', 120)->nullable();
            $table->string('head_employee_id')->nullable()->comment('FK added after employees table exists');
            $table->string('phone', 30)->nullable();
            $table->boolean('is_active')->default(true);

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('clinic_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
