<?php

use App\Models\MManpower;
use App\Models\MSkill;
use App\Models\Quiz;
use App\Models\Training;
use App\Models\TrainingEnrollment;
use App\Models\TrainingMaterial;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function trainingSimulasiLama(): Training
{
    $skill = MSkill::create(['kode_skill' => 'DEMO-HARIS-L1', 'nama_skill' => 'DEMO - Pengenalan Produk', 'kategori' => 'DEMO PELATIHAN']);

    return Training::create(['kode_training' => 'DEMO-HARIS-L1', 'skill_id' => $skill->id, 'level' => 1, 'judul' => 'DEMO - Pengenalan Produk', 'deskripsi' => 'Data contoh. Tambahkan materi video sebelum digunakan untuk demonstrasi.', 'passing_grade' => 80]);
}

test('pratinjau tidak mengubah data dan pembaruan mempertahankan materi dan progres', function () {
    $training = trainingSimulasiLama();
    $manpower = MManpower::create(['mp_id' => 'MP-TEST', 'nik' => 'NIK-TEST', 'nama' => 'Karyawan', 'departemen' => 'IT', 'bagian' => 'STAFF', 'status' => true]);
    $enrollment = TrainingEnrollment::create(['training_id' => $training->id, 'manpower_id' => $manpower->id, 'status' => 'in_progress']);
    $material = TrainingMaterial::create(['training_id' => $training->id, 'urutan' => 1, 'judul' => 'Video produk', 'tipe_media' => 'video_youtube', 'media_url' => 'https://example.com/video']);
    $quiz = Quiz::create(['training_id' => $training->id, 'tipe' => 'pre_test', 'judul' => 'Pre-test DEMO - Pengenalan Produk', 'durasi_menit' => 15, 'passing_grade' => 80]);

    $this->artisan('pojok-ilmu:perbarui-simulasi')->assertSuccessful();
    expect($training->fresh()->kode_training)->toBe('DEMO-HARIS-L1');

    $this->artisan('pojok-ilmu:perbarui-simulasi', ['--jalankan' => true])->assertSuccessful();
    expect($training->fresh()->kode_training)->toBe('TRN-PRODUK-L1')
        ->and($training->fresh()->judul)->toBe('Pengenalan Produk')
        ->and($training->fresh()->skill->nama_skill)->toBe('Pengenalan Produk')
        ->and($quiz->fresh()->judul)->toBe('Pre-test Pengenalan Produk')
        ->and($material->fresh()->training_id)->toBe($training->id)
        ->and($enrollment->fresh()->status)->toBe('in_progress');
    $this->artisan('pojok-ilmu:perbarui-simulasi', ['--jalankan' => true])->assertSuccessful();
});

test('kode yang sudah digunakan membatalkan perubahan', function () {
    $training = trainingSimulasiLama();
    MSkill::create(['kode_skill' => 'TRN-PRODUK-L1', 'nama_skill' => 'Kompetensi lain', 'kategori' => 'Lain']);
    $this->artisan('pojok-ilmu:perbarui-simulasi', ['--jalankan' => true])->assertFailed();
    expect($training->fresh()->kode_training)->toBe('DEMO-HARIS-L1');
});

test('pembaruan mempertahankan judul yang sudah diubah pengguna', function () {
    $training = trainingSimulasiLama();
    $training->update(['judul' => 'Judul pilihan pengguna']);
    $this->artisan('pojok-ilmu:perbarui-simulasi', ['--jalankan' => true])->assertSuccessful();
    expect($training->fresh()->judul)->toBe('Judul pilihan pengguna');
});
