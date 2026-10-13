<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingMaterialProgress extends BaseModel
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'training_material_progresses';

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(TrainingEnrollment::class, 'enrollment_id', 'id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(TrainingMaterial::class, 'training_material_id', 'id');
    }

    public function manpower(): BelongsTo
    {
        return $this->belongsTo(MManpower::class, 'manpower_id', 'id');
    }

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }
}
