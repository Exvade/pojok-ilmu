<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('m_manpower', function (Blueprint $table): void {
            $table->string('haris_plant_id')->nullable()->after('nama_plant')->index();
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('can_manage_competency')->default(false)->after('is_superadmin');
            $table->boolean('can_manage_curriculum')->default(false)->after('can_manage_competency');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['can_manage_competency', 'can_manage_curriculum']);
        });

        Schema::table('m_manpower', function (Blueprint $table): void {
            $table->dropIndex(['haris_plant_id']);
            $table->dropColumn('haris_plant_id');
        });
    }
};
