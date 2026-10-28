<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class S2sProduct extends BaseModel
{
    use HasUuids;

    protected $table = 's2s_products';

    protected function casts(): array
    {
        return [
            's2s_updated_at' => 'datetime',
            'synced_at' => 'datetime',
        ];
    }

    public function trainings(): BelongsToMany
    {
        return $this->belongsToMany(Training::class, 's2s_product_training');
    }
}
