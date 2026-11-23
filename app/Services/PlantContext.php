<?php

namespace App\Services;

use App\Models\MManpower;
use App\Models\User;

/**
 * Konteks plant aktif, mengikuti pola asakai/S2S.
 *
 * `session('active_plant')` berisi ID plant HaRis atau `'all'`. Isolasi data
 * hanya berlaku bila lebih dari satu plant diaktifkan, sehingga mode satu
 * plant berperilaku persis seperti sebelum dukungan multi-plant.
 */
class PlantContext
{
    public const ALL = 'all';

    public static function defaultPlant(): int
    {
        return (int) config('app.default_plant', 3);
    }

    /** @return array<int, int> */
    public static function activePlants(): array
    {
        $plants = array_values(array_filter(array_map('intval', (array) config('app.active_plants', []))));

        return $plants === [] ? [self::defaultPlant()] : $plants;
    }

    public static function isMultiPlant(): bool
    {
        return count(self::activePlants()) > 1;
    }

    /**
     * Plant yang dipakai untuk menyaring data, atau null bila tidak perlu disaring.
     */
    public static function isolationPlant(): ?int
    {
        if (! self::isMultiPlant()) {
            return null;
        }

        $activePlant = session('active_plant');

        return is_numeric($activePlant) ? (int) $activePlant : null;
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

    /**
     * Plant aktif yang boleh diakses pengguna. Superadmin dapat mengakses semua plant aktif.
     *
     * @return array<int, int>
     */
    public static function accessiblePlants(User $user): array
    {
        $activePlants = self::activePlants();

        if ($user->is_superadmin) {
            return $activePlants;
        }

        $plants = array_values(array_intersect($user->plantIds(), $activePlants));

        return $plants !== [] ? $plants : array_values(array_intersect([self::plantForManpower($user->manpower)], $activePlants));
    }

    /**
     * Pilihan nilai plant aktif yang sah bagi pengguna, termasuk `'all'` untuk superadmin.
     *
     * @return array<int, int|string>
     */
    public static function selectableValues(User $user): array
    {
        $plants = self::accessiblePlants($user);

        return $user->is_superadmin ? [self::ALL, ...$plants] : $plants;
    }

    public static function initialValue(User $user): int|string|null
    {
        if ($user->is_superadmin) {
            return self::ALL;
        }

        return self::accessiblePlants($user)[0] ?? null;
    }

    public static function label(int|string|null $plant): string
    {
        return $plant === self::ALL ? 'Semua plant' : 'Plant ' . $plant;
    }
}
