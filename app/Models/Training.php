<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Training extends BaseModel
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'trainings';

    public function skill(): BelongsTo
    {
        return $this->belongsTo(MSkill::class, 'skill_id', 'id');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(TrainingMaterial::class, 'training_id', 'id')->orderBy('urutan', 'asc');
    }

    public function quiz(): HasOne
    {
        return $this->postTest();
    }

    /** Post-test is the assessment that determines a level increase. */
    public function postTest(): HasOne
    {
        return $this->hasOne(Quiz::class, 'training_id', 'id')->where('tipe', 'post_test');
    }

    public function preTest(): HasOne
    {
        return $this->hasOne(Quiz::class, 'training_id', 'id')->where('tipe', 'pre_test');
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class, 'training_id', 'id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(TrainingEnrollment::class, 'training_id', 'id');
    }
}
