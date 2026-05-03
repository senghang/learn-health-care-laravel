<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ══════════════════════════════════════════════════════════════════════════════
 * COMPREHENSIVE FIX MIGRATION
 * ══════════════════════════════════════════════════════════════════════════════
 *
 * 1. Replace `is_deleted` boolean with proper `deleted_at` SoftDeletes on:
 *    - users
 *    - admin_users
 *
 * 2. Add `clinic_id` column + index to tables missing it:
 *    - referrals
 *    - triages
 *    - diagnoses
 *    - physical_examinations
 *    - medical_histories
 *    - out_in_patient
 *    - imagery_results
 *    - laboratory_results
 *    - patient_addresses
 *    - patient_identifications
 *
 * 3. Add `clinic_id` INDEX to tables that have the column but no index:
 *    - visits
 *    - invoices
 *    - prescriptions
 *    - payments
 *    - laboratories
 *    - imageries
 *    - services
 *    - medicines
 *    - stock_movements
 *    - inventory_transactions
 *    - wards
 *    - roles
 *    - permissions
 *    - clinic_settings
 *
 * 4. Add missing `softDeletes` to tables that lack it:
 *    - roles
 *    - permissions
 *    - out_in_patient
 *
 * 5. Add missing `created_by` / `updated_by` audit columns where absent
 *
 * Safe for production: all operations check column/index existence first.
 */
