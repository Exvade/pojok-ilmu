<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MManpower;
use App\Models\MSkill;
use App\Models\Training;
use App\Models\TrainingMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TrainingController extends Controller
{
    public function index()
    {
        $trainings = Training::with(['materials', 'preTest', 'postTest'])
            ->withCount('enrollments')
            ->orderBy('departemen')
            ->orderBy('level')
            ->orderBy('judul')
            ->get();
        $departments = MManpower::query()
            ->where('status', true)
            ->whereNotNull('haris_synced_at')
            ->whereNotNull('departemen')
            ->distinct()
            ->orderBy('departemen')
            ->pluck('departemen');

        return view('admin.trainings.index', compact('trainings', 'departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_training' => 'required|string|unique:trainings,kode_training',
            'departemen' => 'required|string|max:255',
            'level' => 'required|integer|min:1|max:4',
            'judul' => 'required|string',
            'deskripsi' => 'nullable|string',
            'max_retries' => 'required|integer|min:1',
            'passing_grade' => 'required|integer|min:50|max:100',
        ]);

        $skill = MSkill::firstOrCreate(
            ['kode_skill' => strtoupper($request->kode_training)],
            [
                'nama_skill' => $request->judul,
                'kategori' => $request->departemen,
                'deskripsi' => $request->deskripsi,
            ]
        );

        Training::create([
            'kode_training' => strtoupper($request->kode_training),
            'skill_id' => $skill->id,
            'departemen' => $request->departemen,
            'level' => $request->level,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'max_retries' => $request->max_retries,
            'passing_grade' => $request->passing_grade,
            'requires_ojt' => true,
        ]);

        return back()->with('success', 'Product/training berhasil dibuat.');
    }

    public function show(string $id)
    {
        $training = Training::with(['skill', 'materials', 'preTest.questions', 'postTest.questions'])->findOrFail($id);

        return view('admin.trainings.show', compact('training'));
    }

    public function storeMaterial(Request $request, string $trainingId)
    {
        $training = Training::findOrFail($trainingId);

        $request->validate([
            'judul' => 'required|string',
            'tipe_media' => 'required|in:text,image,video_gdrive,video_youtube,pdf',
            'konten_teks' => 'nullable|string',
            'media_url' => 'nullable|string',
            'file_upload' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ]);

        $mediaUrl = $request->media_url;

        // Process File Upload if provided (Image / PDF)
        if ($request->hasFile('file_upload')) {
            $file = $request->file('file_upload');
            $path = $file->store('materials', 'public');
            $mediaUrl = Storage::url($path);
        }

        $nextUrutan = $training->materials()->count() + 1;

        TrainingMaterial::create([
            'training_id' => $training->id,
            'urutan' => $nextUrutan,
            'judul' => $request->judul,
            'tipe_media' => $request->tipe_media,
            'konten_teks' => $request->konten_teks,
            'media_url' => $mediaUrl,
        ]);

        return back()->with('success', 'Materi pembelajaran berhasil ditambahkan.');
    }

    public function destroyMaterial(string $id)
    {
        $material = TrainingMaterial::findOrFail($id);
        $material->delete();

        return back()->with('success', 'Materi berhasil dihapus.');
    }

    public function destroy(string $id)
    {
        $training = Training::findOrFail($id);
        $training->delete();

        return redirect()->route('admin.trainings.index')->with('success', 'Program training berhasil dihapus.');
    }
}
