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

        if (! $manpower) {
            return redirect()->route('operator.dashboard')->with('error', 'Data karyawan tidak ditemukan.');
        }

        $training = Training::with(['skill', 'materials', 'preTest.questions', 'postTest.questions'])->findOrFail($trainingId);
        $enrollment = TrainingEnrollment::where('manpower_id', $manpower->id)
            ->where('training_id', $training->id)
            ->firstOrFail();

        if ($enrollment->status === 'failed_escalated') {
            return redirect()->route('operator.dashboard')->with('error', 'Training ini sedang menunggu tindak lanjut HR atau atasan.');
        }

        if ($training->preTest && ! $enrollment->pre_test_completed_at) {
            return redirect()->route('operator.quizzes.show', $training->preTest->id)
                ->with('error', 'Selesaikan pre-test terlebih dahulu sebelum membuka materi.');
        }

        if ($enrollment->status === 'enrolled') {
            $enrollment->update(['status' => 'in_progress']);
        }

        return view('operator.material-show', compact('training', 'enrollment'));
    }

    public function complete(string $trainingId)
    {
        $manpower = Auth::user()->manpower;
        if (! $manpower) {
            return redirect()->route('operator.dashboard')->with('error', 'Data karyawan tidak ditemukan.');
        }

        $training = Training::with('postTest.questions')->findOrFail($trainingId);
        $enrollment = TrainingEnrollment::where('manpower_id', $manpower->id)
            ->where('training_id', $training->id)
            ->firstOrFail();

        if ($enrollment->status === 'failed_escalated') {
            return redirect()->route('operator.dashboard')->with('error', 'Training ini sedang menunggu tindak lanjut HR atau atasan.');
        }
        if ($training->preTest()->exists() && ! $enrollment->pre_test_completed_at) {
            return redirect()->route('operator.quizzes.show', $training->preTest->id)
                ->with('error', 'Selesaikan pre-test terlebih dahulu.');
        }
        if ($enrollment->next_attempt_at?->isFuture()) {
            return redirect()->route('operator.dashboard')
                ->with('error', 'Masa tunggu remedial masih berjalan sampai ' . $enrollment->next_attempt_at->translatedFormat('d M Y, H:i') . '.');
        }
        if (! $training->postTest || $training->postTest->questions->isEmpty()) {
            return back()->with('error', 'Post-test belum disiapkan oleh pengelola.');
        }

        $enrollment->update(['material_completed_at' => now(), 'status' => 'in_progress']);

        return redirect()->route('operator.quizzes.show', $training->postTest->id)
            ->with('success', 'Materi telah ditandai selesai. Anda dapat memulai post-test.');
    }
}
