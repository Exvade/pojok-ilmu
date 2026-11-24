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

    $levelOneFuelTank = Training::create(['kode_training' => 'TBL-01', 'skill_id' => $skill->id, 'level' => 1, 'judul' => 'Training Fuel Tank']);
    $levelOneFuelTank->s2sProducts()->attach($fuelTank);
    $levelOneRadiator = Training::create(['kode_training' => 'TBL-02', 'skill_id' => $skill->id, 'level' => 1, 'judul' => 'Training Radiator']);
    $levelOneRadiator->s2sProducts()->attach($radiator);
    $levelTwoRadiator = Training::create(['kode_training' => 'TBL-03', 'skill_id' => $skill->id, 'level' => 2, 'judul' => 'Training Radiator Lanjutan']);
    $levelTwoRadiator->s2sProducts()->attach($radiator);
    $withoutProduct = Training::create(['kode_training' => 'TBL-04', 'skill_id' => $skill->id, 'level' => 2, 'judul' => 'Training Tanpa Produk']);

    TrainingEnrollment::create(['training_id' => $levelOneFuelTank->id, 'manpower_id' => $operator->id, 'status' => 'enrolled']);
    TrainingEnrollment::create(['training_id' => $levelOneRadiator->id, 'manpower_id' => $operator->id, 'status' => 'passed', 'ojt_status' => 'approved', 'post_test_completed_at' => now()]);
    TrainingEnrollment::create(['training_id' => $levelTwoRadiator->id, 'manpower_id' => $operator->id, 'status' => 'enrolled']);
    TrainingEnrollment::create(['training_id' => $withoutProduct->id, 'manpower_id' => $operator->id, 'status' => 'enrolled']);

    $response = $this->actingAs($user)->get(route('operator.dashboard'))->assertOk()
        ->assertSeeInOrder(['Level 1', 'COVER FUEL TANK', '12345678', 'BASE RADIATOR', '23746247', 'Level 2', 'BASE RADIATOR', 'Training Tanpa Produk'])
        ->assertSee('Training sekarang')
        ->assertSee('Terkunci. Selesaikan Balok 2 untuk: Training Fuel Tank.')
        ->assertSee('Acceptance level')
        ->assertDontSee('balok tercapai')
        ->assertDontSee('Nanti');

    $levelRows = $response->viewData('levelRows');

    expect($levelRows->keys()->all())->toBe([1, 2])
        ->and($levelRows[1]->pluck('competency_level', 'part_number')->all())->toBe(['12345678' => 0, '23746247' => 1])
        ->and($levelRows[2]->pluck('competency_level', 'part_name')->all())->toBe(['BASE RADIATOR' => 1, 'Training Tanpa Produk' => 0])
        ->and($levelRows[2]->firstWhere('part_name', 'Training Tanpa Produk')['part_number'])->toBeNull();
});

test('level kompetensi produk mengikuti level training tertinggi yang sudah balok dua', function () {
    $operator = MManpower::create(['mp_id' => 'TABEL-OP2', 'nik' => 'TABEL-OP2', 'nama' => 'Operator Level', 'departemen' => 'PRODUKSI', 'bagian' => 'OPERATOR', 'status' => true]);
    $user = User::create(['user_id' => $operator->mp_id, 'name' => $operator->nama, 'role' => 0]);
    $skill = MSkill::create(['kode_skill' => 'TABEL-SKILL2', 'nama_skill' => 'Produk', 'kategori' => 'Produksi']);
    $product = S2sProduct::create(['kode_part' => '43278364', 'nama_part' => 'COVER COMP', 'status' => 'aktif']);

    foreach ([1, 2] as $level) {
        $training = Training::create(['kode_training' => "COMP-{$level}", 'skill_id' => $skill->id, 'level' => $level, 'judul' => "Cover Comp Level {$level}"]);
        $training->s2sProducts()->attach($product);
        TrainingEnrollment::create(['training_id' => $training->id, 'manpower_id' => $operator->id, 'status' => 'passed', 'ojt_status' => 'approved', 'post_test_completed_at' => now()]);
    }

    $levelRows = $this->actingAs($user)->get(route('operator.dashboard'))->assertOk()->viewData('levelRows');

    expect($levelRows->flatten(1)->pluck('competency_level')->unique()->values()->all())->toBe([2]);
});
