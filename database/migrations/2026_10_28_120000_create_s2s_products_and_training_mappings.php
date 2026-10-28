<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('s2s_products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode_part')->unique();
            $table->string('nama_part')->nullable();
            $table->string('status', 20)->default('aktif')->index();
            $table->timestamp('s2s_updated_at')->nullable()->index();
            $table->timestamp('synced_at')->nullable();
            $table->mwtTimestamps();
        });

        Schema::create('s2s_product_training', function (Blueprint $table) {
            $table->foreignUuid('training_id')->constrained('trainings')->cascadeOnDelete();
            $table->foreignUuid('s2s_product_id')->constrained('s2s_products')->cascadeOnDelete();
            $table->primary(['training_id', 's2s_product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('s2s_product_training');
        Schema::dropIfExists('s2s_products');
    }
};
