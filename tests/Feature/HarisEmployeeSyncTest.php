<?php

use App\Models\MManpower;
use App\Models\User;
use App\Services\HarisEmployeeSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config()->set('services.haris.url', 'https://haris.test/api/employees');
    config()->set('services.haris.token', 'test-token');
    config()->set('services.haris.scope_plant_code', null);
    config()->set('services.haris.scope_plant_id', null);
});

test('it synchronizes employees from haris into the local master table', function () {
    Http::fake([
        'https://haris.test/api/employees*' => Http::response([
            'data' => [
                [
                    'namaLengkap' => '  Budi   Santoso ',
                    'nik' => '100001',
                    'jabatan' => 'Operator Produksi',
                    'statusKaryawan' => 'Aktif',
                    'email' => 'budi@example.test',
                    'primary_phone' => '08123456789',
                    'departemen' => [
                        'kode' => ' CKR. PMC ',
                        'nama' => ' PRODUKSI METAL COMPONENT - CIKARANG ',
                    ],
                    'plant' => [
                        'id' => 4,
                        'nama' => 'Cikarang',
                    ],
                ],
                [
                    'namaLengkap' => 'Sari Admin',
                    'nik' => '100004',
                    'jabatan' => 'Staff HR',
                    'statusKaryawan' => 'Aktif',
                    'departemen' => ['kode' => 'PSG.HRD', 'nama' => 'Human Resources Departement - Pasir Gombong'],
                    'plant' => ['nama' => 'Pasir Gombong'],
                ],
                [
                    'namaLengkap' => 'Andi GA',
                    'nik' => '100005',
                    'jabatan' => 'Staff GA',
                    'statusKaryawan' => 'Aktif',
                    'departemen' => ['kode' => 'CKR.GA', 'nama' => 'GA'],
                    'plant' => ['nama' => 'Cikarang'],
                ],
                [
                    'namaLengkap' => 'Tono PPIC',
                    'nik' => '100006',
                    'jabatan' => 'PPIC Staff',
                    'statusKaryawan' => 'Aktif',
                    'departemen' => ['kode' => 'PSG.PPIC', 'nama' => 'PPIC - Pasir Gombong'],
                    'plant' => ['id' => 3, 'nama' => 'Pasir_Gombong'],
                ],
            ],
        ]),
    ]);

    $result = app(HarisEmployeeSyncService::class)->sync();

    expect($result['employee_count'])->toBe(4)
        ->and($result['deactivated_count'])->toBe(0);

    $this->assertDatabaseHas('m_manpower', [
        'mp_id' => '100001',
        'nik' => '100001',
        'nama' => 'Budi Santoso',
        'departemen' => 'PRODUKSI METAL COMPONENT',
        'kode_departemen' => 'CKR. PMC',
        'nama_plant' => 'CIKARANG',
        'haris_plant_id' => '4',
        'bagian' => 'OPERATOR',
        'jabatan' => 'OPERATOR',
        'status' => true,
        'status_karyawan' => 'Aktif',
    ]);

    $this->assertDatabaseHas('m_manpower', [
        'nik' => '100004',
        'departemen' => 'HRD',
        'kode_departemen' => 'PSG.HRD',
    ]);
    $this->assertDatabaseHas('m_manpower', [
        'nik' => '100005',
        'departemen' => 'GA',
        'kode_departemen' => 'CKR.GA',
    ]);
    $this->assertDatabaseHas('m_manpower', [
        'nik' => '100006',
        'departemen' => 'PPIC',
        'jabatan' => 'STAFF',
        'nama_plant' => 'PASIR GOMBONG',
    ]);

    Http::assertSent(fn ($request): bool => $request->url() === 'https://haris.test/api/employees?token=test-token');
});

test('it deactivates previously synchronized employees missing from a safe haris response', function () {
    $staleEmployee = MManpower::create([
        'mp_id' => '100002',
        'nik' => '100002',
        'nama' => 'Karyawan Lama',
        'departemen' => 'QUALITY CONTROL',
        'bagian' => 'Inspector',
        'status' => true,
        'haris_synced_at' => now()->subDay(),
    ]);

    Http::fake([
        'https://haris.test/api/employees*' => Http::response([
            'data' => [
                [
                    'namaLengkap' => 'Karyawan Baru',
                    'nik' => '100003',
                    'jabatan' => 'Inspector',
                    'statusKaryawan' => 'Aktif',
                    'departemen' => ['kode' => 'QC', 'nama' => 'QUALITY CONTROL'],
                    'plant' => ['nama' => 'Cikarang'],
                ],
            ],
        ]),
    ]);

    app(HarisEmployeeSyncService::class)->sync();

    expect($staleEmployee->fresh()->status)->toBeFalse();
});

