<?php

namespace Database\Seeders;

use App\Models\MSkill;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Training;
use App\Models\TrainingMaterial;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $skill = MSkill::create([
            'kode_skill' => 'PRD-BOX-BATTERY',
            'nama_skill' => 'BOX BATTERY',
            'kategori' => 'PRODUKSI PLASTIK',
            'deskripsi' => 'Pengetahuan dasar training BOX BATTERY, standar kerja, kualitas, dan keselamatan kerja.',
        ]);
        $training = Training::create([
            'kode_training' => 'BOX-BATTERY-L1',
            'skill_id' => $skill->id,
            'departemen' => 'PRODUKSI PLASTIK',
            'level' => 1,
            'judul' => 'BOX BATTERY',
            'deskripsi' => 'Training BOX BATTERY untuk manpower Produksi Plastik.',
            'max_retries' => 3,
            'passing_grade' => 80,
            'requires_ojt' => true,
        ]);
        foreach ([
            ['Pengenalan BOX BATTERY', 'Kenali nama training, fungsi, standar visual, dan identitas BOX BATTERY sebelum memulai pekerjaan.'],
            ['Standar proses dan keselamatan', 'Ikuti SOP/WI yang berlaku, gunakan APD lengkap, jaga area kerja tetap rapi, dan laporkan kondisi tidak normal kepada Leader.'],
        ] as $index => [$title, $content]) {
            TrainingMaterial::create(['training_id' => $training->id, 'urutan' => $index + 1, 'judul' => $title, 'tipe_media' => 'text', 'konten_teks' => $content]);
        }
        $preTest = Quiz::create(['training_id' => $training->id, 'tipe' => 'pre_test', 'judul' => 'Pre-test BOX BATTERY', 'durasi_menit' => 10, 'passing_grade' => 80, 'max_retries' => 3, 'cooldown_hours' => 24]);
        $postTest = Quiz::create(['training_id' => $training->id, 'tipe' => 'post_test', 'judul' => 'Post-test BOX BATTERY', 'durasi_menit' => 15, 'passing_grade' => 80, 'max_retries' => 3, 'cooldown_hours' => 24]);
        $this->seedQuestions($preTest, [
            ['Apa yang perlu diperiksa sebelum memulai proses BOX BATTERY?', 'SOP/WI, APD, dan kondisi area kerja', 'Langsung menyalakan mesin', 'Menunggu kondisi tidak sesuai', 'Melewati pemeriksaan awal', 'a'],
            ['Kepada siapa kondisi tidak normal pada proses harus dilaporkan?', 'Leader atau atasan terkait', 'Pelanggan', 'Petugas keamanan', 'Tidak perlu dilaporkan', 'a'],
            ['Apa tujuan menggunakan APD?', 'Melindungi keselamatan saat bekerja', 'Mempercepat cycle time', 'Menggantikan SOP', 'Menambah jumlah produksi', 'a'],
        ]);
        $this->seedQuestions($postTest, [
            ['Dokumen apa yang menjadi acuan utama saat menjalankan proses BOX BATTERY?', 'SOP/WI yang berlaku', 'Ingatan pribadi saja', 'Catatan training lain', 'Instruksi lisan tanpa acuan', 'a'],
            ['Jika menemukan hasil proses NG, tindakan yang tepat adalah?', 'Pisahkan dan laporkan sesuai prosedur', 'Campurkan dengan hasil proses OK', 'Kirim langsung ke proses berikutnya', 'Simpan tanpa penandaan', 'a'],
            ['Mengapa area kerja perlu menerapkan 5R?', 'Agar kerja aman, rapi, dan mudah dikendalikan', 'Agar waktu istirahat lebih lama', 'Agar tidak perlu inspeksi', 'Agar SOP tidak diperlukan', 'a'],
            ['Sebelum hasil proses diteruskan ke proses berikutnya, apa yang harus dipastikan?', 'Hasil proses memenuhi standar pemeriksaan', 'Jumlah hasil proses sebanyak mungkin', 'Kemasan sudah penuh', 'Mesin sudah dimatikan', 'a'],
            ['Bila instruksi kerja tidak jelas, apa yang dilakukan?', 'Bertanya kepada Leader sebelum melanjutkan', 'Melanjutkan berdasarkan perkiraan', 'Mengabaikan instruksi', 'Mengubah standar sendiri', 'a'],
        ]);
    }

    /** @param array<int, array{string, string, string, string, string, string}> $questions */
    private function seedQuestions(Quiz $quiz, array $questions): void
    {
        foreach ($questions as [$question, $optionA, $optionB, $optionC, $optionD, $answer]) {
            QuizQuestion::create(['quiz_id' => $quiz->id, 'pertanyaan' => $question, 'tipe_media' => 'none', 'opsi_a' => $optionA, 'opsi_b' => $optionB, 'opsi_c' => $optionC, 'opsi_d' => $optionD, 'kunci_jawaban' => $answer]);
        }
    }
}
