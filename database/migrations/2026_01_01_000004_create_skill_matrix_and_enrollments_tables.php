<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Auto TNA & Enrollment Track
        Schema::create('training_enrollments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('manpower_id')->constrained('m_manpower')->onDelete('cascade');
            $table->foreignUuid('training_id')->constrained('trainings')->onDelete('cascade');
            $table->enum('status', ['enrolled', 'in_progress', 'passed', 'failed_escalated'])->default('enrolled');
            $table->integer('current_retry_count')->default(0);
            $table->softDeletes();
            $table->mwtTimestamps();
        });

        // 2. Log Percobaan Ujian / Assessment Attempts
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('enrollment_id')->constrained('training_enrollments')->onDelete('cascade');
            $table->foreignId('manpower_id')->constrained('m_manpower')->onDelete('cascade');
            $table->foreignUuid('quiz_id')->constrained('quizzes')->onDelete('cascade');
            $table->integer('attempt_number')->default(1);
            $table->decimal('score', 5, 2)->default(0.00);
            $table->boolean('is_passed')->default(false);
            $table->timestamp('submitted_at')->useCurrent();
            $table->softDeletes();
            $table->mwtTimestamps();
        });

        // 3. Skill Matrix Output Utama
        Schema::create('operator_skill_matrices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('manpower_id')->constrained('m_manpower')->onDelete('cascade');
            $table->foreignUuid('skill_id')->constrained('m_skills')->onDelete('cascade');
            $table->tinyInteger('current_level')->default(0); // 0, 1, 2, 3, 4
            $table->tinyInteger('target_level')->default(1); // 1, 2, 3, 4
            $table->string('status_gap')->default('Gap -1 Level');
            $table->timestamp('last_updated_at')->nullable();
            $table->softDeletes();
            $table->mwtTimestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operator_skill_matrices');
        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('training_enrollments');
    }
};
