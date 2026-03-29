<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Employees — HR records linked to user accounts.
 * Replaces string-based references (prescribed_by, cashier, recorded_by)
 * with proper FK relationships.
 * ADDITIVE: new table, no existing tables modified.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30)->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();

            $table->string('surname', 80);
            $table->string('name', 80);
            $table->string('name_kh', 80)->nullable();
            $table->char('gender', 1)->nullable()->comment('M or F');
            $table->date('birthdate')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 120)->nullable();

            $table->string('employee_type', 40)->comment('doctor|nurse|pharmacist|lab_tech|admin|cashier');
            $table->string('specialization', 120)->nullable();
            $table->string('license_number', 60)->nullable();
            $table->date('hire_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status', 20)->default('active')->comment('active|inactive|on_leave|terminated');
            $table->string('photo_path')->nullable();

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['clinic_id', 'employee_type']);
            $table->index(['clinic_id', 'department_id']);
            $table->index(['clinic_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
