<?php

namespace App\Services;

/**
 * Produces a stable, readable department name from the HaRis department code.
 *
 * The original HaRis code remains stored in m_manpower.kode_departemen; it is
 * the source of truth used to resolve common historical aliases.
 */
class DepartmentNameNormalizer
{
    /**
     * @var array<string, string>
     */
    private const PLANTS = [
        'BDG' => 'BANDUNG',
        'CKR' => 'CIKARANG',
        'JBK' => 'JABABEKA',
        'JKT' => 'JAKARTA',
        'PSG' => 'PASIR GOMBONG',
    ];

    public function normalize(?string $departmentName, ?string $departmentCode, ?string $plantName = null): string
    {
        $name = $this->clean($departmentName) ?? 'TANPA DEPARTEMEN';
        $canonicalDepartment = $this->canonicalDepartment($name);

        return $canonicalDepartment ?? $this->withoutPlantSuffix($name, $departmentCode, $plantName);
    }

    public function normalizePlant(?string $plantName): ?string
    {
        return $this->canonicalPlant($plantName);
    }

    private function canonicalDepartment(string $name): ?string
    {
        return match (true) {
            preg_match('/^(?:GA|GENERAL AFFAIR)(?:\s*-\s*.+)?$/', $name) === 1 => 'GA',
            preg_match('/^(?:HRD|HUMAN RESOURCES DEPART(?:E)?MENT)(?:\s*-\s*.+)?$/', $name) === 1 => 'HRD',
            preg_match('/^(?:IT|INFORMATION TECHNOLOGY)(?:\s*-\s*.+)?$/', $name) === 1 => 'IT',
            preg_match('/^(?:R\s*&\s*M|REPAIR\s*&\s*MAINTENANCE)(?:\s*-\s*.+)?$/', $name) === 1 => 'REPAIR & MAINTENANCE',
            default => null,
        };
    }

    private function withoutPlantSuffix(string $name, ?string $departmentCode, ?string $plantName): string
    {
        $plant = $this->plantFromCode($departmentCode) ?? $this->canonicalPlant($plantName);

        if (! $plant) {
            return $name;
        }

        return preg_replace('/\s*-\s*' . preg_quote($plant, '/') . '$/', '', $name) ?: $name;
    }

    private function plantFromCode(?string $departmentCode): ?string
    {
        $code = $this->clean($departmentCode);
        $prefix = $code ? strtok($code, '.') : false;

        return is_string($prefix) ? self::PLANTS[$prefix] ?? null : null;
    }

    private function canonicalPlant(?string $plantName): ?string
    {
        $plantName = $this->clean($plantName);

        return $plantName ? match ($plantName) {
            'CIKARANG' => 'CIKARANG',
            'BANDUNG' => 'BANDUNG',
            'JABABEKA' => 'JABABEKA',
            'JAKARTA' => 'JAKARTA',
            'PASIR GOMBONG' => 'PASIR GOMBONG',
            default => $plantName,
        } : null;
    }

    private function clean(?string $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = str_replace('_', ' ', $value);
        $value = trim(preg_replace('/\s+/', ' ', $value) ?? '');

        return $value === '' ? null : mb_strtoupper($value);
    }
}
