<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Geographic reference tables (Cambodia admin hierarchy).
 *
 * Province → District → Commune → Village
 * Used for patient address dropdowns and facility location lookups.
 */
return new class extends Migration {

    public function up(): void
    {
        // ── Provinces ─────────────────────────────────────────────────────────
        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name_kh', 100)->nullable();
            $table->string('name_en', 100)->nullable();
            $table->decimal('latitude', 10, 6)->nullable();
            $table->decimal('longitude', 10, 6)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        // ── Districts ─────────────────────────────────────────────────────────
        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->foreignId('province_id')->constrained()->cascadeOnDelete();
            $table->string('name_kh', 100)->nullable();
            $table->string('name_en', 100)->nullable();
            $table->timestamps();

            $table->index('province_id');
        });

        // ── Communes ──────────────────────────────────────────────────────────
        Schema::create('communes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->foreignId('district_id')->constrained()->cascadeOnDelete();
            $table->string('name_kh', 100)->nullable();
            $table->string('name_en', 100)->nullable();
            $table->timestamps();

            $table->index('district_id');
        });

        // ── Villages ──────────────────────────────────────────────────────────
        Schema::create('villages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->foreignId('commune_id')->constrained()->cascadeOnDelete();
            $table->string('name_kh', 100)->nullable();
            $table->string('name_en', 100)->nullable();
            $table->timestamps();

            $table->index('commune_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('villages');
        Schema::dropIfExists('communes');
        Schema::dropIfExists('districts');
        Schema::dropIfExists('provinces');
    }
};
