<?php
/**
 * MIGRATION 1: clinic_code_sequences
 *
 * WHY THIS EXISTS:
 *   PostgreSQL does NOT allow SELECT ... FOR UPDATE with aggregate functions.
 *   The old pattern:
 *       DB::transaction(fn() => Model::whereDate('created_at', today())
 *           ->lockForUpdate()   ← ❌ ERROR: FOR UPDATE with COUNT
 *           ->count());
 *
 *   This migration creates a dedicated sequence table used with an atomic
 *   INSERT ... ON CONFLICT DO UPDATE ... RETURNING, which is PostgreSQL's
 *   idiomatic way to generate concurrent-safe auto-incrementing values.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_code_sequences', function (Blueprint $table) {
            $table->id();

            // Every row is scoped to a clinic
            $table->foreignId('clinic_id')
                ->constrained('clinics')
                ->cascadeOnDelete();

            // Code prefix: 'V', 'PT', 'INV', 'RX', 'PAY', 'LAB', 'STK'
            $table->string('prefix', 10)->notNull();

            // Sequences reset daily — one row per clinic+prefix+date
            $table->date('seq_date')->notNull();

            // The current maximum sequence number for this clinic+prefix+date
            $table->unsignedInteger('last_seq')->default(0)->notNull();

            $table->timestampTz('updated_at')->useCurrent()->useCurrentOnUpdate();

            // This UNIQUE constraint is the upsert target in our atomic SQL
            $table->unique(
                ['clinic_id', 'prefix', 'seq_date'],
                'uq_clinic_code_seq'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_code_sequences');
    }
};
