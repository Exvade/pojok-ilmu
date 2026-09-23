<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('m_skills', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode_skill')->unique();
            $table->string('nama_skill');
            $table->string('kategori'); // Kaizen, Safety, Machine Operation, Part Qualification, etc.
            $table->text('deskripsi')->nullable();
            $table->softDeletes();
            $table->mwtTimestamps();
        });

        Schema::create('m_skill_target_levels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('departemen');
            $table->string('bagian');
            $table->foreignUuid('skill_id')->constrained('m_skills')->onDelete('cascade');
            $table->tinyInteger('target_level')->default(1); // 1, 2, 3, 4
            $table->softDeletes();
            $table->mwtTimestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('m_skill_target_levels');
        Schema::dropIfExists('m_skills');
    }
};
