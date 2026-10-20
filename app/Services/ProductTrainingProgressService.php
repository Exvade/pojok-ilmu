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

    public function labelFor(TrainingEnrollment $enrollment): string
    {
        return match ($this->blockFor($enrollment)) {
            2 => 'Balok 2 - Mampu mengerjakan dengan bimbingan',
            1 => 'Balok 1 - Lulus post-test',
            default => 'Belum mencapai balok',
        };
    }
}
