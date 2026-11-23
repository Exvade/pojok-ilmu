<?php

use App\Models\MManpower;
use App\Models\MSkill;
use App\Models\Training;
use App\Models\TrainingEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function isolationManager(string $userId, array $plants, array $permissions = ['can_assess_ojt' => true]): User
{
    $user = User::create(['user_id' => $userId, 'name' => $userId, 'role' => 2, 'password' => Hash::make('rahasia-2026'), ...$permissions]);

    foreach ($plants as $plant) {
        $user->plants()->create(['plant' => $plant]);
    }

    return $user;
}

function isolationManpower(string $nik, string $plant): MManpower
{
    return MManpower::create([
        'mp_id' => $nik, 'nik' => $nik, 'nama' => 'Operator ' . $nik, 'departemen' => 'PRODUKSI', 'bagian' => 'OPERATOR',
        'status' => true, 'haris_synced_at' => now(), 'haris_plant_id' => $plant,
    ]);
}

function isolationTraining(string $code, int $plant): Training
{
    $skill = MSkill::firstOrCreate(['kode_skill' => 'ISO-SKILL'], ['nama_skill' => 'Skill isolasi', 'kategori' => 'Pengujian']);

    return Training::create([
        'kode_training' => $code, 'skill_id' => $skill->id, 'level' => 1, 'judul' => 'Training ' . $code,
        'departemen' => 'PRODUKSI', 'requires_ojt' => true, 'plant' => $plant,
    ]);
}

function pendingOjt(Training $training, MManpower $manpower): TrainingEnrollment
{
    return TrainingEnrollment::create([
        'training_id' => $training->id, 'manpower_id' => $manpower->id, 'status' => 'in_progress',
        'ojt_status' => 'pending', 'post_test_completed_at' => now(),
    ]);
}

test('mode satu plant tidak menyaring data dan tidak menampilkan pilihan plant', function () {
    config(['app.active_plants' => [3]]);
    $leader = isolationManager('LEADER-P1', [1]);
    MManpower::create(['mp_id' => 'LEADER-P1', 'nik' => 'LEADER-P1', 'nama' => 'Leader', 'departemen' => 'PRODUKSI', 'bagian' => 'LEADER', 'status' => true, 'haris_synced_at' => now(), 'haris_plant_id' => '1']);
    $training = isolationTraining('ISO-P3', 3);
    $enrollment = pendingOjt($training, isolationManpower('OP-P3', '3'));

    $this->actingAs($leader)->withSession(['active_plant' => 1])
        ->get('/admin/ojt')->assertOk()->assertSee('Training ISO-P3')->assertDontSee('data-plant-switcher', false);
    $this->get('/admin/ojt/' . $enrollment->id)->assertOk();
});

test('login pengelola menyimpan plant aktif sesuai akses plant', function () {
    $leader = isolationManager('LEADER-LOGIN', [3]);
    $superadmin = User::create(['user_id' => 'SUPER-LOGIN', 'name' => 'Super', 'role' => 1, 'is_superadmin' => true, 'password' => Hash::make('rahasia-2026')]);

    $this->post('/login', ['user_id' => $leader->user_id, 'password' => 'rahasia-2026'])->assertRedirect('/admin/dashboard');
    expect(session('active_plant'))->toBe(3);

    $this->post('/logout');
    $this->post('/login', ['user_id' => $superadmin->user_id, 'password' => 'rahasia-2026'])->assertRedirect('/admin/dashboard');
    expect(session('active_plant'))->toBe('all');
});

test('mode multi-plant membatasi OJT pada plant milik pengelola', function () {
    config(['app.active_plants' => [1, 3]]);
    $leaderCikarang = isolationManager('LEADER-CKR', [1]);
    $leaderPasirGombong = isolationManager('LEADER-PSG', [3]);
    isolationManpower('LEADER-CKR', '1');
    isolationManpower('LEADER-PSG', '3');
    $training = isolationTraining('ISO-PSG', 3);
    $enrollment = pendingOjt($training, isolationManpower('OP-PSG', '3'));

    $this->actingAs($leaderCikarang)->get('/admin/ojt')->assertOk()->assertDontSee('Training ISO-PSG');
    $this->get('/admin/ojt/' . $enrollment->id)->assertNotFound();

    $this->actingAs($leaderPasirGombong)->withSession(['active_plant' => 3])
        ->get('/admin/ojt')->assertOk()->assertSee('Training ISO-PSG');
    $this->get('/admin/ojt/' . $enrollment->id)->assertOk();
});

test('pengelola tidak dapat berpindah ke plant yang bukan aksesnya', function () {
    config(['app.active_plants' => [1, 3]]);
    $leader = isolationManager('LEADER-SWITCH', [3]);

    $this->actingAs($leader)->get('/admin/ojt')->assertOk()->assertDontSee('data-plant-switcher', false);
    $this->from('/admin/ojt')->post('/admin/active-plant', ['plant' => '1'])->assertSessionHasErrors('plant');

    expect(session('active_plant'))->toBe(3);
});

test('superadmin dapat berpindah plant dan melihat data sesuai plant terpilih', function () {
    config(['app.active_plants' => [1, 3]]);
    $superadmin = User::create(['user_id' => 'SUPER-SWITCH', 'name' => 'Super', 'role' => 1, 'is_superadmin' => true]);
    isolationTraining('ISO-CKR', 1);
    isolationTraining('ISO-PSG', 3);

    $this->actingAs($superadmin)->get('/admin/trainings')->assertOk()
        ->assertSee('data-plant-switcher', false)->assertSee('ISO-CKR')->assertSee('ISO-PSG');

    $this->from('/admin/trainings')->post('/admin/active-plant', ['plant' => '1'])->assertRedirect('/admin/trainings');
    expect(session('active_plant'))->toBe(1);

    $this->get('/admin/trainings')->assertOk()->assertSee('ISO-CKR')->assertDontSee('ISO-PSG');

    $this->post('/admin/active-plant', ['plant' => '9'])->assertSessionHasErrors('plant');
});

test('mode multi-plant menolak penugasan manpower dari plant lain', function () {
    config(['app.active_plants' => [1, 3]]);
    $manager = isolationManager('ADMIN-PSG', [3], ['can_manage_competency' => true, 'can_manage_curriculum' => true]);
    $training = isolationTraining('ISO-ASSIGN', 3);
    $operatorPsg = isolationManpower('OP-ASSIGN-PSG', '3');
    $operatorCkr = isolationManpower('OP-ASSIGN-CKR', '1');

    $this->actingAs($manager)->withSession(['active_plant' => 3])
        ->get('/admin/trainings/' . $training->id . '/participants')->assertOk()
        ->assertSee('OP-ASSIGN-PSG')->assertDontSee('OP-ASSIGN-CKR');

    $this->post('/admin/trainings/' . $training->id . '/participants', ['manpower_ids' => [$operatorPsg->id, $operatorCkr->id]])
        ->assertSessionHasErrors('manpower_ids');

    expect(TrainingEnrollment::where('training_id', $training->id)->count())->toBe(0);
});

test('plant training tidak dapat diubah setelah tersimpan', function () {
    $training = isolationTraining('ISO-LOCK', 3);

    expect(fn () => $training->update(['plant' => 1]))->toThrow(ValidationException::class);
});
