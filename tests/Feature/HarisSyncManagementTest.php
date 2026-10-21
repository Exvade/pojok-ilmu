<?php

use App\Models\HarisSyncLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('only superadmin can access manual haris synchronization', function () {
    $manager = User::create(['user_id' => 'MANAGER', 'name' => 'Manager', 'role' => 2, 'can_manage_competency' => true]);
    $superadmin = User::create(['user_id' => 'SUPERADMIN', 'name' => 'Superadmin', 'role' => 1, 'is_superadmin' => true]);

    $this->actingAs($manager)->get(route('admin.haris-sync.index'))->assertRedirect(route('login'));
    $this->actingAs($superadmin)->get(route('admin.haris-sync.index'))->assertOk()->assertSee('Sinkronisasi HaRis');
});

test('superadmin can inspect incomplete haris employee data', function () {
    config()->set('services.haris.url', 'https://haris.test/api/employees');
    config()->set('services.haris.token', 'test-token');

    Http::fake([
        'https://haris.test/api/employees*' => Http::response([
            'data' => [
                ['namaLengkap' => 'Karyawan Plant Kosong', 'nik' => 'QUALITY-001', 'jabatan' => 'Operator', 'departemen' => ['kode' => 'PSG.PRD', 'nama' => 'Produksi - Pasir Gombong'], 'plant' => ['id' => null, 'nama' => null]],
                ['namaLengkap' => 'Karyawan Lengkap', 'nik' => 'QUALITY-002', 'jabatan' => 'Operator', 'departemen' => ['kode' => 'PSG.PRD', 'nama' => 'Produksi - Pasir Gombong'], 'plant' => ['id' => '3', 'nama' => 'Pasir Gombong']],
            ],
        ]),
    ]);

    $superadmin = User::create(['user_id' => 'SUPERADMIN', 'name' => 'Superadmin', 'role' => 1, 'is_superadmin' => true]);

    $this->actingAs($superadmin)
        ->get(route('admin.haris-data-quality.index'))
        ->assertOk()
        ->assertSee('Kualitas data HaRis')
        ->assertSee('Karyawan Plant Kosong')
        ->assertSee('Plant belum lengkap')
        ->assertDontSee('Karyawan Lengkap');
});

test('superadmin can start haris synchronization from the web', function () {
    config()->set('services.haris.url', 'https://haris.test/api/employees');
    config()->set('services.haris.token', 'test-token');
    config()->set('services.haris.scope_plant_code', null);
    config()->set('services.haris.scope_plant_id', null);

    Http::fake([
        'https://haris.test/api/employees*' => Http::response([
            'data' => [
                ['namaLengkap' => 'Karyawan HaRis', 'nik' => 'SYNC-001', 'jabatan' => 'Operator', 'statusKaryawan' => 'Aktif', 'departemen' => ['kode' => 'PSG.PRD', 'nama' => 'Produksi - Pasir Gombong'], 'plant' => ['id' => '3', 'nama' => 'Pasir Gombong']],
            ],
        ]),
    ]);

    $superadmin = User::create(['user_id' => 'SUPERADMIN', 'name' => 'Superadmin', 'role' => 1, 'is_superadmin' => true]);

    $this->actingAs($superadmin)
        ->post(route('admin.haris-sync.store'))
        ->assertRedirect(route('admin.haris-sync.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('haris_sync_logs', ['status' => 'success', 'employee_count' => 1]);
    expect(HarisSyncLog::latest('started_at')->first()?->status)->toBe('success');
});
