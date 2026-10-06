<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MManpower;
use App\Models\MSkill;
use App\Models\OperatorSkillMatrix;
use App\Models\TrainingEnrollment;
use Illuminate\Http\Request;

class SkillMatrixController extends Controller
{
    /**
     * Dashboard Utama Admin & Visualisasi 4-Block Skill Matrix
     */
    public function index(Request $request)
    {
        $departemenFilter = $request->get('departemen');
        $search = $request->get('search');

        // Stat Widgets
        $totalOperator = MManpower::where('status', true)->whereNotNull('haris_synced_at')->count();
        $totalSkill = MSkill::count();
        $totalTerakreditasi = OperatorSkillMatrix::where('status_gap', 'Lulus / Sesuai Target')->count();
        $totalEscalated = TrainingEnrollment::where('status', 'failed_escalated')->count();

        // Skill Matrix Grid
        $queryManpowers = MManpower::where('status', true)
            ->whereNotNull('haris_synced_at')
            ->with(['skillMatrices.skill']);

        if ($departemenFilter) {
            $queryManpowers->where('departemen', $departemenFilter);
        }

        if ($search) {
            $queryManpowers->where(function ($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                    ->orWhere('mp_id', 'LIKE', "%{$search}%")
                    ->orWhere('nik', 'LIKE', "%{$search}%");
            });
        }

        $manpowers = $queryManpowers->orderBy('nama')->paginate(10)->withQueryString();
        $skills = MSkill::all();

        // Escalated TNA List
        $escalatedEnrollments = TrainingEnrollment::with(['manpower', 'training.skill', 'attempts'])
            ->where('status', 'failed_escalated')
            ->latest()
            ->get();

        $departemens = MManpower::query()
            ->where('status', true)
            ->whereNotNull('haris_synced_at')
            ->distinct()
            ->orderBy('departemen')
            ->pluck('departemen')
            ->filter()
            ->values();

        return view('admin.skill-matrix.index', compact(
            'totalOperator',
            'totalSkill',
            'totalTerakreditasi',
            'totalEscalated',
            'manpowers',
            'skills',
            'escalatedEnrollments',
            'departemens',
            'departemenFilter',
            'search'
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
}
