<?php

use App\Models\MManpower;
use App\Models\MSkill;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function plantTestSkill(): MSkill
{
    return MSkill::create([
        'kode_skill' => 'PLANT-SKILL',
        'nama_skill' => 'Skill plant',
        'kategori' => 'Pengujian',
    ]);
}

test('migrasi mengisi data lama ke plant bawaan', function () {
    $this->artisan('migrate:rollback', ['--step' => 1])->assertSuccessful();

    $skill = plantTestSkill();
    DB::table('trainings')->insert([
        'id' => (string) Str::uuid(), 'kode_training' => 'LAMA', 'skill_id' => $skill->id, 'level' => 1, 'judul' => 'Training lama',
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $manager = User::create(['user_id' => 'LAMA-1', 'name' => 'Pengelola lama', 'role' => 2, 'can_manage_curriculum' => true]);
    $operator = User::create(['user_id' => 'LAMA-2', 'name' => 'Operator lama', 'role' => 0]);
    $superadmin = User::create(['user_id' => 'LAMA-3', 'name' => 'Superadmin lama', 'role' => 1, 'is_superadmin' => true]);

    $this->artisan('migrate')->assertSuccessful();

    expect(DB::table('trainings')->where('kode_training', 'LAMA')->value('plant'))->toBe(3)
        ->and($manager->plantIds())->toBe([3])
        ->and($operator->plantIds())->toBe([])
        ->and($superadmin->plantIds())->toBe([]);
});

test('training baru otomatis masuk plant bawaan bila tidak ada plant aktif', function () {
    $training = Training::create([
        'kode_training' => 'PLANT-DEFAULT',
        'skill_id' => plantTestSkill()->id,
        'level' => 1,
        'judul' => 'Training plant bawaan',
    ]);

    expect($training->fresh()->plant)->toBe(3);
});

test('training baru mengikuti plant aktif pada sesi', function () {
    session(['active_plant' => 2]);

    $training = Training::create([
        'kode_training' => 'PLANT-AKTIF',
        'skill_id' => plantTestSkill()->id,
        'level' => 1,
        'judul' => 'Training plant aktif',
    ]);

    expect($training->fresh()->plant)->toBe(2);
});

test('mode semua plant tetap menyimpan training ke plant bawaan', function () {
    session(['active_plant' => 'all']);

    $training = Training::create([
        'kode_training' => 'PLANT-ALL',
        'skill_id' => plantTestSkill()->id,
        'level' => 1,
        'judul' => 'Training mode semua plant',
    ]);

    expect($training->fresh()->plant)->toBe(3);
});

test('pengelola baru otomatis mendapat plant sesuai data HaRis', function () {
    $superadmin = User::create(['user_id' => 'SUPER', 'name' => 'Super', 'role' => 1, 'is_superadmin' => true]);
    $employee = MManpower::create([
        'mp_id' => '300400', 'nik' => '300400', 'nama' => 'Leader Cikarang', 'departemen' => 'PRODUKSI', 'bagian' => 'LEADER',
        'status' => true, 'haris_synced_at' => now(), 'haris_plant_id' => '1',
    ]);

    $this->actingAs($superadmin)
        ->put('/admin/roles/' . $employee->id, ['can_assess_ojt' => '1', 'password' => 'plant-access-2026'])
        ->assertRedirect('/admin/roles');

    $account = User::where('user_id', '300400')->firstOrFail();

    expect($account->plantIds())->toBe([1]);

    $this->put('/admin/roles/' . $employee->id, ['can_assess_ojt' => '1', 'can_manage_competency' => '1'])
        ->assertRedirect('/admin/roles');

    expect($account->fresh()->plantIds())->toBe([1]);
});
