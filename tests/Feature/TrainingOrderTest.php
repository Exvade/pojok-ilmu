<?php

use App\Models\MManpower;
use App\Models\MSkill;
use App\Models\Training;
use App\Models\TrainingEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('dashboard operator mengurutkan training berdasarkan kode', function () {
    $manpower = MManpower::create([
        'mp_id' => 'URUT-OP',
        'nik' => 'URUT-OP',
        'nama' => 'Operator Urutan',
        'departemen' => 'IT',
        'bagian' => 'STAFF',
        'status' => true,
    ]);
    $user = User::create(['user_id' => $manpower->mp_id, 'name' => $manpower->nama, 'role' => 0]);
    $skill = MSkill::create(['kode_skill' => 'URUT-SKILL', 'nama_skill' => 'Urutan training', 'kategori' => 'Pengujian']);

    $laterCode = Training::create(['kode_training' => 'Z-TRAINING', 'skill_id' => $skill->id, 'level' => 1, 'judul' => 'Training kode Z']);
    $earlierCode = Training::create(['kode_training' => 'A-TRAINING', 'skill_id' => $skill->id, 'level' => 1, 'judul' => 'Training kode A']);
    TrainingEnrollment::create(['training_id' => $laterCode->id, 'manpower_id' => $manpower->id]);
    TrainingEnrollment::create(['training_id' => $earlierCode->id, 'manpower_id' => $manpower->id]);

    $content = $this->actingAs($user)->get(route('operator.dashboard'))->assertOk()->getContent();

    expect(strpos($content, 'A-TRAINING'))->toBeLessThan(strpos($content, 'Z-TRAINING'));
});
