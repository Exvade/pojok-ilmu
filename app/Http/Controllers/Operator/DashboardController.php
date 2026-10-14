<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
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
            ]);
        }

        $enrollments = TrainingEnrollment::with(['training.materials', 'training.preTest', 'training.postTest', 'ojtAssessment'])
            ->where('manpower_id', $manpower->id)
            ->orderBy('created_at')
            ->get();
        $enrollments->each(function (TrainingEnrollment $enrollment) use ($enrollments, $progress): void {
            $enrollment->setAttribute('block', $progress->blockFor($enrollment));
            $enrollment->setAttribute('is_unlocked', $progress->isUnlocked($enrollment, $enrollments));
            $enrollment->setAttribute('block_label', $progress->labelFor($enrollment));
        });
        $currentLevel = $progress->currentLevel($enrollments);

        return view('operator.dashboard', compact('manpower', 'enrollments', 'currentLevel'));
    }
}
