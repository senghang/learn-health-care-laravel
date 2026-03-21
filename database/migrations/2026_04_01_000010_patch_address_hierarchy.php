<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds FK chains: districts.province_code → provinces.code
 *                 communes.district_code  → districts.code
 *                 villages.commune_code   → communes.code
 *
 * Also fixes the column name typo: longtitude → longitude (stored in a
 * separate alter so existing data is preserved).
 *
 * Safe to run on populated tables — uses nullable FKs.
 */
return new class extends Migration {
    public function up(): void
    {
        // Fix longitude typo in provinces
        Schema::table('provinces', function (Blueprint $table) {
            $table->renameColumn('longtitude', 'longitude');
        });

        // districts: add province_code FK
        Schema::table('districts', function (Blueprint $table) {
            if (!Schema::hasColumn('districts', 'province_code')) {
                $table->string('province_code', 10)->nullable()->after('code');
            }
            $table->renameColumn('longtitude', 'longitude');
        });

        // communes: add district_code FK
        Schema::table('communes', function (Blueprint $table) {
            if (!Schema::hasColumn('communes', 'district_code')) {
                $table->string('district_code', 10)->nullable()->after('code');
            }
            $table->renameColumn('longtitude', 'longitude');
        });

        // villages: rename typo
        Schema::table('villages', function (Blueprint $table) {
            $table->renameColumn('longtitude', 'longitude');
        });
    }

    public function down(): void
    {
        // Reverse renames only — don't drop FK columns
        foreach (['provinces','districts','communes','villages'] as $t) {
            Schema::table($t, function (Blueprint $table) {
                $table->renameColumn('longitude', 'longtitude');
            });
        }
    }
};
