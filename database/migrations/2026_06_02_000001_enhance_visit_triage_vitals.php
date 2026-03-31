<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ══════════════════════════════════════════════════════════════════════════════
 * VISIT MODULE — ADDITIVE MIGRATION
 * ══════════════════════════════════════════════════════════════════════════════
 *
 * SAFETY CHECK:
 *   ✅ visits           — adds nullable columns only
 *   ✅ triages          — adds nullable columns only
 *   ✅ vital_signs      — adds nullable column only
 *
 * DOES NOT:
 *   ❌ Rename any existing column
 *   ❌ Drop any existing column
 *   ❌ Modify any existing NOT NULL constraint
 *   ❌ Change any existing FK relationship
 *
 * PURPOSE:
 *   1. Add priority field to visits for triage-level sorting
 *   2. Add clinical_summary for discharge documentation
 *   3. Add patient_code to vital_signs for direct patient queries
 *   4. Add triage_level to triages for severity classification
 * ══════════════════════════════════════════════════════════════════════════════
 */
return new class extends Migration {
    public function up(): void
    {
        // ── visits: add priority + clinical summary ───────────────────────────
        if (Schema::hasTable('visits')) {
            Schema::table('visits', function (Blueprint $table) {
                if (!Schema::hasColumn('visits', 'priority')) {
                    $table->string('priority', 20)->nullable()->after('visit_type')
                          ->comment('Emergency|Urgent|Standard|Low — set at triage');
                }
                if (!Schema::hasColumn('visits', 'clinical_summary')) {
                    $table->text('clinical_summary')->nullable()->after('visit_outcome')
                          ->comment('Brief narrative set at discharge or after all steps');
                }
            });
        }

        // ── triages: add triage_level for severity classification ─────────────
        if (Schema::hasTable('triages')) {
            Schema::table('triages', function (Blueprint $table) {
                if (!Schema::hasColumn('triages', 'triage_level')) {
                    $table->string('triage_level', 20)->nullable()->after('chief_complaint')
                          ->comment('Emergency|Urgent|Standard|Low');
                }
                if (!Schema::hasColumn('triages', 'bmi')) {
                    $table->decimal('bmi', 4, 1)->nullable()->after('weight')
                          ->comment('Auto-calculated from height+weight');
                }
            });
        }

        // ── vital_signs: ensure patient_code exists for direct queries ────────
        if (Schema::hasTable('vital_signs')) {
            if (!Schema::hasColumn('vital_signs', 'patient_code')) {
                Schema::table('vital_signs', function (Blueprint $table) {
                    $table->string('patient_code', 30)->nullable()->after('code');
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('visits')) {
            Schema::table('visits', function (Blueprint $t) {
                if (Schema::hasColumn('visits', 'priority')) $t->dropColumn('priority');
                if (Schema::hasColumn('visits', 'clinical_summary')) $t->dropColumn('clinical_summary');
            });
        }
        if (Schema::hasTable('triages')) {
            Schema::table('triages', function (Blueprint $t) {
                if (Schema::hasColumn('triages', 'triage_level')) $t->dropColumn('triage_level');
                if (Schema::hasColumn('triages', 'bmi')) $t->dropColumn('bmi');
            });
        }
        if (Schema::hasTable('vital_signs') && Schema::hasColumn('vital_signs', 'patient_code')) {
            Schema::table('vital_signs', fn($t) => $t->dropColumn('patient_code'));
        }
    }
};
