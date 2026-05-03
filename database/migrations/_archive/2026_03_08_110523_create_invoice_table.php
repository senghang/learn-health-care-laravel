<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('patient_code');
            $table->string('visit_code')->nullable();
            $table->string('encounter_code')->nullable();
            $table->string('payment_type', 10)
                ->comment('HEF | NSSF | CASH');
            $table->date('invoice_date')->nullable();
            $table->decimal('total', 14, 2)->default(0);
            $table->string('cashier', 120)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_code')
                ->references('code')->on('patients')
                ->restrictOnDelete();

            $table->foreign('visit_code')
                ->references('code')->on('visits')
                ->nullOnDelete();

            $table->index('patient_code');
            $table->index(['visit_code', 'payment_type']);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::create('invoice_services', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_code');
            $table->string('service_code', 60)->nullable();
            $table->string('service_name', 200);
            $table->string('service_category', 80)->nullable();
            $this->extracted($table);
        });

        Schema::create('invoice_medications', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_code');
            $table->string('medicine_code', 60)->nullable();
            $table->string('medicine_name', 200);
            $table->decimal('quantity', 10, 2)->default(0);
            $this->extracted($table);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_medications');
        Schema::dropIfExists('invoice_services');
        Schema::dropIfExists('invoices');
    }

    /**
     * @param Blueprint $table
     * @return void
     */
    public function extracted(Blueprint $table): void
    {
        $table->decimal('price', 14, 2)->default(0);
        $table->decimal('payment', 14, 2)->default(0);
        $table->decimal('paid', 14, 2)->default(0);
        $table->string('discount_type', 12)->nullable()
            ->comment('percentage | amount');
        $table->decimal('discount', 10, 2)->default(0);

        // Audit
        $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamps();
        $table->softDeletes();

        $table->foreign('invoice_code')
            ->references('code')->on('invoices')
            ->cascadeOnDelete();
        $table->index('invoice_code');
    }
};
