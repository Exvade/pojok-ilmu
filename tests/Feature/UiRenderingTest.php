<?php

use App\Models\MManpower;
use App\Models\MSkill;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Training;
use App\Models\TrainingEnrollment;
use App\Models\TrainingMaterial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

test('management pages render shared sidebar and training forms', function () {
    $admin = User::create(['user_id' => 'UI-ADMIN', 'name' => 'UI Admin', 'role' => 1, 'is_superadmin' => true, 'can_manage_competency' => true, 'can_manage_curriculum' => true]);
    $skill = MSkill::create(['kode_skill' => 'UI-SK', 'nama_skill' => 'Keselamatan kerja', 'kategori' => 'Safety']);
    $training = Training::create(['kode_training' => 'UI-TR', 'skill_id' => $skill->id, 'level' => 1, 'judul' => 'Dasar keselamatan', 'passing_grade' => 80, 'max_retries' => 3]);
    $this->actingAs($admin);
    foreach (['/admin/dashboard', '/admin/skills', '/admin/trainings'] as $url) {
        $this->get($url)->assertOk()->assertSee('id="app-sidebar"', false)->assertSee('x-ref="sidebarNavigation"', false)->assertSee('Training', false);
    }
    $this->get('/admin/trainings/' . $training->id)->assertOk()->assertSee('Siapkan pre-test & post-test', false)->assertSee('activeSection', false)->assertSee('x-teleport="body"', false);
    $quiz = Quiz::create(['training_id' => $training->id, 'tipe' => 'post_test', 'judul' => 'Evaluasi keselamatan', 'durasi_menit' => 15, 'passing_grade' => 80]);
    QuizQuestion::create(['quiz_id' => $quiz->id, 'pertanyaan' => 'Apa langkah pertama?', 'opsi_a' => 'Periksa alat', 'opsi_b' => 'Mulai bekerja', 'opsi_c' => 'Abaikan', 'opsi_d' => 'Pulang', 'kunci_jawaban' => 'a']);
    $this->get('/admin/trainings/' . $training->id)->assertOk()->assertSee('Tambah Soal Kuis')->assertSee('Apa langkah pertama?');
    $skill->delete();
    $this->get('/admin/trainings/' . $training->id)->assertOk()->assertSee('Training Level 1');
    $this->get('/admin/trainings')->assertOk()->assertSee('Training', false)->assertDontSee('Level 4');

    $this->from('/admin/trainings')->post('/admin/trainings', [
        'kode_training' => 'UI-LEVEL-4',
        'departemens' => ['IT'],
        'level' => 4,
        'judul' => 'Training tidak valid',
        'max_retries' => 3,
        'passing_grade' => 80,
    ])->assertSessionHasErrors('level');
});

test('invalid form preserves user input and reopens the correct modal', function () {
    $admin = User::create(['user_id' => 'UI-ADMIN', 'name' => 'UI Admin', 'role' => 1, 'is_superadmin' => true, 'can_manage_competency' => true, 'can_manage_curriculum' => true]);
    $this->actingAs($admin)->from('/admin/trainings')->post('/admin/trainings', [
        '_modal' => 'add-product-modal', 'judul' => 'Judul yang dipertahankan', 'passing_grade' => 91,
    ])->assertSessionHasErrors('kode_training')->assertRedirect('/admin/trainings');
    $this->get('/admin/trainings')->assertOk()->assertSee('Judul yang dipertahankan')->assertSee('value="91"', false)->assertSee(', true)', false);
});

test('operator material and assessment pages render media and answer controls', function () {
    $employee = MManpower::create(['mp_id' => 'UI-OP', 'nik' => 'UI-OP', 'nama' => 'UI Operator', 'departemen' => 'PRODUKSI', 'bagian' => 'ASSEMBLY', 'status' => true]);
    $user = User::create(['user_id' => 'UI-OP', 'name' => 'UI Operator', 'role' => 0]);
    $skill = MSkill::create(['kode_skill' => 'UI-SK', 'nama_skill' => 'Keselamatan kerja', 'kategori' => 'Safety']);
    $training = Training::create(['kode_training' => 'UI-TR', 'skill_id' => $skill->id, 'level' => 1, 'judul' => 'Dasar keselamatan', 'passing_grade' => 80, 'max_retries' => 3]);
    TrainingMaterial::create(['training_id' => $training->id, 'judul' => 'Panduan kerja', 'tipe_media' => 'pdf', 'media_url' => '/storage/panduan.pdf', 'urutan' => 1]);
    $quiz = Quiz::create(['training_id' => $training->id, 'judul' => 'Evaluasi keselamatan', 'durasi_menit' => 15, 'passing_grade' => 80]);
    QuizQuestion::create(['quiz_id' => $quiz->id, 'pertanyaan' => 'Apa langkah pertama?', 'opsi_a' => 'Periksa alat', 'opsi_b' => 'Mulai bekerja', 'opsi_c' => 'Abaikan', 'opsi_d' => 'Pulang', 'kunci_jawaban' => 'a']);
    TrainingEnrollment::create(['training_id' => $training->id, 'manpower_id' => $employee->id, 'status' => 'in_progress', 'material_completed_at' => now()]);
    $material = $this->actingAs($user)->get('/operator/materials/' . $training->id)->assertOk()->assertSee('Buka dokumen PDF')->assertDontSee('Administrasi pelatihan');
    $assessment = $this->get('/operator/quizzes/' . $quiz->id)->assertOk()->assertSee('Periksa alat')->assertSee('quizEngine(', false);
    $result = $this->post('/operator/quizzes/' . $quiz->id . '/submit', ['answers' => [$quiz->questions->first()->id => 'a']])->assertOk();
    // Optional isolated fixtures for browser QA; never insert test enrollments in the local MySQL database.
    if (getenv('UI_CAPTURE_FIXTURES') === '1') {
        foreach (['material' => $material, 'assessment' => $assessment, 'result' => $result] as $name => $response) {
            File::ensureDirectoryExists(storage_path('app/ui-audit'));
            File::put(storage_path('app/ui-audit/' . $name . '.html'), $response->getContent());
        }
    }
});

