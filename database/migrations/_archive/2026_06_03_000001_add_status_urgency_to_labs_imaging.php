<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ══════════════════════════════════════════════════════════════════════════════
 * LAB & IMAGING — ADDITIVE MIGRATION
 * ══════════════════════════════════════════════════════════════════════════════
 *
 * SAFETY CHECK:
 *   ✅ laboratories         — adds nullable columns only
 *   ✅ laboratory_results   — adds nullable columns only
 *   ✅ imageries            — adds nullable columns only
 *
 * DOES NOT:
 *   ❌ Rename any existing column
 *   ❌ Drop any existing column
 *   ❌ Modify any existing constraint
 *
 * WHY: Models reference status/urgency/category/flag columns that may not
 *      exist in the original migrations. This ensures they exist.
 * ══════════════════════════════════════════════════════════════════════════════
 */
return new class extends Migration {
    public function up(): void
    {
        // ── laboratories: add status, urgency, category ───────────────────────
        if (Schema::hasTable('laboratories')) {
            Schema::table('laboratories', function (Blueprint $table) {
                if (!Schema::hasColumn('laboratories', 'status')) {
                    $table->string('status', 20)->default('requested')->after('title')
                          ->comment('requested|collected|processing|completed|cancelled');
                }
                if (!Schema::hasColumn('laboratories', 'urgency')) {
                    $table->string('urgency', 20)->default('normal')->after('status')
                          ->comment('normal|urgent|stat');
                }
                if (!Schema::hasColumn('laboratories', 'category')) {
                    $table->string('category', 80)->nullable()->after('encounter_code')
                          ->comment('Hematology|Biochemistry|Microbiology|Urinalysis|Serology|Other');
                }
            });
        }

        // ── laboratory_results: add flag for H/L/N/Critical ───────────────────
        if (Schema::hasTable('laboratory_results')) {
            Schema::table('laboratory_results', function (Blueprint $table) {
                if (!Schema::hasColumn('laboratory_results', 'flag')) {
                    $table->string('flag', 10)->nullable()->after('interpretation')
                          ->comment('H|L|N|Critical — abnormal flag');
                }
            });
        }

        // ── imageries: add status, urgency ────────────────────────────────────
        if (Schema::hasTable('imageries')) {
            Schema::table('imageries', function (Blueprint $table) {
                if (!Schema::hasColumn('imageries', 'status')) {
                    $table->string('status', 20)->default('requested')->after('category')
                          ->comment('requested|completed|cancelled');
                }
                if (!Schema::hasColumn('imageries', 'urgency')) {
                    $table->string('urgency', 20)->default('normal')->after('status')
                          ->comment('normal|urgent|stat');
                }
            });
        }
    }

    public function down(): void
    {
        $drops = [
            ['laboratories', ['status', 'urgency', 'category']],
            ['laboratory_results', ['flag']],
            ['imageries', ['status', 'urgency']],
        ];

        foreach ($drops as [$table, $cols]) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $t) use ($table, $cols) {
                    foreach ($cols as $col) {
                        if (Schema::hasColumn($table, $col)) $t->dropColumn($col);
                    }
                });
            }
        }
    }
};
