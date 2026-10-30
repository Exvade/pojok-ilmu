<?php

namespace App\Models;

use App\Support\TrustedMediaUrl;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingMaterial extends BaseModel
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'training_materials';

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class, 'training_id', 'id');
    }

    public function progresses(): HasMany
    {
        return $this->hasMany(TrainingMaterialProgress::class, 'training_material_id', 'id');
    }

    /**
     * Parse Google Drive Embed URL if applicable
     */
    public function getEmbedUrlAttribute(): ?string
    {
        return TrustedMediaUrl::embedUrl($this->media_url, $this->tipe_media);
    }
}
