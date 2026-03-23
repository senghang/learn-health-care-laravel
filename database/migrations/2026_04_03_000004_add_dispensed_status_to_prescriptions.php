<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('prescriptions') && !Schema::hasColumn('prescriptions', 'dispensed_status')) {
            Schema::table('prescriptions', function (Blueprint $table) {
                $table->string('dispensed_status', 20)->nullable()->after('prescribed_by')
                    ->comment('null | partial | dispensed');
                $table->string('dispensed_by', 120)->nullable()->after('dispensed_status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('prescriptions')) {
            Schema::table('prescriptions', function (Blueprint $table) {
                $table->dropColumn(['dispensed_status', 'dispensed_by']);
            });
        }
    }
};
