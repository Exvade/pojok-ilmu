<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Training;
use App\Models\TrainingEnrollment;
use Illuminate\Support\Facades\Auth;

class MaterialController extends Controller
{
    public function show(string $trainingId)
    {
        $user = Auth::user();
        $manpower = $user->manpower;

        $training = Training::with(['skill', 'materials', 'quiz.questions'])->findOrFail($trainingId);

        // Update enrollment status to in_progress
        if ($manpower) {
            $enrollment = TrainingEnrollment::where('manpower_id', $manpower->id)
                ->where('training_id', $training->id)
                ->first();

            if ($enrollment && $enrollment->status === 'enrolled') {
                $enrollment->status = 'in_progress';
                $enrollment->save();
            }
        }

        return view('operator.material-show', compact('training'));
    }
}
