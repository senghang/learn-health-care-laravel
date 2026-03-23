<?php
/**
 * MIGRATION 2: Add clinic_id to clinical tables that are missing it.
 *
 * PROBLEM: visits, invoices, prescriptions, payments did not have clinic_id.
 * This breaks the multi-tenant model because:
 *   - ClinicScope cannot filter without clinic_id column
 *   - Cross-clinic data leakage becomes possible
 *   - Composite unique codes (clinic_id, code) are impossible
 *
 * APPROACH: Safe for production —
 *   1. Add column as nullable
 *   2. Backfill from related patient.clinic_id
 *   3. Add FK constraint
 *   4. Add indexes for query performance
 *   (NOT null constraint added separately after backfill is verified)
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Tables to patch: [table, backfill_join_column] */
    private array $tables = [
        ['visits',        'patient_code'],
        ['invoices',      'patient_code'],
        ['prescriptions', 'patient_code'],
        ['payments',      'patient_code'],
        ['laboratories',  'patient_code'],
    ];

    public function up(): void
    {
        foreach ($this->tables as [$table, $joinCol]) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            if (!Schema::hasColumn($table, 'clinic_id')) {
                // Step 1: Add nullable first (safe for existing data)
                Schema::table($table, function (Blueprint $t) {
                    $t->unsignedBigInteger('clinic_id')
                        ->nullable()
                        ->after('id');
                });

                // Step 2: Backfill from patients table
                DB::statement("
                    UPDATE {$table} t
                    SET clinic_id = p.clinic_id
                    FROM patients p
                    WHERE t.{$joinCol} = p.code
                      AND t.clinic_id IS NULL
                ");

                // Step 3: Add FK (after backfill ensures data integrity)
                Schema::table($table, function (Blueprint $t) {
                    $t->foreign('clinic_id')
                        ->references('id')
                        ->on('clinics')
                        ->restrictOnDelete();
                });
            }

            // Step 4: Composite unique (clinic_id, code) — idempotent
            DB::statement("
                DO \$\$
                BEGIN
                    IF NOT EXISTS (
                        SELECT 1 FROM pg_constraint
                        WHERE conname = 'uq_{$table}_clinic_code'
                    ) THEN
                        ALTER TABLE {$table}
                        ADD CONSTRAINT uq_{$table}_clinic_code
                        UNIQUE (clinic_id, code);
                    END IF;
                END
                \$\$
            ");

            // Step 5: Performance indexes
            DB::statement("
                CREATE INDEX IF NOT EXISTS idx_{$table}_clinic_id
                ON {$table} (clinic_id)
            ");
        }

        // Extra index for visits (most queried table)
        if (Schema::hasTable('visits')) {
            DB::statement("
                CREATE INDEX IF NOT EXISTS idx_visits_clinic_admitted
                ON visits (clinic_id, admitted_at DESC)
            ");
            DB::statement("
                CREATE INDEX IF NOT EXISTS idx_visits_clinic_type
                ON visits (clinic_id, visit_type)
            ");
        }

        // Extra index for invoices
        if (Schema::hasTable('invoices')) {
            DB::statement("
                CREATE INDEX IF NOT EXISTS idx_invoices_clinic_status
                ON invoices (clinic_id, status)
            ");
            DB::statement("
                CREATE INDEX IF NOT EXISTS idx_invoices_clinic_date
                ON invoices (clinic_id, invoice_date DESC)
            ");
        }
    }

    public function down(): void
    {
        foreach ($this->tables as [$table, $_]) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'clinic_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropForeign(['clinic_id']);
                    $t->dropColumn('clinic_id');
                });
            }
        }
    }
};
