<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('m_manpower', function (Blueprint $table): void {
            $table->index(['status', 'haris_plant_id', 'haris_synced_at'], 'manpower_active_scope_index');
        });

        Schema::table('training_enrollments', function (Blueprint $table): void {
            $table->index(['manpower_id', 'training_id', 'deleted_at'], 'enrollment_manpower_training_index');
            $table->index(['status', 'ojt_status', 'post_test_completed_at', 'deleted_at'], 'enrollment_progress_summary_index');
            $table->index(['manpower_id', 'status', 'updated_at'], 'enrollment_manpower_status_index');
        });

        Schema::table('quizzes', function (Blueprint $table): void {
            $table->index(['training_id', 'tipe', 'deleted_at'], 'quiz_training_type_index');
        });

        Schema::table('training_materials', function (Blueprint $table): void {
            $table->index(['training_id', 'urutan', 'deleted_at'], 'material_training_order_index');
        });

        Schema::table('quiz_attempts', function (Blueprint $table): void {
            $table->index(['enrollment_id', 'quiz_id', 'deleted_at'], 'attempt_enrollment_quiz_index');
        });

        Schema::table('operator_skill_matrices', function (Blueprint $table): void {
            $table->index(['manpower_id', 'skill_id', 'deleted_at'], 'skill_matrix_manpower_skill_index');
        });
    }

    public function down(): void
    {
        Schema::table('operator_skill_matrices', function (Blueprint $table): void {
            $table->dropIndex('skill_matrix_manpower_skill_index');
        });

        Schema::table('quiz_attempts', function (Blueprint $table): void {
            $table->dropIndex('attempt_enrollment_quiz_index');
        });

        Schema::table('training_materials', function (Blueprint $table): void {
            $table->dropIndex('material_training_order_index');
        });

        Schema::table('quizzes', function (Blueprint $table): void {
            $table->dropIndex('quiz_training_type_index');
        });

        Schema::table('training_enrollments', function (Blueprint $table): void {
            $table->dropIndex('enrollment_manpower_status_index');
            $table->dropIndex('enrollment_progress_summary_index');
            $table->dropIndex('enrollment_manpower_training_index');
        });

        Schema::table('m_manpower', function (Blueprint $table): void {
            $table->dropIndex('manpower_active_scope_index');
        });
    }
};
