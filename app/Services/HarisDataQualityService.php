<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class HarisDataQualityService
{
    /**
     * @return array{total: int, issues: Collection<int, array{nik: ?string, nama: ?string, kode_departemen: ?string, departemen: ?string, jabatan: ?string, plant_id: ?string, plant: ?string, problems: array<int, string>}>}
     */
    public function inspect(): array
    {
        $url = config('services.haris.url');
        $token = config('services.haris.token');

        if (blank($url) || blank($token)) {
            throw new RuntimeException('Konfigurasi HaRis belum lengkap.');
        }

        $response = Http::acceptJson()
            ->connectTimeout(10)
            ->timeout(60)
            ->retry(
                (int) config('services.haris.retry_times'),
                (int) config('services.haris.retry_delay_ms'),
            )
            ->get($url, ['token' => $token]);

        $response->throw();

        $employees = collect($response->json('data'))
            ->filter(fn (mixed $employee): bool => is_array($employee));

        if ($employees->isEmpty()) {
            throw new RuntimeException('HaRis tidak mengirim data karyawan yang valid.');
        }

        $issues = $employees
            ->map(function (array $employee): ?array {
                $departmentCode = $this->value(data_get($employee, 'departemen.kode'));
                $department = $this->value(data_get($employee, 'departemen.nama'));
                $plantId = $this->value(data_get($employee, 'plant.id'));
                $plant = $this->value(data_get($employee, 'plant.nama'));
                $problems = [];

                if ($plantId === null || $plant === null) {
                    $problems[] = 'Plant belum lengkap';
                }

                if ($departmentCode === null || $department === null) {
                    $problems[] = 'Departemen belum lengkap';
                }

                if ($this->value($employee['jabatan'] ?? null) === null) {
                    $problems[] = 'Jabatan belum diisi';
                }

                if ($problems === []) {
                    return null;
                }

                return [
                    'nik' => $this->value($employee['nik'] ?? null),
                    'nama' => $this->value($employee['namaLengkap'] ?? null),
                    'kode_departemen' => $departmentCode,
                    'departemen' => $department,
                    'jabatan' => $this->value($employee['jabatan'] ?? null),
                    'plant_id' => $plantId,
                    'plant' => $plant,
                    'problems' => $problems,
                ];
            })
            ->filter()
            ->sortBy(['kode_departemen', 'nama'])
            ->values();

        return [
            'total' => $employees->count(),
            'issues' => $issues,
        ];
    }

    private function value(mixed $value): ?string
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
