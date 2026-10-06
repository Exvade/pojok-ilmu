<?php

use App\Models\MManpower;
use App\Models\MSkill;
use App\Models\MSkillTargetLevel;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Training;
use App\Models\TrainingEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('operator accessing dashboard auto generates TNA and skill matrix based on target levels', function () {
    $operator = MManpower::create([
        'mp_id' => 'OP100',
        'nik' => '100100',
        'nama' => 'Joko Operator',
        'departemen' => 'PRODUKSI PLASTIK',
        'bagian' => 'ASSEMBLY',
        'status' => true,
    ]);

    $user = User::create([
        'user_id' => 'OP100',
        'name' => 'Joko Operator',
        'role' => 0,
    ]);

    $skill = MSkill::create([
        'kode_skill' => 'SKILL-TEST-01',
        'nama_skill' => 'Kualifikasi Part Assembly',
        'kategori' => 'Product Knowledge',
    ]);

    MSkillTargetLevel::create([
        'departemen' => 'PRODUKSI PLASTIK',
        'bagian' => 'ASSEMBLY',
        'skill_id' => $skill->id,
        'target_level' => 2,
    ]);

    $training = Training::create([
        'kode_training' => 'TRN-TEST-L1',
        'skill_id' => $skill->id,
        'level' => 1,
        'judul' => 'Training Level 1 Assembly',
        'passing_grade' => 80,
    ]);

    $response = $this->actingAs($user)->get('/operator/dashboard');
    $response->assertStatus(200);

    // Verify Skill Matrix generated
    $this->assertDatabaseHas('operator_skill_matrices', [
        'manpower_id' => $operator->id,
        'skill_id' => $skill->id,
        'current_level' => 0,
        'target_level' => 2,
        'status_gap' => 'Gap -2 Level',
    ]);

    // Verify Auto-TNA enrollment created
    $this->assertDatabaseHas('training_enrollments', [
        'manpower_id' => $operator->id,
        'training_id' => $training->id,
        'status' => 'enrolled',
    ]);
});

test('operator passing quiz automatically upgrades skill matrix current level and updates gap status', function () {
    $operator = MManpower::create([
        'mp_id' => 'OP101',
        'nik' => '100101',
        'nama' => 'Siti Trainee',
        'departemen' => 'PRODUKSI PLASTIK',
        'bagian' => 'ASSEMBLY',
        'status' => true,
    ]);

    $user = User::create([
        'user_id' => 'OP101',
        'name' => 'Siti Trainee',
        'role' => 0,
    ]);

    $skill = MSkill::create([
        'kode_skill' => 'SKILL-TEST-02',
        'nama_skill' => 'Pengenalan Kaizen',
        'kategori' => 'CI',
    ]);

    $training = Training::create([
        'kode_training' => 'TRN-KZN-1',
        'skill_id' => $skill->id,
        'level' => 1,
        'judul' => 'Modul Kaizen L1',
        'passing_grade' => 80,
    ]);

    $quiz = Quiz::create([
        'training_id' => $training->id,
        'judul' => 'Assessment Kaizen L1',
        'durasi_menit' => 10,
        'passing_grade' => 80,
    ]);

    $q1 = QuizQuestion::create([
        'quiz_id' => $quiz->id,
        'pertanyaan' => 'Apa itu Kaizen?',
        'opsi_a' => 'Perbaikan berkesinambungan',
        'opsi_b' => 'Pemberhentian produksi',
        'opsi_c' => 'Jam lembur',
        'opsi_d' => 'Pembelian mesin',
        'kunci_jawaban' => 'a',
    ]);

    $enrollment = TrainingEnrollment::create([
        'manpower_id' => $operator->id,
        'training_id' => $training->id,
        'status' => 'in_progress',
    ]);

    // Submit correct answer (100% score)
    $response = $this->actingAs($user)->post("/operator/quizzes/{$quiz->id}/submit", [
        'answers' => [
            $q1->id => 'a',
        ],
    ]);

    $response->assertStatus(200);

    // Verify enrollment status is passed
    $this->assertDatabaseHas('training_enrollments', [
        'id' => $enrollment->id,
        'status' => 'passed',
    ]);

    // Verify Skill Matrix current level upgraded to 1
    $this->assertDatabaseHas('operator_skill_matrices', [
        'manpower_id' => $operator->id,
        'skill_id' => $skill->id,
        'current_level' => 1,
    ]);
});
