<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('s2s_competency_syncs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('enrollment_id')->constrained('training_enrollments')->cascadeOnDelete();
            $table->foreignUuid('s2s_product_id')->constrained('s2s_products')->restrictOnDelete();
            $table->string('nik');
            $table->string('kode_part');
            $table->string('kode_training');
            $table->string('judul_training');
            $table->unsignedTinyInteger('level');
            $table->uuid('idempotency_key')->unique();
            $table->string('status', 20)->default('queued')->index();
            $table->unsignedInteger('attempt_count')->default(0);
            $table->unsignedSmallInteger('last_http_status')->nullable();
            $table->string('s2s_action', 40)->nullable();
            $table->json('response_payload')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->mwtTimestamps();
            $table->unique(['enrollment_id', 's2s_product_id'], 's2s_sync_enrollment_product_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('s2s_competency_syncs');
    }
};
