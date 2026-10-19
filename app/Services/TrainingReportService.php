<?php

namespace App\Services;

use App\Models\TrainingEnrollment;
use Illuminate\Database\Eloquent\Builder;

class TrainingReportService
{
    /** @return array<string, string> */
    public function statuses(): array
    {
        return [
            'belum_dimulai' => 'Belum dimulai',
            'dalam_proses' => 'Sedang training',
            'remedi' => 'Perlu remedi',
            'menunggu_ojt' => 'Menunggu OJT',
            'ojt_ulang' => 'Perlu OJT ulang',
            'balok_2' => 'Balok 2',
        ];
    }

    public function applyStatusFilter(Builder $query, string $status): Builder
    {
        return match ($status) {
            'belum_dimulai' => $query->where('status', 'enrolled'),
            'dalam_proses' => $query->where('status', 'in_progress')->whereNull('post_test_completed_at')->whereNull('next_attempt_at'),
            'remedi' => $query->where(function (Builder $query): void {
                $query->where('status', 'failed_escalated')
                    ->orWhere(function (Builder $query): void {
                        $query->whereNull('post_test_completed_at')->whereNotNull('next_attempt_at');
                    });
            }),
            'menunggu_ojt' => $query->whereNotNull('post_test_completed_at')->where('ojt_status', 'pending'),
            'ojt_ulang' => $query->where('ojt_status', 'rejected'),
            'balok_2' => $query->where('status', 'passed')->where('ojt_status', 'approved'),
            default => $query,
        };
    }

    public function statusFor(TrainingEnrollment $enrollment): string
    {
        if ($enrollment->status === 'passed' && $enrollment->ojt_status === 'approved') {
            return 'balok_2';
        }

        if ($enrollment->ojt_status === 'rejected') {
            return 'ojt_ulang';
        }

        if ($enrollment->post_test_completed_at && $enrollment->ojt_status === 'pending') {
            return 'menunggu_ojt';
        }

        if ($enrollment->status === 'failed_escalated' || (! $enrollment->post_test_completed_at && $enrollment->next_attempt_at)) {
            return 'remedi';
        }

        return $enrollment->status === 'enrolled' ? 'belum_dimulai' : 'dalam_proses';
    }

    public function labelFor(string $status): string
    {
        return $this->statuses()[$status] ?? 'Sedang training';
    }
}
