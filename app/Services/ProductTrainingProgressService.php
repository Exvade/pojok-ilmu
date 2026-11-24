<?php

namespace App\Services;

use App\Models\TrainingEnrollment;
use Illuminate\Support\Collection;

class ProductTrainingProgressService
{
    public function blockFor(TrainingEnrollment $enrollment): int
    {
        if ($enrollment->status === 'passed' && $enrollment->ojt_status === 'approved') {
            return 2;
        }

        return $enrollment->post_test_completed_at ? 1 : 0;
    }

    /** @param Collection<int, TrainingEnrollment> $enrollments */
    public function isUnlocked(TrainingEnrollment $enrollment, Collection $enrollments): bool
    {
        return $this->blockingEnrollments($enrollment, $enrollments)->isEmpty()
            && $this->missingPrerequisiteLevels($enrollment, $enrollments) === [];
    }

    /** @return array<int, int> */
    public function missingPrerequisiteLevels(TrainingEnrollment $enrollment, Collection $enrollments): array
    {
        $targetLevel = $enrollment->training->level;
        if ($targetLevel <= 1) {
            return [];
        }

        $assignedLevels = $enrollments
            ->filter(fn (TrainingEnrollment $item): bool => $item->training->level < $targetLevel)
            ->groupBy(fn (TrainingEnrollment $item): int => $item->training->level);

        return collect(range(1, $targetLevel - 1))
            ->filter(fn (int $level): bool => ! $assignedLevels->has($level))
            ->values()
            ->all();
    }

    /** @return Collection<int, TrainingEnrollment> */
    public function blockingEnrollments(TrainingEnrollment $enrollment, Collection $enrollments): Collection
    {
        return $enrollments
            ->filter(fn (TrainingEnrollment $item): bool => $item->training->level < $enrollment->training->level)
            ->filter(fn (TrainingEnrollment $item): bool => $this->blockFor($item) < 2)
            ->values();
    }

    /** @param Collection<int, TrainingEnrollment> $enrollments */
    public function currentLevel(Collection $enrollments): int
    {
        $levels = $enrollments->groupBy(fn (TrainingEnrollment $enrollment): int => $enrollment->training->level);
        $currentLevel = 0;

        foreach (range(1, 3) as $level) {
            $levelEnrollments = $levels->get($level);
            if (! $levelEnrollments instanceof Collection || $levelEnrollments->isEmpty()) {
                break;
            }

            if (! $levelEnrollments->every(fn (TrainingEnrollment $item): bool => $this->blockFor($item) === 2)) {
                break;
            }

            $currentLevel = $level;
        }

        return $currentLevel;
    }

    /**
     * Level kompetensi per produk S2S: level training tertinggi untuk produk tersebut yang sudah Balok 2.
     *
     * @param  Collection<int, TrainingEnrollment>  $enrollments
     * @return array<string, int>
     */
    public function productCompetencyLevels(Collection $enrollments): array
    {
        $levels = [];

        foreach ($enrollments as $enrollment) {
            if ($this->blockFor($enrollment) !== 2) {
                continue;
            }

            foreach ($enrollment->training->s2sProducts as $product) {
                $levels[$product->id] = max($levels[$product->id] ?? 0, (int) $enrollment->training->level);
            }
        }

        return $levels;
    }

    /**
     * Baris tabel training operator, dikelompokkan per level training. Satu baris per produk S2S;
     * training tanpa produk tetap tampil satu baris memakai judul training.
     *
     * @param  Collection<int, TrainingEnrollment>  $enrollments
     * @return Collection<int, Collection<int, array{enrollment: TrainingEnrollment, part_name: string, part_number: ?string, competency_level: int}>>
     */
    public function levelRows(Collection $enrollments): Collection
    {
        $productLevels = $this->productCompetencyLevels($enrollments);

        return $enrollments
            ->flatMap(function (TrainingEnrollment $enrollment) use ($productLevels): array {
                $products = $enrollment->training->s2sProducts->sortBy('kode_part')->values();

                if ($products->isEmpty()) {
                    return [[
                        'enrollment' => $enrollment,
                        'part_name' => $enrollment->training->judul,
                        'part_number' => null,
                        'competency_level' => $this->blockFor($enrollment) === 2 ? (int) $enrollment->training->level : 0,
                    ]];
                }

                return $products->map(fn ($product): array => [
                    'enrollment' => $enrollment,
                    'part_name' => $product->nama_part ?: $enrollment->training->judul,
                    'part_number' => $product->kode_part,
                    'competency_level' => $productLevels[$product->id] ?? 0,
                ])->all();
            })
            ->groupBy(fn (array $row): int => (int) $row['enrollment']->training->level)
            ->sortKeys();
    }

    public function labelFor(TrainingEnrollment $enrollment): string
    {
        return match ($this->blockFor($enrollment)) {
            2 => 'Balok 2 - Mampu mengerjakan dengan bimbingan',
            1 => 'Balok 1 - Lulus post-test',
            default => 'Belum mencapai balok',
        };
    }
}
