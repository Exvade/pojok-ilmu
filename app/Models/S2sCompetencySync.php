<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class S2sCompetencySync extends BaseModel
{
    use HasUuids;

    protected $table = 's2s_competency_syncs';

    protected function casts(): array
    {
        return [
            'response_payload' => 'array',
            'sent_at' => 'datetime',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(TrainingEnrollment::class, 'enrollment_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(S2sProduct::class, 's2s_product_id');
    }
}
