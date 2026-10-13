<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\TrainingEnrollment;
use App\Services\ProductTrainingProgressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    public function show(string $quizId, ProductTrainingProgressService $progress)
    {
        $manpower = Auth::user()->manpower;
        if (! $manpower) {
            return redirect()->route('operator.dashboard')->with('error', 'Data karyawan tidak ditemukan.');
        }

        $quiz = Quiz::with(['training', 'questions'])->findOrFail($quizId);
        $enrollment = TrainingEnrollment::where('manpower_id', $manpower->id)
            ->where('training_id', $quiz->training_id)->firstOrFail();

        if ($redirect = $this->validateQuizAccess($quiz, $enrollment, $progress)) {
            return $redirect;
        }

        // The server, not the browser timer, is the source of truth for an active quiz.
        if ($enrollment->active_quiz_id === $quiz->id && $enrollment->assessment_expires_at?->isPast()) {
            $this->finalizeTimeout($quiz, $enrollment, $manpower->id);

            return redirect()->route('operator.materials.show', $quiz->training_id)
                ->with('error', 'Waktu pengerjaan telah habis. Hasil percobaan dicatat oleh sistem.');
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

    public function submit(Request $request, string $quizId, ProductTrainingProgressService $progress)
    {
        $manpower = Auth::user()->manpower;
        if (! $manpower) {
            return redirect()->route('operator.dashboard')->with('error', 'Data karyawan tidak terdaftar.');
        }

        $quiz = Quiz::with(['training', 'questions'])->findOrFail($quizId);
        if ($quiz->questions->isEmpty()) {
            return back()->with('error', 'Evaluasi belum memiliki soal.');
        }

        $answers = $request->input('answers', []);

        return DB::transaction(function () use ($answers, $quiz, $manpower, $progress) {
            $enrollment = TrainingEnrollment::where('manpower_id', $manpower->id)
                ->where('training_id', $quiz->training_id)->lockForUpdate()->firstOrFail();

            if ($redirect = $this->validateQuizAccess($quiz, $enrollment, $progress)) {
                return $redirect;
            }

            if ($enrollment->active_quiz_id !== $quiz->id || ! $enrollment->assessment_expires_at) {
                return back()->with('error', 'Sesi evaluasi tidak ditemukan. Mulai kembali dari halaman materi.');
            }

            if ($enrollment->assessment_expires_at->isPast()) {
                $this->recordOutcome($quiz, $enrollment, $manpower->id, 0, now());

                return redirect()->route('operator.materials.show', $quiz->training_id)
                    ->with('error', 'Waktu pengerjaan telah habis. Hasil percobaan dicatat oleh sistem.');
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

    private function validateQuizAccess(Quiz $quiz, TrainingEnrollment $enrollment, ProductTrainingProgressService $progress): ?RedirectResponse
    {
        if (! in_array($quiz->tipe, ['pre_test', 'post_test'], true)) {
            return redirect()->route('operator.dashboard')->with('error', 'Tahap ini belum digunakan pada alur training saat ini.');
        }

        $allEnrollments = TrainingEnrollment::with('training')->where('manpower_id', $enrollment->manpower_id)->get();
        if (! $progress->isUnlocked($enrollment, $allEnrollments)) {
            return redirect()->route('operator.dashboard')->with('error', 'Selesaikan OJT produk pada level sebelumnya terlebih dahulu.');
        }

        if ($enrollment->status === 'failed_escalated') {
            return redirect()->route('operator.dashboard')->with('error', 'Training ini belum dapat dilanjutkan karena batas percobaan telah tercapai.');
        }

        if ($quiz->tipe === 'pre_test') {
            return $enrollment->pre_test_completed_at
                ? redirect()->route('operator.materials.show', $quiz->training_id)->with('error', 'Pre-test untuk training ini sudah selesai.')
                : null;
        }

        if ($quiz->training->preTest()->exists() && ! $enrollment->pre_test_completed_at) {
            return redirect()->route('operator.quizzes.show', $quiz->training->preTest->id)
                ->with('error', 'Selesaikan pre-test sebelum mempelajari materi.');
        }

        if ($quiz->tipe === 'post_test') {
            if ($enrollment->post_test_completed_at) {
                return redirect()->route('operator.dashboard')->with('error', 'Post-test sudah lulus. Training menunggu proses OJT.');
            }
            if ($enrollment->next_attempt_at?->isFuture()) {
                return redirect()->route('operator.materials.show', $quiz->training_id)
                    ->with('error', 'Post-test dapat dicoba kembali pada ' . $enrollment->next_attempt_at->translatedFormat('d M Y, H:i') . '.');
            }
            if (! $enrollment->material_completed_at) {
                return redirect()->route('operator.materials.show', $quiz->training_id)
                    ->with('error', 'Tandai materi selesai dipelajari sebelum memulai post-test.');
            }

            return null;
        }

        return redirect()->route('operator.dashboard')->with('error', 'Tahap training tidak tersedia.');
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

        if ($quiz->tipe === 'post_test') {
            if ($isPassed) {
                $enrollment->post_test_completed_at = now();
                $enrollment->status = 'in_progress';
                $enrollment->ojt_status = 'pending';
            } else {
                // Karyawan kembali mempelajari materi sebelum mencoba lagi.
                $enrollment->current_retry_count++;
                $enrollment->material_completed_at = null;
                $enrollment->status = 'in_progress';
                $enrollment->next_attempt_at = now()->addHours($quiz->cooldown_hours ?: 24);
            }
            $enrollment->save();

            return [$attempt, $isPassed, $passingGrade];
        }

        return [$attempt, $isPassed, $passingGrade];
    }
}
