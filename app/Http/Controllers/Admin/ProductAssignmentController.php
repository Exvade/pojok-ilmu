<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MManpower;
use App\Models\Training;
use App\Services\TrainingAssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductAssignmentController extends Controller
{
    public function __construct(private readonly TrainingAssignmentService $assignmentService) {}
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

        return $this->save($request, Training::findOrFail($data['training_id']), $data);
    }

    public function edit(Training $training): View
    {
        $training->load('enrollments:id,training_id,manpower_id,status');
        $manpowers = MManpower::query()
            ->where('status', true)
            ->whereNotNull('haris_synced_at')
            ->orderBy('nama')
            ->get();

        return view('admin.trainings.participants', compact('training', 'manpowers'));
    }

    public function storeForTraining(Request $request, Training $training): RedirectResponse
    {
        $data = $request->validate(['manpower_ids' => 'nullable|array', 'manpower_ids.*' => 'exists:m_manpower,id', 'reason' => 'nullable|string|max:1000']);

        return $this->save($request, $training, $data);
    }

    public function history(Training $training): View
    {
        $logs = $training->assignmentLogs()
            ->with(['manpower', 'actor'])
            ->latest()
            ->paginate(20);

        return view('admin.product-assignments.history', compact('training', 'logs'));
    }

    private function save(Request $request, Training $training, array $data): RedirectResponse
    {
        try {
            $this->assignmentService->sync($training, $data['manpower_ids'] ?? [], $data['reason'] ?? null, $request->user());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with('error', 'Penugasan training belum dapat disimpan. Silakan coba kembali.');
        }

        return back()->with('success', 'Peserta training berhasil diperbarui.');
    }
}
