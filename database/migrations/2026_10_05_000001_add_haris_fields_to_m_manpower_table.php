<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('m_manpower', function (Blueprint $table): void {
            $table->string('kode_departemen')->nullable()->after('departemen');
            $table->string('nama_plant')->nullable()->after('kode_departemen');
            $table->string('jabatan')->nullable()->after('bagian');
            $table->string('status_karyawan')->nullable()->after('status');
            $table->string('email')->nullable()->after('status_karyawan');
            $table->string('phone')->nullable()->after('email');
            $table->timestamp('haris_synced_at')->nullable()->after('phone');

            $table->index('kode_departemen');
            $table->index('haris_synced_at');
        });
    }

    public function down(): void
    {
        Schema::table('m_manpower', function (Blueprint $table): void {
            $table->dropIndex(['kode_departemen']);
            $table->dropIndex(['haris_synced_at']);
            $table->dropColumn([
                'kode_departemen',
                'nama_plant',
                'jabatan',
                'status_karyawan',
                'email',
                'phone',
                'haris_synced_at',
            ]);
        });
    }
};
