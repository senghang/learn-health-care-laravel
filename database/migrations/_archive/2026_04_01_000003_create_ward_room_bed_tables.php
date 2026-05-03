<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Three-level bed management: wards → rooms → beds
 *
 * Ward   — a named department/service (e.g. Paediatrics, ICU, Maternity)
 * Room   — a physical room inside a ward (Room 201, Theatre A)
 * Bed    — an individual bed inside a room
 *
 * Bed status lifecycle:
 *   available → occupied  (on admission/encounter create)
 *   occupied  → cleaning  (on discharge)
 *   cleaning  → available (on housekeeping sign-off)
 *   any       → reserved  (scheduled admission)
 */
return new class extends Migration {
    public function up(): void
    {
        // ── Wards ─────────────────────────────────────────────────────────────
        Schema::create('wards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();

            $table->string('code', 20)->unique();
            $table->string('name');
            $table->string('name_kh')->nullable();
            $table->string('name_en')->nullable();

            // IPD | OPD | ICU | Emergency | Theatre | Outpatient
            $table->string('type', 30)->default('IPD');

            $table->unsignedSmallInteger('capacity')->default(0)
                  ->comment('Total bed capacity — denormalised for quick reads');
            $table->boolean('is_active')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('clinic_id');
        });

        // ── Rooms ─────────────────────────────────────────────────────────────
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ward_id')->constrained('wards')->cascadeOnDelete();

            $table->string('code', 20)->unique();
            $table->string('name');

            // general | isolation | icu | theatre | observation
            $table->string('type', 30)->default('general');
            $table->tinyInteger('floor')->default(1);
            $table->boolean('is_active')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('ward_id');
        });

        // ── Beds ──────────────────────────────────────────────────────────────
        Schema::create('beds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->foreignId('ward_id')->constrained('wards')->cascadeOnDelete();

            $table->string('code', 20)->unique();
            $table->string('name');                    // display label e.g. "Bed 3A"

            // available | occupied | cleaning | reserved | maintenance
            $table->string('status', 20)->default('available');

            // standard | icu | paediatric | bariatric | delivery
            $table->string('type', 30)->default('standard');

            $table->boolean('is_active')->default(true);

            // Current occupant (nullable — set on admission, cleared on discharge)
            $table->string('current_visit_code', 30)->nullable();
            $table->string('current_patient_code', 30)->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['room_id', 'status']);
            $table->index('ward_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beds');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('wards');
    }
};
