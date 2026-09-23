<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuizAttempt extends BaseModel
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'quiz_attempts';

    protected function casts(): array
    {
        return [
            'score' => 'float',
            'is_passed' => 'boolean',
            'submitted_at' => 'datetime',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(TrainingEnrollment::class, 'enrollment_id', 'id');
    }

    public function manpower(): BelongsTo
    {
        return $this->belongsTo(MManpower::class, 'manpower_id', 'id');
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class, 'quiz_id', 'id');
    }
}
