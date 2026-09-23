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
        // 1. Manpower Table (Master Data Karyawan / Operator - Sama dengan S2S)
        Schema::create('m_manpower', function (Blueprint $table) {
            $table->id();
            $table->string('mp_id')->unique(); // Linked to User (NPK / ID Operator)
            $table->string('nik')->unique();
            $table->string('nama');
            $table->string('departemen');
            $table->string('bagian');
            $table->string('qrcode')->nullable();
            $table->boolean('status')->default(true);
            $table->softDeletes();
            $table->mwtTimestamps();
        });

        // 2. Users Table (Sama dengan S2S: Auto Increment BigInt ID)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable()->unique(); // Maps to m_manpower.mp_id
            $table->string('password')->nullable(); // Nullable for operators (login via scan/NPK)
            $table->integer('role')->default(0); // 1 = Admin/Superadmin, 2 = Management/HR/Supervisor, 0 = Operator
            $table->boolean('is_superadmin')->default(false);
            $table->rememberToken();
            $table->mwtTimestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
        Schema::dropIfExists('m_manpower');
    }
};
