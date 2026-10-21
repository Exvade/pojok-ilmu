<?php

namespace App\Services;

class DepartmentGroupService
{
    public static function normalize(?string $department): ?string
    {
        $department = trim((string) $department);

        if ($department === '') {
            return null;
        }

        $department = preg_replace('/\s+/', ' ', $department) ?? $department;

        return strtoupper($department);
    }
}
