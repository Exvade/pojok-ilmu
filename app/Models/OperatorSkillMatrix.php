<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OperatorSkillMatrix extends BaseModel
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'operator_skill_matrices';

    protected function casts(): array
    {
        return [
            'current_level' => 'integer',
            'target_level' => 'integer',
            'last_updated_at' => 'datetime',
        ];
    }

    public function manpower(): BelongsTo
    {
        return $this->belongsTo(MManpower::class, 'manpower_id', 'id');
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(MSkill::class, 'skill_id', 'id');
    }

    /**
     * Hitung ulang status gap berdasarkan current vs target level
     */
    public function updateGapStatus(): void
    {
        $gap = $this->target_level - $this->current_level;

        if ($gap <= 0) {
            $this->status_gap = 'Lulus / Sesuai Target';
        } else {
            $this->status_gap = 'Gap -' . $gap . ' Level';
        }

        $this->save();
    }
}
