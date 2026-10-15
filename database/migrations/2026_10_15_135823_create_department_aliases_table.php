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
        Schema::create('department_aliases', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('departemen');
            $table->string('alias');
            $table->string('alias_key')->unique();
            $table->softDeletes();
            $table->mwtTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('department_aliases');
    }
};
