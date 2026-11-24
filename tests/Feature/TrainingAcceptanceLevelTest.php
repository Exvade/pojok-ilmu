<?php

use App\Models\MSkill;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/** @return array<string, mixed> */
function acceptanceLevelPayload(array $overrides = []): array
{
    return [
        'kode_training' => 'ACC-FORM-01',
        'departemens' => ['PRODUKSI PLASTIK A'],
        'level' => 1,
        'judul' => 'Training Acceptance',
        'max_retries' => 3,
        'passing_grade' => 80,
        ...$overrides,
    ];
}

test('acceptance level training bawaan 2 dan dapat dinaikkan', function () {
    $admin = User::create(['user_id' => 'ADMIN-ACC', 'name' => 'Pengelola Kurikulum', 'role' => 1, 'can_manage_curriculum' => true]);

    $this->actingAs($admin)->post(route('admin.trainings.store'), acceptanceLevelPayload())->assertSessionHas('success');
    $training = Training::where('kode_training', 'ACC-FORM-01')->firstOrFail();
    expect($training->acceptance_level)->toBe(2);

    $this->put(route('admin.trainings.update', $training->id), acceptanceLevelPayload(['acceptance_level' => 4]))->assertSessionHasNoErrors();
    expect($training->fresh()->acceptance_level)->toBe(4);

    $this->get(route('admin.trainings.index'))->assertOk()->assertSee('Acceptance level');
});

test('acceptance level di bawah 2 atau di atas 4 ditolak', function (int $acceptanceLevel, string $message) {
    $admin = User::create(['user_id' => 'ADMIN-ACC2', 'name' => 'Pengelola Kurikulum', 'role' => 1, 'can_manage_curriculum' => true]);
    $skill = MSkill::create(['kode_skill' => 'ACC-SKILL', 'nama_skill' => 'Produk', 'kategori' => 'Produksi']);
    $training = Training::create(['kode_training' => 'ACC-FORM-02', 'skill_id' => $skill->id, 'level' => 1, 'judul' => 'Training Lama', 'departemen' => 'PRODUKSI PLASTIK A']);

    $this->actingAs($admin)->post(route('admin.trainings.store'), acceptanceLevelPayload(['acceptance_level' => $acceptanceLevel]))
        ->assertSessionHasErrors(['acceptance_level' => $message]);
    $this->put(route('admin.trainings.update', $training->id), acceptanceLevelPayload(['kode_training' => 'ACC-FORM-02', 'acceptance_level' => $acceptanceLevel]))
        ->assertSessionHasErrors(['acceptance_level' => $message]);

    expect(Training::where('kode_training', 'ACC-FORM-01')->exists())->toBeFalse()
        ->and($training->fresh()->acceptance_level)->toBe(2);
})->with([
    'di bawah 2' => [1, 'Acceptance level minimal 2. Nilai di bawah 2 tidak diperbolehkan.'],
    'di atas 4' => [5, 'Acceptance level maksimal 4.'],
]);
