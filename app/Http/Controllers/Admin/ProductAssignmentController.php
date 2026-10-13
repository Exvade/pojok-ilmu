<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MManpower;
use App\Models\Training;
use App\Models\TrainingEnrollment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class ProductAssignmentController extends Controller
{
    public function index(Request $request): View
    {
        $department = $request->string('departemen')->toString();
        $products = Training::with('enrollments:id,training_id,manpower_id')
            ->withCount('enrollments')
            ->when($department !== '', fn ($query) => $query->where('departemen', $department))
            ->orderBy('departemen')
            ->orderBy('level')
            ->orderBy('judul')
            ->get();
        $manpowers = MManpower::query()
            ->where('status', true)
            ->whereNotNull('haris_synced_at')
            ->when($department !== '', fn ($query) => $query->where('departemen', $department))
            ->orderBy('nama')
            ->get();
        $departments = MManpower::query()
            ->where('status', true)
            ->whereNotNull('haris_synced_at')
            ->whereNotNull('departemen')
            ->distinct()
            ->orderBy('departemen')
            ->pluck('departemen');

        return view('admin.product-assignments.index', compact('products', 'manpowers', 'departments', 'department'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'training_id' => 'required|exists:trainings,id',
            'manpower_ids' => 'required|array|min:1',
            'manpower_ids.*' => 'exists:m_manpower,id',
        ]);

        $training = Training::findOrFail($data['training_id']);
        $validManpowerIds = MManpower::query()
            ->whereIn('id', $data['manpower_ids'])
            ->where('status', true)
            ->whereNotNull('haris_synced_at')
            ->where('departemen', $training->departemen)
            ->pluck('id')
            ->all();

        if (count($validManpowerIds) !== count(array_unique($data['manpower_ids']))) {
            return back()->with('error', 'Penugasan hanya dapat diberikan kepada manpower aktif dari departemen training yang sama.');
        }

        try {
            DB::beginTransaction();
            foreach ($validManpowerIds as $manpowerId) {
                TrainingEnrollment::firstOrCreate([
                    'training_id' => $data['training_id'],
                    'manpower_id' => $manpowerId,
                ], ['status' => 'enrolled', 'ojt_status' => 'not_started']);
            }
            DB::commit();
        } catch (Throwable $exception) {
            DB::rollBack();
            report($exception);

            return back()->with('error', 'Penugasan training belum dapat disimpan. Silakan coba kembali.');
        }

        return back()->with('success', 'Training berhasil ditugaskan ke manpower terpilih.');
    }
}
