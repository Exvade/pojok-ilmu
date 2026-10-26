<?php

namespace Database\Seeders;

use App\Models\MManpower;
use App\Models\MSkill;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Training;
use App\Models\TrainingEnrollment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DemoPelatihanSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new RuntimeException('Data demo hanya boleh dibuat pada lingkungan nonproduksi.');
        }

        $nik = trim((string) getenv('DEMO_NIK'));
        if ($nik === '') {
            throw new RuntimeException('Tentukan NIK karyawan HaRis melalui variabel DEMO_NIK sebelum menjalankan seeder.');
        }

        DB::transaction(function () use ($nik): void {
            $manpower = MManpower::where('nik', $nik)->where('status', true)->whereNotNull('haris_synced_at')->first();
            if (! $manpower || blank($manpower->trainingDepartment())) {
                throw new RuntimeException('NIK tidak ditemukan, tidak aktif, belum tersinkron dari HaRis, atau belum memiliki departemen.');
            }

            $users = User::withTrashed()->whereIn('user_id', [$nik, $manpower->mp_id])->get();
            if ($users->contains(fn (User $user): bool => $user->trashed() || (int) $user->role !== 0 || $user->isManagement())) {
                throw new RuntimeException('Karyawan memiliki akun pengelola atau akun yang tidak aktif. Pilih karyawan dengan akses operator.');
            }

            foreach ([1 => 'Pengenalan Produk', 2 => 'Proses Produk', 3 => 'Pemeriksaan Produk'] as $level => $title) {
                $code = 'DEMO-HARIS-L' . $level;
                $trainingTitle = 'DEMO - ' . $title;
                $skill = MSkill::withTrashed()->where('kode_skill', $code)->first();
                $training = Training::withTrashed()->where('kode_training', $code)->first();
                if (($skill && ($skill->trashed() || $skill->nama_skill !== $trainingTitle)) || ($training && ($training->trashed() || $training->judul !== $trainingTitle || (int) $training->level !== $level || ! $skill || $training->skill_id !== $skill->id))) {
                    throw new RuntimeException('Kode ' . $code . ' sudah digunakan oleh data lain. Seeder dibatalkan tanpa mengubah data.');
                }
                if ($training && ! $training->appliesToDepartment($manpower->trainingDepartment())) {
                    throw new RuntimeException('Departemen karyawan tidak sesuai dengan training demo yang sudah ada. Seeder dibatalkan tanpa mengubah data.');
                }

                $skill ??= MSkill::create(['kode_skill' => $code, 'nama_skill' => $trainingTitle, 'kategori' => 'DEMO PELATIHAN']);
                $training ??= Training::create([
                    'kode_training' => $code, 'skill_id' => $skill->id, 'departemen' => $manpower->trainingDepartment(),
                    'level' => $level, 'judul' => $trainingTitle, 'deskripsi' => 'Data contoh. Tambahkan materi video sebelum digunakan untuk demonstrasi.',
                    'passing_grade' => 80, 'max_retries' => 3, 'requires_ojt' => true,
                ]);
                TrainingEnrollment::firstOrCreate(['training_id' => $training->id, 'manpower_id' => $manpower->id], ['status' => 'enrolled', 'ojt_status' => 'not_started']);

                foreach (['pre_test' => 'Pre-test', 'post_test' => 'Post-test'] as $type => $label) {
                    $quiz = Quiz::firstOrCreate(['training_id' => $training->id, 'tipe' => $type], [
                        'judul' => $label . ' ' . $trainingTitle, 'durasi_menit' => 15, 'passing_grade' => 80,
                        'max_retries' => 3, 'cooldown_hours' => 24,
                    ]);
                    foreach ($this->questions() as [$question, $a, $b, $c, $d]) {
                        QuizQuestion::firstOrCreate(['quiz_id' => $quiz->id, 'pertanyaan' => $question], [
                            'tipe_media' => 'none', 'opsi_a' => $a, 'opsi_b' => $b, 'opsi_c' => $c, 'opsi_d' => $d, 'kunci_jawaban' => 'a',
                        ]);
                    }
                }
            }
        });
    }

    /** @return array<int, array{string, string, string, string, string}> */
    private function questions(): array
    {
        return [
            ['Apa acuan utama dalam menjalankan pekerjaan?', 'SOP atau instruksi kerja yang berlaku', 'Perkiraan pribadi', 'Kebiasaan tanpa acuan', 'Instruksi yang belum disetujui'],
            ['Apa yang dilakukan ketika menemukan produk tidak sesuai?', 'Pisahkan dan laporkan sesuai prosedur', 'Campurkan dengan produk yang baik', 'Abaikan dan lanjutkan pekerjaan', 'Kirim tanpa pemeriksaan'],
            ['Mengapa alat pelindung diri harus digunakan?', 'Untuk mengurangi risiko kecelakaan kerja', 'Agar proses tidak perlu diperiksa', 'Untuk menggantikan instruksi kerja', 'Agar pekerjaan tidak perlu dicatat'],
        ];
    }
}
