<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OjtAssessment;
use App\Models\TrainingEnrollment;
use App\Services\ActivityLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class OjtController extends Controller
{
    public function __construct(private ActivityLogger $activityLogger) {}
    private const THEORY_QUESTIONS = [
        'part_name' => ['label' => 'Sebutkan nama part yang Anda proses.', 'maximum' => 8],
        'part_material' => ['label' => 'Sebutkan nama material part yang Anda proses.', 'maximum' => 8],
        'work_tools' => ['label' => 'Sebutkan alat bantu kerja yang digunakan.', 'maximum' => 8],
        'work_process' => ['label' => 'Sebutkan urutan proses produksi Assembly.', 'maximum' => 10],
        'safety' => ['label' => 'Apakah ada subpart yang Anda proses untuk part yang Anda proses sebelumnya?', 'maximum' => 8],
        'control_plan' => ['label' => 'Sebutkan Control Plan yang ada pada proses.', 'maximum' => 10],
        'ng_types' => ['label' => 'Sebutkan jenis-jenis NG pada part dan jenis NG Assembly.', 'maximum' => 8],
        'marking' => ['label' => 'Apa yang dimaksud dengan proses marking?', 'maximum' => 8],
        'scrap' => ['label' => 'Berapa cycle packing part yang Anda proses?', 'maximum' => 8],
        'handling' => ['label' => 'Apa warna label yang digunakan dan apa saja yang dicantumkan?', 'maximum' => 8],
        'k3' => ['label' => 'Apa yang dimaksud dengan 5R dan K3?', 'maximum' => 8],
        'checking' => ['label' => 'Kenapa proses pengecekan part perlu dilakukan?', 'maximum' => 8],
    ];

    private const PRACTICE_ITEMS = [
        'improvement' => ['label' => 'Frekuensi perbaikan', 'maximum' => 20],
        'reject' => ['label' => 'Reject yang diizinkan', 'maximum' => 30],
        'cycle_time' => ['label' => 'Cycle time & target', 'maximum' => 30],
        'apd' => ['label' => 'Penggunaan APD', 'maximum' => 10],
        'five_r' => ['label' => 'Penerapan 5R', 'maximum' => 10],
    ];

    public function index(Request $request): View
    {
        $departemen = trim($request->string('departemen')->toString());
        $search = trim($request->string('search')->toString());
        $user = $request->user();
        $visibleEnrollments = TrainingEnrollment::query()
            ->whereHas('training', fn ($training) => $training->where('requires_ojt', true));

        if (! $user->is_superadmin) {
            $departemen = $user->manpower?->trainingDepartment() ?? $departemen;
        }

        $visibleEnrollments->when($departemen !== '', fn ($enrollment) => $enrollment->whereHas('manpower', fn ($manpower) => $manpower->forTrainingDepartment($departemen)));

        $query = (clone $visibleEnrollments)
            ->with(['manpower', 'training', 'ojtAssessment'])
            ->whereNotNull('post_test_completed_at')
            ->whereIn('ojt_status', ['pending', 'rejected'])
            ->when($search !== '', function ($enrollment) use ($search): void {
                $enrollment->whereHas('manpower', function ($manpower) use ($search): void {
                    $manpower->where('nama', 'like', "%{$search}%")->orWhere('mp_id', 'like', "%{$search}%");
                });
            });

        $enrollments = $query->latest('post_test_completed_at')->paginate(15)->withQueryString();
        $departemens = (clone $visibleEnrollments)
            ->with('manpower:id,departemen,department_group,department_group_override')
            ->get()
            ->map(fn (TrainingEnrollment $enrollment): ?string => $enrollment->manpower?->trainingDepartment())
            ->filter()
            ->unique()
            ->sort()
            ->values();
        $summary = [
            'ready' => (clone $visibleEnrollments)->whereNotNull('post_test_completed_at')->where('ojt_status', 'pending')->count(),
            'retry' => (clone $visibleEnrollments)->whereNotNull('post_test_completed_at')->where('ojt_status', 'rejected')->count(),
            'notReady' => (clone $visibleEnrollments)->whereNull('post_test_completed_at')->count(),
            'completed' => (clone $visibleEnrollments)->where('status', 'passed')->where('ojt_status', 'approved')->count(),
        ];

        return view('admin.ojt.index', compact('enrollments', 'departemens', 'departemen', 'search', 'summary'));
    }

    public function show(Request $request, string $enrollment): View
    {
        $enrollment = $this->eligibleEnrollment($request, $enrollment);
        $assessment = $enrollment->ojtAssessment;

        return view('admin.ojt.form-clean', [
            'enrollment' => $enrollment,
            'assessment' => $assessment,
            'theoryQuestions' => self::THEORY_QUESTIONS,
            'practiceItems' => self::PRACTICE_ITEMS,
        ]);
    }

    public function store(Request $request, string $enrollment): RedirectResponse
    {
        $enrollment = $this->eligibleEnrollment($request, $enrollment);
        $rules = [
            'training_date' => 'required|date',
            'training_time' => 'nullable|date_format:H:i',
            'location' => 'nullable|string|max:255',
            'categories' => 'required|array|min:1',
            'categories.*' => 'in:pengetahuan_produk,pengetahuan_proses,pengoperasian_mesin,proses_kerja,lainnya',
            'tools' => 'nullable|string|max:3000',
            'materials' => 'nullable|array|max:3',
            'materials.*' => 'nullable|string|max:1000',
            'conclusion' => 'nullable|string|max:3000',
            'recommendation' => 'nullable|string|max:3000',
        ];
        foreach (self::THEORY_QUESTIONS as $key => $question) {
            $rules["theory.$key.answer"] = 'required|string|max:2000';
            $rules["theory.$key.score"] = 'required|numeric|min:0|max:' . $question['maximum'];
        }
        foreach (self::PRACTICE_ITEMS as $key => $item) {
            $rules["practice.$key.actual"] = 'required|string|max:2000';
            $rules["practice.$key.score"] = 'required|numeric|min:0|max:' . $item['maximum'];
        }
        $data = $request->validate($rules);
        $theoryScore = collect($data['theory'])->sum(fn (array $answer): float => (float) $answer['score']);
        $practiceScore = collect($data['practice'])->sum(fn (array $result): float => (float) $result['score']);
        $isPassed = $theoryScore >= 75 && $practiceScore >= 75;

        try {
            DB::transaction(function () use ($data, $enrollment, $theoryScore, $practiceScore, $isPassed, $request): void {
                OjtAssessment::updateOrCreate(
                    ['enrollment_id' => $enrollment->id],
                    [
                        'assessor_user_id' => auth()->id(),
                        'training_date' => $data['training_date'],
                        'training_time' => $data['training_time'] ?? null,
                        'location' => $data['location'] ?? null,
                        'categories' => $data['categories'],
                        'tools' => $data['tools'] ?? null,
                        'materials' => array_values(array_filter($data['materials'] ?? [])),
                        'theory_answers' => $data['theory'],
                        'theory_score' => $theoryScore,
                        'practice_results' => $data['practice'],
                        'practice_score' => $practiceScore,
                        'is_passed' => $isPassed,
                        'conclusion' => $data['conclusion'] ?? null,
                        'recommendation' => $data['recommendation'] ?? null,
                    ]
                );
                $enrollment->forceFill([
                    'status' => $isPassed ? 'passed' : 'in_progress',
                    'ojt_status' => $isPassed ? 'approved' : 'rejected',
                    'ojt_note' => $data['recommendation'] ?? null,
                    'ojt_assessed_by_user_id' => auth()->id(),
                    'ojt_assessed_at' => now(),
                ])->save();
                $this->activityLogger->record('ojt', $isPassed ? 'ojt.approved' : 'ojt.rejected', $enrollment, [
                    'training_title' => $enrollment->training?->judul,
                    'manpower_name' => $enrollment->manpower?->nama,
                    'manpower_nik' => $enrollment->manpower?->nik,
                    'theory_score' => $theoryScore,
                    'practice_score' => $practiceScore,
                ], $request->user());
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'Hasil OJT belum dapat disimpan. Silakan coba kembali.')->withInput();
        }

        return redirect()->route('admin.ojt.index')->with('success', $isPassed
            ? 'OJT disetujui. Manpower telah mencapai Balok 2: mampu mengerjakan dengan bimbingan.'
            : 'Hasil OJT tersimpan. Manpower belum mencapai Balok 2 dan perlu pembinaan atau OJT ulang.');
    }

    private function eligibleEnrollment(Request $request, string $id): TrainingEnrollment
    {
        $enrollment = TrainingEnrollment::with(['manpower', 'training', 'ojtAssessment'])
            ->whereKey($id)
            ->whereNotNull('post_test_completed_at')
            ->whereIn('ojt_status', ['pending', 'rejected'])
            ->whereHas('training', fn ($training) => $training->where('requires_ojt', true))
            ->firstOrFail();

        if (! $request->user()->is_superadmin && ! $enrollment->training->appliesToDepartment($request->user()->manpower?->trainingDepartment())) {
            abort(403, 'OJT hanya dapat dinilai pada departemen Anda.');
        }

        return $enrollment;
    }
}
