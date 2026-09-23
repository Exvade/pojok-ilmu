<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MSkillTargetLevel extends BaseModel
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'm_skill_target_levels';

    public function skill(): BelongsTo
    {
        return $this->belongsTo(MSkill::class, 'skill_id', 'id');
    }
}
