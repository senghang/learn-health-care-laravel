<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('clinic_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();

            // e.g. 'prescription.header_logo', 'invoice.footer_text', 'system.timezone'
            $table->string('key', 100);

            // Null-safe JSON value — simple strings, arrays, booleans all serialised
            $table->text('value')->nullable();

            // Optional locale scoping: null = applies to all locales
            $table->char('locale', 5)->nullable()->comment('km | en | null = all');

            $table->timestamps();

            $table->unique(['clinic_id', 'key', 'locale']);
            $table->index('clinic_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_settings');
    }
};
