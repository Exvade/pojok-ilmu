<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\TrainingEnrollment;
use App\Services\ProductTrainingProgressService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ProfileController extends Controller
{
    public function show(Request $request, ProductTrainingProgressService $progress): View
    {
        $manpower = $request->user()->manpower;
        abort_unless($manpower, 404);

        $enrollments = TrainingEnrollment::with(['training', 'attempts.quiz', 'ojtAssessment.assessor'])
            ->where('manpower_id', $manpower->id)
            ->orderBy('created_at')
            ->get();
        $enrollments->each(function (TrainingEnrollment $enrollment) use ($enrollments, $progress): void {
            $blockingEnrollments = $progress->blockingEnrollments($enrollment, $enrollments);

            $enrollment->setAttribute('block', $progress->blockFor($enrollment));
            $enrollment->setAttribute('is_unlocked', $blockingEnrollments->isEmpty());
            $enrollment->setAttribute('blocking_training_titles', $blockingEnrollments
                ->map(fn (TrainingEnrollment $item): string => $item->training->judul)
                ->all());
        });
        $levels = $enrollments
            ->groupBy(fn (TrainingEnrollment $enrollment): int => $enrollment->training->level)
            ->sortKeys()
            ->map(function (Collection $levelEnrollments, int $level) use ($enrollments, $progress): array {
                $firstEnrollment = $levelEnrollments->first();

                return [
                    'number' => $level,
                    'enrollments' => $levelEnrollments,
                    'completed' => $levelEnrollments->where('block', 2)->count(),
                    'is_complete' => $levelEnrollments->every(fn (TrainingEnrollment $item): bool => $item->block === 2),
                    'is_unlocked' => $progress->isUnlocked($firstEnrollment, $enrollments),
                    'blocking_training_titles' => $progress->blockingEnrollments($firstEnrollment, $enrollments)
                        ->map(fn (TrainingEnrollment $item): string => $item->training->judul)
                        ->all(),
                ];
            })
            ->values();
        $currentLevel = $progress->currentLevel($enrollments);
        $nextLevel = $levels->first(fn (array $level): bool => ! $level['is_complete']);

        return view('operator.profile', compact('manpower', 'enrollments', 'levels', 'currentLevel', 'nextLevel'));
    }

    public function ojtResult(Request $request, string $enrollment): View
    {
        $manpower = $request->user()->manpower;
        abort_unless($manpower, 404);

        $enrollment = TrainingEnrollment::with(['training', 'ojtAssessment.assessor'])
            ->whereKey($enrollment)
            ->where('manpower_id', $manpower->id)
            ->firstOrFail();

        return view('operator.ojt-result', compact('enrollment'));
    }
}
