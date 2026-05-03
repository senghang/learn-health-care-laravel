<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration 3: Fix NULL columns and add default_locale to clinics.
 * No CHECK constraints — validation is handled at the application layer.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. clinics: add default_locale if missing ─────────────────────────
        if (Schema::hasTable('clinics') && !Schema::hasColumn('clinics', 'default_locale')) {
            Schema::table('clinics', function (Blueprint $table) {
                $table->char('default_locale', 2)
                    ->default('km')
                    ->after('subdomain')
                    ->comment('UI language: km or en');
            });
        }

        // ── 2. visits: backfill NULL JSONB columns and set defaults ───────────
        if (Schema::hasTable('visits')) {
            DB::statement("UPDATE visits SET done_steps    = '[]' WHERE done_steps    IS NULL");
            DB::statement("UPDATE visits SET skipped_steps = '[]' WHERE skipped_steps IS NULL");

            DB::statement("
                ALTER TABLE visits
                    ALTER COLUMN done_steps     SET DEFAULT '[]'::jsonb,
                    ALTER COLUMN skipped_steps  SET DEFAULT '[]'::jsonb
            ");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('clinics') && Schema::hasColumn('clinics', 'default_locale')) {
            Schema::table('clinics', fn($t) => $t->dropColumn('default_locale'));
        }
    }
};
