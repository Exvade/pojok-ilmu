<?php

use App\Models\MManpower;
use App\Models\MSkill;
use App\Models\QuizQuestion;
use App\Models\Training;
use App\Models\TrainingEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

function curriculumAdmin(): User
{
    return User::create([
        'user_id' => 'IMPORT-ADMIN',
        'name' => 'Admin Impor',
        'role' => 1,
        'is_superadmin' => true,
        'can_manage_curriculum' => true,
    ]);
}

function importTraining(string $code, string $title): Training
{
    $skill = MSkill::create(['kode_skill' => $code, 'nama_skill' => $title, 'kategori' => 'IT']);

    return Training::create(['kode_training' => $code, 'skill_id' => $skill->id, 'departemen' => 'IT', 'level' => 1, 'judul' => $title]);
}

test('competency spreadsheet import creates and updates skills by code', function () {
    $admin = curriculumAdmin();
    $file = UploadedFile::fake()->createWithContent('kompetensi.csv', implode("\n", [
        'kode_skill,nama_skill,kategori,deskripsi',
        'IT-SE-GIT,Dasar Pemrograman dan Git,Software Engineering,Dasar pengelolaan source code',
    ]));

    $preview = $this->actingAs($admin)
        ->post(route('admin.imports.store', 'skills'), ['file' => $file])
        ->assertOk()
        ->assertViewHas('preview');

    $this->post(route('admin.imports.confirm', 'skills'), [
        'preview_token' => $preview->viewData('preview')['token'],
    ])->assertRedirect(route('admin.imports.create', 'skills'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('m_skills', [
        'kode_skill' => 'IT-SE-GIT',
        'nama_skill' => 'Dasar Pemrograman dan Git',
    ]);

    $updateFile = UploadedFile::fake()->createWithContent('kompetensi.csv', implode("\n", [
        'kode_skill,nama_skill,kategori,deskripsi',
        'IT-SE-GIT,Git dan Kolaborasi Tim,Software Engineering,Deskripsi diperbarui',
    ]));

    $preview = $this->actingAs($admin)
        ->post(route('admin.imports.store', 'skills'), ['file' => $updateFile])
        ->assertOk()
        ->assertViewHas('preview');

    $this->post(route('admin.imports.confirm', 'skills'), [
        'preview_token' => $preview->viewData('preview')['token'],
    ])->assertSessionHas('success');

    expect(MSkill::where('kode_skill', 'IT-SE-GIT')->count())->toBe(1);
    $this->assertDatabaseHas('m_skills', ['kode_skill' => 'IT-SE-GIT', 'nama_skill' => 'Git dan Kolaborasi Tim']);
});

test('the competency import page is available to curriculum administrators', function () {
    $this->actingAs(curriculumAdmin())
        ->get(route('admin.imports.create', 'skills'))
        ->assertOk()
        ->assertSee('Impor master kompetensi')
        ->assertSee('Unduh template Excel');
});

test('question spreadsheet import rejects a training code that does not exist', function () {
    $file = UploadedFile::fake()->createWithContent('soal.csv', implode("\n", [
        'kode_training,tahap,judul_evaluasi,durasi_menit,nilai_lulus,batas_percobaan,masa_tunggu_jam,pertanyaan,opsi_a,opsi_b,opsi_c,opsi_d,kunci_jawaban',
        'TIDAK-ADA,post_test,Post-test,15,80,3,24,Apa fungsi Git?,A,B,C,D,a',
    ]));

    $this->actingAs(curriculumAdmin())
        ->post(route('admin.imports.store', 'questions'), ['file' => $file])
        ->assertOk()
        ->assertSee('Kode training tidak ditemukan.');

    expect(QuizQuestion::count())->toBe(0);
});

test('assignment preview explains department mismatches and imports valid rows only', function () {
    $admin = curriculumAdmin();
    $training = importTraining('IT-01', 'Dasar IT');
    $valid = MManpower::create(['mp_id' => 'MP-VALID', 'nik' => 'NIK-VALID', 'nama' => 'Karyawan IT', 'departemen' => 'IT', 'bagian' => 'STAFF', 'status' => true, 'haris_synced_at' => now()]);
    $other = MManpower::create(['mp_id' => 'MP-LAIN', 'nik' => 'NIK-LAIN', 'nama' => 'Karyawan Produksi', 'departemen' => 'PRODUKSI', 'bagian' => 'OPERATOR', 'status' => true, 'haris_synced_at' => now()]);
    $file = UploadedFile::fake()->createWithContent('penugasan.csv', "kode_training,nik\nIT-01,NIK-VALID\n,NIK-LAIN\n");

    $preview = $this->actingAs($admin)->post(route('admin.assignments.import.store'), ['file' => $file])
        ->assertOk()
        ->assertSee('Departemen Karyawan Produksi (PRODUKSI) tidak sesuai. Training Dasar IT ditujukan untuk: IT.')
        ->assertViewHas('preview', fn (array $preview): bool => $preview['valid_count'] === 1);

    $this->post(route('admin.assignments.import.confirm'), ['preview_token' => $preview->viewData('preview')['token']])->assertSessionHas('success');
    expect(TrainingEnrollment::where('training_id', $training->id)->where('manpower_id', $valid->id)->exists())->toBeTrue();
    expect(TrainingEnrollment::where('training_id', $training->id)->where('manpower_id', $other->id)->exists())->toBeFalse();
});

test('assignment preview distinguishes unknown inactive and unsynchronized employees', function () {
    importTraining('IT-02', 'Training IT');
    MManpower::create(['mp_id' => 'MP-NONAKTIF', 'nik' => 'NIK-NONAKTIF', 'nama' => 'Tidak Aktif', 'departemen' => 'IT', 'bagian' => 'STAFF', 'status' => false, 'haris_synced_at' => now()]);
    MManpower::create(['mp_id' => 'MP-BELUM', 'nik' => 'NIK-BELUM', 'nama' => 'Belum Sinkron', 'departemen' => 'IT', 'bagian' => 'STAFF', 'status' => true, 'haris_synced_at' => null]);
    $file = UploadedFile::fake()->createWithContent('penugasan.csv', "kode_training,nik\nIT-02,NIK-TIDAK-ADA\n,NIK-NONAKTIF\n,NIK-BELUM\n");

    $this->actingAs(curriculumAdmin())->post(route('admin.assignments.import.store'), ['file' => $file])
        ->assertOk()
        ->assertSee('NIK NIK-TIDAK-ADA tidak ditemukan.')
        ->assertSee('Karyawan Tidak Aktif tidak aktif dan tidak dapat ditugaskan.')
        ->assertSee('Data karyawan Belum Sinkron belum tersinkron dari HaRis.');
});

test('assignment preview accepts employees from any training department', function () {
    $training = importTraining('IT-03', 'Training lintas departemen');
    $training->update(['departemen' => null, 'departemens' => ['IT', 'PRODUKSI']]);
    MManpower::create(['mp_id' => 'MP-PRODUKSI', 'nik' => 'NIK-PRODUKSI', 'nama' => 'Karyawan Produksi', 'departemen' => 'PRODUKSI', 'bagian' => 'OPERATOR', 'status' => true, 'haris_synced_at' => now()]);
    $file = UploadedFile::fake()->createWithContent('penugasan.csv', "kode_training,nik\nIT-03,NIK-PRODUKSI\n");

    $this->actingAs(curriculumAdmin())->post(route('admin.assignments.import.store'), ['file' => $file])
        ->assertOk()
        ->assertViewHas('preview', fn (array $preview): bool => $preview['valid_count'] === 1);
});

test('import reports missing template columns', function () {
    $file = UploadedFile::fake()->createWithContent('kompetensi.csv', "kode_skill,nama_skill\nIT-01,Dasar IT\n");

    $this->actingAs(curriculumAdmin())->post(route('admin.imports.store', 'skills'), ['file' => $file])
        ->assertSessionHasErrors(['file' => 'Kolom berikut tidak ditemukan: kategori. Gunakan baris judul dari template dan jangan mengubah nama kolom.']);
});

test('training preview identifies unknown departments and invalid numeric values', function () {
    $file = UploadedFile::fake()->createWithContent('training.csv', "kode_training,departemen,level,judul,deskripsi,nilai_lulus,batas_percobaan\nTRN-01,DEPARTEMEN SALAH,2.5,Training,,abc,0\n");

    $this->actingAs(curriculumAdmin())->post(route('admin.imports.store', 'trainings'), ['file' => $file])
        ->assertOk()
        ->assertSee('Departemen tidak dikenali: DEPARTEMEN SALAH.')
        ->assertSee('Level harus berupa angka 1, 2, atau 3.')
        ->assertSee('Nilai lulus harus berupa angka bulat antara 50 dan 100.')
        ->assertSee('Batas percobaan harus berupa angka bulat positif.');
});

test('material and question previews explain invalid input', function () {
    importTraining('TRN-02', 'Training IT');
    $material = UploadedFile::fake()->createWithContent('materi.csv', "kode_training,urutan,judul_materi,tipe_media,konten_teks,url_media\nTRN-02,0,Materi,pdf,,\n");
    $question = UploadedFile::fake()->createWithContent('soal.csv', "kode_training,judul_evaluasi,durasi_menit,nilai_lulus,batas_percobaan,masa_tunggu_jam,pertanyaan,opsi_a,opsi_b,opsi_c,opsi_d,kunci_jawaban\nTRN-02,Evaluasi,0,100,3,24,Pertanyaan,A,B,C,D,e\n");
    $this->actingAs(curriculumAdmin());

    $this->post(route('admin.imports.store', 'materials'), ['file' => $material])->assertOk()
        ->assertSee('Urutan harus berupa angka bulat positif.')
        ->assertSee('Kolom URL media wajib diisi untuk video, gambar, atau PDF.');
    $this->post(route('admin.imports.store', 'questions'), ['file' => $question])->assertOk()
        ->assertSee('Durasi menit harus berupa angka bulat positif.')
        ->assertSee('Kunci jawaban harus berupa satu huruf: a, b, c, atau d.');
});
