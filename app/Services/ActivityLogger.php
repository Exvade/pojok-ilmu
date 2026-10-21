<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function record(string $category, string $event, Model $subject, array $data, ?User $actor = null): void
    {
        ActivityLog::create([
            'actor_user_id' => $actor?->id ?? auth()->id(),
            'category' => $category,
            'event' => $event,
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'data' => $data,
        ]);
    }
}
