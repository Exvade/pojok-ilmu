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

function assessmentFixture(): array
{
    $employee = MManpower::create([
        'mp_id' => 'ASSESS-OP', 'nik' => 'ASSESS-OP', 'nama' => 'Operator Assessment',
        'departemen' => 'PRODUKSI', 'bagian' => 'OPERATOR', 'status' => true,
    ]);
    $user = User::create(['user_id' => 'ASSESS-OP', 'name' => 'Operator Assessment', 'role' => 0]);
    $skill = MSkill::create(['kode_skill' => 'ASSESS-SK', 'nama_skill' => 'Keselamatan', 'kategori' => 'Safety']);
    $training = Training::create([
        'kode_training' => 'ASSESS-TR', 'skill_id' => $skill->id, 'level' => 1,
        'judul' => 'Dasar Keselamatan', 'passing_grade' => 80, 'max_retries' => 3,
    ]);
    $preTest = Quiz::create([
        'training_id' => $training->id, 'tipe' => 'pre_test', 'judul' => 'Pre-test Keselamatan',
        'durasi_menit' => 15, 'passing_grade' => 80, 'max_retries' => 3, 'cooldown_hours' => 24,
    ]);
    $postTest = Quiz::create([
        'training_id' => $training->id, 'tipe' => 'post_test', 'judul' => 'Post-test Keselamatan',
        'durasi_menit' => 15, 'passing_grade' => 80, 'max_retries' => 3, 'cooldown_hours' => 24,
    ]);
    foreach ([$preTest, $postTest] as $quiz) {
        QuizQuestion::create([
            'quiz_id' => $quiz->id, 'pertanyaan' => 'Pilih jawaban benar',
            'opsi_a' => 'Benar', 'opsi_b' => 'Salah', 'opsi_c' => 'Tidak tahu', 'opsi_d' => 'Lewati',
            'kunci_jawaban' => 'a',
        ]);
    }
    $enrollment = TrainingEnrollment::create([
        'training_id' => $training->id, 'manpower_id' => $employee->id, 'status' => 'enrolled',
    ]);

    return compact('user', 'training', 'preTest', 'postTest', 'enrollment');
}

test('pre-test must be completed before material and does not consume post-test retry', function () {
    ['user' => $user, 'training' => $training, 'preTest' => $preTest, 'enrollment' => $enrollment] = assessmentFixture();

    $this->actingAs($user)->get('/operator/materials/' . $training->id)
        ->assertRedirect('/operator/quizzes/' . $preTest->id);

    $this->get('/operator/quizzes/' . $preTest->id)->assertOk();
    $this->post('/operator/quizzes/' . $preTest->id . '/submit', [
        'answers' => [$preTest->questions()->first()->id => 'b'],
    ])->assertOk()->assertSee('Pre-test selesai');

    $enrollment->refresh();
    expect($enrollment->pre_test_completed_at)->not->toBeNull()
        ->and($enrollment->current_retry_count)->toBe(0)
        ->and($enrollment->status)->toBe('in_progress');
});

test('failed post-test requires remedial completion and applies configured cooldown', function () {
    ['user' => $user, 'training' => $training, 'postTest' => $postTest, 'enrollment' => $enrollment] = assessmentFixture();
    $enrollment->update(['pre_test_completed_at' => now(), 'material_completed_at' => now(), 'status' => 'in_progress']);

    $this->actingAs($user)->get('/operator/quizzes/' . $postTest->id)->assertOk();
    $this->post('/operator/quizzes/' . $postTest->id . '/submit', [
        'answers' => [$postTest->questions()->first()->id => 'b'],
    ])->assertOk();

    $enrollment->refresh();
    expect($enrollment->current_retry_count)->toBe(1)
        ->and($enrollment->material_completed_at)->toBeNull()
        ->and($enrollment->next_attempt_at)->not->toBeNull();

    $this->get('/operator/quizzes/' . $postTest->id)->assertRedirect('/operator/materials/' . $training->id);
    $this->get('/operator/materials/' . $training->id)->assertOk();
    $this->post('/operator/materials/' . $training->id . '/complete')->assertRedirect('/operator/dashboard');
});
