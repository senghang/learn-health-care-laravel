<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit log — immutable record of every write event.
 *
 * The AuditLog model is written by an Eloquent observer attached to any
 * model that uses the LogsActivity concern.
 *
 * event: created | updated | deleted | restored | printed | exported
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('clinic_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // The model class (short name) and its PK
            $table->string('model', 80);
            $table->string('model_id', 40)->nullable();

            // created | updated | deleted | restored | printed | exported
            $table->string('event', 20);

            // Snapshots — null on create/delete to save space
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();

            // No updated_at — audit rows are never modified
            $table->timestamp('created_at')->useCurrent();

            $table->index(['clinic_id', 'created_at']);
            $table->index(['model', 'model_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
