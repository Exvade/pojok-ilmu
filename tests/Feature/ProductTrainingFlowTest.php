<?php

use App\Models\MManpower;
use App\Models\MSkill;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Training;
use App\Models\TrainingEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

test('post-test yang lulus menghasilkan balok satu dan menunggu ojt', function () {
    $manpower = MManpower::create([
        'mp_id' => 'PRD-OP-01',
        'nik' => 'PRD-OP-01',
        'nama' => 'Operator Produk',
        'departemen' => 'PRODUKSI',
        'bagian' => 'OPERATOR',
        'status' => true,
    ]);
    $user = User::create(['user_id' => 'PRD-OP-01', 'name' => 'Operator Produk', 'role' => 0]);
    $skill = MSkill::create(['kode_skill' => 'PRD-BOX', 'nama_skill' => 'Box Battery', 'kategori' => 'PRODUKSI']);
    $training = Training::create([
        'kode_training' => 'PRD-BOX-01',
        'skill_id' => $skill->id,
        'departemen' => 'PRODUKSI',
        'level' => 1,
        'judul' => 'Box Battery',
        'passing_grade' => 80,
        'max_retries' => 3,
        'requires_ojt' => true,
    ]);
    $postTest = Quiz::create(['training_id' => $training->id, 'tipe' => 'post_test', 'judul' => 'Post-test Box Battery', 'passing_grade' => 80]);
    $question = QuizQuestion::create(['quiz_id' => $postTest->id, 'pertanyaan' => 'Pilih jawaban benar', 'opsi_a' => 'Benar', 'opsi_b' => 'Salah', 'opsi_c' => 'Tidak tahu', 'opsi_d' => 'Lewati', 'kunci_jawaban' => 'a']);
    $enrollment = TrainingEnrollment::create(['training_id' => $training->id, 'manpower_id' => $manpower->id, 'status' => 'in_progress', 'material_completed_at' => now()]);

    $this->actingAs($user)->get('/operator/quizzes/' . $postTest->id)->assertOk();
    $this->post('/operator/quizzes/' . $postTest->id . '/submit', ['answers' => [$question->id => 'a']])->assertOk();

    $this->assertDatabaseHas('training_enrollments', [
        'id' => $enrollment->id,
        'status' => 'in_progress',
        'ojt_status' => 'pending',
    ]);
    expect($enrollment->fresh()->post_test_completed_at)->not->toBeNull();
});

test('pengelola dapat menugaskan produk ke manpower dari departemen yang sama', function () {
    $admin = User::create([
        'user_id' => 'PRD-ADMIN',
        'name' => 'Pengelola Produk',
        'role' => 1,
        'can_manage_competency' => true,
    ]);
    $manpower = MManpower::create([
        'mp_id' => 'PRD-OP-02',
        'nik' => 'PRD-OP-02',
        'nama' => 'Operator Produk Dua',
        'departemen' => 'PRODUKSI',
        'bagian' => 'OPERATOR',
        'status' => true,
        'haris_synced_at' => now(),
    ]);
    $skill = MSkill::create(['kode_skill' => 'PRD-ASSIGN', 'nama_skill' => 'Produk Uji', 'kategori' => 'PRODUKSI']);
    $training = Training::create([
        'kode_training' => 'PRD-ASSIGN-01',
        'skill_id' => $skill->id,
        'departemen' => 'PRODUKSI',
        'level' => 1,
        'judul' => 'Produk Uji',
    ]);

    $this->actingAs($admin)->post('/admin/product-assignments', [
        'training_id' => $training->id,
        'manpower_ids' => [$manpower->id],
    ])->assertSessionHas('success');

    $this->assertDatabaseHas('training_enrollments', [
        'training_id' => $training->id,
        'manpower_id' => $manpower->id,
        'status' => 'enrolled',
    ]);
});

test('pengelola dapat mengubah data training yang sudah dibuat', function () {
    $admin = User::create([
        'user_id' => 'TRN-EDITOR',
        'name' => 'Pengelola Training',
        'role' => 1,
        'can_manage_curriculum' => true,
    ]);
    $skill = MSkill::create(['kode_skill' => 'TRN-EDIT', 'nama_skill' => 'Box Battery', 'kategori' => 'PRODUKSI']);
    $training = Training::create([
        'kode_training' => 'TRN-EDIT-01',
        'skill_id' => $skill->id,
        'departemen' => 'PRODUKSI',
        'level' => 1,
        'judul' => 'BOX BATTERY',
        'passing_grade' => 80,
        'max_retries' => 3,
    ]);

    $this->actingAs($admin)->put('/admin/trainings/' . $training->id, [
        'kode_training' => 'TRN-EDIT-01',
        'departemens' => ['PRODUKSI'],
        'level' => 2,
        'judul' => 'BOX BATTERY REVISI',
        'deskripsi' => 'Training revisi untuk BOX BATTERY.',
        'passing_grade' => 85,
        'max_retries' => 2,
    ])->assertSessionHas('success');

    $this->assertDatabaseHas('trainings', [
        'id' => $training->id,
        'level' => 2,
        'judul' => 'BOX BATTERY REVISI',
        'passing_grade' => 85,
        'max_retries' => 2,
    ]);
});

