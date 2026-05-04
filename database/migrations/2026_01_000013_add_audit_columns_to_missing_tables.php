<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add created_by / updated_by audit columns to tables that were missing them.
 *
 * Affected tables:
 *   - patient_addresses
 *   - patient_identifications
 *   - patient_contacts
 *   - vital_sign_observations
 *   - medical_histories
 *   - physical_examinations
 *   - laboratory_results
 *   - imagery_results
 *   - rooms
 *   - beds
 */
return new class extends Migration {

    private array $tables = [
        'patient_addresses',
        'patient_identifications',
        'patient_contacts',
        'vital_sign_observations',
        'medical_histories',
        'physical_examinations',
        'laboratory_results',
        'imagery_results',
        'rooms',
        'beds',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->foreignId('created_by')->nullable()->after('id')->constrained('users')->nullOnDelete();
                $t->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropConstrainedForeignId('created_by');
                $t->dropConstrainedForeignId('updated_by');
            });
        }
    }
};
