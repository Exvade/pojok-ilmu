<?php

namespace App\Services;

use App\Models\MManpower;
use App\Models\Training;
use App\Models\TrainingEnrollment;
use Illuminate\Support\Collection;

class TrainingAssignmentTargetService
{
    /** @var array<int, int> */
    public const TARGETS = [1 => 6, 2 => 5, 3 => 3];

    /**
     * @param  Collection<int, MManpower>  $manpowers
     * @param  array<string|int, array<int, int>>  $incomingTrainingIds
     * @return Collection<string|int, array{levels: array<int, int>, gaps: array<int, int>, total: int, meets_target: bool}>
     */
    public function summaries(Collection $manpowers, array $incomingTrainingIds = []): Collection
    {
        if ($manpowers->isEmpty()) {
            return collect();
        }

        $manpowerIds = $manpowers->pluck('id')->all();
        $assignments = [];

        TrainingEnrollment::query()
            ->join('trainings', 'training_enrollments.training_id', '=', 'trainings.id')
            ->whereIn('training_enrollments.manpower_id', $manpowerIds)
            ->whereNull('trainings.deleted_at')
            ->get(['training_enrollments.manpower_id', 'training_enrollments.training_id', 'trainings.level'])
            ->each(function (object $assignment) use (&$assignments): void {
                $assignments[$assignment->manpower_id][$assignment->training_id] = (int) $assignment->level;
            });

        $incomingIds = collect($incomingTrainingIds)->flatten()->unique()->values();
        $incomingLevels = Training::query()
            ->whereIn('id', $incomingIds)
            ->pluck('level', 'id');

        foreach ($incomingTrainingIds as $manpowerId => $trainingIds) {
            foreach ($trainingIds as $trainingId) {
                if ($incomingLevels->has($trainingId)) {
                    $assignments[$manpowerId][$trainingId] = (int) $incomingLevels->get($trainingId);
                }
            }
        }

        return $manpowers->mapWithKeys(function (MManpower $manpower) use ($assignments): array {
            $levels = [1 => 0, 2 => 0, 3 => 0];
            foreach ($assignments[$manpower->id] ?? [] as $level) {
                $levels[$level]++;
            }

            $gaps = collect(self::TARGETS)
                ->mapWithKeys(fn (int $target, int $level): array => [$level => max(0, $target - $levels[$level])])
                ->all();

            return [$manpower->id => [
                'levels' => $levels,
                'gaps' => $gaps,
                'total' => array_sum($levels),
                'meets_target' => array_sum($gaps) === 0,
            ]];
        });
    }
}
