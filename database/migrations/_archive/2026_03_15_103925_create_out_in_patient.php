<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        /*
         * out_in_patient — unified encounter table for both OPD and IPD.
         * visit_type mirrors the parent visit: 'OPD' | 'IPD'
         * Code format:
         *   OPD visits → OPD-{visit_code}
         *   IPD visits → IPD-{visit_code}
         */
        Schema::create('out_in_patient', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('visit_code');
            $table->string('visit_type', 3)->default('OPD')->comment('OPD | IPD');
            $table->string('name', 200)->nullable()->comment('Ward name for IPD / clinic name for OPD');
            $table->string('service_type', 80)->nullable();
            $table->string('bed', 30)->nullable()->comment('Bed number — IPD only');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->string('encountered_by', 120)->nullable();
            $this->extracted($table);
            $table->index('visit_type');
        });

        // ── Emergencies (separate — different clinical data) ──────────────────
        Schema::create('emergencies', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('visit_code');
            $table->string('name', 120)->nullable();
            $table->string('service_type', 80)->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->string('encountered_by', 120)->nullable();
            $table->text('emergency_notes');
            $this->extracted($table);
        });

        // ── Surgeries ─────────────────────────────────────────────────────────
        Schema::create('surgeries', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('visit_code');
            $table->string('parent_code')->nullable();
            $table->string('theater_name', 120)->nullable();
            $table->string('service_type', 80)->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->text('reason')->nullable();
            $table->string('anesthesia_type', 80)->nullable();
            $table->text('procedure_notes')->nullable();
            $table->json('complications')->nullable();
            $table->json('specimens')->nullable();
            $table->decimal('blood_loss', 8, 2)->nullable();
            $table->string('surgeon_name', 120)->nullable();
            $table->string('anesthetist_name', 120)->nullable();
            $table->json('assistant_names')->nullable();

            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('visit_code')
                ->references('code')->on('visits')
                ->cascadeOnDelete();
            $table->index('visit_code');
        });

        // ── Progress Notes ────────────────────────────────────────────────────
        Schema::create('progress_notes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('visit_code');
            $table->string('parent_code')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->string('encountered_by', 120)->nullable();
            $table->string('title');

            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('visit_code')
                ->references('code')->on('visits')
                ->cascadeOnDelete();
            $table->index('visit_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progress_notes');
        Schema::dropIfExists('surgeries');
        Schema::dropIfExists('emergencies');
        Schema::dropIfExists('out_in_patient');
    }

    /**
     * @param Blueprint $table
     * @return void
     */
    public function extracted(Blueprint $table): void
    {
        $table->string('title')->nullable();

        // Audit fields
        $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamps();
        $table->softDeletes();

        $table->foreign('visit_code')
            ->references('code')->on('visits')
            ->cascadeOnDelete();

        $table->index('visit_code');
    }
};
