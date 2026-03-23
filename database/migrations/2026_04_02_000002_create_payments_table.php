<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('invoice_code');
            $table->string('patient_code');
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('method', 20)->default('CASH')
                  ->comment('CASH | HEF | NSSF | CARD | BAKONG');
            $table->string('reference', 80)->nullable()
                  ->comment('Cheque no, transaction ID, etc');
            $table->text('note')->nullable();
            $table->string('collected_by', 120)->nullable();
            $table->dateTime('paid_at')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('invoice_code')->references('code')->on('invoices')->restrictOnDelete();
            $table->foreign('patient_code')->references('code')->on('patients')->restrictOnDelete();

            $table->index(['clinic_id', 'paid_at']);
            $table->index('invoice_code');
        });

        // Add status column to invoices if not present
        if (!Schema::hasColumn('invoices', 'status')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->string('status', 20)->default('pending')
                      ->comment('pending | partial | paid')->after('cashier');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
