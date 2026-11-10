<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MManpower;
use App\Models\MSkill;
use App\Models\S2sProduct;
use App\Models\Training;
use App\Models\TrainingMaterial;
use App\Services\DepartmentGroupService;
use App\Services\TrainingAssignmentService;
use App\Support\TrustedMediaUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Throwable;

class TrainingController extends Controller
{
    public function __construct(private readonly TrainingAssignmentService $assignmentService) {}

    public function index()
    {
        $trainings = Training::with(['materials', 'preTest', 'postTest', 's2sProducts'])
            ->withCount('enrollments')
            ->orderBy('departemen')
            ->orderBy('level')
            ->orderBy('judul')
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
        $productOptions = S2sProduct::query()
            ->orderBy('status')
            ->orderBy('kode_part')
            ->get()
            ->mapWithKeys(fn (S2sProduct $product): array => [
                $product->id => $product->kode_part . ($product->nama_part ? ' - ' . $product->nama_part : '') . ($product->status === 'aktif' ? '' : ' (nonaktif)'),
            ])
            ->all();

        $manpowerOptions = MManpower::query()
            ->where('status', true)
            ->whereNotNull('haris_synced_at')
            ->orderBy('nama')
            ->get()
            ->mapWithKeys(fn (MManpower $manpower): array => [
                $manpower->id => trim($manpower->nama . ' · ' . $manpower->nik . ' · ' . ($manpower->trainingDepartment() ?: 'Tanpa departemen')),
            ])
            ->all();

        return view('admin.trainings.index', compact('trainings', 'departments', 'productOptions', 'manpowerOptions'));
    }

    public function store(Request $request)
    {
        $request->merge(['kode_training' => strtoupper(trim($request->string('kode_training')->toString()))]);

        $data = $request->validate([
            'kode_training' => ['required', 'string', 'max:255', Rule::unique('trainings', 'kode_training')->whereNull('deleted_at')],
            'departemens' => ['required', 'array', 'min:1'],
            'departemens.*' => ['string', 'max:255'],
            'level' => 'required|integer|min:1|max:3',
            'judul' => 'required|string',
            'deskripsi' => 'nullable|string',
            'max_retries' => 'required|integer|min:1',
            'passing_grade' => 'required|integer|min:50|max:100',
            's2s_product_ids' => ['nullable', 'array'],
            's2s_product_ids.*' => ['uuid', 'exists:s2s_products,id'],
            'manpower_ids' => ['nullable', 'array'],
            'manpower_ids.*' => ['integer', 'exists:m_manpower,id'],
        ]);

        DB::transaction(function () use ($request, $data): void {
            $skill = MSkill::firstOrCreate(
                ['kode_skill' => strtoupper($request->kode_training)],
                [
                    'nama_skill' => $request->judul,
                    'kategori' => $this->departmentLabel($request->input('departemens')),
                    'deskripsi' => $request->deskripsi,
                ]
            );

            $training = Training::create([
                'kode_training' => strtoupper($request->kode_training),
                'skill_id' => $skill->id,
                ...$this->departmentAttributes($request->input('departemens')),
                'level' => $request->level,
                'judul' => $request->judul,
                'deskripsi' => $request->deskripsi,
                'max_retries' => $request->max_retries,
                'passing_grade' => $request->passing_grade,
                'requires_ojt' => true,
            ]);
            $training->s2sProducts()->sync($data['s2s_product_ids'] ?? []);
            $this->assignmentService->sync($training, $data['manpower_ids'] ?? [], 'Penugasan awal saat training dibuat.', $request->user());
        });

        return back()->with('success', 'Training dan penugasan awal berhasil disimpan.');
    }

    public function show(string $id)
    {
        $training = Training::with(['skill', 'materials', 'preTest.questions', 'postTest.questions'])->findOrFail($id);

        return view('admin.trainings.show', compact('training'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $training = Training::findOrFail($id);
        $request->merge(['kode_training' => strtoupper(trim($request->string('kode_training')->toString()))]);
        $data = $request->validate([
            'kode_training' => ['required', 'string', 'max:255', Rule::unique('trainings', 'kode_training')->ignore($training->id)->whereNull('deleted_at')],
            'departemens' => ['required', 'array', 'min:1'],
            'departemens.*' => ['string', 'max:255'],
            'level' => 'required|integer|min:1|max:3',
            'judul' => 'required|string',
            'deskripsi' => 'nullable|string',
            'max_retries' => 'required|integer|min:1',
            'passing_grade' => 'required|integer|min:50|max:100',
            's2s_product_ids' => ['nullable', 'array'],
            's2s_product_ids.*' => ['uuid', 'exists:s2s_products,id'],
        ]);

        try {
            DB::transaction(function () use ($training, $data): void {
                $departments = $this->departmentsWithParticipants($training, $data['departemens']);
                $training->update([
                    'kode_training' => strtoupper($data['kode_training']),
                    ...$this->departmentAttributes($departments),
                    'level' => $data['level'],
                    'judul' => $data['judul'],
                    'deskripsi' => $data['deskripsi'] ?? null,
                    'max_retries' => $data['max_retries'],
                    'passing_grade' => $data['passing_grade'],
                ]);
                $training->quizzes()->update([
                    'max_retries' => $data['max_retries'],
                ]);
                $training->postTest()->update([
                    'passing_grade' => $data['passing_grade'],
                ]);
                $training->s2sProducts()->sync($data['s2s_product_ids'] ?? []);
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'Data training belum dapat diubah. Silakan coba kembali.');
        }

        return back()->with('success', 'Data training berhasil diubah.');
    }

    public function storeMaterial(Request $request, string $trainingId)
    {
        $training = Training::findOrFail($trainingId);

        $request->validate([
            'judul' => 'required|string',
            'tipe_media' => 'required|in:text,image,video_gdrive,video_youtube,pdf',
            'konten_teks' => 'nullable|string',
            'media_url' => [
                'nullable',
                'string',
                'max:2048',
                function (string $attribute, mixed $value, \Closure $fail) use ($request): void {
                    $type = $request->input('tipe_media');
                    if (in_array($type, ['video_gdrive', 'video_youtube'], true) && ! TrustedMediaUrl::isTrustedEmbedUrl((string) $value, $type)) {
                        $fail('URL video harus berupa tautan HTTPS Google Drive atau YouTube yang valid.');
                    }
                },
            ],
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

    /** @param array<int, string> $departmentValues @return array{departemen: ?string, departemens: array<int, string>} */
    private function departmentAttributes(array $departmentValues): array
    {
        $departments = collect($departmentValues)
            ->reject(fn (string $department): bool => $department === '__all__')
            ->map(fn (string $department): ?string => DepartmentGroupService::normalize($department))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return [
            'departemen' => count($departments) === 1 ? $departments[0] : null,
            'departemens' => $departments,
        ];
    }

    /** @param array<int, string> $departmentValues */
    private function departmentLabel(array $departmentValues): string
    {
        $departments = $this->departmentAttributes($departmentValues)['departemens'];

        return $departments === [] ? 'Semua departemen' : implode(', ', $departments);
    }

    /** @param array<int, string> $departments */
    private function departmentsWithParticipants(Training $training, array $departments): array
    {
        if (in_array('__all__', $departments, true)) {
            return $departments;
        }

        $participantDepartments = MManpower::query()
            ->whereIn('id', $training->enrollments()->pluck('manpower_id'))
            ->get()
            ->map(fn (MManpower $manpower): ?string => $manpower->trainingDepartment())
            ->filter()
            ->all();

        return collect($departments)->merge($participantDepartments)->unique()->values()->all();
    }
}
