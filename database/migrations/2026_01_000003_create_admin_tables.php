<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Super-admin / platform-level tables.
 *
 * These are separate from the per-clinic RBAC tables so that platform
 * administrators can manage all clinics without being members of any clinic.
 *
 * Includes:
 *   - admin_roles
 *   - admin_users
 *   - admin_permissions
 *   - admin_role_permission
 */
return new class extends Migration {

    public function up(): void
    {
        // ── Admin Roles ───────────────────────────────────────────────────────
        Schema::create('admin_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->string('slug', 80)->unique();
            $table->string('description', 255)->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        // ── Admin Users ───────────────────────────────────────────────────────
        Schema::create('admin_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->foreignId('admin_role_id')->nullable()
                ->constrained('admin_roles')->nullOnDelete();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        // ── Admin Permissions ─────────────────────────────────────────────────
        Schema::create('admin_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 120)->unique();
            $table->string('group', 60)->nullable();
            $table->timestamps();
        });

        // ── Admin Role ↔ Permission (pivot) ───────────────────────────────────
        Schema::create('admin_role_permission', function (Blueprint $table) {
            $table->foreignId('admin_role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admin_permission_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['admin_role_id', 'admin_permission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_role_permission');
        Schema::dropIfExists('admin_permissions');
        Schema::dropIfExists('admin_users');
        Schema::dropIfExists('admin_roles');
    }
};
