<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('patient_addresses', function (Blueprint $table) {
            $table->id();
            $table->string('patient_code');
            $table->string('province_code', 10)->nullable();
            $table->string('province_name', 100)->nullable();
            $table->string('district_code', 10)->nullable();
            $table->string('district_name', 100)->nullable();
            $table->string('commune_code', 10)->nullable();
            $table->string('commune_name', 100)->nullable();
            $table->string('village_code', 10)->nullable();
            $table->string('village_name', 100)->nullable();
            $table->string('house_number', 20)->nullable();
            $table->string('street_number', 20)->nullable();
            $table->string('location')->nullable()->comment('GPS lat/lng');

            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_code')
                ->references('code')->on('patients')
                ->cascadeOnDelete();

            $table->index('patient_code');
        });

        Schema::create('patient_identifications', function (Blueprint $table) {
            $table->id();
            $table->string('patient_code');
            $table->string('card_code');
            $table->string('card_type', 60)->comment('NID, Passport, HEF card …');

            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_code')
                ->references('code')->on('patients')
                ->cascadeOnDelete();

            $table->index('patient_code');
            $table->index('card_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_identifications');
        Schema::dropIfExists('patient_addresses');
    }
};
