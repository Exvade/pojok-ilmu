<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Training;
use App\Models\TrainingEnrollment;
use App\Services\ProductTrainingProgressService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(ProductTrainingProgressService $progress)
    {
        $user = Auth::user();
        $manpower = $user->manpower;

        if (! $manpower) {
            return view('operator.dashboard', [
                'manpower' => null,
                'enrollments' => collect(),
                'currentLevel' => 0,
                'levelRows' => collect(),
            ]);
        }

        $enrollments = TrainingEnrollment::with(['training.materials', 'training.s2sProducts', 'training.preTest', 'training.postTest', 'ojtAssessment'])
            ->where('manpower_id', $manpower->id)
            ->orderBy(Training::select('kode_training')
                ->whereColumn('trainings.id', 'training_enrollments.training_id'))
            ->get();
        $enrollments->each(function (TrainingEnrollment $enrollment) use ($enrollments, $progress): void {
            $blockingEnrollments = $progress->blockingEnrollments($enrollment, $enrollments);

            $enrollment->setAttribute('block', $progress->blockFor($enrollment));
            $enrollment->setAttribute('is_unlocked', $progress->isUnlocked($enrollment, $enrollments));
            $enrollment->setAttribute('blocking_training_titles', $blockingEnrollments
                ->map(fn (TrainingEnrollment $item): string => $item->training->judul)
                ->all());
            $enrollment->setAttribute('block_label', $progress->labelFor($enrollment));
        });
        $currentLevel = $progress->currentLevel($enrollments);

        $levelRows = $progress->levelRows($enrollments);

        return view('operator.dashboard', compact('manpower', 'enrollments', 'currentLevel', 'levelRows'));
    }
}
