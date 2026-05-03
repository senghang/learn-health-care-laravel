<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Patient demographic tables.
 *
 * Includes:
 *   - patients               (core demographics)
 *   - patient_addresses      (structured address with geo hierarchy)
 *   - patient_identifications (government IDs, passports, etc.)
 *   - patient_contacts       (emergency contacts)
 */
return new class extends Migration {

    public function up(): void
    {
        // ── Patients ──────────────────────────────────────────────────────────
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->string('code', 30)->unique();
            $table->string('surname', 80);
            $table->string('name', 80);
            $table->char('sex', 1)->comment('M | F | O');
            $table->date('birthdate')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('nationality', 80)->nullable();
            $table->string('occupation', 120)->nullable();
            $table->string('marital_status', 30)->nullable();
            $table->string('blood_type', 10)->nullable();
            $table->string('status', 30)->default('Active');
            $table->string('spid', 60)->nullable()->comment('National ID / Passport number');
            $table->string('emergency_contact_name', 120)->nullable();
            $table->string('emergency_contact_phone', 30)->nullable();
            $table->string('photo_path')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('clinic_id');
            $table->index(['surname', 'name']);
            $table->index('phone');
        });

        // ── Patient Addresses ─────────────────────────────────────────────────
        Schema::create('patient_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->nullable()->constrained()->nullOnDelete();
            $table->string('patient_code', 30);
            $table->string('address_type', 30)->default('current')
                ->comment('current | permanent | workplace');
            $table->string('province_code', 20)->nullable();
            $table->string('province_name', 100)->nullable();
            $table->string('district_code', 20)->nullable();
            $table->string('district_name', 100)->nullable();
            $table->string('commune_code', 20)->nullable();
            $table->string('commune_name', 100)->nullable();
            $table->string('village_code', 20)->nullable();
            $table->string('village_name', 100)->nullable();
            $table->string('house_number', 20)->nullable();
            $table->string('street_number', 20)->nullable();
            $table->string('location')->nullable()->comment('Free-text or map link');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_code')->references('code')->on('patients')->cascadeOnDelete();
            $table->index(['clinic_id', 'patient_code']);
        });

        // ── Patient Identifications ───────────────────────────────────────────
        Schema::create('patient_identifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->nullable()->constrained()->nullOnDelete();
            $table->string('patient_code', 30);
            $table->string('id_type', 40)->comment('national_id | passport | birth_cert | other');
            $table->string('id_number', 60);
            $table->date('issued_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('issued_by', 120)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_code')->references('code')->on('patients')->cascadeOnDelete();
            $table->index(['clinic_id', 'patient_code']);
        });

        // ── Patient Contacts (emergency / next-of-kin) ────────────────────────
        Schema::create('patient_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('patient_code', 30);
            $table->string('contact_name', 120);
            $table->string('contact_phone', 30)->nullable();
            $table->string('relationship', 60)->nullable();
            $table->boolean('is_emergency')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_code')->references('code')->on('patients')->cascadeOnDelete();
            $table->index('patient_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_contacts');
        Schema::dropIfExists('patient_identifications');
        Schema::dropIfExists('patient_addresses');
        Schema::dropIfExists('patients');
    }
};
