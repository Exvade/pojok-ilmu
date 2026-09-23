<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class QuizController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'training_id' => 'required|exists:trainings,id',
            'judul' => 'required|string',
            'durasi_menit' => 'required|integer|min:1',
            'passing_grade' => 'required|integer|min:50|max:100',
            'max_retries' => 'required|integer|min:1',
        ]);

        Quiz::create([
            'training_id' => $request->training_id,
            'judul' => $request->judul,
            'durasi_menit' => $request->durasi_menit,
            'passing_grade' => $request->passing_grade,
            'max_retries' => $request->max_retries,
        ]);

        return back()->with('success', 'Modul Ujian / Kuis berhasil dibuat.');
    }

    public function storeQuestion(Request $request, string $quizId)
    {
        $quiz = Quiz::findOrFail($quizId);

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

        QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'pertanyaan' => $request->pertanyaan,
            'tipe_media' => $request->tipe_media,
            'media_url' => $mediaUrl,
            'opsi_a' => $request->opsi_a,
            'opsi_b' => $request->opsi_b,
            'opsi_c' => $request->opsi_c,
            'opsi_d' => $request->opsi_d,
            'kunci_jawaban' => strtolower($request->kunci_jawaban),
        ]);

        return back()->with('success', 'Soal kuis berhasil ditambahkan.');
    }

    public function destroyQuestion(string $id)
    {
        $question = QuizQuestion::findOrFail($id);
        $question->delete();

        return back()->with('success', 'Soal kuis berhasil dihapus.');
    }
}
