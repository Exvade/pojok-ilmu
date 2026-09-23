<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingEnrollment extends BaseModel
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'training_enrollments';

    public function manpower(): BelongsTo
    {
        return $this->belongsTo(MManpower::class, 'manpower_id', 'id');
    }

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class, 'training_id', 'id');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class, 'enrollment_id', 'id')->orderBy('attempt_number', 'desc');
    }
}