test('operator dashboard enables fifteen minute kiosk inactivity timeout', function () {
    $employee = MManpower::create(['mp_id' => 'KIOSK-OP', 'nik' => 'KIOSK-OP', 'nama' => 'Operator Kiosk', 'departemen' => 'PRODUKSI', 'bagian' => 'OPERATOR', 'status' => true]);
    $user = User::create(['user_id' => $employee->mp_id, 'name' => $employee->nama, 'role' => 0]);

    $this->actingAs($user)->get('/operator/dashboard')
        ->assertOk()
        ->assertSee('data-kiosk-idle-timeout="900000"', false)
        ->assertSee('data-kiosk-logout-url="' . route('logout') . '"', false)
        ->assertSee('href="' . route('operator.profile') . '"', false)
        ->assertSee('aria-label="Lihat profil saya"', false)
        ->assertDontSee('>Profil saya<', false);
});

test('flash messages safely encode script markup and apostrophes', function () {
    $this->withSession(['success' => "O'Brien </script><script>alert(1)</script>"])->get('/login')
        ->assertOk()->assertDontSee("O'Brien </script><script>alert(1)</script>", false)->assertSee('O\\u0027Brien', false);
});

test('skill matrix department filter excludes inactive and unsynchronized manpower', function () {
    $admin = User::create(['user_id' => 'UI-ADMIN', 'name' => 'UI Admin', 'role' => 1, 'is_superadmin' => true, 'can_manage_competency' => true, 'can_manage_curriculum' => true]);
    $activeManpower = MManpower::create(['mp_id' => 'ACTIVE-P3', 'nik' => 'ACTIVE-P3', 'nama' => 'Aktif', 'departemen' => 'IT', 'bagian' => 'STAFF', 'status' => true, 'haris_synced_at' => now()]);
    MManpower::create(['mp_id' => 'INACTIVE-OUTSIDE', 'nik' => 'INACTIVE-OUTSIDE', 'nama' => 'Nonaktif', 'departemen' => 'WELDING - CIKARANG', 'bagian' => 'OPERATOR', 'status' => false, 'haris_synced_at' => now()]);
    MManpower::create(['mp_id' => 'LOCAL-DUMMY', 'nik' => 'LOCAL-DUMMY', 'nama' => 'Lokal', 'departemen' => 'DUMMY', 'bagian' => 'STAFF', 'status' => true]);

    $skill = MSkill::create(['kode_skill' => 'UI-PRODUCT', 'nama_skill' => 'Software Engineering', 'kategori' => 'IT']);
    $itProduct = Training::create(['kode_training' => 'IT-SE-01', 'skill_id' => $skill->id, 'departemen' => 'IT', 'level' => 1, 'judul' => 'Training Software Engineering', 'passing_grade' => 80, 'max_retries' => 3]);
    $qualityProduct = Training::create(['kode_training' => 'QC-01', 'skill_id' => $skill->id, 'departemen' => 'QUALITY CONTROL', 'level' => 1, 'judul' => 'Training Quality Control', 'passing_grade' => 80, 'max_retries' => 3]);
    TrainingEnrollment::create(['training_id' => $itProduct->id, 'manpower_id' => $activeManpower->id, 'status' => 'passed', 'ojt_status' => 'approved', 'post_test_completed_at' => now()]);

    $this->actingAs($admin)->get('/admin/dashboard?departemen=IT')
        ->assertOk()
        ->assertSee('Training Software Engineering')
        ->assertSee('Balok 2')
        ->assertDontSee('Training Quality Control')
        ->assertDontSee('WELDING - CIKARANG')
        ->assertDontSee('DUMMY');
});
