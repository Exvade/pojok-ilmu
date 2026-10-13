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
        Schema::create('ojt_assessments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('enrollment_id')->unique()->constrained('training_enrollments')->cascadeOnDelete();
            $table->foreignUuid('assessor_user_id')->constrained('users')->restrictOnDelete();
            $table->date('training_date');
            $table->time('training_time')->nullable();
            $table->string('location')->nullable();
            $table->json('categories');
            $table->text('tools')->nullable();
            $table->json('materials')->nullable();
            $table->json('theory_answers');
            $table->decimal('theory_score', 5, 2);
            $table->json('practice_results');
            $table->decimal('practice_score', 5, 2);
            $table->boolean('is_passed');
            $table->text('conclusion')->nullable();
            $table->text('recommendation')->nullable();
            $table->softDeletes();
            $table->mwtTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ojt_assessments');
    }
};
