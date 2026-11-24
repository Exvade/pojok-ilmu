<?php

use App\Models\MManpower;
use App\Models\MSkill;
use App\Models\S2sProduct;
use App\Models\Training;
use App\Models\TrainingEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('training operator tampil sebagai tabel produk per level dengan level kompetensi', function () {
    $operator = MManpower::create(['mp_id' => 'TABEL-OP', 'nik' => 'TABEL-OP', 'nama' => 'Operator Tabel', 'departemen' => 'PRODUKSI', 'bagian' => 'OPERATOR', 'status' => true]);
    $user = User::create(['user_id' => $operator->mp_id, 'name' => $operator->nama, 'role' => 0]);
    $skill = MSkill::create(['kode_skill' => 'TABEL-SKILL', 'nama_skill' => 'Produk', 'kategori' => 'Produksi']);

    $fuelTank = S2sProduct::create(['kode_part' => '12345678', 'nama_part' => 'COVER FUEL TANK', 'status' => 'aktif']);
    $radiator = S2sProduct::create(['kode_part' => '23746247', 'nama_part' => 'BASE RADIATOR', 'status' => 'aktif']);

    $levelOneFuelTank = makeTrainingReady(Training::create(['kode_training' => 'TBL-01', 'skill_id' => $skill->id, 'level' => 1, 'judul' => 'Training Fuel Tank']));
    $levelOneFuelTank->s2sProducts()->attach($fuelTank);
    $levelOneRadiator = Training::create(['kode_training' => 'TBL-02', 'skill_id' => $skill->id, 'level' => 1, 'judul' => 'Training Radiator']);
    $levelOneRadiator->s2sProducts()->attach($radiator);
    $levelTwoRadiator = Training::create(['kode_training' => 'TBL-03', 'skill_id' => $skill->id, 'level' => 2, 'judul' => 'Training Radiator Lanjutan', 'acceptance_level' => 3]);
    $levelTwoRadiator->s2sProducts()->attach($radiator);
    $withoutProduct = Training::create(['kode_training' => 'TBL-04', 'skill_id' => $skill->id, 'level' => 2, 'judul' => 'Training Tanpa Produk']);

    TrainingEnrollment::create(['training_id' => $levelOneFuelTank->id, 'manpower_id' => $operator->id, 'status' => 'enrolled']);
    TrainingEnrollment::create(['training_id' => $levelOneRadiator->id, 'manpower_id' => $operator->id, 'status' => 'passed', 'ojt_status' => 'approved', 'post_test_completed_at' => now()]);
    TrainingEnrollment::create(['training_id' => $levelTwoRadiator->id, 'manpower_id' => $operator->id, 'status' => 'enrolled']);
    TrainingEnrollment::create(['training_id' => $withoutProduct->id, 'manpower_id' => $operator->id, 'status' => 'enrolled']);

    $response = $this->actingAs($user)->get(route('operator.dashboard'))->assertOk()
        ->assertSeeInOrder(['Level 1', 'COVER FUEL TANK', '12345678', 'BASE RADIATOR', '23746247', 'Level 2', 'BASE RADIATOR', 'Training Tanpa Produk'])
        ->assertSee('Training sekarang')
        ->assertSee('Terkunci. Selesaikan level kompetensi 2 untuk: Training Fuel Tank.')
        ->assertSee('Level Manpower')
        ->assertSee('Acceptance level')
        ->assertDontSee('Balok')
        ->assertDontSee('balok')
        ->assertDontSee('Nanti');

    $levelRows = $response->viewData('levelRows');

    expect($levelRows->keys()->all())->toBe([1, 2])
        ->and($levelRows[1]->pluck('competency_level', 'part_number')->all())->toBe(['12345678' => 0, '23746247' => 2])
        ->and($levelRows[2]->pluck('competency_level', 'part_name')->all())->toBe(['BASE RADIATOR' => 0, 'Training Tanpa Produk' => 0])
        ->and($levelRows[2]->map(fn (array $row): int => $row['enrollment']->training->acceptance_level)->all())->toBe([3, 2])
        ->and($levelRows[2]->firstWhere('part_name', 'Training Tanpa Produk')['part_number'])->toBeNull();
});

