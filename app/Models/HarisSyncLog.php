<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class HarisSyncLog extends BaseModel
{
    use HasUuids;

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }
}
