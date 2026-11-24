<?php

use App\Exports\TrainingProgressReportExport;
use App\Models\MManpower;
use App\Models\MSkill;
use App\Models\Training;
use App\Models\TrainingEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;

uses(RefreshDatabase::class);

test('hr can filter and export training progress report', function () {
    Carbon::setTestNow('2026-10-19 09:30:00');
    Excel::fake();

    $admin = User::create([
        'user_id' => 'REPORT-HR',
        'name' => 'HR Training',
        'role' => 1,
        'can_manage_competency' => true,
    ]);
    $skill = MSkill::create(['kode_skill' => 'REPORT-SKILL', 'nama_skill' => 'Proses produksi', 'kategori' => 'PRODUKSI']);
    $training = Training::create([
        'kode_training' => 'REPORT-TRAINING',
        'skill_id' => $skill->id,
        'departemen' => 'PRODUKSI',
        'level' => 1,
        'judul' => 'BOX BATTERY',
        'passing_grade' => 80,
        'max_retries' => 3,
        'requires_ojt' => true,
    ]);
    $notStarted = MManpower::create(['mp_id' => 'REPORT-001', 'nik' => 'REPORT-001', 'nama' => 'Belum Mulai', 'departemen' => 'PRODUKSI', 'bagian' => 'OPERATOR', 'status' => true, 'haris_synced_at' => now(), 'haris_plant_id' => '3']);
    $pendingOjt = MManpower::create(['mp_id' => 'REPORT-002', 'nik' => 'REPORT-002', 'nama' => 'Menunggu OJT', 'departemen' => 'PRODUKSI', 'bagian' => 'OPERATOR', 'status' => true, 'haris_synced_at' => now(), 'haris_plant_id' => '3']);
    $completed = MManpower::create(['mp_id' => 'REPORT-003', 'nik' => 'REPORT-003', 'nama' => 'Sudah Kompeten', 'departemen' => 'PRODUKSI', 'bagian' => 'OPERATOR', 'status' => true, 'haris_synced_at' => now(), 'haris_plant_id' => '3']);

    TrainingEnrollment::create(['training_id' => $training->id, 'manpower_id' => $notStarted->id, 'status' => 'enrolled', 'ojt_status' => 'not_started']);
    TrainingEnrollment::create(['training_id' => $training->id, 'manpower_id' => $pendingOjt->id, 'status' => 'in_progress', 'ojt_status' => 'pending', 'post_test_completed_at' => now()]);
    TrainingEnrollment::create(['training_id' => $training->id, 'manpower_id' => $completed->id, 'status' => 'passed', 'ojt_status' => 'approved', 'post_test_completed_at' => now(), 'ojt_assessed_at' => now()]);

    $this->actingAs($admin)
        ->get(route('admin.training-reports.index', ['departemen' => 'PRODUKSI']))
        ->assertOk()
        ->assertSee('Laporan training')
        ->assertSee('Menunggu OJT')
        ->assertSee('Sudah Kompeten')
        ->assertSee('Level kompetensi 2');

    $this->get(route('admin.training-reports.export', ['status' => 'menunggu_ojt']))->assertOk();

    Excel::assertDownloaded('laporan-progres-training-20261019-093000.xlsx', function (TrainingProgressReportExport $export): bool {
        return true;
    });
});
