<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Polymorphic translations table.
 *
 * Any Eloquent model that uses the HasTranslations trait can register
 * bilingual field values here without adding name_kh / name_en columns
 * to every table.
 *
 * Usage:
 *   $ward->translate('name', 'km', 'សេវាស្ត្រី');
 *   $ward->translate('name', 'en', 'Maternity Ward');
 *   $ward->translation('name', 'km');   // returns 'សេវាស្ត្រី'
 *
 * Tables that already carry explicit name_kh / name_en columns
 * (wards, services, medicines, etc.) should migrate those values here
 * when ready and drop the dedicated columns.  Both approaches coexist
 * during the transition.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->id();

            // Fully-qualified class name: App\Models\WardModel
            $table->string('model');
            $table->unsignedBigInteger('model_id');

            $table->char('locale', 5)->comment('km | en');
            $table->string('field', 80)->comment('e.g. name | description | unit');
            $table->text('value');

            $table->timestamps();

            $table->unique(['model', 'model_id', 'locale', 'field'], 'translations_unique');
            $table->index(['model', 'model_id']);
            $table->index('locale');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
