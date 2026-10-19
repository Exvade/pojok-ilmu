<?php

namespace App\Services;

class DepartmentGroupService
{
    public const PRODUCTION_PLASTIC = 'PRODUKSI PLASTIK';

    public static function normalize(?string $department): ?string
    {
        $department = trim((string) $department);

        if ($department === '') {
            return null;
        }

        $department = preg_replace('/\s+/', ' ', $department) ?? $department;
        $upperDepartment = strtoupper($department);

        if (str_starts_with($upperDepartment, self::PRODUCTION_PLASTIC)) {
            return self::PRODUCTION_PLASTIC;
        }

        return $upperDepartment;
    }
}
