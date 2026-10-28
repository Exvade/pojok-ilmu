<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $plantId = (string) config('services.haris.scope_plant_id', '3');
        $pasirGombongDepartments = DB::table('m_manpower')
            ->where('status', true)
            ->where('haris_plant_id', $plantId)
            ->where('departemen', 'like', 'PRODUKSI PLASTIK%')
            ->distinct()
            ->orderBy('departemen')
            ->pluck('departemen')
            ->map(fn (string $department): string => strtoupper(trim($department)))
            ->values()
            ->all();

        if ($pasirGombongDepartments === []) {
            return;
        }

        $pasirGombongLookup = array_fill_keys($pasirGombongDepartments, true);

        DB::table('trainings')->orderBy('id')->each(function (object $training) use ($pasirGombongDepartments, $pasirGombongLookup): void {
            $departments = json_decode((string) $training->departemens, true);
            $departments = is_array($departments) ? $departments : array_filter([$training->departemen]);
            $departments = collect($departments)
                ->map(fn (mixed $department): string => strtoupper(trim((string) $department)))
                ->filter()
                ->values();

            if (! $departments->contains(fn (string $department): bool => str_starts_with($department, 'PRODUKSI PLASTIK'))) {
                return;
            }

            $normalized = $departments
                ->flatMap(function (string $department) use ($pasirGombongDepartments, $pasirGombongLookup): array {
                    if ($department === 'PRODUKSI PLASTIK') {
                        return $pasirGombongDepartments;
                    }

                    if (str_starts_with($department, 'PRODUKSI PLASTIK')) {
                        return isset($pasirGombongLookup[$department]) ? [$department] : [];
                    }

                    return [$department];
                })
                ->unique()
                ->values()
                ->all();

            if ($normalized === $departments->all()) {
                return;
            }

            DB::table('trainings')->where('id', $training->id)->update([
                'departemen' => count($normalized) === 1 ? $normalized[0] : null,
                'departemens' => json_encode($normalized),
                'updated_at' => now(),
            ]);
        });
    }

    public function down(): void
    {
        // Data lintas plant yang telah dibuang tidak dapat dipulihkan secara aman.
    }
};
