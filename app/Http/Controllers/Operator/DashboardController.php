<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\MSkillTargetLevel;
use App\Models\OperatorSkillMatrix;
use App\Models\Training;
use App\Models\TrainingEnrollment;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $manpower = $user->manpower;

        if (! $manpower) {
            return view('operator.dashboard', [
                'manpower' => null,
                'enrollments' => collect(),
                'matrices' => collect(),
            ]);
        }

        // 1. Auto-TNA Check: Check if there are target levels for operator's departemen & bagian
        $targetLevels = MSkillTargetLevel::where('departemen', $manpower->departemen)
            ->where('bagian', $manpower->bagian)
            ->with('skill.trainings')
            ->get();

        foreach ($targetLevels as $tl) {
            // Check or create Skill Matrix
            $matrix = OperatorSkillMatrix::firstOrCreate(
                [
                    'manpower_id' => $manpower->id,
                    'skill_id' => $tl->skill_id,
                ],
                [
                    'current_level' => 0,
                    'target_level' => $tl->target_level,
                    'status_gap' => 'Gap -' . $tl->target_level . ' Level',
                    'last_updated_at' => now(),
                ]
            );

            // Auto enroll into training if gap exists
            if ($matrix->current_level < $matrix->target_level) {
                // Find training for next level (current_level + 1)
                $nextLevel = $matrix->current_level + 1;
                $training = Training::where('skill_id', $tl->skill_id)
                    ->where('level', $nextLevel)
                    ->first();

                if ($training) {
                    TrainingEnrollment::firstOrCreate(
                        [
                            'manpower_id' => $manpower->id,
                            'training_id' => $training->id,
                        ],
                        [
                            'status' => 'enrolled',
                            'current_retry_count' => 0,
                        ]
                    );
                }
            }
        }

        // Fetch operator enrollments & skill matrices
        $enrollments = TrainingEnrollment::with(['training.skill', 'training.quiz', 'attempts'])
            ->where('manpower_id', $manpower->id)
            ->latest()
            ->get();

        $matrices = OperatorSkillMatrix::with('skill')
            ->where('manpower_id', $manpower->id)
            ->get();

        return view('operator.dashboard', compact('manpower', 'enrollments', 'matrices'));
    }
}
