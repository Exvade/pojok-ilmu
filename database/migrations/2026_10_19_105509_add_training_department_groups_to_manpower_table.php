<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('m_manpower', function (Blueprint $table) {
            $table->string('department_group')->nullable()->after('departemen')->index();
            $table->string('department_group_override')->nullable()->after('department_group')->index();
        });

        DB::table('m_manpower')->orderBy('id')->each(function (object $manpower): void {
            DB::table('m_manpower')->where('id', $manpower->id)->update([
                'department_group' => $this->departmentGroup($manpower->departemen),
            ]);
        });

        DB::table('trainings')->orderBy('id')->each(function (object $training): void {
            $departments = json_decode((string) $training->departemens, true);
            $departments = is_array($departments) ? $departments : array_filter([$training->departemen]);
            $departments = array_values(array_unique(array_filter(array_map(
                fn (mixed $department): ?string => $this->departmentGroup(is_string($department) ? $department : null),
                $departments,
            ))));

            DB::table('trainings')->where('id', $training->id)->update([
                'departemen' => count($departments) === 1 ? $departments[0] : null,
                'departemens' => json_encode($departments),
            ]);
        });

        DB::table('m_skill_target_levels')->orderBy('id')->each(function (object $target): void {
            DB::table('m_skill_target_levels')->where('id', $target->id)->update([
                'departemen' => $this->departmentGroup($target->departemen),
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('m_manpower', function (Blueprint $table) {
            $table->dropIndex(['department_group']);
            $table->dropIndex(['department_group_override']);
            $table->dropColumn(['department_group', 'department_group_override']);
        });
    }

    private function departmentGroup(?string $department): ?string
    {
        $department = trim((string) $department);

        if ($department === '') {
            return null;
        }

        $department = preg_replace('/\s+/', ' ', $department) ?? $department;
        $department = strtoupper($department);

        return str_starts_with($department, 'PRODUKSI PLASTIK') ? 'PRODUKSI PLASTIK' : $department;
    }
};
