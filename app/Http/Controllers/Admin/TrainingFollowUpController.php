<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrainingEnrollment;
use App\Services\TrainingReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class TrainingFollowUpController extends Controller
{
    public function __construct(private readonly TrainingReportService $report) {}

    public function index(): View
    {
        $base = TrainingEnrollment::query()
            ->whereHas('manpower', fn (Builder $manpower) => $manpower->where('status', true)->whereNotNull('haris_synced_at'))
            ->with(['manpower', 'training']);

        $groups = [
            'Menunggu penilaian OJT' => $this->report->applyStatusFilter(clone $base, 'menunggu_ojt')->latest('post_test_completed_at')->get(),
            'Perlu OJT ulang' => $this->report->applyStatusFilter(clone $base, 'ojt_ulang')->latest('updated_at')->get(),
            'Remedi atau ujian terkunci' => $this->report->applyStatusFilter(clone $base, 'remedi')->latest('next_attempt_at')->get(),
            'Tidak ada perkembangan 7 hari' => (clone $base)->whereIn('status', ['enrolled', 'in_progress'])->where('updated_at', '<=', now()->subDays(7))->latest('updated_at')->get(),
        ];

        return view('admin.training-follow-ups.index', compact('groups'));
    }
}
