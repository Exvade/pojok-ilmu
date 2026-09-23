<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingMaterial extends BaseModel
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'training_materials';

    public function training(): BelongsTo
    {
        return $this->belongsTo(Training::class, 'training_id', 'id');
    }

    /**
     * Parse Google Drive Embed URL if applicable
     */
    public function getEmbedUrlAttribute(): ?string
    {
        if ($this->tipe_media === 'video_gdrive' && ! empty($this->media_url)) {
            // Check if full GDrive view URL, convert to preview URL
            if (preg_match('/(?:file\/d\/|id=)([a-zA-Z0-9_-]+)/', $this->media_url, $matches)) {
                return 'https://drive.google.com/file/d/' . $matches[1] . '/preview';
            }

            return $this->media_url;
        }

        if ($this->tipe_media === 'video_youtube' && ! empty($this->media_url)) {
            if (preg_match('/(?:v=|\/embed\/|youtu\.be\/)([a-zA-Z0-9_-]+)/', $this->media_url, $matches)) {
                return 'https://www.youtube.com/embed/' . $matches[1];
            }

            return $this->media_url;
        }

        return $this->media_url;
    }
}
