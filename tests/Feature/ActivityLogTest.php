<?php

use App\Models\ActivityLog;
use App\Models\MManpower;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('superadmin can review recorded access changes', function () {
    $superadmin = User::create(['user_id' => 'AUDIT-ADMIN', 'name' => 'Admin Audit', 'role' => 1, 'is_superadmin' => true]);
    $manpower = MManpower::create(['mp_id' => 'AUDIT-USER', 'nik' => 'AUDIT-USER', 'nama' => 'Pengelola Audit', 'departemen' => 'IT', 'bagian' => 'STAFF', 'status' => true, 'haris_synced_at' => now()]);

    $this->actingAs($superadmin)->put(route('admin.roles.update', $manpower), [
        'can_manage_competency' => '1',
        'password' => 'kata-sandi-audit',
    ])->assertRedirect(route('admin.roles.index'));

    $log = ActivityLog::query()->firstOrFail();
    expect($log->category)->toBe('access')->and($log->event)->toBe('access.updated')->and($log->actor_user_id)->toBe($superadmin->id);
    $this->actingAs($superadmin)->get(route('admin.activity-logs.index'))->assertOk()->assertSee('Riwayat aktivitas')->assertSee('Pengelola Audit');
});
