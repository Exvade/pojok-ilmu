<?php

namespace App\Services;

use App\Models\DepartmentAlias;
use App\Models\MManpower;

class DepartmentAliasResolver
{
    public function resolve(?string $value): ?string
    {
        $key = $this->key($value);

        if ($key === '') {
            return null;
        }

        $department = MManpower::query()
            ->where('status', true)
            ->whereNotNull('haris_synced_at')
            ->whereNotNull('departemen')
            ->get(['departemen'])
            ->first(fn (MManpower $manpower): bool => $this->key($manpower->departemen) === $key)
            ?->departemen;

        if ($department) {
            return $department;
        }

        return DepartmentAlias::query()->where('alias_key', $key)->value('departemen');
    }

    public function key(?string $value): string
    {
        $value = str_replace('_', ' ', (string) $value);

        return mb_strtoupper(trim(preg_replace('/\s+/', ' ', $value) ?? ''));
    }
}
