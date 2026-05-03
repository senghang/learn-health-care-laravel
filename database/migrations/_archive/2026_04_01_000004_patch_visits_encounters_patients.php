<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Links encounters and visits to the bed/facility hierarchy,
 * and adds the missing clinic_id scope to visits.
 */
return new class extends Migration {
    public function up(): void
    {
        // ── visits: add clinic_id for proper multi-tenant scoping ─────────────
        Schema::table('visits', function (Blueprint $table) {
            $table->foreignId('clinic_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('clinics')
                  ->nullOnDelete();
            $table->string('given_name', 120)->nullable()->after('name')
                  ->comment('Alias for the name column — used by workflow create form');
            $table->index('clinic_id');
        });

        // ── out_in_patient: add bed + room FKs ───────────────────────────────
        Schema::table('out_in_patient', function (Blueprint $table) {
            $table->unsignedBigInteger('ward_id')->nullable()->after('visit_type');
            $table->unsignedBigInteger('room_id')->nullable()->after('ward_id');
            $table->unsignedBigInteger('bed_id')->nullable()->after('room_id');

            $table->foreign('ward_id')->references('id')->on('wards')->nullOnDelete();
            $table->foreign('room_id')->references('id')->on('rooms')->nullOnDelete();
            $table->foreign('bed_id')->references('id')->on('beds')->nullOnDelete();
        });

        // ── patients: add missing fields from the API spec ───────────────────
        Schema::table('patients', function (Blueprint $table) {
            $table->json('photos')->nullable()->after('spid');
            $table->json('disabilities')->nullable()->after('photos');
            $table->dateTime('death_date')->nullable()->after('disabilities');
        });
    }

    public function down(): void
    {
        Schema::table('out_in_patient', function (Blueprint $table) {
            $table->dropForeign(['ward_id']);
            $table->dropForeign(['room_id']);
            $table->dropForeign(['bed_id']);
            $table->dropColumn(['ward_id','room_id','bed_id']);
        });

        Schema::table('visits', function (Blueprint $table) {
            $table->dropForeign(['clinic_id']);
            $table->dropColumn(['clinic_id','given_name']);
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['photos','disabilities','death_date']);
        });
    }
};
