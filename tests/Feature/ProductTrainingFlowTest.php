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
        'departemen' => 'PRODUKSI',
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
        'nik' => 'TRN-MP-01',
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
    $file = UploadedFile::fake()->createWithContent('penugasan.csv', "kode_training,npk\nTRN-IMPORT-01,TRN-MP-01\n");

    $this->actingAs($admin)->post('/admin/imports/assignments', ['file' => $file])->assertSessionHas('success');
    $this->actingAs($admin)->post('/admin/imports/assignments', ['file' => $file])->assertSessionHas('success');

    $this->assertDatabaseHas('training_enrollments', [
        'training_id' => $training->id,
        'manpower_id' => $manpower->id,
        'status' => 'enrolled',
    ]);
    $this->assertDatabaseCount('training_enrollments', 1);
});
