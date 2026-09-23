<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trainings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode_training')->unique();
            $table->foreignUuid('skill_id')->constrained('m_skills')->onDelete('cascade');
            $table->tinyInteger('level')->default(1); // 1, 2, 3, 4
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->integer('max_retries')->default(3);
            $table->integer('passing_grade')->default(80);
            $table->softDeletes();
            $table->mwtTimestamps();
        });

        Schema::create('training_materials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('training_id')->constrained('trainings')->onDelete('cascade');
            $table->integer('urutan')->default(1);
            $table->string('judul');
            $table->enum('tipe_media', ['text', 'image', 'video_gdrive', 'video_youtube', 'pdf'])->default('text');
            $table->longText('konten_teks')->nullable();
            $table->string('media_url')->nullable(); // Local file path or GDrive/YouTube URL
            $table->softDeletes();
            $table->mwtTimestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_materials');
        Schema::dropIfExists('trainings');
    }
};
