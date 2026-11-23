<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Akses plant milik akun pengelola (pola yang sama dengan asakai).
 */
class UserPlant extends BaseModel
{
    use HasUuids;

    protected $table = 'user_plants';

    protected function casts(): array
    {
        return [
            'plant' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
