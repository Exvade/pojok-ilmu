<?php

use App\Models\MManpower;
use App\Models\MSkill;
use App\Models\OjtAssessment;
use App\Models\Training;
use App\Models\TrainingEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('leader can approve standard production OJT and award Balok 2', function () {
    $leader = User::create([
        'user_id' => 'OJT-LEADER',
        'name' => 'Leader Produksi',
        'role' => 2,
        'can_assess_ojt' => true,
    ]);
    MManpower::create([
        'mp_id' => 'OJT-LEADER',
        'nik' => 'OJT-LEADER',
        'nama' => 'Leader Produksi',
        'departemen' => 'PRODUKSI PLASTIK',
        'bagian' => 'LEADER',
        'status' => true,
        'haris_synced_at' => now(),
    ]);
    $manpower = MManpower::create([
        'mp_id' => 'OJT-MP',
        'nik' => 'OJT-MP',
        'nama' => 'Manpower Produksi',
        'departemen' => 'PRODUKSI PLASTIK',
        'bagian' => 'OPERATOR',
        'status' => true,
        'haris_synced_at' => now(),
    ]);
    $skill = MSkill::create(['kode_skill' => 'OJT-SKILL', 'nama_skill' => 'Box Battery', 'kategori' => 'PRODUKSI PLASTIK']);
    $training = Training::create(['kode_training' => 'OJT-BOX', 'skill_id' => $skill->id, 'departemen' => 'PRODUKSI PLASTIK', 'level' => 1, 'judul' => 'BOX BATTERY', 'passing_grade' => 80, 'max_retries' => 3, 'requires_ojt' => true]);
    $enrollment = TrainingEnrollment::create(['training_id' => $training->id, 'manpower_id' => $manpower->id, 'status' => 'in_progress', 'ojt_status' => 'pending', 'post_test_completed_at' => now()]);
    $theory = collect(range(1, 12))->mapWithKeys(fn (int $number) => [array_keys([
        'part_name' => 8, 'part_material' => 8, 'work_tools' => 8, 'work_process' => 10, 'safety' => 8, 'control_plan' => 10,
        'ng_types' => 8, 'marking' => 8, 'scrap' => 8, 'handling' => 8, 'k3' => 8, 'checking' => 8,
    ])[$number - 1] => ['answer' => 'Jawaban ' . $number, 'score' => $number === 4 || $number === 6 ? 10 : 8]])->all();
    $practice = [
        'improvement' => ['actual' => 'Sesuai standar', 'score' => 20],
        'reject' => ['actual' => 'Sesuai standar', 'score' => 30],
        'cycle_time' => ['actual' => 'Sesuai target', 'score' => 30],
        'apd' => ['actual' => 'APD digunakan', 'score' => 10],
        'five_r' => ['actual' => 'Area rapi', 'score' => 10],
    ];

    $this->actingAs($leader)->get('/admin/ojt')->assertOk()->assertSee('BOX BATTERY');
    $this->post('/admin/ojt/' . $enrollment->id, [
        'training_date' => now()->toDateString(),
        'training_time' => '08:00',
        'location' => 'Line 1',
        'categories' => ['pengetahuan_produk'],
        'tools' => 'Produk dan dokumen kerja',
        'materials' => ['SOP Box Battery'],
        'theory' => $theory,
        'practice' => $practice,
        'conclusion' => 'Memenuhi standar.',
        'recommendation' => 'Pertahankan penerapan SOP.',
    ])->assertRedirect('/admin/ojt');

    $enrollment->refresh();
    expect($enrollment->status)->toBe('passed')
        ->and($enrollment->ojt_status)->toBe('approved')
        ->and(OjtAssessment::where('enrollment_id', $enrollment->id)->firstOrFail()->is_passed)->toBeTrue();
});
