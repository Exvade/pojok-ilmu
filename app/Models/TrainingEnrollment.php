<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingEnrollment extends BaseModel
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'training_enrollments';

    protected function casts(): array
    {
        return [
            'pre_test_completed_at' => 'datetime',
            'material_completed_at' => 'datetime',
            'next_attempt_at' => 'datetime',
            'assessment_started_at' => 'datetime',
            'assessment_expires_at' => 'datetime',
            'post_test_completed_at' => 'datetime',
            'assessment_completed_at' => 'datetime',
            'ojt_assessed_at' => 'datetime',
            'escalated_at' => 'datetime',
            'last_follow_up_at' => 'datetime',
            'escalation_resolved_at' => 'datetime',
        ];
    }

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

    public function materialProgresses(): HasMany
    {
        return $this->hasMany(TrainingMaterialProgress::class, 'enrollment_id', 'id');
    }

    public function ojtAssessment(): HasOne
    {
        return $this->hasOne(OjtAssessment::class, 'enrollment_id', 'id');
    }

    public function activeQuiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class, 'active_quiz_id', 'id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id', 'id');
    }

    public function assignmentLogs(): HasMany
    {
        return $this->hasMany(TrainingAssignmentLog::class, 'enrollment_id', 'id');
    }
}
