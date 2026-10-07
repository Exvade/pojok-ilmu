<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\OperatorSkillMatrix;
use App\Models\TrainingEnrollment;
use App\Services\TrainingNeedAnalysisService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(TrainingNeedAnalysisService $tna)
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

        // A fallback for newly synced employees; the scheduled command does this in batch.
        $tna->synchronizeFor($manpower);

        // Fetch operator enrollments & skill matrices
        $enrollments = TrainingEnrollment::with(['training.skill', 'training.preTest', 'training.postTest', 'attempts'])
            ->where('manpower_id', $manpower->id)
            ->latest()
            ->get();

        $matrices = OperatorSkillMatrix::with('skill')
            ->where('manpower_id', $manpower->id)
            ->get();

        return view('operator.dashboard', compact('manpower', 'enrollments', 'matrices'));
    }
}
