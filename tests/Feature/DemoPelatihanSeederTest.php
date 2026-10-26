<?php

use App\Models\MManpower;
use App\Models\QuizQuestion;
use App\Models\Training;
use App\Models\TrainingEnrollment;
use App\Models\TrainingMaterial;
use Database\Seeders\DemoPelatihanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('seeder menyediakan akun demo dan training bertingkat tanpa materi', function () {
    $this->seed(DemoPelatihanSeeder::class);

    $manpower = MManpower::where('nik', 'DEMO-POJOK-001')->firstOrFail();
    $trainings = Training::whereIn('kode_training', ['DEMO-POJOK-L1', 'DEMO-POJOK-L2', 'DEMO-POJOK-L3'])->with(['preTest.questions', 'postTest.questions', 'materials'])->orderBy('level')->get();

    expect($trainings)->toHaveCount(3);
    expect($trainings->pluck('level')->all())->toBe([1, 2, 3]);
    expect(TrainingEnrollment::where('manpower_id', $manpower->id)->count())->toBe(3);
    foreach ($trainings as $training) {
        expect($training->materials)->toBeEmpty();
        expect($training->preTest->questions)->toHaveCount(3);
        expect($training->postTest->questions)->toHaveCount(3);
    }

    $this->post(route('login.post'), ['user_id' => 'DEMO-POJOK-001'])->assertRedirect('/operator/dashboard');
    $this->get('/operator/dashboard')->assertOk()->assertSee('KARYAWAN DEMO POJOK ILMU')->assertSee('DEMO - Pengenalan Produk');
});

test('seeder dapat dijalankan ulang tanpa menggandakan data atau menghapus materi dan progres', function () {
    $this->seed(DemoPelatihanSeeder::class);
    $training = Training::where('kode_training', 'DEMO-POJOK-L1')->firstOrFail();
    $material = TrainingMaterial::create(['training_id' => $training->id, 'urutan' => 1, 'judul' => 'Video demo', 'tipe_media' => 'video_youtube', 'media_url' => 'https://example.com/video']);
    $enrollment = TrainingEnrollment::where('training_id', $training->id)->firstOrFail();
    $enrollment->update(['status' => 'in_progress']);

    $this->seed(DemoPelatihanSeeder::class);

    expect(Training::where('kode_training', 'like', 'DEMO-POJOK-L%')->count())->toBe(3);
    expect(TrainingEnrollment::count())->toBe(3);
    expect(QuizQuestion::count())->toBe(18);
    expect($material->fresh())->not->toBeNull();
    expect($enrollment->fresh()->status)->toBe('in_progress');
});

test('seeder menolak penggunaan identitas demo yang sudah dipakai data lain', function () {
    MManpower::create(['mp_id' => 'DEMO-POJOK-001', 'nik' => 'NIK-ASLI', 'nama' => 'Data lain', 'departemen' => 'IT', 'bagian' => 'STAFF', 'status' => true]);

    expect(fn () => $this->seed(DemoPelatihanSeeder::class))->toThrow(RuntimeException::class, 'Identitas demo sudah digunakan');
    expect(Training::count())->toBe(0);
    expect(MManpower::where('nik', 'NIK-ASLI')->value('nama'))->toBe('Data lain');
});

test('seeder tidak dapat dijalankan pada lingkungan produksi', function () {
    $originalEnvironment = $this->app['env'];
    $this->app['env'] = 'production';

    try {
        expect(fn () => (new DemoPelatihanSeeder)->run())->toThrow(RuntimeException::class, 'Data demo hanya boleh dibuat pada lingkungan nonproduksi.');
        expect(MManpower::count())->toBe(0);
    } finally {
        $this->app['env'] = $originalEnvironment;
    }
});
