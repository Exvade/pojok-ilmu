<?php

namespace App\Http\Controllers\Admin;

use App\Exports\TrainingProgressReportExport;
use App\Http\Controllers\Controller;
use App\Models\MManpower;
use App\Models\TrainingEnrollment;
use App\Services\TrainingReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TrainingReportController extends Controller
{
    public function __construct(private readonly TrainingReportService $report) {}

    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $baseQuery = $this->baseQuery($filters);
        $summary = $this->summary($baseQuery);
        $departments = MManpower::query()
            ->where('status', true)
            ->whereNotNull('haris_synced_at')
            ->get()
            ->map(fn (MManpower $manpower): ?string => $manpower->trainingDepartment())
            ->filter()
            ->unique()
            ->sort()
            ->values();
        $enrollments = $this->report
            ->applyStatusFilter(clone $baseQuery, $filters['status'])
            ->with(['manpower', 'training'])
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        $enrollments->getCollection()->each(fn (TrainingEnrollment $enrollment) => $this->decorate($enrollment));

        return view('admin.training-reports.index', [
            'departments' => $departments,
            'departmentSummary' => $this->departmentSummary($baseQuery),
            'enrollments' => $enrollments,
            'filters' => $filters,
            'statuses' => $this->report->statuses(),
            'summary' => $summary,
        ]);
    }

    public function export(Request $request): BinaryFileResponse
    {
        $filters = $this->filters($request);
        $enrollments = $this->report
            ->applyStatusFilter($this->baseQuery($filters), $filters['status'])
            ->with(['manpower', 'training'])
            ->latest('updated_at')
            ->get();

        $rows = $enrollments->map(function (TrainingEnrollment $enrollment): array {
            $this->decorate($enrollment);

            return [
                $enrollment->manpower?->mp_id ?? '-',
                $enrollment->manpower?->nama ?? 'Karyawan diarsipkan',
                $enrollment->manpower?->trainingDepartment() ?? '-',
                $enrollment->manpower?->departemen ?? '-',
                $enrollment->manpower?->bagian ?? '-',
                $enrollment->training?->kode_training ?? '-',
                $enrollment->training?->judul ?? 'Training diarsipkan',
                $enrollment->training?->level ?? '-',
                $enrollment->report_status_label,
                $enrollment->post_test_completed_at?->translatedFormat('d M Y, H:i') ?? '-',
                $enrollment->ojt_assessed_at?->translatedFormat('d M Y, H:i') ?? '-',
                $enrollment->updated_at?->translatedFormat('d M Y, H:i') ?? '-',
            ];
        })->all();

        return Excel::download(
            new TrainingProgressReportExport($rows),
            'laporan-progres-training-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    /** @return array{departemen: string, search: string, status: string} */
    private function filters(Request $request): array
    {
        return [
            'departemen' => trim($request->string('departemen')->toString()),
            'search' => trim($request->string('search')->toString()),
            'status' => trim($request->string('status')->toString()),
        ];
    }

    /** @param array{departemen: string, search: string, status: string} $filters */
    private function baseQuery(array $filters): Builder
    {
        return TrainingEnrollment::query()
            ->whereHas('manpower', fn (Builder $manpower) => $manpower->where('status', true)->whereNotNull('haris_synced_at'))
            ->when($filters['departemen'] !== '', fn (Builder $query) => $query->whereHas('manpower', fn (Builder $manpower) => $manpower->forTrainingDepartment($filters['departemen'])))
            ->when($filters['search'] !== '', function (Builder $query) use ($filters): void {
                $query->where(function (Builder $nestedQuery) use ($filters): void {
                    $nestedQuery->whereHas('manpower', function (Builder $manpower) use ($filters): void {
                        $manpower->where('nama', 'like', "%{$filters['search']}%")
                            ->orWhere('mp_id', 'like', "%{$filters['search']}%")
                            ->orWhere('nik', 'like', "%{$filters['search']}%");
                    })->orWhereHas('training', function (Builder $training) use ($filters): void {
                        $training->where('judul', 'like', "%{$filters['search']}%")
                            ->orWhere('kode_training', 'like', "%{$filters['search']}%");
                    });
                });
            });
    }

    /** @return array{total: int, not_started: int, in_progress: int, pending_ojt: int, retry_ojt: int, block_two: int} */
    private function summary(Builder $query): array
    {
        return [
            'total' => (clone $query)->count(),
            'not_started' => (clone $query)->where('status', 'enrolled')->count(),
            'in_progress' => $this->report->applyStatusFilter(clone $query, 'dalam_proses')->count(),
            'pending_ojt' => $this->report->applyStatusFilter(clone $query, 'menunggu_ojt')->count(),
            'retry_ojt' => $this->report->applyStatusFilter(clone $query, 'ojt_ulang')->count(),
            'block_two' => $this->report->applyStatusFilter(clone $query, 'balok_2')->count(),
        ];
    }

    /** @return Collection<int, object> */
    private function departmentSummary(Builder $query): Collection
    {
        return (clone $query)
            ->join('m_manpower as report_manpower', 'training_enrollments.manpower_id', '=', 'report_manpower.id')
            ->selectRaw("COALESCE(NULLIF(report_manpower.department_group_override, ''), NULLIF(report_manpower.department_group, ''), report_manpower.departemen) as department_group")
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN training_enrollments.status = 'enrolled' THEN 1 ELSE 0 END) as belum_dimulai")
            ->selectRaw("SUM(CASE WHEN training_enrollments.status = 'in_progress' AND training_enrollments.post_test_completed_at IS NULL THEN 1 ELSE 0 END) as dalam_proses")
            ->selectRaw("SUM(CASE WHEN training_enrollments.post_test_completed_at IS NOT NULL AND training_enrollments.ojt_status = 'pending' THEN 1 ELSE 0 END) as menunggu_ojt")
            ->selectRaw("SUM(CASE WHEN training_enrollments.ojt_status = 'rejected' THEN 1 ELSE 0 END) as ojt_ulang")
            ->selectRaw("SUM(CASE WHEN training_enrollments.status = 'passed' AND training_enrollments.ojt_status = 'approved' THEN 1 ELSE 0 END) as balok_dua")
            ->groupByRaw("COALESCE(NULLIF(report_manpower.department_group_override, ''), NULLIF(report_manpower.department_group, ''), report_manpower.departemen)")
            ->orderBy('department_group')
            ->get();
    }

    private function decorate(TrainingEnrollment $enrollment): void
    {
        $status = $this->report->statusFor($enrollment);
        $enrollment->setAttribute('report_status', $status);
        $enrollment->setAttribute('report_status_label', $this->report->labelFor($status));
    }
}
