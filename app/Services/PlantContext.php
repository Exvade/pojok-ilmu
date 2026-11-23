<?php

namespace App\Services;

use App\Models\MManpower;

/**
 * Menentukan plant aktif untuk data baru.
 *
 * Mengikuti pola asakai/S2S: `session('active_plant')` berisi ID plant atau
 * `'all'`. Bila tidak ada plant spesifik, dipakai plant bawaan aplikasi.
 */
class PlantContext
{
    public static function defaultPlant(): int
    {
        return (int) config('app.default_plant', 3);
    }

    public static function plantForWrite(): int
    {
        $activePlant = session('active_plant');

        return is_numeric($activePlant) ? (int) $activePlant : self::defaultPlant();
    }

    /**
     * Plant asal karyawan menurut HaRis, atau plant bawaan bila belum tersedia.
     */
    public static function plantForManpower(?MManpower $manpower): int
    {
        $plantId = $manpower?->haris_plant_id;

        return is_numeric($plantId) ? (int) $plantId : self::defaultPlant();
    }
}
