<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getConnectionName()
    {
        return app()->environment('testing')
            ? parent::getConnectionName()
            : config('database.s2s_connection', env('DB_S2S_CONNECTION', 's2s'));
    }

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_superadmin' => 'boolean',
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
        return $this->is_superadmin || $this->role === 1;
    }

    public function isManagement(): bool
    {
        return $this->is_superadmin || $this->role === 1 || $this->role === 2;
    }

    public function isOperator(): bool
    {
        return $this->role === 0;
    }
}
