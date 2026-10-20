<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingAssignmentLog extends BaseModel
{
    use HasUuids, SoftDeletes;

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class);
    }

    public function manpower(): BelongsTo
    {
        return $this->belongsTo(MManpower::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(TrainingEnrollment::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
