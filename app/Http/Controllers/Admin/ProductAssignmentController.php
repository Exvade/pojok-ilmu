<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MManpower;
use App\Models\Training;
use App\Models\TrainingAssignmentLog;
use App\Models\TrainingEnrollment;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class ProductAssignmentController extends Controller
{
    public function __construct(private ActivityLogger $activityLogger) {}
    public function index(Request $request): View
    {
        $department = $request->string('departemen')->toString();
        $products = Training::with('enrollments:id,training_id,manpower_id,status')
            ->withCount('enrollments')
            ->when($department !== '', fn ($query) => $query->forDepartment($department))
            ->orderBy('departemen')
            ->orderBy('level')
            ->orderBy('judul')
            ->get();
        $manpowers = MManpower::query()
            ->where('status', true)
            ->whereNotNull('haris_synced_at')
            ->when($department !== '', fn ($query) => $query->forTrainingDepartment($department))
            ->orderBy('nama')
            ->get();
        $departments = MManpower::query()
            ->where('status', true)
            ->whereNotNull('haris_synced_at')
            ->get()
            ->map(fn (MManpower $manpower): ?string => $manpower->trainingDepartment())
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return view('admin.product-assignments.index-clean', compact('products', 'manpowers', 'departments', 'department'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'training_id' => 'required|exists:trainings,id',
            'manpower_ids' => 'nullable|array',
            'manpower_ids.*' => 'exists:m_manpower,id',
            'reason' => 'nullable|string|max:1000',
        ]);

        $training = Training::findOrFail($data['training_id']);
        $requestedManpowerIds = $data['manpower_ids'] ?? [];
        $validManpowerIds = MManpower::query()
            ->whereIn('id', $requestedManpowerIds)
            ->where('status', true)
            ->whereNotNull('haris_synced_at')
            ->get()
            ->filter(fn (MManpower $manpower): bool => $training->appliesToDepartment($manpower->trainingDepartment()))
            ->pluck('id')
            ->all();

        if (count($validManpowerIds) !== count(array_unique($requestedManpowerIds))) {
            return back()->with('error', 'Penugasan hanya dapat diberikan kepada manpower aktif dari departemen yang dipilih pada training.');
        }

        try {
            DB::beginTransaction();
            $canceledEnrollments = TrainingEnrollment::query()
                ->where('training_id', $training->id)
                ->where('status', 'enrolled')
                ->when($validManpowerIds !== [], fn ($query) => $query->whereNotIn('manpower_id', $validManpowerIds))
                ->get();

            foreach ($canceledEnrollments as $enrollment) {
                $enrollment->delete();
                $this->logAssignmentChange(
                    $training,
                    $enrollment,
                    'canceled',
                    'enrolled',
                    'dibatalkan',
                    $data['reason'] ?? null,
                    $request->user()->id,
                );
            }

            foreach ($validManpowerIds as $manpowerId) {
                $enrollment = TrainingEnrollment::withTrashed()->firstOrNew([
                    'training_id' => $data['training_id'],
                    'manpower_id' => $manpowerId,
                ]);

                if ($enrollment->exists && ! $enrollment->trashed()) {
                    continue;
                }

                $previousStatus = $enrollment->exists ? 'dibatalkan' : null;

                if ($enrollment->exists) {
                    $enrollment->restore();
                }

                $enrollment->forceFill([
                    'status' => 'enrolled',
                    'ojt_status' => 'not_started',
                ]);
                $enrollment->save();

                $this->logAssignmentChange(
                    $training,
                    $enrollment,
                    'assigned',
                    $previousStatus,
                    'enrolled',
                    $data['reason'] ?? null,
                    $request->user()->id,
                );
            }
            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();
            report($exception);

            return back()->with('error', 'Penugasan training belum dapat disimpan. Silakan coba kembali.');
        }

        return back()->with('success', 'Penugasan training berhasil diperbarui.');
    }

    public function history(Training $training): View
    {
        $logs = $training->assignmentLogs()
            ->with(['manpower', 'actor'])
            ->latest()
            ->paginate(20);

        return view('admin.product-assignments.history', compact('training', 'logs'));
    }

    private function logAssignmentChange(
        Training $training,
        TrainingEnrollment $enrollment,
        string $action,
        ?string $statusBefore,
        string $statusAfter,
        ?string $reason,
        string $actorUserId,
    ): void {
        TrainingAssignmentLog::create([
            'training_id' => $training->id,
            'manpower_id' => $enrollment->manpower_id,
            'enrollment_id' => $enrollment->id,
            'actor_user_id' => $actorUserId,
            'action' => $action,
            'status_before' => $statusBefore,
            'status_after' => $statusAfter,
            'reason' => filled($reason) ? $reason : null,
        ]);
        $this->activityLogger->record('assignment', "assignment.{$action}", $enrollment, [
            'training_title' => $training->judul,
            'manpower_name' => $enrollment->manpower?->nama,
            'manpower_nik' => $enrollment->manpower?->nik,
            'status_before' => $statusBefore,
            'status_after' => $statusAfter,
            'reason' => $reason,
        ]);
    }
}
