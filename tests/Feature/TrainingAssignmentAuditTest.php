<?php

use App\Models\MManpower;
use App\Models\MSkill;
use App\Models\Training;
use App\Models\TrainingEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('penugasan dan pembatalan training tercatat sebagai audit', function () {
    $admin = User::create([
        'user_id' => 'AUDIT-ADMIN',
        'name' => 'Pengelola Audit',
        'role' => 1,
        'can_manage_competency' => true,
    ]);
    $manpower = MManpower::create([
        'mp_id' => 'AUDIT-OPERATOR',
        'nik' => 'AUDIT-OPERATOR',
        'nama' => 'Operator Audit',
        'departemen' => 'PRODUKSI',
        'bagian' => 'OPERATOR',
        'status' => true,
        'haris_synced_at' => now(),
    ]);
    $skill = MSkill::create(['kode_skill' => 'AUDIT-SKILL', 'nama_skill' => 'Keterampilan Audit', 'kategori' => 'PRODUKSI']);
    $training = Training::create([
        'kode_training' => 'AUDIT-TRAINING',
        'skill_id' => $skill->id,
        'departemen' => 'PRODUKSI',
        'level' => 1,
        'judul' => 'Training Audit',
    ]);

    $this->actingAs($admin)->post(route('admin.product-assignments.store'), [
        'training_id' => $training->id,
        'manpower_ids' => [$manpower->id],
        'reason' => 'Penugasan awal lini produksi.',
    ])->assertSessionHas('success');

    $this->assertDatabaseHas('training_assignment_logs', [
        'training_id' => $training->id,
        'manpower_id' => $manpower->id,
        'actor_user_id' => $admin->id,
        'action' => 'assigned',
        'status_after' => 'enrolled',
        'reason' => 'Penugasan awal lini produksi.',
    ]);

    $this->post(route('admin.product-assignments.store'), [
        'training_id' => $training->id,
        'manpower_ids' => [],
        'reason' => 'Kebutuhan lini berubah.',
    ])->assertSessionHas('success');

    $enrollment = TrainingEnrollment::withTrashed()->firstOrFail();
    expect($enrollment->trashed())->toBeTrue();
    $this->assertDatabaseHas('training_assignment_logs', [
        'training_id' => $training->id,
        'manpower_id' => $manpower->id,
        'action' => 'canceled',
        'status_before' => 'enrolled',
        'status_after' => 'dibatalkan',
        'reason' => 'Kebutuhan lini berubah.',
    ]);

    $this->get(route('admin.product-assignments.history', $training))
        ->assertOk()
        ->assertSee('Riwayat perubahan')
        ->assertSee('Operator Audit')
        ->assertSee('Kebutuhan lini berubah.');
});
