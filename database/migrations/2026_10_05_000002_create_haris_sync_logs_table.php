<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('haris_sync_logs', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('status');
            $table->unsignedInteger('employee_count')->default(0);
            $table->unsignedInteger('deactivated_count')->default(0);
            $table->text('message');
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->mwtTimestamps();

            $table->index(['status', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('haris_sync_logs');
    }
};
