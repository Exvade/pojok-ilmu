<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $productionDepartments = DB::table('m_manpower')
            ->where('departemen', 'like', 'PRODUKSI PLASTIK%')
            ->distinct()
            ->orderBy('departemen')
            ->pluck('departemen')
            ->map(fn (string $department): string => strtoupper(trim($department)))
            ->values()
            ->all();

        if ($productionDepartments === []) {
            $productionDepartments = [
                'PRODUKSI PLASTIK A',
                'PRODUKSI PLASTIK B',
                'PRODUKSI PLASTIK C',
                'PRODUKSI PLASTIK CRUSHER, HELPER & EKSPOR',
            ];
        }

        DB::table('m_manpower')->orderBy('id')->each(function (object $manpower): void {
            DB::table('m_manpower')->where('id', $manpower->id)->update([
                'department_group' => strtoupper(trim((string) $manpower->departemen)),
                'department_group_override' => null,
            ]);
        });

        DB::table('trainings')->orderBy('id')->each(function (object $training) use ($productionDepartments): void {
            $departments = json_decode((string) $training->departemens, true);
            $departments = is_array($departments) ? $departments : array_filter([$training->departemen]);

            if (! in_array('PRODUKSI PLASTIK', $departments, true)) {
                return;
            }

            $departments = collect($departments)
                ->flatMap(fn (mixed $department): array => $department === 'PRODUKSI PLASTIK' ? $productionDepartments : [(string) $department])
                ->filter()
                ->unique()
                ->values()
                ->all();

            DB::table('trainings')->where('id', $training->id)->update([
                'departemen' => count($departments) === 1 ? $departments[0] : null,
                'departemens' => json_encode($departments),
            ]);
        });

        DB::table('m_skill_target_levels')
            ->where('departemen', 'PRODUKSI PLASTIK')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->each(function (object $target) use ($productionDepartments): void {
                foreach ($productionDepartments as $index => $department) {
                    if ($index === 0) {
                        DB::table('m_skill_target_levels')->where('id', $target->id)->update(['departemen' => $department]);

                        continue;
                    }

                    DB::table('m_skill_target_levels')->insert([
                        'id' => (string) Str::uuid(),
                        'departemen' => $department,
                        'bagian' => $target->bagian,
                        'skill_id' => $target->skill_id,
                        'target_level' => $target->target_level,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Pemisahan kembali ke data HaRis tidak dapat dibalikkan tanpa menyatukan departemen secara paksa.
    }
};
