<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OjtAssessment extends BaseModel
{
    use HasFactory, HasUuids, SoftDeletes;

    protected function casts(): array
    {
        return [
            'training_date' => 'date',
            'categories' => 'array',
            'materials' => 'array',
            'theory_answers' => 'array',
            'theory_score' => 'decimal:2',
            'practice_results' => 'array',
            'practice_score' => 'decimal:2',
            'is_passed' => 'boolean',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(TrainingEnrollment::class, 'enrollment_id', 'id');
    }

    public function assessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessor_user_id', 'id');
    }
}
