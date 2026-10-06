<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class MManpower extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'm_manpower';

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'haris_synced_at' => 'datetime',
        ];
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'user_id', 'mp_id');
    }

    public function skillMatrices(): HasMany
    {
        return $this->hasMany(OperatorSkillMatrix::class, 'manpower_id', 'id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(TrainingEnrollment::class, 'manpower_id', 'id');
    }
}
