<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MManpower;
use App\Models\OperatorSkillMatrix;
use App\Models\Training;
use App\Models\TrainingEnrollment;
use App\Services\ProductTrainingProgressService;
use App\Services\TrainingNeedAnalysisService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SkillMatrixController extends Controller
{
    public function index(Request $request, ProductTrainingProgressService $progress): View
    {
        $departemenFilter = trim($request->string('departemen')->toString());
        $search = trim($request->string('search')->toString());

        if ($departemenFilter === '' && ! $request->has('departemen')) {
            $departemenFilter = $request->user()?->manpower?->trainingDepartment() ?? '';
        }

        $manpowerQuery = MManpower::query()
            ->where('status', true)
            ->whereNotNull('haris_synced_at')
            ->when($departemenFilter !== '', fn ($query) => $query->forTrainingDepartment($departemenFilter))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nestedQuery) use ($search): void {
                    $nestedQuery->where('nama', 'like', "%{$search}%")
                        ->orWhere('mp_id', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%");
                });
            });

        $manpowerIds = (clone $manpowerQuery)->pluck('id');
        $products = Training::query()
            ->whereHas('enrollments', fn ($query) => $query->whereIn('manpower_id', $manpowerIds))
            ->when($departemenFilter !== '', fn ($query) => $query->forDepartment($departemenFilter))
            ->orderBy('level')
            ->orderBy('judul')
            ->get();
        $productIds = $products->pluck('id');
        $enrollments = TrainingEnrollment::query()
            ->whereIn('manpower_id', $manpowerIds)
            ->whereIn('training_id', $productIds)
            ->get();

        $manpowers = $manpowerQuery
            ->with(['enrollments' => fn ($query) => $query->whereIn('training_id', $productIds)->with('training')])
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();
        $manpowers->getCollection()->each(function (MManpower $manpower) use ($progress): void {
            $manpower->enrollments->each(function (TrainingEnrollment $enrollment) use ($progress): void {
                $enrollment->setAttribute('block', $progress->blockFor($enrollment));
            });
        });

        $departemens = MManpower::query()
            ->where('status', true)
            ->whereNotNull('haris_synced_at')
            ->get()
            ->map(fn (MManpower $manpower): ?string => $manpower->trainingDepartment())
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return view('admin.product-matrix-clean', compact(
            'manpowerIds',
            'manpowers',
            'products',
            'enrollments',
            'departemens',
            'departemenFilter',
            'search'
        ));
    }

    public function showManpower(string $id, ProductTrainingProgressService $progress): View
    {
        $manpower = MManpower::with([
            'enrollments.training',
            'enrollments.attempts.quiz',
            'enrollments.ojtAssessment.assessor',
        ])->findOrFail($id);
        $enrollments = $manpower->enrollments
            ->sortBy([
                fn (TrainingEnrollment $enrollment): int => $enrollment->training?->level ?? 0,
                fn (TrainingEnrollment $enrollment): string => $enrollment->training?->judul ?? '',
            ])
            ->values();

        $enrollments->each(function (TrainingEnrollment $enrollment) use ($progress): void {
            $enrollment->setAttribute('block', $progress->blockFor($enrollment));
            $enrollment->setAttribute('block_label', $progress->labelFor($enrollment));
        });

        $currentLevel = $progress->currentLevel($enrollments);
        $totalBalokSatu = $enrollments->where('block', 1)->count();
        $totalBalokDua = $enrollments->where('block', 2)->count();

        return view('admin.manpower-training-profile', compact(
            'manpower',
            'enrollments',
            'currentLevel',
            'totalBalokSatu',
            'totalBalokDua'
        ));
    }

    /**
     * Reset Kuota Remedi Operator (Eskalasi Handled)
     */
    public function resetRemedi(Request $request, string $enrollmentId)
    {
        $enrollment = TrainingEnrollment::findOrFail($enrollmentId);
        $enrollment->status = 'in_progress';
        $enrollment->current_retry_count = 0;
        $enrollment->save();

        return back()->with('success', 'Kuota remedi operator berhasil di-reset oleh HR/Admin.');
    }

    /**
     * Record a validated initial level before the employee starts the next gap training.
     */
    public function setInitialLevel(Request $request, string $matrixId, TrainingNeedAnalysisService $tna)
    {
        $data = $request->validate([
            'current_level' => 'required|integer|min:0|max:4',
        ]);

        $matrix = OperatorSkillMatrix::with(['manpower', 'skill'])->findOrFail($matrixId);
        $level = (int) $data['current_level'];

        $matrix->current_level = $level;
        $matrix->last_updated_at = now();
        $matrix->updateGapStatus();

        // Automatically generated, untouched lower-level assignments are no longer relevant
        // once HR has validated a higher baseline. In-progress work is left untouched.
        TrainingEnrollment::query()
            ->where('manpower_id', $matrix->manpower_id)
            ->where('status', 'enrolled')
            ->whereHas('training', fn ($query) => $query
                ->where('skill_id', $matrix->skill_id)
                ->where('level', '<=', $level))
            ->delete();

        $newAssignments = $matrix->manpower ? $tna->synchronizeFor($matrix->manpower) : 0;

        return back()->with(
            'success',
            "Level awal {$matrix->skill?->nama_skill} diperbarui ke Level {$level}. {$newAssignments} training berikutnya ditugaskan."
        );
    }
}
