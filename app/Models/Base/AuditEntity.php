<?php

namespace App\Models\Base;

use Illuminate\Database\Schema\Blueprint;

/**
 * AuditEntity — blueprint helper + model trait for standardized audit columns.
 *
 * ═══════════════════════════════════════════════════════════════════════════════
 * PROBLEM: Audit columns are inconsistently defined across migrations:
 *   - Some use foreignId('created_by')->constrained('users')
 *   - Some use foreignId('created_by')->nullable()
 *   - Some miss updated_by entirely
 *   - Some tables have no audit columns at all
 *   - SoftDeletes is missing on several tables
 *   - clinic_id indexing is inconsistent
 *
 * SOLUTION: One reusable helper that adds ALL standard columns consistently.
 * ═══════════════════════════════════════════════════════════════════════════════
 *
 * USAGE IN MIGRATIONS:
 *
 *   use App\Models\Base\AuditEntity;
 *
 *   Schema::create('employees', function (Blueprint $table) {
 *       $table->id();
 *       $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
 *       // ... business columns ...
 *
 *       AuditEntity::columns($table);     // adds created_by, updated_by, timestamps, softDeletes
 *       AuditEntity::clinicIndex($table); // adds index on clinic_id
 *   });
 *
 * USAGE IN MODELS:
 *
 *   class EmployeeModel extends Model {
 *       use SoftDeletes, Auditable, ClinicScope;  // standard trait combo
 *   }
 *
 * ═══════════════════════════════════════════════════════════════════════════════
 * WHAT AuditEntity::columns() ADDS:
 *
 *   created_by  — FK to users, nullable, nullOnDelete
 *   updated_by  — FK to users, nullable, nullOnDelete
 *   timestamps  — created_at, updated_at
 *   softDeletes — deleted_at
 *
 * WHAT AuditEntity::clinicIndex() ADDS:
 *
 *   INDEX on clinic_id (required for ClinicScope performance)
 * ═══════════════════════════════════════════════════════════════════════════════
 */
class AuditEntity
{
    /**
     * Add standard audit columns to a migration blueprint.
     *
     * Call this LAST in your Schema::create() callback, after all business columns.
     */
    public static function columns(Blueprint $table): void
    {
        $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamps();
        $table->softDeletes();
    }

    /**
     * Add clinic_id index for ClinicScope performance.
     *
     * Call after AuditEntity::columns() or anywhere the table has a clinic_id column.
     */
    public static function clinicIndex(Blueprint $table, string $tableName = ''): void
    {
        $table->index('clinic_id');
    }

    /**
     * Add clinic_id FK + index in one call (for tables that don't have it yet).
     */
    public static function clinicColumn(Blueprint $table): void
    {
        $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
        $table->index('clinic_id');
    }

    /**
     * Full tenant setup: clinic_id FK + index + audit columns.
     * Use for new tables that need everything.
     */
    public static function tenantColumns(Blueprint $table): void
    {
        self::clinicColumn($table);
        self::columns($table);
    }
}
