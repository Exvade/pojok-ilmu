<?php

use App\Models\MManpower;
use App\Models\MSkill;
use App\Models\S2sProduct;
use App\Models\Training;
use App\Models\TrainingEnrollment;
use App\Models\User;
use App\Services\ProductTrainingProgressService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function competencySummaryOperator(string $nik): array
{
    $manpower = MManpower::create(['mp_id' => $nik, 'nik' => $nik, 'nama' => 'Operator ' . $nik, 'departemen' => 'PRODUKSI PLASTIK A', 'bagian' => 'OPERATOR', 'status' => true]);

    return [$manpower, User::create(['user_id' => $nik, 'name' => $manpower->nama, 'role' => 0])];
}

test('operator dapat mengunduh ringkasan kompetensi dalam bentuk pdf', function () {
    [$manpower, $user] = competencySummaryOperator('PDF-OP');
    $skill = MSkill::create(['kode_skill' => 'PDF-SKILL', 'nama_skill' => 'Produk', 'kategori' => 'Produksi']);
    $training = Training::create(['kode_training' => 'PDF-01', 'skill_id' => $skill->id, 'level' => 1, 'judul' => 'Housing RCL']);
    $training->s2sProducts()->attach(S2sProduct::create(['kode_part' => '12345678', 'nama_part' => 'HOUSING RCL', 'status' => 'aktif']));
    TrainingEnrollment::create(['training_id' => $training->id, 'manpower_id' => $manpower->id, 'status' => 'passed', 'ojt_status' => 'approved', 'post_test_completed_at' => now(), 'ojt_assessed_at' => now()]);

    $this->actingAs($user)->get(route('operator.dashboard'))->assertOk()
        ->assertSee('Unduh kompetensi (PDF)')
        ->assertSee(route('operator.competency-summary.pdf'), false);

    $response = $this->get(route('operator.competency-summary.pdf'))->assertOk()
        ->assertHeader('content-type', 'application/pdf');

    expect($response->headers->get('content-disposition'))->toContain('Ringkasan Kompetensi - Operator Pdf-Op - ' . now()->translatedFormat('d M Y') . '.pdf')
        ->and($response->getContent())->toStartWith('%PDF');
});

test('isi ringkasan kompetensi memuat identitas dan capaian per level', function () {
    [$manpower, $user] = competencySummaryOperator('PDF-ISI');
    $skill = MSkill::create(['kode_skill' => 'PDF-SKILL-2', 'nama_skill' => 'Produk', 'kategori' => 'Produksi']);
    $levelOne = Training::create(['kode_training' => 'ISI-01', 'skill_id' => $skill->id, 'level' => 1, 'judul' => 'Base Radiator']);
    $levelOne->s2sProducts()->attach(S2sProduct::create(['kode_part' => '23746247', 'nama_part' => 'BASE RADIATOR', 'status' => 'aktif']));
    $levelTwo = Training::create(['kode_training' => 'ISI-02', 'skill_id' => $skill->id, 'level' => 2, 'judul' => 'Cover Comp']);
    TrainingEnrollment::create(['training_id' => $levelOne->id, 'manpower_id' => $manpower->id, 'status' => 'in_progress', 'post_test_completed_at' => now()]);
    TrainingEnrollment::create(['training_id' => $levelTwo->id, 'manpower_id' => $manpower->id, 'status' => 'enrolled']);

    $this->actingAs($user);
    $html = view('pdf.competency-summary', [
        'manpower' => $manpower,
        'enrollments' => $enrollments = app(ProductTrainingProgressService::class)->operatorEnrollments($manpower),
        'currentLevel' => 0,
        'levelRows' => app(ProductTrainingProgressService::class)->levelRows($enrollments),
    ])->render();

    expect($html)->toContain('Level Manpower', 'Operator PDF-ISI', 'PDF-ISI', 'PRODUKSI PLASTIK A', 'Level 1', 'BASE RADIATOR', '23746247', 'Menunggu OJT', 'Level 2', 'Cover Comp', 'Belum kompeten', 'Next progress')
        ->not->toContain('Sedang berjalan')
        ->not->toContain('Balok');
});

test('pengguna tanpa data karyawan tidak dapat mengunduh ringkasan kompetensi', function () {
    $user = User::create(['user_id' => 'TANPA-MP', 'name' => 'Tanpa data', 'role' => 0]);

    $response = $this->actingAs($user)->get(route('operator.competency-summary.pdf'));

    expect($response->headers->get('content-type'))->not->toBe('application/pdf')
        ->and($response->getStatusCode())->toBeIn([302, 404]);
});
