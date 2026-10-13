<?php

use App\Models\MManpower;
use App\Models\MSkill;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Training;
use App\Models\TrainingEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
