<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, HasUuids, Notifiable, SoftDeletes;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_superadmin' => 'boolean',
            'can_manage_competency' => 'boolean',
            'can_manage_curriculum' => 'boolean',
            'role' => 'integer',
        ];
    }

    /**
     * Relasi ke data Master Manpower
     */
    public function manpower(): BelongsTo
    {
        return $this->belongsTo(MManpower::class, 'user_id', 'mp_id');
    }

    /**
     * Accessor untuk nama user (fallback ke MManpower jika tidak ada)
     */
    public function getNameAttribute(): string
    {
        return $this->attributes['name'] ?? $this->manpower?->nama ?? $this->user_id;
    }

    public function isAdmin(): bool
    {
        return $this->is_superadmin;
    }

    public function isManagement(): bool
    {
        return $this->is_superadmin || $this->can_manage_competency || $this->can_manage_curriculum;
    }

    public function isOperator(): bool
    {
        return $this->role === 0;
    }

    public function canManageCompetency(): bool
    {
        return $this->is_superadmin || $this->can_manage_competency;
    }

    public function canManageCurriculum(): bool
    {
        return $this->is_superadmin || $this->can_manage_curriculum;
    }

    public function hasManagementPermission(string $permission): bool
    {
        return match ($permission) {
            'superadmin' => $this->is_superadmin,
            'competency' => $this->canManageCompetency(),
            'curriculum' => $this->canManageCurriculum(),
            default => false,
        };
    }
}
