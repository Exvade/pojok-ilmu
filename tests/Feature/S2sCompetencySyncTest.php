<?php

use App\Jobs\SendS2sCompetency;
use App\Models\MManpower;
use App\Models\MSkill;
use App\Models\S2sCompetencySync;
use App\Models\S2sProduct;
use App\Models\Training;
use App\Models\TrainingEnrollment;
use App\Services\S2sCompetencySyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config()->set('services.s2s.base_url', 'http://10.10.16.3');
    config()->set('services.s2s.competency_token', 'token-kompetensi-pengujian');
});

function enrollmentUntukSinkronisasiS2s(): TrainingEnrollment
{
    $manpower = MManpower::create(['mp_id' => 'S2S-OP-01', 'nik' => '3734.250710.CKR', 'nama' => 'Karyawan Uji S2S', 'departemen' => 'IT', 'bagian' => 'OPERATOR', 'status' => true]);
    $skill = MSkill::create(['kode_skill' => 'S2S-BOX', 'nama_skill' => 'Box Battery', 'kategori' => 'IT']);
    $training = Training::create(['kode_training' => 'BOX-BATTERY-L2', 'skill_id' => $skill->id, 'departemen' => 'IT', 'level' => 2, 'judul' => 'Setting Parameter Box Battery', 'requires_ojt' => true]);
    $product = S2sProduct::create(['kode_part' => 'BOX-BATTERY', 'nama_part' => 'Box Battery', 'status' => 'aktif']);
    $training->s2sProducts()->attach($product);

    return TrainingEnrollment::create([
        'training_id' => $training->id,
        'manpower_id' => $manpower->id,
        'status' => 'passed',
        'ojt_status' => 'approved',
        'ojt_assessed_at' => now(),
    ]);
}

test('kompetensi ojt lulus masuk antrean dan dikirim ke s2s', function () {
    config()->set('services.s2s.competency_sync_enabled', true);
    Queue::fake();
    $enrollment = enrollmentUntukSinkronisasiS2s();

    app(S2sCompetencySyncService::class)->queueApprovedEnrollment($enrollment->id);

    $sync = S2sCompetencySync::firstOrFail();
    expect($sync->status)->toBe('queued')
        ->and($sync->level)->toBe(2)
        ->and($sync->kode_part)->toBe('BOX-BATTERY');
    Queue::assertPushed(SendS2sCompetency::class, fn (SendS2sCompetency $job): bool => $job->syncId === $sync->id);

    Http::fake([
        'http://10.10.16.3/api/integrations/v1/manpower-competencies/*' => Http::response([
            'message' => 'Kompetensi berhasil diproses.',
            'data' => ['action' => 'updated'],
        ]),
    ]);

    app(S2sCompetencySyncService::class)->send($sync->id);

    expect($sync->fresh()->status)->toBe('succeeded')
        ->and($sync->fresh()->s2s_action)->toBe('updated');
    Http::assertSent(fn ($request): bool => $request->method() === 'PUT'
        && $request->hasHeader('Authorization', 'Bearer token-kompetensi-pengujian')
        && $request->hasHeader('Idempotency-Key')
        && data_get($request->data(), 'level') === 2
        && data_get($request->data(), 'sumber') === 'pojok-ilmu');
});

test('lokal mencatat sinkronisasi sebagai dinonaktifkan tanpa mengirim ke s2s', function () {
    config()->set('services.s2s.competency_sync_enabled', false);
    Queue::fake();

    app(S2sCompetencySyncService::class)->queueApprovedEnrollment(enrollmentUntukSinkronisasiS2s()->id);

    expect(S2sCompetencySync::firstOrFail()->status)->toBe('disabled');
    Queue::assertNothingPushed();
});
