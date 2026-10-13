<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Training;
use App\Models\TrainingEnrollment;
use App\Models\TrainingMaterialProgress;
use App\Services\ProductTrainingProgressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaterialController extends Controller
{
    public function show(string $trainingId, ProductTrainingProgressService $progress)
    {
        $user = Auth::user();
        $manpower = $user->manpower;

        if (! $manpower) {
            return redirect()->route('operator.dashboard')->with('error', 'Data karyawan tidak ditemukan.');
        }

        $training = Training::with(['skill', 'materials', 'preTest.questions', 'postTest.questions'])->findOrFail($trainingId);
        $enrollment = TrainingEnrollment::where('manpower_id', $manpower->id)
            ->where('training_id', $training->id)
            ->with('materialProgresses')
            ->firstOrFail();

        $allEnrollments = TrainingEnrollment::with('training')->where('manpower_id', $manpower->id)->get();
        if (! $progress->isUnlocked($enrollment, $allEnrollments)) {
            return redirect()->route('operator.dashboard')->with('error', 'Selesaikan OJT seluruh training pada level sebelumnya sebelum membuka training ini.');
        }

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

        $completedMaterialIds = $enrollment->materialProgresses
            ->whereNotNull('completed_at')
            ->pluck('training_material_id');

        return view('operator.material-show', compact('training', 'enrollment', 'completedMaterialIds'));
    }

    public function completeMaterial(Request $request, string $trainingId, string $materialId, ProductTrainingProgressService $progress)
    {
        $manpower = Auth::user()->manpower;
        if (! $manpower) {
            return redirect()->route('operator.dashboard')->with('error', 'Data karyawan tidak ditemukan.');
        }

        $training = Training::with('materials')->findOrFail($trainingId);
        $enrollment = TrainingEnrollment::where('manpower_id', $manpower->id)
            ->where('training_id', $training->id)
            ->firstOrFail();
        $allEnrollments = TrainingEnrollment::with('training')->where('manpower_id', $manpower->id)->get();
        if (! $progress->isUnlocked($enrollment, $allEnrollments)) {
            return redirect()->route('operator.dashboard')->with('error', 'Training ini belum terbuka.');
        }
        $material = $training->materials->firstWhere('id', $materialId);

        if (! $material) {
            abort(404);
        }
        if ($enrollment->status === 'failed_escalated') {
            return redirect()->route('operator.dashboard')->with('error', 'Training ini sedang menunggu tindak lanjut HR atau atasan.');
        }
        if ($training->preTest()->exists() && ! $enrollment->pre_test_completed_at) {
            return redirect()->route('operator.quizzes.show', $training->preTest->id)
                ->with('error', 'Selesaikan pre-test terlebih dahulu.');
        }

        TrainingMaterialProgress::updateOrCreate(
            ['enrollment_id' => $enrollment->id, 'training_material_id' => $material->id],
            ['manpower_id' => $manpower->id, 'completed_at' => now()]
        );

        if ($enrollment->status === 'enrolled') {
            $enrollment->update(['status' => 'in_progress']);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Aktivitas belajar materi berhasil dicatat.',
                'data' => ['material_id' => $material->id],
                'errors' => null,
            ]);
        }

        return back()->with('success', 'Aktivitas belajar materi berhasil dicatat.');
    }

    public function complete(string $trainingId, ProductTrainingProgressService $progress)
    {
        $manpower = Auth::user()->manpower;
        if (! $manpower) {
            return redirect()->route('operator.dashboard')->with('error', 'Data karyawan tidak ditemukan.');
        }

        $training = Training::with(['materials', 'postTest.questions'])->findOrFail($trainingId);
        $enrollment = TrainingEnrollment::where('manpower_id', $manpower->id)
            ->where('training_id', $training->id)
            ->firstOrFail();
        $allEnrollments = TrainingEnrollment::with('training')->where('manpower_id', $manpower->id)->get();
        if (! $progress->isUnlocked($enrollment, $allEnrollments)) {
            return redirect()->route('operator.dashboard')->with('error', 'Training ini belum terbuka.');
        }

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
        $completedMaterials = TrainingMaterialProgress::query()
            ->where('enrollment_id', $enrollment->id)
            ->whereNotNull('completed_at')
            ->count();
        if ($training->materials->isNotEmpty() && $completedMaterials < $training->materials->count()) {
            return back()->with('error', 'Selesaikan seluruh materi sebelum memulai post-test.');
        }
        if (! $training->postTest || $training->postTest->questions->isEmpty()) {
            return back()->with('error', 'Post-test belum disiapkan oleh pengelola.');
        }

        $enrollment->update(['material_completed_at' => now(), 'status' => 'in_progress']);

        return redirect()->route('operator.quizzes.show', $training->postTest->id)
            ->with('success', 'Materi telah ditandai selesai. Anda dapat memulai post-test.');
    }
}