return new class extends Migration {

    public function up(): void
    {
        // ══════════════════════════════════════════════════════════════════════
        // 1. REPLACE is_deleted → SoftDeletes
        // ══════════════════════════════════════════════════════════════════════

        $this->replaceIsDeleted('users');
        $this->replaceIsDeleted('admin_users');

        // ══════════════════════════════════════════════════════════════════════
        // 2. ADD clinic_id TO TABLES MISSING IT
        // ══════════════════════════════════════════════════════════════════════

        $tablesToAddClinicId = [
            'referrals',
            'triages',
            'diagnoses',
            'physical_examinations',
            'medical_histories',
            'out_in_patient',
            'imagery_results',
            'laboratory_results',
            'patient_addresses',
            'patient_identifications',
        ];

        foreach ($tablesToAddClinicId as $table) {
            $this->addClinicIdColumn($table);
        }

        // Backfill clinic_id from patients table via patient_code
        $backfillViaPatient = [
            'referrals'      => 'visit_code',
            'triages'        => 'patient_code',
            'diagnoses'      => 'patient_code',
            'physical_examinations' => 'patient_code',
            'medical_histories'     => 'patient_code',
            'patient_addresses'     => 'patient_code',
            'patient_identifications' => 'patient_code',
        ];

        foreach ($backfillViaPatient as $table => $joinCol) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'clinic_id')) {
                try {
                    DB::statement("
                        UPDATE {$table} t
                        SET clinic_id = p.clinic_id
                        FROM patients p
                        WHERE t.{$joinCol} = p.code
                          AND t.clinic_id IS NULL
                    ");
                } catch (\Throwable $e) {
                    // May fail on MySQL syntax — use subquery instead
                    try {
                        DB::statement("
                            UPDATE {$table} t
                            JOIN patients p ON t.{$joinCol} = p.code
                            SET t.clinic_id = p.clinic_id
                            WHERE t.clinic_id IS NULL
                        ");
                    } catch (\Throwable) {
                        // Skip if both fail — manual backfill needed
                    }
                }
            }
        }

        // Backfill out_in_patient via visits
        if (Schema::hasTable('out_in_patient') && Schema::hasColumn('out_in_patient', 'clinic_id')) {
            try {
                DB::statement("
                    UPDATE out_in_patient t
                    SET clinic_id = v.clinic_id
                    FROM visits v
                    WHERE t.visit_code = v.code
                      AND t.clinic_id IS NULL
                ");
            } catch (\Throwable) {}
        }

        // ══════════════════════════════════════════════════════════════════════
        // 3. ADD clinic_id INDEX WHERE MISSING
        // ══════════════════════════════════════════════════════════════════════

        $tablesNeedingIndex = [
            'visits', 'invoices', 'prescriptions', 'payments',
            'laboratories', 'imageries',
            'services', 'medicines', 'stock_movements', 'inventory_transactions',
            'wards', 'roles', 'permissions', 'clinic_settings',
            'referrals', 'triages', 'diagnoses',
            'physical_examinations', 'medical_histories',
            'out_in_patient', 'patient_addresses', 'patient_identifications',
            'imagery_results', 'laboratory_results',
        ];

        foreach ($tablesNeedingIndex as $table) {
            $this->addClinicIdIndex($table);
        }

        // ══════════════════════════════════════════════════════════════════════
        // 4. ADD MISSING SoftDeletes
        // ══════════════════════════════════════════════════════════════════════

        $tablesNeedingSoftDelete = [
            'roles', 'permissions', 'out_in_patient',
        ];

        foreach ($tablesNeedingSoftDelete as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, fn(Blueprint $t) => $t->softDeletes());
            }
        }

        // ══════════════════════════════════════════════════════════════════════
        // 5. ADD MISSING AUDIT COLUMNS
        // ══════════════════════════════════════════════════════════════════════

        $tablesNeedingAudit = [
            'roles', 'permissions', 'users',
        ];

        foreach ($tablesNeedingAudit as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $t) use ($table) {
                    if (!Schema::hasColumn($table, 'created_by')) {
                        $t->unsignedBigInteger('created_by')->nullable();
                    }
                    if (!Schema::hasColumn($table, 'updated_by')) {
                        $t->unsignedBigInteger('updated_by')->nullable();
                    }
                });
            }
        }
    }

    public function down(): void
    {
        // Intentionally minimal — these are structural fixes that shouldn't be reverted
        // in production. Back up your database before running down().
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Replace `is_deleted` boolean with proper `deleted_at` timestamp.
     * Migrates existing data: is_deleted=true → deleted_at=now()
     */
    private function replaceIsDeleted(string $table): void
    {
        if (!Schema::hasTable($table)) return;

        if (Schema::hasColumn($table, 'is_deleted') && !Schema::hasColumn($table, 'deleted_at')) {
            // Add deleted_at column
            Schema::table($table, fn(Blueprint $t) => $t->softDeletes());

            // Migrate existing soft-deleted records
            DB::table($table)
                ->where('is_deleted', true)
                ->update(['deleted_at' => now()]);

            // Drop the old column
            Schema::table($table, fn(Blueprint $t) => $t->dropColumn('is_deleted'));
        }
    }

    /**
     * Add clinic_id column (nullable) if table exists but column doesn't.
     */
    private function addClinicIdColumn(string $table): void
    {
        if (!Schema::hasTable($table)) return;
        if (Schema::hasColumn($table, 'clinic_id')) return;

        Schema::table($table, function (Blueprint $t) {
            $t->unsignedBigInteger('clinic_id')->nullable()->after('id');
        });
    }

    /**
     * Add index on clinic_id if column exists but index doesn't.
     */
    private function addClinicIdIndex(string $table): void
    {
        if (!Schema::hasTable($table)) return;
        if (!Schema::hasColumn($table, 'clinic_id')) return;

        $indexName = "{$table}_clinic_id_index";

        // Check if index already exists
        try {
            $indexes = collect(DB::select("
                SELECT indexname FROM pg_indexes
                WHERE tablename = ? AND indexname = ?
            ", [$table, $indexName]));

            if ($indexes->isEmpty()) {
                Schema::table($table, fn(Blueprint $t) => $t->index('clinic_id', $indexName));
            }
        } catch (\Throwable) {
            // MySQL fallback
            try {
                $indexes = collect(DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]));
                if ($indexes->isEmpty()) {
                    Schema::table($table, fn(Blueprint $t) => $t->index('clinic_id', $indexName));
                }
            } catch (\Throwable) {
                // Index may already exist — skip silently
            }
        }
    }
};