test('it keeps Plant 3 and management employees across plants in the local scope', function () {
    config()->set('services.haris.scope_plant_code', 'PSG');
    config()->set('services.haris.scope_plant_id', '3');
    User::create(['user_id' => '100013', 'name' => 'Management lokal', 'role' => 2]);

    Http::fake([
        'https://haris.test/api/employees*' => Http::response([
            'data' => [
                ['namaLengkap' => 'Operator P3', 'nik' => '100010', 'jabatan' => 'Operator', 'statusKaryawan' => 'Aktif', 'departemen' => ['kode' => 'PSG.PPIC', 'nama' => 'PPIC - Pasir Gombong'], 'plant' => ['id' => '3', 'nama' => 'Pasir Gombong']],
                ['namaLengkap' => 'Operator Cikarang', 'nik' => '100011', 'jabatan' => 'Operator', 'statusKaryawan' => 'Aktif', 'departemen' => ['kode' => 'CKR.PPIC', 'nama' => 'PPIC - Cikarang'], 'plant' => ['id' => '4', 'nama' => 'Cikarang']],
                ['namaLengkap' => 'Manager Cikarang', 'nik' => '100012', 'jabatan' => 'Manager Pabrik', 'statusKaryawan' => 'Aktif', 'departemen' => ['kode' => 'CKR.PPIC', 'nama' => 'PPIC - Cikarang'], 'plant' => ['id' => '4', 'nama' => 'Cikarang']],
                ['namaLengkap' => 'Management Terdaftar', 'nik' => '100013', 'jabatan' => 'Staff HR', 'statusKaryawan' => 'Aktif', 'departemen' => ['kode' => 'BDG.HRD', 'nama' => 'HRD - Bandung'], 'plant' => ['id' => '1', 'nama' => 'Bandung']],
            ],
        ]),
    ]);

    $result = app(HarisEmployeeSyncService::class)->sync();

    expect($result['employee_count'])->toBe(3);
    $this->assertDatabaseHas('m_manpower', ['nik' => '100010', 'status' => true]);
    $this->assertDatabaseHas('m_manpower', ['nik' => '100012', 'status' => true]);
    $this->assertDatabaseHas('m_manpower', ['nik' => '100013', 'status' => true]);
    $this->assertDatabaseMissing('m_manpower', ['nik' => '100011']);
});

test('it keeps production plastic variants as separate training departments', function () {
    $plasticA = MManpower::create(['mp_id' => 'DEPT-A', 'nik' => 'DEPT-A', 'nama' => 'Operator Plastik A', 'departemen' => 'PRODUKSI PLASTIK A', 'department_group' => 'PRODUKSI PLASTIK', 'bagian' => 'OPERATOR', 'status' => true]);
    $plasticB = MManpower::create(['mp_id' => 'DEPT-B', 'nik' => 'DEPT-B', 'nama' => 'Operator Plastik B', 'departemen' => 'PRODUKSI PLASTIK B', 'department_group' => 'PRODUKSI PLASTIK', 'bagian' => 'OPERATOR', 'status' => true]);

    expect($plasticA->trainingDepartment())->toBe('PRODUKSI PLASTIK A')
        ->and($plasticB->trainingDepartment())->toBe('PRODUKSI PLASTIK B')
        ->and(MManpower::query()->forTrainingDepartment('PRODUKSI PLASTIK A')->pluck('id')->all())
        ->toContain($plasticA->id)
        ->not->toContain($plasticB->id);
});

test('it does not deactivate employees outside the active plant scope', function () {
    config()->set('services.haris.scope_plant_code', 'PSG');
    config()->set('services.haris.scope_plant_id', '3');

    $outsideScope = MManpower::create([
        'mp_id' => '100020',
        'nik' => '100020',
        'nama' => 'Operator Cikarang Lama',
        'departemen' => 'PPIC',
        'kode_departemen' => 'CKR.PPIC',
        'haris_plant_id' => '4',
        'bagian' => 'OPERATOR',
        'jabatan' => 'OPERATOR',
        'status' => true,
        'haris_synced_at' => now()->subDay(),
    ]);

    Http::fake([
        'https://haris.test/api/employees*' => Http::response([
            'data' => [
                ['namaLengkap' => 'Operator P3', 'nik' => '100021', 'jabatan' => 'Operator', 'statusKaryawan' => 'Aktif', 'departemen' => ['kode' => 'PSG.PPIC', 'nama' => 'PPIC - Pasir Gombong'], 'plant' => ['id' => '3', 'nama' => 'Pasir Gombong']],
            ],
        ]),
    ]);

    app(HarisEmployeeSyncService::class)->sync();

    expect($outsideScope->fresh()->status)->toBeTrue();
});
