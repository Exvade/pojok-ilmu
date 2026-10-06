<?php

use App\Models\MManpower;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('only superadmin can assign competency and curriculum permissions from HaRis manpower', function () {
    $superadmin = User::create(['user_id' => 'SUPER', 'name' => 'Deft', 'role' => 1, 'is_superadmin' => true, 'can_manage_competency' => true, 'can_manage_curriculum' => true]);
    $employee = MManpower::create(['mp_id' => '100200', 'nik' => '100200', 'nama' => 'Pengelola Baru', 'departemen' => 'IT', 'bagian' => 'STAFF', 'status' => true, 'haris_synced_at' => now()]);

    $this->actingAs($superadmin)->get('/admin/roles')->assertOk()->assertSee('Pengelola Baru');
    $this->put('/admin/roles/' . $employee->id, ['can_manage_competency' => '1', 'password' => 'role-access-2026'])
        ->assertRedirect('/admin/roles');

    $account = User::where('user_id', '100200')->firstOrFail();
    expect($account->canManageCompetency())->toBeTrue()->and($account->canManageCurriculum())->toBeFalse()->and(Hash::check('role-access-2026', $account->password))->toBeTrue();
    $this->actingAs($account)->get('/admin/skills')->assertOk();
    $this->actingAs($account)->get('/admin/trainings')->assertRedirect('/login');
});

test('superadmin bootstrap rejects non HaRis manpower and promotes the selected HaRis employee', function () {
    MManpower::create(['mp_id' => 'DUMMY', 'nik' => '9999', 'nama' => 'Dummy', 'departemen' => 'IT', 'bagian' => 'STAFF', 'status' => true]);
    $deft = MManpower::create(['mp_id' => '4067.260721.CKR', 'nik' => '4067.260721.CKR', 'nama' => 'DEFT VALIAN EXANOVA', 'departemen' => 'IT', 'bagian' => 'STAFF', 'status' => true, 'haris_synced_at' => now()]);

    $this->artisan('pojok-ilmu:bootstrap-superadmin', ['nik' => $deft->nik, '--password' => 'superadmin-2026', '--purge-dummy' => true])->assertSuccessful();

    $this->assertDatabaseHas('users', ['user_id' => $deft->mp_id, 'is_superadmin' => true, 'can_manage_competency' => true, 'can_manage_curriculum' => true]);
    $this->assertDatabaseMissing('m_manpower', ['mp_id' => 'DUMMY']);
});
