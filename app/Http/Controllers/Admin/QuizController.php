<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Training;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class QuizController extends Controller
{
    public function prepareEvaluationSet(string $trainingId)
    {
        try {
            DB::transaction(function () use ($trainingId): void {
                $training = Training::findOrFail($trainingId);

                foreach ([
                    'pre_test' => ['Pre-test ' . $training->judul, 10, 80],
                    'post_test' => ['Post-test ' . $training->judul, 15, $training->passing_grade],
                ] as $type => [$title, $duration, $passingGrade]) {
                    Quiz::firstOrCreate([
                        'training_id' => $training->id,
                        'tipe' => $type,
                    ], [
                        'judul' => $title,
                        'durasi_menit' => $duration,
                        'passing_grade' => $passingGrade,
                        'max_retries' => $training->max_retries,
                        'cooldown_hours' => 24,
                    ]);
                }
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'Tahap evaluasi belum dapat disiapkan. Silakan coba kembali.');
        }

        return back()->with('success', 'Pre-test dan post-test telah disiapkan. Isi soal pada bagian evaluasi.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'training_id' => 'required|exists:trainings,id',
            'judul' => 'required|string',
            'tipe' => 'required|in:pre_test,post_test',
            'durasi_menit' => 'required|integer|min:1',
            'passing_grade' => 'required|integer|min:50|max:100',
            'max_retries' => 'required|integer|min:1',
            'cooldown_hours' => 'required|integer|min:1|max:168',
        ]);

        $alreadyExists = Quiz::where('training_id', $request->training_id)
            ->where('tipe', $request->tipe)
            ->exists();

        if ($alreadyExists) {
            return back()->withInput()->withErrors(['tipe' => 'Jenis evaluasi ini sudah dibuat untuk training tersebut.']);
        }

        Quiz::create([
            'training_id' => $request->training_id,
            'judul' => $request->judul,
            'tipe' => $request->tipe,
            'durasi_menit' => $request->durasi_menit,
            'passing_grade' => $request->passing_grade,
            'max_retries' => $request->max_retries,
            'cooldown_hours' => $request->cooldown_hours,
        ]);

        return back()->with('success', match ($request->tipe) {
            'pre_test' => 'Modul pre-test berhasil dibuat.',
            default => 'Modul post-test berhasil dibuat.',
        });
    }

    public function storeQuestion(Request $request, string $quizId)
    {
        $quiz = Quiz::findOrFail($quizId);

        return $this->createQuestion($request, $quiz);
    }

    public function storeQuestionGeneric(Request $request)
    {
        $request->validate(['quiz_id' => 'required|exists:quizzes,id']);

        return $this->createQuestion($request, Quiz::findOrFail($request->quiz_id));
    }

    private function createQuestion(Request $request, Quiz $quiz)
    {
        $request->validate([
            'pertanyaan' => 'required|string',
            'tipe_media' => 'required|in:none,image,video_gdrive,video_youtube',
            'media_url' => 'nullable|string',
            'file_upload' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:3072',
            'opsi_a' => 'required|string',
            'opsi_b' => 'required|string',
            'opsi_c' => 'required|string',
            'opsi_d' => 'required|string',
            'kunci_jawaban' => 'required|in:a,b,c,d',
        ]);

        $mediaUrl = $request->media_url;

        if ($request->hasFile('file_upload')) {
            $path = $request->file('file_upload')->store('quiz_questions', 'public');
            $mediaUrl = Storage::url($path);
        }

        $quiz->loadMissing('training.quizzes');
        $evaluationQuizzes = $quiz->training->quizzes->whereIn('tipe', ['pre_test', 'post_test']);

        DB::transaction(function () use ($evaluationQuizzes, $quiz, $request, $mediaUrl): void {
            foreach ($evaluationQuizzes->isNotEmpty() ? $evaluationQuizzes : collect([$quiz]) as $evaluationQuiz) {
                QuizQuestion::create([
                    'quiz_id' => $evaluationQuiz->id,
                    'pertanyaan' => $request->pertanyaan,
                    'tipe_media' => $request->tipe_media,
                    'media_url' => $mediaUrl,
                    'opsi_a' => $request->opsi_a,
                    'opsi_b' => $request->opsi_b,
                    'opsi_c' => $request->opsi_c,
                    'opsi_d' => $request->opsi_d,
                    'kunci_jawaban' => strtolower($request->kunci_jawaban),
                ]);
            }
        });

        return back()->with('success', 'Soal berhasil ditambahkan untuk pre-test dan post-test.');
    }

    public function destroyQuestion(string $id)
    {
        $question = QuizQuestion::findOrFail($id);
        $question->load('quiz.training.quizzes');

        DB::transaction(function () use ($question): void {
            $question->delete();
            $siblingQuizIds = $question->quiz->training->quizzes
                ->whereIn('tipe', ['pre_test', 'post_test'])
                ->where('id', '!=', $question->quiz_id)
                ->pluck('id');
            QuizQuestion::query()
                ->whereIn('quiz_id', $siblingQuizIds)
                ->where('pertanyaan', $question->pertanyaan)
                ->where('opsi_a', $question->opsi_a)
                ->where('opsi_b', $question->opsi_b)
                ->where('opsi_c', $question->opsi_c)
                ->where('opsi_d', $question->opsi_d)
                ->delete();
        });

        return back()->with('success', 'Soal berhasil dihapus dari pre-test dan post-test.');
    }
}
