<?php

use App\Models\MSkill;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

function curriculumAdmin(): User
{
    return User::create([
        'user_id' => 'IMPORT-ADMIN',
        'name' => 'Admin Impor',
        'role' => 1,
        'is_superadmin' => true,
        'can_manage_curriculum' => true,
    ]);
}

test('competency spreadsheet import creates and updates skills by code', function () {
    $admin = curriculumAdmin();
    $file = UploadedFile::fake()->createWithContent('kompetensi.csv', implode("\n", [
        'kode_skill,nama_skill,kategori,deskripsi',
        'IT-SE-GIT,Dasar Pemrograman dan Git,Software Engineering,Dasar pengelolaan source code',
    ]));

    $preview = $this->actingAs($admin)
        ->post(route('admin.imports.store', 'skills'), ['file' => $file])
        ->assertOk()
        ->assertViewHas('preview');

    $this->post(route('admin.imports.confirm', 'skills'), [
        'preview_token' => $preview->viewData('preview')['token'],
    ])->assertRedirect(route('admin.imports.create', 'skills'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('m_skills', [
        'kode_skill' => 'IT-SE-GIT',
        'nama_skill' => 'Dasar Pemrograman dan Git',
    ]);

    $updateFile = UploadedFile::fake()->createWithContent('kompetensi.csv', implode("\n", [
        'kode_skill,nama_skill,kategori,deskripsi',
        'IT-SE-GIT,Git dan Kolaborasi Tim,Software Engineering,Deskripsi diperbarui',
    ]));

    $preview = $this->actingAs($admin)
        ->post(route('admin.imports.store', 'skills'), ['file' => $updateFile])
        ->assertOk()
        ->assertViewHas('preview');

    $this->post(route('admin.imports.confirm', 'skills'), [
        'preview_token' => $preview->viewData('preview')['token'],
    ])->assertSessionHas('success');

    expect(MSkill::where('kode_skill', 'IT-SE-GIT')->count())->toBe(1);
    $this->assertDatabaseHas('m_skills', ['kode_skill' => 'IT-SE-GIT', 'nama_skill' => 'Git dan Kolaborasi Tim']);
});

test('the competency import page is available to curriculum administrators', function () {
    $this->actingAs(curriculumAdmin())
        ->get(route('admin.imports.create', 'skills'))
        ->assertOk()
        ->assertSee('Impor master kompetensi')
        ->assertSee('Unduh template Excel');
});

test('question spreadsheet import rejects a training code that does not exist', function () {
    $file = UploadedFile::fake()->createWithContent('soal.csv', implode("\n", [
        'kode_training,tahap,judul_evaluasi,durasi_menit,nilai_lulus,batas_percobaan,masa_tunggu_jam,pertanyaan,opsi_a,opsi_b,opsi_c,opsi_d,kunci_jawaban',
        'TIDAK-ADA,post_test,Post-test,15,80,3,24,Apa fungsi Git?,A,B,C,D,a',
    ]));

    $this->actingAs(curriculumAdmin())
        ->post(route('admin.imports.store', 'questions'), ['file' => $file])
        ->assertOk()
        ->assertSee('Kode training tidak ditemukan.');

    expect(QuizQuestion::count())->toBe(0);
});
