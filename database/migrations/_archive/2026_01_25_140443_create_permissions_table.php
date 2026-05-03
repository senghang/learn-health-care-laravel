<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')
                    ->constrained()
                    ->cascadeOnDelete();

            $table->string('name');
            $table->string('slug');
            $table->string('group')->nullable(); // better than integer

            $table->timestamps();

            $table->unique(['clinic_id', 'slug']);
            $table->index('clinic_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
