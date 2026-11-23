<?php

namespace App\Models\Concerns;

use App\Services\PlantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/**
 * Menyaring data berdasarkan plant aktif (pola yang sama dengan asakai).
 *
 * Model wajib memiliki kolom `plant`. Penyaringan hanya aktif dalam mode
 * multi-plant ketika sesi memiliki plant spesifik.
 */
trait IsolatesPlant
{
    protected static function bootIsolatesPlant(): void
    {
        static::addGlobalScope('isolated_plant', function (Builder $query): void {
            $plant = PlantContext::isolationPlant();

            if ($plant !== null) {
                $query->where($query->getModel()->qualifyColumn('plant'), $plant);
            }
        });

        static::creating(function (Model $model): void {
            $model->plant ??= PlantContext::plantForWrite();
        });

        static::updating(function (Model $model): void {
            if ($model->isDirty('plant')) {
                throw ValidationException::withMessages([
                    'plant' => 'Plant data yang sudah tersimpan tidak dapat diubah.',
                ]);
            }
        });
    }
}