test('status kompeten mengikuti acceptance level dan next progress satu bulan setelah capaian terakhir', function () {
    $this->travelTo(now()->setDate(2026, 11, 24)->setTime(9, 0));
    $operator = MManpower::create(['mp_id' => 'TABEL-OP2', 'nik' => 'TABEL-OP2', 'nama' => 'Operator Level', 'departemen' => 'PRODUKSI', 'bagian' => 'OPERATOR', 'status' => true]);
    $user = User::create(['user_id' => $operator->mp_id, 'name' => $operator->nama, 'role' => 0]);
    $skill = MSkill::create(['kode_skill' => 'TABEL-SKILL2', 'nama_skill' => 'Produk', 'kategori' => 'Produksi']);

    $defaultAcceptance = Training::create(['kode_training' => 'ACC-1', 'skill_id' => $skill->id, 'level' => 1, 'judul' => 'Acceptance Bawaan']);
    $higherAcceptance = Training::create(['kode_training' => 'ACC-2', 'skill_id' => $skill->id, 'level' => 1, 'judul' => 'Acceptance Tinggi', 'acceptance_level' => 3]);
    $postTestOnly = Training::create(['kode_training' => 'ACC-3', 'skill_id' => $skill->id, 'level' => 1, 'judul' => 'Baru Post-test']);
    $notStarted = Training::create(['kode_training' => 'ACC-4', 'skill_id' => $skill->id, 'level' => 1, 'judul' => 'Belum Mulai']);

    TrainingEnrollment::create(['training_id' => $defaultAcceptance->id, 'manpower_id' => $operator->id, 'status' => 'passed', 'ojt_status' => 'approved', 'post_test_completed_at' => '2026-10-01 08:00:00', 'ojt_assessed_at' => '2026-10-31 08:00:00']);
    TrainingEnrollment::create(['training_id' => $higherAcceptance->id, 'manpower_id' => $operator->id, 'status' => 'passed', 'ojt_status' => 'approved', 'post_test_completed_at' => '2026-10-01 08:00:00', 'ojt_assessed_at' => '2026-10-10 08:00:00']);
    TrainingEnrollment::create(['training_id' => $postTestOnly->id, 'manpower_id' => $operator->id, 'status' => 'in_progress', 'post_test_completed_at' => '2026-11-05 08:00:00']);
    TrainingEnrollment::create(['training_id' => $notStarted->id, 'manpower_id' => $operator->id, 'status' => 'in_progress']);

    $response = $this->actingAs($user)->get(route('operator.dashboard'))->assertOk();
    $enrollments = $response->viewData('enrollments')->keyBy('training.kode_training');

    expect($defaultAcceptance->fresh()->acceptance_level)->toBe(2)
        ->and($enrollments->map->is_competent->all())->toBe(['ACC-1' => true, 'ACC-2' => false, 'ACC-3' => false, 'ACC-4' => false])
        ->and($enrollments->map(fn ($enrollment) => $enrollment->next_progress_at?->toDateString())->all())
        ->toBe(['ACC-1' => '2026-11-30', 'ACC-2' => '2026-11-10', 'ACC-3' => '2026-12-05', 'ACC-4' => null]);

    $html = view('pdf.competency-summary', [
        'manpower' => $operator,
        'enrollments' => $response->viewData('enrollments'),
        'currentLevel' => 0,
        'levelRows' => $response->viewData('levelRows'),
    ])->render();

    expect($html)->toContain('Next progress', '30 Nov 2026', 'Kompeten', 'Belum kompeten')
        ->not->toContain('Sedang berjalan')
        ->not->toContain('Balok');
});
