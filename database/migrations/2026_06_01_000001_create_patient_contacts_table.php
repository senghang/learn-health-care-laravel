<?php

use App\Models\Base\AuditEntity;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ══════════════════════════════════════════════════════════════════════════════
 * PATIENT CONTACTS — ADDITIVE MIGRATION
 * ══════════════════════════════════════════════════════════════════════════════
 *
 * SAFETY CHECK:
 *   ✅ patient_contacts — new table (Schema::create)
 *   ✅ patients — no changes (emergency_contact_name/phone already exist)
 *
 * DOES NOT:
 *   ❌ Rename any existing column
 *   ❌ Drop any existing column
 *   ❌ Modify any existing NOT NULL constraint
 *   ❌ Change any existing FK relationship
 *
 * WHY: The existing emergency_contact_name / emergency_contact_phone on
 *       the patients table holds a single contact. This table allows
 *       multiple contacts (next of kin, guardian, employer, etc.)
 *       while keeping backward compatibility with the existing fields.
 *
 * ══════════════════════════════════════════════════════════════════════════════
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('patient_contacts')) {
            Schema::create('patient_contacts', function (Blueprint $table) {
                $table->id();
                $table->string('patient_code');
                $table->string('contact_name', 120);
                $table->string('contact_phone', 30)->nullable();
                $table->string('relationship', 60)->nullable()
                      ->comment('Spouse, Parent, Child, Sibling, Guardian, Other');
                $table->boolean('is_emergency')->default(false)
                      ->comment('Flag as emergency contact');
                $table->text('notes')->nullable();

                // Audit fields
                AuditEntity::columns($table);

                // FK to patients.code — cascade delete when patient is removed
                $table->foreign('patient_code')
                      ->references('code')->on('patients')
                      ->cascadeOnDelete();

                $table->index('patient_code');
                $table->index('is_emergency');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_contacts');
    }
};
