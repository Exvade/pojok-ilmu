<?php

use App\Models\MManpower;
use App\Models\MSkill;
use App\Models\QuizQuestion;
use App\Models\Training;
use App\Models\TrainingEnrollment;
use App\Models\TrainingMaterial;
use App\Models\User;
use Database\Seeders\DemoPelatihanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->previousDemoNik = getenv('DEMO_NIK');
    putenv('DEMO_NIK=NIK-HARIS-TEST');
    MManpower::create(['mp_id' => 'MP-HARIS-TEST', 'nik' => 'NIK-HARIS-TEST', 'nama' => 'Karyawan HaRis', 'departemen' => 'IT', 'bagian' => 'STAFF', 'status' => true, 'haris_synced_at' => now()]);
});

afterEach(function () {
    $this->previousDemoNik === false ? putenv('DEMO_NIK') : putenv('DEMO_NIK=' . $this->previousDemoNik);
});

test('seeder menugaskan karyawan HaRis dan menyediakan training bertingkat tanpa materi', function () {
    $this->seed(DemoPelatihanSeeder::class);

    $manpower = MManpower::where('nik', 'NIK-HARIS-TEST')->firstOrFail();
    $trainings = Training::whereIn('kode_training', ['TRN-PRODUK-L1', 'TRN-PRODUK-L2', 'TRN-PRODUK-L3'])->with(['skill', 'preTest.questions', 'postTest.questions', 'materials'])->orderBy('level')->get();

    expect($trainings)->toHaveCount(3);
    expect($trainings->pluck('level')->all())->toBe([1, 2, 3]);
    expect(MManpower::count())->toBe(1);
    expect($manpower->departemen)->toBe('IT');
    expect(TrainingEnrollment::where('manpower_id', $manpower->id)->count())->toBe(3);
    foreach ($trainings as $training) {
        expect(strtolower(implode(' ', [$training->kode_training, $training->judul, $training->deskripsi, $training->skill->nama_skill, $training->skill->kategori])))->not->toContain('demo');
        expect($training->materials)->toBeEmpty();
        expect($training->preTest->questions)->toHaveCount(3);
        expect($training->postTest->questions)->toHaveCount(3);
    }

    $this->post(route('login.post'), ['user_id' => 'NIK-HARIS-TEST'])->assertRedirect('/operator/dashboard');
    $this->get('/operator/dashboard')->assertOk()->assertSee('Karyawan HaRis')->assertSee('Pengenalan Produk');
});

test('seeder dapat dijalankan ulang tanpa menggandakan data atau menghapus materi dan progres', function () {
    $this->seed(DemoPelatihanSeeder::class);
    $training = Training::where('kode_training', 'TRN-PRODUK-L1')->firstOrFail();
    $material = TrainingMaterial::create(['training_id' => $training->id, 'urutan' => 1, 'judul' => 'Video demo', 'tipe_media' => 'video_youtube', 'media_url' => 'https://example.com/video']);
    $enrollment = TrainingEnrollment::where('training_id', $training->id)->firstOrFail();
    $enrollment->update(['status' => 'in_progress']);

    $this->seed(DemoPelatihanSeeder::class);

    expect(Training::where('kode_training', 'like', 'TRN-PRODUK-L%')->count())->toBe(3);
    expect(TrainingEnrollment::count())->toBe(3);
    expect(QuizQuestion::count())->toBe(18);
    expect($material->fresh())->not->toBeNull();
    expect($enrollment->fresh()->status)->toBe('in_progress');
});

test('seeder tidak menggandakan data lama yang mungkin memiliki progres', function () {
    $skill = MSkill::create(['kode_skill' => 'DEMO-HARIS-L1', 'nama_skill' => 'DEMO - Pengenalan Produk', 'kategori' => 'DEMO PELATIHAN']);
    $training = Training::create(['kode_training' => 'DEMO-HARIS-L1', 'skill_id' => $skill->id, 'level' => 1, 'judul' => 'DEMO - Pengenalan Produk', 'passing_grade' => 80]);

    expect(fn () => $this->seed(DemoPelatihanSeeder::class))->toThrow(RuntimeException::class, 'Data seeder versi sebelumnya sudah ada');
    expect(Training::count())->toBe(1);
    expect($training->fresh()->judul)->toBe('DEMO - Pengenalan Produk');
});

test('seeder menolak NIK yang tidak ditemukan tanpa membuat data demo', function () {
    putenv('DEMO_NIK=NIK-TIDAK-ADA');
    expect(fn () => $this->seed(DemoPelatihanSeeder::class))->toThrow(RuntimeException::class, 'NIK tidak ditemukan');
    expect(Training::count())->toBe(0);
    expect(MManpower::where('nik', 'NIK-HARIS-TEST')->value('nama'))->toBe('Karyawan HaRis');
});

test('seeder menolak akun pengelola', function () {
    User::create(['user_id' => 'MP-HARIS-TEST', 'name' => 'Pengelola', 'role' => 1, 'is_superadmin' => true]);

    expect(fn () => $this->seed(DemoPelatihanSeeder::class))->toThrow(RuntimeException::class, 'Karyawan memiliki akun pengelola');
    expect(Training::count())->toBe(0);
});

test('seeder meminta NIK secara eksplisit', function () {
    putenv('DEMO_NIK');
    expect(fn () => $this->seed(DemoPelatihanSeeder::class))->toThrow(RuntimeException::class, 'Tentukan NIK karyawan HaRis');
    expect(Training::count())->toBe(0);
});

test('seeder tidak dapat dijalankan pada lingkungan produksi', function () {
    $originalEnvironment = $this->app['env'];
    $this->app['env'] = 'production';

    try {
        expect(fn () => (new DemoPelatihanSeeder)->run())->toThrow(RuntimeException::class, 'Data demo hanya boleh dibuat pada lingkungan nonproduksi.');
        expect(Training::count())->toBe(0);
    } finally {
        $this->app['env'] = $originalEnvironment;
    }
});
