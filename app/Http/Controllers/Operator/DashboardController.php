<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
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

        $enrollments = $progress->operatorEnrollments($manpower);
        $currentLevel = $progress->currentLevel($enrollments);

        $levelRows = $progress->levelRows($enrollments);

        return view('operator.dashboard', compact('manpower', 'enrollments', 'currentLevel', 'levelRows'));
    }
}
