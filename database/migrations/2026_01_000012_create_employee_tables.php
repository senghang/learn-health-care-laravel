<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Employee / HR tables.
 *
 * Includes:
 *   - departments   (clinical and administrative departments)
 *   - employees     (staff profiles linked to user accounts)
 */
return new class extends Migration {

    public function up(): void
    {
        // ── Departments ───────────────────────────────────────────────────────
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30)->unique();
            $table->string('name', 120);
            $table->string('name_kh', 120)->nullable();
            $table->unsignedBigInteger('head_employee_id')->nullable()
                ->comment('FK set after employees table exists');
            $table->string('phone', 30)->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('clinic_id');
        });

        // ── Employees ─────────────────────────────────────────────────────────
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30)->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('department_id')->nullable()
                ->constrained('departments')->nullOnDelete();
            $table->string('surname', 80);
            $table->string('name', 80);
            $table->string('name_kh', 120)->nullable();
            $table->char('gender', 1)->nullable()->comment('M | F | O');
            $table->date('birthdate')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 120)->nullable();
            $table->string('employee_type', 40)->nullable()
                ->comment('doctor | nurse | pharmacist | cashier | admin | other');
            $table->string('specialization', 120)->nullable();
            $table->string('license_number', 60)->nullable();
            $table->date('hire_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status', 20)->default('active')
                ->comment('active | inactive | on_leave | terminated');
            $table->string('photo_path')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['clinic_id', 'employee_type']);
            $table->index('user_id');
        });

        // Add FK from departments.head_employee_id → employees.id
        Schema::table('departments', function (Blueprint $table) {
            $table->foreign('head_employee_id')
                ->references('id')->on('employees')
                ->nullOnDelete();
        });

        // Add FK from users.employee_id → employees.id
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('employee_id')
                ->references('id')->on('employees')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
        });
        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['head_employee_id']);
        });
        Schema::dropIfExists('employees');
        Schema::dropIfExists('departments');
    }
};
