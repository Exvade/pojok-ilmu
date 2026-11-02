<?php

namespace App\Services;

use App\Models\MManpower;
use App\Models\Training;
use App\Models\TrainingAssignmentLog;
use App\Models\TrainingEnrollment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TrainingAssignmentService
{
    public function __construct(private readonly ActivityLogger $activityLogger) {}

    /** @param array<int, int|string> $requestedManpowerIds */
    public function sync(Training $training, array $requestedManpowerIds, ?string $reason, User $actor): void
    {
        $requestedManpowerIds = array_values(array_unique(array_map('intval', $requestedManpowerIds)));
        $validManpowerIds = MManpower::query()->whereIn('id', $requestedManpowerIds)
            ->where('status', true)->whereNotNull('haris_synced_at')->get()
            ->filter(fn (MManpower $manpower): bool => $training->appliesToDepartment($manpower->trainingDepartment()))
            ->pluck('id')->all();

        if (count($validManpowerIds) !== count($requestedManpowerIds)) {
            throw ValidationException::withMessages([
                'manpower_ids' => 'Peserta harus merupakan manpower aktif dari departemen yang dipilih pada training.',
            ]);
        }

        DB::transaction(fn () => $this->syncRecords($training, $validManpowerIds, $reason, $actor));
    }

    /** @param array<int, int> $validManpowerIds */
    private function syncRecords(Training $training, array $validManpowerIds, ?string $reason, User $actor): void
    {
        $canceled = TrainingEnrollment::query()->where('training_id', $training->id)->where('status', 'enrolled')
            ->when($validManpowerIds !== [], fn ($query) => $query->whereNotIn('manpower_id', $validManpowerIds))->get();
        foreach ($canceled as $enrollment) {
            $enrollment->delete();
            $this->log($training, $enrollment, 'canceled', 'enrolled', 'dibatalkan', $reason, $actor);
        }
        foreach ($validManpowerIds as $manpowerId) {
            $enrollment = TrainingEnrollment::withTrashed()->firstOrNew(['training_id' => $training->id, 'manpower_id' => $manpowerId]);
            if ($enrollment->exists && ! $enrollment->trashed()) {
                continue;
            }
            $before = $enrollment->exists ? 'dibatalkan' : null;
            if ($enrollment->exists) {
                $enrollment->restore();
            }
            $enrollment->forceFill(['status' => 'enrolled', 'ojt_status' => 'not_started'])->save();
            $this->log($training, $enrollment, 'assigned', $before, 'enrolled', $reason, $actor);
        }
    }

    private function log(Training $training, TrainingEnrollment $enrollment, string $action, ?string $before, string $after, ?string $reason, User $actor): void
    {
        TrainingAssignmentLog::create(['training_id' => $training->id, 'manpower_id' => $enrollment->manpower_id, 'enrollment_id' => $enrollment->id, 'actor_user_id' => $actor->id, 'action' => $action, 'status_before' => $before, 'status_after' => $after, 'reason' => filled($reason) ? $reason : null]);
        $this->activityLogger->record('assignment', "assignment.{$action}", $enrollment, ['training_title' => $training->judul, 'manpower_name' => $enrollment->manpower?->nama, 'manpower_nik' => $enrollment->manpower?->nik, 'status_before' => $before, 'status_after' => $after, 'reason' => $reason]);
    }
}
