<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\OperatorSkillMatrix;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\TrainingEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    public function show(string $quizId)
    {
        $user = Auth::user();
        $manpower = $user->manpower;

        $quiz = Quiz::with(['training.skill', 'questions'])->findOrFail($quizId);

        if (! $manpower) {
            return redirect()->route('operator.dashboard')->with('error', 'Data Manpower tidak ditemukan.');
        }

        $enrollment = TrainingEnrollment::where('manpower_id', $manpower->id)
            ->where('training_id', $quiz->training_id)
            ->firstOrFail();

        // Check if enrollment is failed and escalated
        if ($enrollment->status === 'failed_escalated') {
            return redirect()->route('operator.dashboard')
                ->with('error', 'Anda telah melebihi batas pengulangan kuis. Kasus ini telah di-eskalasi ke HR/Atasan.');
        }

        return view('operator.quiz-show', compact('quiz', 'enrollment'));
    }

    public function submit(Request $request, string $quizId)
    {
        $user = Auth::user();
        $manpower = $user->manpower;

        if (! $manpower) {
            return redirect()->route('operator.dashboard')->with('error', 'Data Manpower tidak terdaftar.');
        }

        $quiz = Quiz::with(['training.skill', 'questions'])->findOrFail($quizId);
        $enrollment = TrainingEnrollment::where('manpower_id', $manpower->id)
            ->where('training_id', $quiz->training_id)
            ->firstOrFail();

        $answers = $request->input('answers', []); // array of [question_id => selected_option]
        $totalQuestions = $quiz->questions->count();

        if ($totalQuestions === 0) {
            return back()->with('error', 'Kuis belum memiliki soal.');
        }

        $correctCount = 0;

        foreach ($quiz->questions as $q) {
            $selected = strtolower($answers[$q->id] ?? '');
            if ($selected === strtolower($q->kunci_jawaban)) {
                $correctCount++;
            }
        }

        $score = round(($correctCount / $totalQuestions) * 100, 2);
        $passingGrade = $quiz->passing_grade ?? $quiz->training->passing_grade ?? 80;
        $isPassed = ($score >= $passingGrade);

        DB::beginTransaction();
        try {
            // Count attempt number
            $previousAttempts = QuizAttempt::where('enrollment_id', $enrollment->id)->count();
            $attemptNumber = $previousAttempts + 1;

            // Save Attempt
            $attempt = QuizAttempt::create([
                'enrollment_id' => $enrollment->id,
                'manpower_id' => $manpower->id,
                'quiz_id' => $quiz->id,
                'attempt_number' => $attemptNumber,
                'score' => $score,
                'is_passed' => $isPassed,
                'submitted_at' => now(),
            ]);

            if ($isPassed) {
                // 1. Mark enrollment as passed
                $enrollment->status = 'passed';
                $enrollment->save();

                // 2. UPDATE SKILL MATRIX (Output Utama System!)
                $skill = $quiz->training->skill;
                $trainingLevel = $quiz->training->level;

                $matrix = OperatorSkillMatrix::firstOrCreate(
                    [
                        'manpower_id' => $manpower->id,
                        'skill_id' => $skill->id,
                    ],
                    [
                        'current_level' => 0,
                        'target_level' => $trainingLevel,
                        'status_gap' => 'Gap -' . $trainingLevel . ' Level',
                    ]
                );

                // Update current level if higher than previous
                if ($trainingLevel > $matrix->current_level) {
                    $matrix->current_level = $trainingLevel;
                    $matrix->last_updated_at = now();
                    $matrix->updateGapStatus();
                }
            } else {
                // Increment retry count
                $enrollment->current_retry_count += 1;
                $maxRetries = $quiz->max_retries ?? $quiz->training->max_retries ?? 3;

                if ($enrollment->current_retry_count >= $maxRetries) {
                    $enrollment->status = 'failed_escalated';
                }

                $enrollment->save();
            }

            DB::commit();

            return view('operator.quiz-result', compact('quiz', 'attempt', 'enrollment', 'passingGrade', 'score', 'isPassed'));
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal memproses hasil kuis: ' . $e->getMessage());
        }
    }
}
