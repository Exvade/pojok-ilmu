<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('trainings')->where('level', '>', 3)->update(['level' => 3]);
        DB::table('m_skill_target_levels')->where('target_level', '>', 3)->update(['target_level' => 3]);
        DB::table('operator_skill_matrices')->where('current_level', '>', 3)->update(['current_level' => 3]);
        DB::table('operator_skill_matrices')->where('target_level', '>', 3)->update(['target_level' => 3]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nilai level sebelumnya tidak dapat dipulihkan dengan aman.
    }
};
