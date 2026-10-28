<?php

namespace App\Services;

use App\Models\S2sProduct;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class S2sProductSyncService
{
    /** @return array{product_count: int, message: string} */
    public function sync(): array
    {
        $baseUrl = rtrim((string) config('services.s2s.base_url'), '/');
        $token = (string) config('services.s2s.parts_token');

        if ($baseUrl === '' || $token === '') {
            throw new RuntimeException('Konfigurasi koneksi S2S belum lengkap. Isi alamat server dan token produk terlebih dahulu.');
        }

        try {
            $page = 1;
            $processed = 0;
            $updatedSince = S2sProduct::query()->max('s2s_updated_at');

            do {
                $query = ['page' => $page, 'per_page' => 500];

                if ($updatedSince) {
                    $query['updated_since'] = Carbon::parse($updatedSince)->toIso8601String();
                }

                $response = Http::withToken($token)
                    ->acceptJson()
                    ->connectTimeout(10)
                    ->timeout(60)
                    ->retry(3, 1000)
                    ->get($baseUrl . '/api/integrations/v1/parts', $query);

                if ($response->status() === 401 || $response->status() === 403) {
                    throw new RuntimeException('S2S menolak akses produk. Periksa token integrasi dan izin IP server Pojok Ilmu.');
                }

                $response->throw();
                $parts = $response->json('data');

                if (! is_array($parts)) {
                    throw new RuntimeException('Format respons produk dari S2S tidak sesuai.');
                }

                $now = now();
                $existingIds = S2sProduct::query()
                    ->whereIn('kode_part', collect($parts)->pluck('kode_part')->filter()->map(fn ($code) => trim((string) $code))->all())
                    ->pluck('id', 'kode_part');
                $records = collect($parts)
                    ->filter(fn (mixed $part): bool => is_array($part) && filled($part['kode_part'] ?? null))
                    ->map(fn (array $part): array => [
                        'id' => (string) ($existingIds[trim((string) $part['kode_part'])] ?? Str::uuid()),
                        'kode_part' => trim((string) $part['kode_part']),
                        'nama_part' => filled($part['nama_part'] ?? null) ? trim((string) $part['nama_part']) : null,
                        'status' => strtolower(trim((string) ($part['status'] ?? 'aktif'))),
                        's2s_updated_at' => filled($part['updated_at'] ?? null) ? Carbon::parse($part['updated_at']) : null,
                        'synced_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])
                    ->values();

                if ($records->isNotEmpty()) {
                    S2sProduct::query()->upsert(
                        $records->all(),
                        ['kode_part'],
                        ['nama_part', 'status', 's2s_updated_at', 'synced_at', 'updated_at'],
                    );
                }

                $processed += $records->count();
                $total = (int) data_get($response->json(), 'meta.total', 0);
                $page++;
            } while ($processed < $total && $parts !== []);
        } catch (RuntimeException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            throw new RuntimeException('Sinkronisasi produk S2S gagal. Periksa koneksi server lalu coba kembali.', previous: $exception);
        }

        return [
            'product_count' => $processed,
            'message' => sprintf('Sinkronisasi produk S2S berhasil. %d produk diperbarui.', $processed),
        ];
    }
}
