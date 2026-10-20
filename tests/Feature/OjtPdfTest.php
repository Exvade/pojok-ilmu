<?php

use App\Models\MManpower;
use App\Models\MSkill;
use App\Models\OjtAssessment;
use App\Models\Training;
use App\Models\TrainingEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('operator can download their completed ojt result as pdf', function () {
    $manpower = MManpower::create(['mp_id' => 'PDF-OP-01', 'nik' => 'PDF-OP-01', 'nama' => 'Operator PDF', 'departemen' => 'PRODUKSI', 'bagian' => 'OPERATOR', 'status' => true]);
    $operator = User::create(['user_id' => 'PDF-OP-01', 'name' => 'Operator PDF', 'role' => 0]);
    MManpower::create(['mp_id' => 'PDF-LEADER', 'nik' => 'PDF-LEADER', 'nama' => 'Leader PDF', 'departemen' => 'PRODUKSI', 'bagian' => 'LEADER', 'status' => true]);
    $assessor = User::create(['user_id' => 'PDF-LEADER', 'name' => 'Leader PDF', 'role' => 1, 'can_assess_ojt' => true]);
    $skill = MSkill::create(['kode_skill' => 'PDF-SKILL', 'nama_skill' => 'Keterampilan PDF', 'kategori' => 'PRODUKSI']);
    $training = Training::create(['kode_training' => 'PDF-OJT', 'skill_id' => $skill->id, 'departemen' => 'PRODUKSI', 'level' => 1, 'judul' => 'BOX BATTERY']);
    $enrollment = TrainingEnrollment::create(['training_id' => $training->id, 'manpower_id' => $manpower->id, 'status' => 'passed', 'ojt_status' => 'approved']);
    OjtAssessment::create([
        'enrollment_id' => $enrollment->id, 'assessor_user_id' => $assessor->id, 'training_date' => now(), 'categories' => ['pengetahuan_produk'],
        'theory_answers' => ['part_name' => ['answer' => 'Box battery', 'score' => 8]], 'theory_score' => 80,
        'practice_results' => ['improvement' => ['actual' => 'Sesuai standar', 'score' => 20]], 'practice_score' => 80,
        'is_passed' => true, 'conclusion' => 'Mampu bekerja dengan bimbingan.', 'recommendation' => 'Pertahankan standar kerja.',
    ]);

    $this->actingAs($operator)
        ->get(route('operator.ojt-result.pdf', $enrollment))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf')
        ->assertHeader('content-disposition', 'attachment; filename=hasil-ojt-pdf-ojt-pdf-op-01.pdf');

    $this->actingAs($assessor)
        ->get(route('admin.ojt.pdf', $enrollment))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});
