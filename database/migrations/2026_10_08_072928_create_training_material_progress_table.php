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
        if (! Schema::hasTable('training_material_progresses')) {
            Schema::create('training_material_progresses', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('enrollment_id')->constrained('training_enrollments')->cascadeOnDelete();
                $table->foreignId('manpower_id')->constrained('m_manpower')->cascadeOnDelete();
                $table->foreignUuid('training_material_id')->constrained('training_materials')->cascadeOnDelete();
                $table->timestamp('completed_at')->nullable();
                $table->softDeletes();
                $table->mwtTimestamps();
                $table->unique(['enrollment_id', 'training_material_id'], 'tm_progress_enrollment_material_unique');
            });

            return;
        }

        if (! Schema::hasColumn('training_material_progresses', 'enrollment_id')) {
            Schema::table('training_material_progresses', function (Blueprint $table) {
                $table->foreignUuid('enrollment_id')->after('id')->constrained('training_enrollments')->cascadeOnDelete();
            });
        }

        if (! Schema::hasColumn('training_material_progresses', 'manpower_id')) {
            Schema::table('training_material_progresses', function (Blueprint $table) {
                $table->foreignId('manpower_id')->after('enrollment_id')->constrained('m_manpower')->cascadeOnDelete();
            });
        }

        if (! Schema::hasIndex('training_material_progresses', 'tm_progress_enrollment_material_unique')) {
            Schema::table('training_material_progresses', function (Blueprint $table) {
                $table->unique(['enrollment_id', 'training_material_id'], 'tm_progress_enrollment_material_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_material_progresses');
    }
};
