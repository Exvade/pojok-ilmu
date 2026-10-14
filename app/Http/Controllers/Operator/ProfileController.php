<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\TrainingEnrollment;
use App\Services\ProductTrainingProgressService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

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
        $enrollments->each(fn (TrainingEnrollment $enrollment) => $enrollment->setAttribute('block', $progress->blockFor($enrollment)));

        return view('operator.profile', compact('manpower', 'enrollments'));
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
