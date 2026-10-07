<?php

use App\Models\MManpower;
use App\Models\MSkill;
use App\Models\MSkillTargetLevel;
use App\Models\OperatorSkillMatrix;
use App\Models\Training;
use App\Models\TrainingEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function tnaFixture(): array
{
    $employee = MManpower::create([
        'mp_id' => 'TNA-OP', 'nik' => 'TNA-OP', 'nama' => 'Operator TNA',
        'departemen' => 'PRODUKSI', 'bagian' => 'OPERATOR', 'status' => true, 'haris_synced_at' => now(),
    ]);
    $skill = MSkill::create(['kode_skill' => 'TNA-SK', 'nama_skill' => 'Kualifikasi Mesin', 'kategori' => 'Produksi']);
    MSkillTargetLevel::create([
        'departemen' => 'PRODUKSI', 'bagian' => 'OPERATOR', 'skill_id' => $skill->id, 'target_level' => 3,
    ]);
    $levelOne = Training::create(['kode_training' => 'TNA-L1', 'skill_id' => $skill->id, 'level' => 1, 'judul' => 'Level 1']);
    $levelThree = Training::create(['kode_training' => 'TNA-L3', 'skill_id' => $skill->id, 'level' => 3, 'judul' => 'Level 3']);

    return compact('employee', 'skill', 'levelOne', 'levelThree');
}

test('batch tna creates a matrix and the next required training without employee login', function () {
    ['employee' => $employee, 'skill' => $skill, 'levelOne' => $levelOne] = tnaFixture();

    $this->artisan('pojok-ilmu:generate-tna')->assertSuccessful();

    $this->assertDatabaseHas('operator_skill_matrices', [
        'manpower_id' => $employee->id, 'skill_id' => $skill->id, 'current_level' => 0, 'target_level' => 3,
    ]);
    $this->assertDatabaseHas('training_enrollments', [
        'manpower_id' => $employee->id, 'training_id' => $levelOne->id, 'status' => 'enrolled',
    ]);
});

test('validated initial level removes untouched lower training and assigns the next level', function () {
    ['employee' => $employee, 'skill' => $skill, 'levelOne' => $levelOne, 'levelThree' => $levelThree] = tnaFixture();
    $matrix = OperatorSkillMatrix::create([
        'manpower_id' => $employee->id, 'skill_id' => $skill->id,
        'current_level' => 0, 'target_level' => 3, 'status_gap' => 'Gap -3 Level',
    ]);
    $oldEnrollment = TrainingEnrollment::create([
        'manpower_id' => $employee->id, 'training_id' => $levelOne->id, 'status' => 'enrolled',
    ]);
    $admin = User::create([
        'user_id' => 'TNA-ADMIN', 'name' => 'Admin TNA', 'role' => 1,
        'is_superadmin' => true, 'can_manage_competency' => true,
    ]);

    $this->actingAs($admin)->post('/admin/matrix/' . $matrix->id . '/initial-level', ['current_level' => 2])
        ->assertSessionHas('success');

    $this->assertDatabaseHas('operator_skill_matrices', ['id' => $matrix->id, 'current_level' => 2, 'status_gap' => 'Gap -1 Level']);
    $this->assertSoftDeleted('training_enrollments', ['id' => $oldEnrollment->id]);
    $this->assertDatabaseHas('training_enrollments', [
        'manpower_id' => $employee->id, 'training_id' => $levelThree->id, 'status' => 'enrolled',
    ]);
});
