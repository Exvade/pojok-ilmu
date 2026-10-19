<?php

namespace App\Services;

use App\Models\HarisSyncLog;
use App\Models\MManpower;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class HarisEmployeeSyncService
{
    /**
     * @return array{employee_count: int, deactivated_count: int, message: string}
     */
    public function sync(): array
    {
        $startedAt = now();

        try {
            $url = config('services.haris.url');
            $token = config('services.haris.token');

            if (blank($url) || blank($token)) {
                throw new RuntimeException('Konfigurasi HaRis belum lengkap.');
            }

            $response = Http::acceptJson()
                ->connectTimeout(10)
                ->timeout(60)
                ->get($url, ['token' => $token]);

            $response->throw();

            $managementUserIds = User::query()
                ->where(function ($query): void {
                    $query->where('is_superadmin', true)->orWhereIn('role', [1, 2]);
                })
                ->pluck('user_id')
                ->filter()
                ->all();

            $records = collect($response->json('data'))
                ->filter(fn (mixed $employee): bool => is_array($employee))
                ->map(fn (array $employee): ?array => $this->mapEmployee($employee, $startedAt))
                ->filter()
                ->filter(fn (array $employee): bool => $this->isIncludedInScope($employee, $managementUserIds))
                ->unique('nik')
                ->values();

            if ($records->isEmpty()) {
                throw new RuntimeException('HaRis tidak mengirim data karyawan yang valid.');
            }

            // Compare the response with the same Plant 3/management scope. This
            // keeps the incomplete-response safeguard useful after the scope is
            // deliberately narrowed from all plants to Plant 3.
            $existingCount = MManpower::query()
                ->whereNotNull('haris_synced_at')
                ->get(['nik', 'kode_departemen', 'jabatan'])
                ->filter(fn (MManpower $employee): bool => $this->isIncludedInScope($employee->getAttributes(), $managementUserIds))
                ->count();

            $minimumSafeCount = (int) floor($existingCount * 0.7);

            if ($existingCount > 0 && $records->count() < $minimumSafeCount) {
                throw new RuntimeException('Jumlah data dari HaRis di bawah ambang aman. Penonaktifan dibatalkan.');
            }

            $deactivatedCount = DB::transaction(function () use ($records): int {
                $syncedNiks = $records->pluck('nik')->all();

                MManpower::withTrashed()
                    ->whereIn('nik', $syncedNiks)
                    ->restore();

                foreach ($records->chunk(500) as $chunk) {
                    MManpower::upsert(
                        $chunk->all(),
                        ['nik'],
                        [
                            'nama',
                            'departemen',
                            'department_group',
                            'kode_departemen',
                            'nama_plant',
                            'haris_plant_id',
                            'bagian',
                            'jabatan',
                            'status',
                            'status_karyawan',
                            'email',
                            'phone',
                            'haris_synced_at',
                            'updated_at',
                        ],
                    );
                }

                return MManpower::query()
                    ->whereNotNull('haris_synced_at')
                    ->whereNotIn('nik', $syncedNiks)
                    ->where('status', true)
                    ->update([
                        'status' => false,
                        'status_karyawan' => 'Nonaktif',
                        'updated_at' => now(),
                    ]);
            });

            $result = [
                'employee_count' => $records->count(),
                'deactivated_count' => $deactivatedCount,
                'message' => sprintf(
                    'Sinkronisasi HaRis berhasil: %d karyawan diproses, %d dinonaktifkan.',
                    $records->count(),
                    $deactivatedCount,
                ),
            ];

            HarisSyncLog::create([
                'status' => 'success',
                ...$result,
                'started_at' => $startedAt,
                'completed_at' => now(),
            ]);

            return $result;
        } catch (Throwable $exception) {
            $message = 'Sinkronisasi HaRis gagal. Periksa konfigurasi dan koneksi API.';

            HarisSyncLog::create([
                'status' => 'failed',
                'employee_count' => 0,
                'deactivated_count' => 0,
                'message' => $message,
                'started_at' => $startedAt,
                'completed_at' => now(),
            ]);

            throw new RuntimeException($message, previous: $exception);
        }
    }

    /**
     * @param  array<string, mixed>  $employee
     * @return array<string, mixed>|null
     */
    private function mapEmployee(array $employee, Carbon $syncedAt): ?array
    {
        $nik = $this->normalize($employee['nik'] ?? null);
        $name = $this->normalize($employee['namaLengkap'] ?? null);

        if (blank($nik) || blank($name)) {
            return null;
        }

        $departmentCode = $this->normalize(data_get($employee, 'departemen.kode'));
        $plantName = app(DepartmentNameNormalizer::class)->normalizePlant(
            $this->normalize(data_get($employee, 'plant.nama')),
        );
        $plantId = $this->normalize((string) data_get($employee, 'plant.id', ''));
        $departmentName = app(DepartmentNameNormalizer::class)->normalize(
            $this->normalize(data_get($employee, 'departemen.nama')),
            $departmentCode,
            $plantName,
        );
        $jobTitle = app(JobTitleNormalizer::class)->normalize($this->normalize($employee['jabatan'] ?? null));
        $status = $this->normalize($employee['statusKaryawan'] ?? null) ?? 'Nonaktif';

        return [
            'mp_id' => $nik,
            'nik' => $nik,
            'nama' => $name,
            'departemen' => $departmentName,
            'department_group' => DepartmentGroupService::normalize($departmentName),
            'kode_departemen' => $departmentCode,
            'nama_plant' => $plantName,
            'haris_plant_id' => $plantId,
            'bagian' => $jobTitle,
            'jabatan' => $jobTitle,
            'status' => strcasecmp($status, 'Aktif') === 0,
            'status_karyawan' => $status,
            'email' => $this->normalize($employee['email'] ?? null),
            'phone' => $this->normalize($employee['primary_phone'] ?? null),
            'haris_synced_at' => $syncedAt,
            'created_at' => $syncedAt,
            'updated_at' => $syncedAt,
        ];
    }

    private function normalize(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim(preg_replace('/\s+/', ' ', $value) ?? '');

        return $value === '' ? null : $value;
    }

    /**
     * Pojok Ilmu currently serves Plant 3 (Pasir Gombong). Formal management
     * roles and users provisioned as Management stay available across plants.
     * Set HARIS_SCOPE_PLANT_ID and HARIS_SCOPE_PLANT_CODE empty to intentionally
     * synchronize all plants.
     *
     * @param  array<string, mixed>  $employee
     * @param  array<int, string>  $managementUserIds
     */
    private function isIncludedInScope(array $employee, array $managementUserIds): bool
    {
        $scopePlantId = trim((string) config('services.haris.scope_plant_id'));
        $scopePlantCode = strtoupper(trim((string) config('services.haris.scope_plant_code')));

        if ($scopePlantId === '' && $scopePlantCode === '') {
            return true;
        }

        if (in_array($employee['nik'], $managementUserIds, true) || $this->isManagementJobTitle($employee['jabatan'])) {
            return true;
        }

        if ($scopePlantId !== '') {
            return (string) ($employee['haris_plant_id'] ?? '') === $scopePlantId;
        }

        return str_starts_with(strtoupper((string) ($employee['kode_departemen'] ?? '')), $scopePlantCode . '.');
    }

    private function isManagementJobTitle(?string $jobTitle): bool
    {
        if (blank($jobTitle)) {
            return false;
        }

        return preg_match('/\b(MANAGER|MANAJER|DIREKTUR|DIRECTOR)\b/i', $jobTitle) === 1;
    }
}
