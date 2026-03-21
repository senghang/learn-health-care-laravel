<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds columns to the existing clinics table that are needed for
 * multi-clinic production use.  Safe to run on a populated table.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            // Branding
            $table->string('tagline')->nullable()->after('name_en');
            $table->string('phone', 30)->nullable()->after('owner_name');
            $table->string('email', 120)->nullable()->after('phone');
            $table->text('address')->nullable()->after('email');

            // Contract / billing
            $table->string('plan', 30)->default('standard')->after('support')
                  ->comment('standard | premium | trial');
            $table->unsignedSmallInteger('max_users')->default(10)->after('plan');

            // Locale
            $table->char('default_locale', 5)->default('km')->after('is_active')
                  ->comment('km | en');

            // Soft delete
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->dropColumn([
                'tagline','phone','email','address',
                'plan','max_users','default_locale','deleted_at',
            ]);
        });
    }
};
