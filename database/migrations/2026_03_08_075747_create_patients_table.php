<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('surname');
            $table->string('name');
            $table->char('gender', 1)->comment('F or M');
            $table->date('birthdate')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('nationality', 80)->nullable();
            $table->string('occupation', 120)->nullable();
            $table->string('marital_status', 30)->nullable();
            $table->string('status', 30)->default('Active')->comment('Active, Inactive');
            $table->string('spid')->nullable()->comment('National social protection ID — future required');

            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['surname', 'name']);
            $table->index('phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
