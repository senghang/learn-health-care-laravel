<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('visit_code')->nullable();
            $table->string('encounter_code')->nullable();
            $table->string('direction', 4)
                ->comment('FROM = referred in | TO = referred out');
            $table->string('referral_number', 80)->nullable()
                ->comment('Official referral letter/form number');
            $table->string('transportation', 80)->nullable();
            $table->text('reason')->nullable();
            $table->boolean('has_called')->default(false);
            $table->string('caretaker_name', 120)->nullable();
            $table->string('caretaker_phone', 30)->nullable();
            $table->string('referred_by', 120)->nullable();
            $table->string('referred_by_phone', 30)->nullable();
            $table->dateTime('referred_at')->nullable();
            $table->string('received_by', 120)->nullable();
            $table->string('received_by_phone', 30)->nullable();
            $table->dateTime('received_at')->nullable();
            $table->text('medications')->nullable()
                ->comment('Medications sent with patient on referral');

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('visit_code')
                ->references('code')->on('visits')
                ->nullOnDelete();

            $table->index(['visit_code', 'direction']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
