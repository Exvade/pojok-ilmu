<?php

use App\Models\MManpower;
use App\Models\MSkill;
use App\Models\Quiz;
use App\Models\Training;
use App\Models\TrainingEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function readinessTraining(string $code, string $title): Training
{
    $skill = MSkill::firstOrCreate(['kode_skill' => 'SIAP-SKILL'], ['nama_skill' => 'Produk', 'kategori' => 'Produksi']);

    return Training::create(['kode_training' => $code, 'skill_id' => $skill->id, 'level' => 1, 'judul' => $title, 'departemen' => 'PRODUKSI PLASTIK A']);
}

test('training dianggap siap bila punya materi dan post-test berisi soal', function () {
    $empty = readinessTraining('SIAP-0', 'Training Kosong');
    $postTestWithoutQuestion = readinessTraining('SIAP-1', 'Post-test Tanpa Soal');
    Quiz::create(['training_id' => $postTestWithoutQuestion->id, 'tipe' => 'post_test', 'judul' => 'Post-test', 'durasi_menit' => 15, 'passing_grade' => 80, 'max_retries' => 3, 'cooldown_hours' => 24]);
    $ready = makeTrainingReady(readinessTraining('SIAP-2', 'Training Lengkap'));

    expect($empty->readinessIssues())->toBe(['Belum ada materi', 'Post-test belum ada'])
        ->and($postTestWithoutQuestion->fresh()->readinessIssues())->toBe(['Belum ada materi', 'Post-test belum memiliki soal'])
        ->and($ready->fresh()->isReady())->toBeTrue();
});

test('daftar training menandai dan dapat memfilter training yang belum siap', function () {
    readinessTraining('SIAP-KOSONG', 'Training Kosong');
    makeTrainingReady(readinessTraining('SIAP-LENGKAP', 'Training Lengkap'));
    $admin = User::create(['user_id' => 'ADMIN-SIAP', 'name' => 'Pengelola Kurikulum', 'role' => 1, 'can_manage_curriculum' => true]);

    $this->actingAs($admin)->get(route('admin.trainings.index'))->assertOk()
        ->assertSee('Belum siap')
        ->assertSee('Belum ada materi · Post-test belum ada.')
        ->assertSee('Training Lengkap');

    $this->get(route('admin.trainings.index', ['kesiapan' => 'belum-siap']))->assertOk()
        ->assertSee('Training Kosong')
        ->assertDontSee('Training Lengkap');
});

test('operator melihat materi sedang disiapkan untuk training yang belum siap dan kartu di layar kecil', function () {
    $operator = MManpower::create(['mp_id' => 'SIAP-OP', 'nik' => 'SIAP-OP', 'nama' => 'Operator Siap', 'departemen' => 'PRODUKSI PLASTIK A', 'bagian' => 'OPERATOR', 'status' => true]);
    $user = User::create(['user_id' => 'SIAP-OP', 'name' => 'Operator Siap', 'role' => 0]);
    $empty = readinessTraining('SIAP-OP-1', 'Training Kosong');
    $ready = makeTrainingReady(readinessTraining('SIAP-OP-2', 'Training Lengkap'));
    TrainingEnrollment::create(['training_id' => $empty->id, 'manpower_id' => $operator->id, 'status' => 'enrolled']);
    TrainingEnrollment::create(['training_id' => $ready->id, 'manpower_id' => $operator->id, 'status' => 'enrolled']);

    $response = $this->actingAs($user)->get(route('operator.dashboard'))->assertOk()
        ->assertSee('Materi sedang disiapkan')
        ->assertSee('Training sekarang')
        ->assertSee('data-training-cards', false)
        ->assertSee('Anda memiliki 1 training yang belum mencapai level kompetensi 2, yaitu Training Lengkap.');

    expect(substr_count($response->getContent(), 'data-training-preparing'))->toBe(2);
});

test('tanggal ditampilkan dalam bahasa indonesia', function () {
    expect(app()->getLocale())->toBe('id')
        ->and(now()->setDate(2026, 10, 14)->translatedFormat('d M Y'))->toBe('14 Okt 2026');
});
