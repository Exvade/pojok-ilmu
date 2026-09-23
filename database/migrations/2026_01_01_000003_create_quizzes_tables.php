<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('training_id')->constrained('trainings')->onDelete('cascade');
            $table->string('judul');
            $table->integer('durasi_menit')->default(15);
            $table->integer('passing_grade')->default(80);
            $table->integer('max_retries')->default(3);
            $table->softDeletes();
            $table->mwtTimestamps();
        });

        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('quiz_id')->constrained('quizzes')->onDelete('cascade');
            $table->text('pertanyaan');
            $table->enum('tipe_media', ['none', 'image', 'video_gdrive', 'video_youtube'])->default('none');
            $table->string('media_url')->nullable();
            $table->text('opsi_a');
            $table->text('opsi_b');
            $table->text('opsi_c');
            $table->text('opsi_d');
            $table->enum('kunci_jawaban', ['a', 'b', 'c', 'd']);
            $table->softDeletes();
            $table->mwtTimestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
        Schema::dropIfExists('quizzes');
    }
};
