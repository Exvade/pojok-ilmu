<?php

use App\Models\MSkill;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Training;
use App\Models\TrainingMaterial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('curriculum manager can audit training readiness and level coverage', function () {
    $manager = User::create([
        'user_id' => 'CURRICULUM-AUDIT',
        'name' => 'Pengelola Kurikulum',
        'role' => 1,
        'can_manage_curriculum' => true,
    ]);
    $skill = MSkill::create(['kode_skill' => 'AUDIT-SKILL', 'nama_skill' => 'Audit training', 'kategori' => 'PRODUKSI']);
    $completeTraining = Training::create([
        'kode_training' => 'AUDIT-L1',
        'skill_id' => $skill->id,
        'departemen' => 'PRODUKSI',
        'departemens' => ['PRODUKSI'],
        'level' => 1,
        'judul' => 'Training lengkap',
        'passing_grade' => 80,
        'max_retries' => 3,
        'requires_ojt' => true,
    ]);
    TrainingMaterial::create(['training_id' => $completeTraining->id, 'urutan' => 1, 'judul' => 'Materi lengkap', 'tipe_media' => 'text', 'konten_teks' => 'Isi materi']);
    $postTest = Quiz::create(['training_id' => $completeTraining->id, 'tipe' => 'post_test', 'judul' => 'Post-test lengkap', 'passing_grade' => 80, 'max_retries' => 3]);
    QuizQuestion::create(['quiz_id' => $postTest->id, 'pertanyaan' => 'Apa jawaban yang benar?', 'opsi_a' => 'A', 'opsi_b' => 'B', 'opsi_c' => 'C', 'opsi_d' => 'D', 'kunci_jawaban' => 'a']);
    Training::create([
        'kode_training' => 'AUDIT-ENG-L2',
        'skill_id' => $skill->id,
        'departemen' => 'ENGINEERING',
        'departemens' => ['ENGINEERING'],
        'level' => 2,
        'judul' => 'Training belum lengkap',
        'passing_grade' => 80,
        'max_retries' => 3,
        'requires_ojt' => true,
    ]);

    $this->actingAs($manager)
        ->get(route('admin.training-audit.index'))
        ->assertOk()
        ->assertSee('Audit master training')
        ->assertSee('Training lengkap')
        ->assertSee('Training belum lengkap')
        ->assertSee('Materi belum ada')
        ->assertSee('Post-test belum dibuat')
        ->assertSee('Level 1 belum tersedia');
});
