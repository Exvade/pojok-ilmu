<?php

namespace App\Services;

class JobTitleNormalizer
{
    public function normalize(?string $jobTitle): string
    {
        $title = $this->clean($jobTitle) ?? 'TANPA JABATAN';

        return match (true) {
            preg_match('/\bSTAFF\b/', $title) === 1 => 'STAFF',
            preg_match('/^OPERATOR\b/', $title) === 1 => 'OPERATOR',
            preg_match('/^(?:MANAGER|MANAJER)\b/', $title) === 1 => 'MANAGER',
            preg_match('/^(?:SUPERVISOR|SUPERVISER)\b/', $title) === 1 => 'SUPERVISOR',
            preg_match('/^FOREMAN\b/', $title) === 1 => 'FOREMAN',
            preg_match('/^(?:KEPALA BAGIAN|KABAG)\b/', $title) === 1 => 'KEPALA BAGIAN',
            preg_match('/^(?:KEPALA SEKSI|KASI)\b/', $title) === 1 => 'KEPALA SEKSI',
            preg_match('/^(?:DIREKTUR|DIRECTOR)\b/', $title) === 1 => 'DIREKTUR',
            default => $title,
        };
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
