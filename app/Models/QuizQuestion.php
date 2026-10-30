<?php

namespace App\Models;

use App\Support\TrustedMediaUrl;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuizQuestion extends BaseModel
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'quiz_questions';

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class, 'quiz_id', 'id');
    }

    public function getEmbedUrlAttribute(): ?string
    {
        return TrustedMediaUrl::embedUrl($this->media_url, $this->tipe_media);
    }
}
