<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\OperatorSkillMatrix;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\TrainingEnrollment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    public function show(string $quizId)
    {
        $manpower = Auth::user()->manpower;
        if (! $manpower) {
            return redirect()->route('operator.dashboard')->with('error', 'Data karyawan tidak ditemukan.');
        }

        $quiz = Quiz::with(['training.skill', 'questions'])->findOrFail($quizId);
        $enrollment = TrainingEnrollment::where('manpower_id', $manpower->id)
            ->where('training_id', $quiz->training_id)->firstOrFail();

        if ($redirect = $this->validateAssessmentAccess($quiz, $enrollment)) {
            return $redirect;
        }

        // The server, not the browser timer, is the source of truth for an active assessment.
        if ($enrollment->active_quiz_id === $quiz->id && $enrollment->assessment_expires_at?->isPast()) {
            $this->finalizeTimeout($quiz, $enrollment, $manpower->id);

            return redirect()->route('operator.materials.show', $quiz->training_id)
                ->with('error', 'Waktu assessment telah habis. Hasil percobaan dicatat oleh sistem.');
        }

        if ($enrollment->active_quiz_id !== $quiz->id) {
            $enrollment->forceFill([
                'active_quiz_id' => $quiz->id,
                'assessment_started_at' => now(),
                'assessment_expires_at' => now()->addMinutes($quiz->durasi_menit),
            ])->save();
            $enrollment->refresh();
        }

        return view('operator.quiz-show', compact('quiz', 'enrollment'));
    }

    public function submit(Request $request, string $quizId)
    {
        $manpower = Auth::user()->manpower;
        if (! $manpower) {
            return redirect()->route('operator.dashboard')->with('error', 'Data karyawan tidak terdaftar.');
        }

        $quiz = Quiz::with(['training.skill', 'questions'])->findOrFail($quizId);
        if ($quiz->questions->isEmpty()) {
            return back()->with('error', 'Assessment belum memiliki soal.');
        }

        $answers = $request->input('answers', []);

        return DB::transaction(function () use ($answers, $quiz, $manpower) {
            $enrollment = TrainingEnrollment::where('manpower_id', $manpower->id)
                ->where('training_id', $quiz->training_id)->lockForUpdate()->firstOrFail();

            if ($redirect = $this->validateAssessmentAccess($quiz, $enrollment)) {
                return $redirect;
            }

            if ($enrollment->active_quiz_id !== $quiz->id || ! $enrollment->assessment_expires_at) {
                return back()->with('error', 'Sesi assessment tidak ditemukan. Mulai assessment kembali dari halaman materi.');
            }

            if ($enrollment->assessment_expires_at->isPast()) {
                $this->recordOutcome($quiz, $enrollment, $manpower->id, 0, now());

                return redirect()->route('operator.materials.show', $quiz->training_id)
                    ->with('error', 'Waktu assessment telah habis. Hasil percobaan dicatat oleh sistem.');
            }

            $correctCount = 0;
            foreach ($quiz->questions as $question) {
                if (strtolower((string) ($answers[$question->id] ?? '')) === strtolower($question->kunci_jawaban)) {
                    $correctCount++;
                }
            }
            $score = round(($correctCount / $quiz->questions->count()) * 100, 2);
            [$attempt, $isPassed, $passingGrade] = $this->recordOutcome($quiz, $enrollment, $manpower->id, $score, now());

            return view('operator.quiz-result', compact('quiz', 'attempt', 'enrollment', 'passingGrade', 'score', 'isPassed'));
        });
    }

    private function validateAssessmentAccess(Quiz $quiz, TrainingEnrollment $enrollment): ?RedirectResponse
    {
        if ($enrollment->status === 'failed_escalated') {
            return redirect()->route('operator.dashboard')->with('error', 'Akses assessment ditutup karena sudah mencapai batas percobaan. Hubungi HR atau atasan.');
        }

        if ($quiz->tipe === 'pre_test') {
            return $enrollment->pre_test_completed_at
                ? redirect()->route('operator.materials.show', $quiz->training_id)->with('error', 'Pre-test untuk training ini sudah selesai.')
                : null;
        }

        if ($enrollment->status === 'passed') {
            return redirect()->route('operator.dashboard')->with('error', 'Anda sudah lulus post-test training ini.');
        }
        if ($quiz->training->preTest()->exists() && ! $enrollment->pre_test_completed_at) {
            return redirect()->route('operator.quizzes.show', $quiz->training->preTest->id)
                ->with('error', 'Selesaikan pre-test sebelum mempelajari materi.');
        }
        if (! $enrollment->material_completed_at) {
            return redirect()->route('operator.materials.show', $quiz->training_id)
                ->with('error', 'Tandai materi selesai dipelajari sebelum memulai post-test.');
        }
        if ($enrollment->next_attempt_at?->isFuture()) {
            return redirect()->route('operator.dashboard')->with(
                'error',
                'Post-test dapat dicoba kembali pada ' . $enrollment->next_attempt_at->translatedFormat('d M Y, H:i') . '.'
            );
        }

        return null;
    }

    private function finalizeTimeout(Quiz $quiz, TrainingEnrollment $enrollment, int $manpowerId): void
    {
        DB::transaction(function () use ($quiz, $enrollment, $manpowerId) {
            $locked = TrainingEnrollment::whereKey($enrollment->id)->lockForUpdate()->firstOrFail();
            if ($locked->active_quiz_id === $quiz->id && $locked->assessment_expires_at?->isPast()) {
                $this->recordOutcome($quiz, $locked, $manpowerId, 0, $locked->assessment_expires_at);
            }
        });
    }

    /** @return array{QuizAttempt, bool, int} */
    private function recordOutcome(Quiz $quiz, TrainingEnrollment $enrollment, int $manpowerId, float $score, $submittedAt): array
    {
        $passingGrade = $quiz->passing_grade ?? $quiz->training->passing_grade ?? 80;
        $isPassed = $score >= $passingGrade;
        $attempt = QuizAttempt::create([
            'enrollment_id' => $enrollment->id,
            'manpower_id' => $manpowerId,
            'quiz_id' => $quiz->id,
            'attempt_number' => QuizAttempt::where('enrollment_id', $enrollment->id)->where('quiz_id', $quiz->id)->count() + 1,
            'score' => $score,
            'is_passed' => $isPassed,
            'submitted_at' => $submittedAt,
        ]);

        $enrollment->forceFill([
            'active_quiz_id' => null,
            'assessment_started_at' => null,
            'assessment_expires_at' => null,
        ]);

        if ($quiz->tipe === 'pre_test') {
            // Diagnostic only: it never changes the competency level or retry counter.
            $enrollment->pre_test_completed_at = now();
            $enrollment->status = 'in_progress';
            $enrollment->save();

            return [$attempt, $isPassed, $passingGrade];
        }

        if ($isPassed) {
            $enrollment->status = 'passed';
            $enrollment->next_attempt_at = null;
            $enrollment->save();

            $skill = $quiz->training->skill;
            $level = $quiz->training->level;
            $matrix = OperatorSkillMatrix::firstOrCreate(
                ['manpower_id' => $manpowerId, 'skill_id' => $skill->id],
                ['current_level' => 0, 'target_level' => $level, 'status_gap' => 'Gap -' . $level . ' Level']
            );
            if ($level > $matrix->current_level) {
                $matrix->current_level = $level;
                $matrix->last_updated_at = now();
                $matrix->updateGapStatus();
            }
        } else {
            $enrollment->current_retry_count++;
            $enrollment->material_completed_at = null;
            $enrollment->next_attempt_at = null;
            $maxRetries = $quiz->max_retries ?? $quiz->training->max_retries ?? 3;

            if ($enrollment->current_retry_count >= $maxRetries) {
                $enrollment->status = 'failed_escalated';
            } else {
                $enrollment->status = 'in_progress';
                $enrollment->next_attempt_at = now()->addHours($quiz->cooldown_hours ?: 24);
            }
            $enrollment->save();
        }

        return [$attempt, $isPassed, $passingGrade];
    }
}
