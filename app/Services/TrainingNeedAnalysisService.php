<?php

namespace App\Services;

use App\Models\MManpower;
use App\Models\MSkillTargetLevel;
use App\Models\OperatorSkillMatrix;
use App\Models\Training;
use App\Models\TrainingEnrollment;

class TrainingNeedAnalysisService
{
    /**
     * Build or refresh a single employee's matrix and next required training.
     * Existing current levels are never reset by this process.
     */
    public function synchronizeFor(MManpower $manpower): int
    {
        $targets = MSkillTargetLevel::query()
            ->where('departemen', $manpower->trainingDepartment())
            ->where('bagian', $manpower->bagian)
            ->get();

        $assigned = 0;

        foreach ($targets as $target) {
            $matrix = OperatorSkillMatrix::firstOrCreate(
                ['manpower_id' => $manpower->id, 'skill_id' => $target->skill_id],
                [
                    'current_level' => 0,
                    'target_level' => $target->target_level,
                    'status_gap' => 'Gap -' . $target->target_level . ' Level',
                    'last_updated_at' => now(),
                ],
            );

            if ($matrix->target_level !== (int) $target->target_level) {
                $matrix->target_level = $target->target_level;
                $matrix->last_updated_at = now();
                $matrix->updateGapStatus();
            }

            if ($matrix->current_level >= $matrix->target_level) {
                continue;
            }

            $training = Training::query()
                ->where('skill_id', $target->skill_id)
                ->where('level', $matrix->current_level + 1)
                ->first();

            if (! $training) {
                continue;
            }

            $enrollment = TrainingEnrollment::firstOrCreate(
                ['manpower_id' => $manpower->id, 'training_id' => $training->id],
                ['status' => 'enrolled', 'current_retry_count' => 0],
            );

            if ($enrollment->wasRecentlyCreated) {
                $assigned++;
            }
        }

        return $assigned;
    }
}
