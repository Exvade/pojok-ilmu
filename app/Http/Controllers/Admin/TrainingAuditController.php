<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MManpower;
use App\Models\Training;
use App\Services\TrainingAssignmentTargetService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class TrainingAuditController extends Controller
{
    public function __construct(private readonly TrainingAssignmentTargetService $assignmentTargets) {}

    public function index(Request $request): View
    {
        $trainings = Training::query()
            ->with(['preTest.questions', 'postTest.questions'])
            ->withCount(['materials', 'enrollments'])
            ->orderBy('departemen')
            ->orderBy('level')
            ->orderBy('judul')
            ->get()
            ->map(fn (Training $training): Training => $this->decorate($training));

        $levelCoverage = $this->levelCoverage($trainings);
        $activeManpowers = MManpower::query()
            ->where('status', true)
            ->whereNotNull('haris_synced_at')
            ->orderBy('nama')
            ->get();
        $manpowerTargets = $this->assignmentTargets->summaries($activeManpowers)
            ->map(fn (array $summary, string|int $manpowerId): array => [
                'manpower' => $activeManpowers->firstWhere('id', $manpowerId),
                'summary' => $summary,
            ])
            ->filter(fn (array $target): bool => $target['summary']['total'] === 0 || ! $target['summary']['meets_target'])
            ->values();

        return view('admin.training-audit.index', [
            'trainings' => $trainings,
            'levelCoverage' => $levelCoverage,
            'manpowerTargets' => $manpowerTargets,
            'summary' => [
                'total' => $trainings->count(),
                'ready' => $trainings->where('audit_ready', true)->count(),
                'duplicateCodes' => $trainings
                    ->groupBy(fn (Training $training): string => strtoupper(trim($training->kode_training)))
                    ->filter(fn (Collection $group): bool => $group->count() > 1)
                    ->count(),
                'contentIssues' => $trainings->filter(fn (Training $training): bool => $training->audit_content_issues !== [])->count(),
                'unassigned' => $trainings->where('enrollments_count', 0)->count(),
                'levelIssues' => $levelCoverage->filter(fn (array $coverage): bool => $coverage['issues'] !== [])->count(),
                'withoutAssignments' => $manpowerTargets->where('summary.total', 0)->count(),
                'belowTarget' => $manpowerTargets->where('summary.meets_target', false)->count(),
            ],
        ]);
    }

    private function decorate(Training $training): Training
    {
        $contentIssues = [];
        $postTestQuestions = $training->postTest?->questions->count() ?? 0;

        if ($training->materials_count === 0) {
            $contentIssues[] = 'Materi belum ada';
        }

        if (! $training->postTest) {
            $contentIssues[] = 'Post-test belum dibuat';
        } elseif ($postTestQuestions === 0) {
            $contentIssues[] = 'Soal post-test belum ada';
        }

        $training->setAttribute('audit_content_issues', $contentIssues);
        $training->setAttribute('audit_post_test_questions', $postTestQuestions);
        $training->setAttribute('audit_pre_test_questions', $training->preTest?->questions->count() ?? 0);
        $training->setAttribute('audit_ready', $contentIssues === [] && $training->enrollments_count > 0);

        return $training;
    }

    /** @param Collection<int, Training> $trainings @return Collection<int, array{department: string, levels: array<int, int>, issues: array<int, string>}> */
    private function levelCoverage(Collection $trainings): Collection
    {
        return $trainings
            ->flatMap(function (Training $training): array {
                $departments = $training->departmentList();

                return $departments === [] ? ['Semua departemen'] : $departments;
            })
            ->unique()
            ->sort()
            ->values()
            ->map(function (string $department) use ($trainings): array {
                $levels = [1 => 0, 2 => 0, 3 => 0];

                $trainings
                    ->filter(fn (Training $training): bool => $department === 'Semua departemen'
                        ? $training->departmentList() === []
                        : in_array($department, $training->departmentList(), true))
                    ->each(function (Training $training) use (&$levels): void {
                        $levels[$training->level]++;
                    });

                $issues = [];
                if ($levels[2] > 0 && $levels[1] === 0) {
                    $issues[] = 'Level 1 belum tersedia';
                }
                if ($levels[3] > 0 && $levels[2] === 0) {
                    $issues[] = 'Level 2 belum tersedia';
                }

                return ['department' => $department, 'levels' => $levels, 'issues' => $issues];
            });
    }
}
