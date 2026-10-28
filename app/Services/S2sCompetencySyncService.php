<?php

namespace App\Services;

use App\Jobs\SendS2sCompetency;
use App\Models\S2sCompetencySync;
use App\Models\TrainingEnrollment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class S2sCompetencySyncService
{
    public function queueApprovedEnrollment(string $enrollmentId): void
    {
        $enrollment = TrainingEnrollment::query()
            ->with(['manpower', 'training.s2sProducts'])
            ->findOrFail($enrollmentId);

        if ($enrollment->status !== 'passed' || $enrollment->ojt_status !== 'approved' || ! $enrollment->manpower || ! $enrollment->training) {
            return;
        }

        foreach ($enrollment->training->s2sProducts as $product) {
            $sync = S2sCompetencySync::query()->firstOrCreate(
                ['enrollment_id' => $enrollment->id, 's2s_product_id' => $product->id],
                [
                    'nik' => $enrollment->manpower->nik,
                    'kode_part' => $product->kode_part,
                    'kode_training' => $enrollment->training->kode_training,
                    'judul_training' => $enrollment->training->judul,
                    'level' => $enrollment->training->level,
                    'idempotency_key' => (string) Str::uuid(),
                    'status' => $this->isEnabled() ? 'queued' : 'disabled',
                ],
            );

            if ($this->isEnabled() && $sync->status !== 'succeeded') {
                $sync->forceFill(['status' => 'queued', 'last_error' => null])->save();
                SendS2sCompetency::dispatch($sync->id);
            }
        }
    }

    public function retry(S2sCompetencySync $sync): void
    {
        if (! $this->isEnabled()) {
            throw new RuntimeException('Pengiriman kompetensi ke S2S sedang dinonaktifkan pada lingkungan ini.');
        }

        if ($sync->status === 'succeeded') {
            return;
        }

        $sync->forceFill(['status' => 'queued', 'last_error' => null])->save();
        SendS2sCompetency::dispatch($sync->id);
    }

    public function send(string $syncId): void
    {
        $sync = S2sCompetencySync::query()->findOrFail($syncId);

        if (! $this->isEnabled()) {
            $sync->forceFill(['status' => 'disabled', 'last_error' => 'Pengiriman dinonaktifkan oleh konfigurasi lingkungan.'])->save();

            return;
        }

        $baseUrl = rtrim((string) config('services.s2s.base_url'), '/');
        $token = (string) config('services.s2s.competency_token');

        if ($baseUrl === '' || $token === '') {
            throw new RuntimeException('Konfigurasi pengiriman kompetensi S2S belum lengkap.');
        }

        $sync->increment('attempt_count');
        $sync->forceFill(['status' => 'sending', 'last_error' => null])->save();

        $response = Http::withToken($token)
            ->acceptJson()
            ->asJson()
            ->connectTimeout(10)
            ->timeout(60)
            ->withHeaders(['Idempotency-Key' => $sync->idempotency_key])
            ->put($baseUrl . '/api/integrations/v1/manpower-competencies/' . rawurlencode($sync->nik) . '/' . rawurlencode($sync->kode_part), [
                'level' => $sync->level,
                'status' => 'kompeten',
                'kode_training' => $sync->kode_training,
                'judul_training' => $sync->judul_training,
                'kompeten_pada' => $sync->enrollment?->ojt_assessed_at?->toIso8601String() ?? now()->toIso8601String(),
                'sumber' => 'pojok-ilmu',
            ]);

        $payload = $response->json();

        if ($response->successful()) {
            $sync->forceFill([
                'status' => 'succeeded',
                'last_http_status' => $response->status(),
                's2s_action' => data_get($payload, 'data.action'),
                'response_payload' => is_array($payload) ? $payload : null,
                'last_error' => null,
                'sent_at' => now(),
            ])->save();

            return;
        }

        $message = data_get($payload, 'message') ?: 'S2S menolak pengiriman kompetensi.';
        $sync->forceFill([
            'status' => 'failed',
            'last_http_status' => $response->status(),
            'response_payload' => is_array($payload) ? $payload : null,
            'last_error' => str($message)->limit(1000),
        ])->save();

        if (in_array($response->status(), [429, 500, 502, 503, 504], true)) {
            throw new RuntimeException($message);
        }
    }

    private function isEnabled(): bool
    {
        return (bool) config('services.s2s.competency_sync_enabled');
    }
}
