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
        Schema::create('villages', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name_kh');
            $table->string('name_en');
            $table->string('latitude')->nullable();
            $table->string('longtitude')->nullable();
            $table->string('commune');
            $table->string('district');
            $table->string('province');
            $table->text('note')->nullable();            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('villages');
    }
};
