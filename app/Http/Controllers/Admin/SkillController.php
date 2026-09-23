<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MManpower;
use App\Models\MSkill;
use App\Models\MSkillTargetLevel;
use App\Models\OperatorSkillMatrix;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SkillController extends Controller
{
    public function index()
    {
        $skills = MSkill::with('targetLevels')->latest()->get();
        $targetLevels = MSkillTargetLevel::with('skill')->get();

        return view('admin.skills.index', compact('skills', 'targetLevels'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_skill' => 'required|string|unique:m_skills,kode_skill',
            'nama_skill' => 'required|string',
            'kategori' => 'required|string',
            'deskripsi' => 'nullable|string',
        ]);

        MSkill::create([
            'kode_skill' => strtoupper($request->kode_skill),
            'nama_skill' => $request->nama_skill,
            'kategori' => $request->kategori,
            'deskripsi' => $request->deskripsi,
        ]);

        return back()->with('success', 'Master Skill berhasil ditambahkan.');
    }

    public function storeTargetLevel(Request $request)
    {
        $request->validate([
            'departemen' => 'required|string',
            'bagian' => 'required|string',
            'skill_id' => 'required|exists:m_skills,id',
            'target_level' => 'required|integer|min:1|max:4',
        ]);

        DB::beginTransaction();
        try {
            $target = MSkillTargetLevel::updateOrCreate(
                [
                    'departemen' => $request->departemen,
                    'bagian' => $request->bagian,
                    'skill_id' => $request->skill_id,
                ],
                [
                    'target_level' => $request->target_level,
                ]
            );

            // Sync Operator Skill Matrices untuk operator di departemen & bagian ini
            $operators = MManpower::where('departemen', $request->departemen)
                ->where('bagian', $request->bagian)
                ->get();

            foreach ($operators as $op) {
                $matrix = OperatorSkillMatrix::firstOrCreate(
                    [
                        'manpower_id' => $op->id,
                        'skill_id' => $request->skill_id,
                    ],
                    [
                        'current_level' => 0,
                        'target_level' => $request->target_level,
                        'status_gap' => 'Gap -' . $request->target_level . ' Level',
                    ]
                );

                $matrix->target_level = $request->target_level;
                $matrix->updateGapStatus();
            }

            DB::commit();

            return back()->with('success', 'Target level skill berhasil disimpan & disinkronkan ke Operator Skill Matrix.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal menyimpan target level: ' . $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        $skill = MSkill::findOrFail($id);
        $skill->delete();

        return back()->with('success', 'Master skill berhasil dihapus.');
    }
}
