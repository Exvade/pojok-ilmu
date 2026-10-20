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
        Schema::create('training_assignment_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('training_id')->constrained('trainings')->restrictOnDelete();
            $table->foreignId('manpower_id')->constrained('m_manpower')->restrictOnDelete();
            $table->foreignUuid('enrollment_id')->nullable()->constrained('training_enrollments')->nullOnDelete();
            $table->foreignUuid('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 20);
            $table->string('status_before', 50)->nullable();
            $table->string('status_after', 50)->nullable();
            $table->text('reason')->nullable();
            $table->index(['training_id', 'created_at']);
            $table->index(['manpower_id', 'created_at']);
            $table->softDeletes();
            $table->mwtTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_assignment_logs');
    }
};