test('pengelola dapat mengimpor penugasan training tanpa membuat data ganda', function () {
    $admin = User::create([
        'user_id' => 'TRN-IMPORTER',
        'name' => 'Pengelola Penugasan',
        'role' => 1,
        'can_manage_competency' => true,
    ]);
    $manpower = MManpower::create([
        'mp_id' => 'TRN-MP-01',
        'nik' => 'NIK-TRN-01',
        'nama' => 'Operator Impor',
        'departemen' => 'PRODUKSI',
        'bagian' => 'OPERATOR',
        'status' => true,
        'haris_synced_at' => now(),
    ]);
    $skill = MSkill::create(['kode_skill' => 'TRN-IMPORT', 'nama_skill' => 'Box Battery', 'kategori' => 'PRODUKSI']);
    $training = Training::create([
        'kode_training' => 'TRN-IMPORT-01',
        'skill_id' => $skill->id,
        'departemen' => 'PRODUKSI',
        'level' => 1,
        'judul' => 'BOX BATTERY',
    ]);
    $file = UploadedFile::fake()->createWithContent('penugasan.csv', "kode_training,nik\nTRN-IMPORT-01,NIK-TRN-01\n");

    $preview = $this->actingAs($admin)
        ->post('/admin/imports/assignments', ['file' => $file])
        ->assertOk()
        ->assertViewHas('preview');
    $this->post(route('admin.assignments.import.confirm'), [
        'preview_token' => $preview->viewData('preview')['token'],
    ])->assertSessionHas('success');

    $preview = $this->post('/admin/imports/assignments', ['file' => $file])
        ->assertOk()
        ->assertViewHas('preview');
    $this->post(route('admin.assignments.import.confirm'), [
        'preview_token' => $preview->viewData('preview')['token'],
    ])->assertSessionHas('success');

    $this->assertDatabaseHas('training_enrollments', [
        'training_id' => $training->id,
        'manpower_id' => $manpower->id,
        'status' => 'enrolled',
    ]);
    $this->assertDatabaseCount('training_enrollments', 1);
});

test('pengelola dapat membatalkan penugasan yang belum dimulai tanpa menghapus riwayat training', function () {
    $admin = User::create([
        'user_id' => 'TRN-ASSIGNMENT-MANAGER',
        'name' => 'Pengelola Penugasan',
        'role' => 1,
        'can_manage_competency' => true,
    ]);
    $unstartedManpower = MManpower::create([
        'mp_id' => 'TRN-UNSTARTED', 'nik' => 'TRN-UNSTARTED', 'nama' => 'Belum Mulai', 'departemen' => 'PRODUKSI', 'bagian' => 'OPERATOR', 'status' => true, 'haris_synced_at' => now(),
    ]);
    $inProgressManpower = MManpower::create([
        'mp_id' => 'TRN-IN-PROGRESS', 'nik' => 'TRN-IN-PROGRESS', 'nama' => 'Sudah Mulai', 'departemen' => 'PRODUKSI', 'bagian' => 'OPERATOR', 'status' => true, 'haris_synced_at' => now(),
    ]);
    $skill = MSkill::create(['kode_skill' => 'TRN-CANCEL', 'nama_skill' => 'Box Battery', 'kategori' => 'PRODUKSI']);
    $training = Training::create(['kode_training' => 'TRN-CANCEL-01', 'skill_id' => $skill->id, 'departemen' => 'PRODUKSI', 'level' => 1, 'judul' => 'BOX BATTERY']);
    $unstartedEnrollment = TrainingEnrollment::create(['training_id' => $training->id, 'manpower_id' => $unstartedManpower->id, 'status' => 'enrolled']);
    $inProgressEnrollment = TrainingEnrollment::create(['training_id' => $training->id, 'manpower_id' => $inProgressManpower->id, 'status' => 'in_progress']);

    $this->actingAs($admin)->post('/admin/product-assignments', [
        'training_id' => $training->id,
        'manpower_ids' => [],
    ])->assertSessionHas('success');

    $this->assertSoftDeleted('training_enrollments', ['id' => $unstartedEnrollment->id]);
    $this->assertDatabaseHas('training_enrollments', ['id' => $inProgressEnrollment->id, 'status' => 'in_progress', 'deleted_at' => null]);
});

test('pengelola dapat melihat profil dan riwayat training per manpower', function () {
    $admin = User::create([
        'user_id' => 'TRN-PROFILE-MANAGER',
        'name' => 'Pengelola Kompetensi',
        'role' => 1,
        'can_manage_competency' => true,
    ]);
    $manpower = MManpower::create([
        'mp_id' => 'TRN-PROFILE-01', 'nik' => 'TRN-PROFILE-01', 'nama' => 'Operator Profil', 'departemen' => 'PRODUKSI', 'bagian' => 'OPERATOR', 'status' => true, 'haris_synced_at' => now(),
    ]);
    $skill = MSkill::create(['kode_skill' => 'TRN-PROFILE', 'nama_skill' => 'Box Battery', 'kategori' => 'PRODUKSI']);
    $training = Training::create(['kode_training' => 'TRN-PROFILE-01', 'skill_id' => $skill->id, 'departemen' => 'PRODUKSI', 'level' => 1, 'judul' => 'BOX BATTERY']);
    TrainingEnrollment::create([
        'training_id' => $training->id,
        'manpower_id' => $manpower->id,
        'status' => 'passed',
        'ojt_status' => 'approved',
        'post_test_completed_at' => now(),
    ]);

    $this->actingAs($admin)->get('/admin/manpower/' . $manpower->id . '/training-profile')
        ->assertOk()
        ->assertSee('Operator Profil')
        ->assertSee('BOX BATTERY')
        ->assertSee('Balok 2')
        ->assertSee('Disetujui');
});
