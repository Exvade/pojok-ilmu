<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MSkill extends BaseModel
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'm_skills';

    public function targetLevels(): HasMany
    {
        return $this->hasMany(MSkillTargetLevel::class, 'skill_id', 'id');
    }

    public function trainings(): HasMany
    {
        return $this->hasMany(Training::class, 'skill_id', 'id');
    }

    public function matrices(): HasMany
    {
        return $this->hasMany(OperatorSkillMatrix::class, 'skill_id', 'id');
    }
}
