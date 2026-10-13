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
        return ! $enrollments
            ->filter(fn (TrainingEnrollment $item): bool => $item->training->level < $enrollment->training->level)
            ->contains(fn (TrainingEnrollment $item): bool => $this->blockFor($item) < 2);
    }

    /** @param Collection<int, TrainingEnrollment> $enrollments */
    public function currentLevel(Collection $enrollments): int
    {
        $completedLevels = $enrollments
            ->groupBy(fn (TrainingEnrollment $enrollment): int => $enrollment->training->level)
            ->filter(fn (Collection $items): bool => $items->isNotEmpty() && $items->every(fn (TrainingEnrollment $item): bool => $this->blockFor($item) === 2))
            ->keys()
            ->map(fn (mixed $level): int => (int) $level);

        return $completedLevels->isEmpty() ? 0 : $completedLevels->max();
    }

    public function labelFor(TrainingEnrollment $enrollment): string
    {
        return match ($this->blockFor($enrollment)) {
            2 => 'Balok 2 - Training disetujui OJT',
            1 => 'Balok 1 - Post-test lulus, menunggu OJT',
            default => 'Belum mencapai balok',
        };
    }
}
