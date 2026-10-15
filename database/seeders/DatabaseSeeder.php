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
        $skill = MSkill::updateOrCreate(['kode_skill' => 'PRD-BOX-BATTERY'], [
            'nama_skill' => 'PRODUK BATTERY',
            'kategori' => 'PRODUKSI PLASTIK',
            'deskripsi' => 'Simulasi training bertingkat untuk produk battery di area Produksi Plastik.',
        ]);
        $this->seedTraining($skill, 'BOX-BATTERY-L1', 1, 'BOX BATTERY', 'Training dasar produk BOX BATTERY untuk manpower Produksi Plastik.', [
            ['Pengenalan BOX BATTERY', 'Kenali fungsi produk, standar visual, dan identitas BOX BATTERY sebelum memulai pekerjaan.'],
            ['Standar proses dan keselamatan', 'Ikuti SOP/WI, gunakan APD lengkap, jaga area kerja rapi, dan laporkan kondisi tidak normal kepada Leader.'],
        ], [
            ['Dokumen apa yang menjadi acuan utama saat menjalankan proses BOX BATTERY?', 'SOP/WI yang berlaku', 'Ingatan pribadi saja', 'Catatan training lain', 'Instruksi lisan tanpa acuan', 'a'],
            ['Jika menemukan hasil proses NG, tindakan yang tepat adalah?', 'Pisahkan dan laporkan sesuai prosedur', 'Campurkan dengan hasil OK', 'Kirim langsung ke proses berikutnya', 'Simpan tanpa penandaan', 'a'],
            ['Mengapa area kerja perlu menerapkan 5R?', 'Agar kerja aman, rapi, dan mudah dikendalikan', 'Agar waktu istirahat lebih lama', 'Agar tidak perlu inspeksi', 'Agar SOP tidak diperlukan', 'a'],
        ]);
        $this->seedTraining($skill, 'COVER-ASSY-BATTERY-L1', 1, 'COVER ASSY BATTERY', 'Training dasar perakitan COVER ASSY BATTERY sesuai standar kerja.', [
            ['Komponen dan standar visual', 'Kenali part, posisi pemasangan, serta kriteria OK dan NG pada COVER ASSY BATTERY.'],
            ['Urutan perakitan', 'Lakukan pemasangan sesuai urutan WI dan pastikan part tidak rusak sebelum diteruskan.'],
        ], [
            ['Apa yang harus dipastikan sebelum merakit COVER ASSY BATTERY?', 'Part sesuai standar dan tidak rusak', 'Part berjumlah banyak', 'Mesin sedang berhenti', 'Kemasan sudah penuh', 'a'],
            ['Jika posisi part tidak sesuai WI, apa yang dilakukan?', 'Hentikan dan laporkan kepada Leader', 'Lanjutkan sampai selesai', 'Ubah WI sendiri', 'Simpan sebagai produk OK', 'a'],
            ['Apa tujuan pemeriksaan visual setelah perakitan?', 'Memastikan hasil sesuai standar', 'Mempercepat pengiriman', 'Mengurangi penggunaan APD', 'Menggantikan SOP', 'a'],
        ]);
        $this->seedTraining($skill, 'FINAL-CHECK-BATTERY-L1', 1, 'FINAL CHECK BATTERY', 'Training dasar pemeriksaan akhir produk battery sebelum proses berikutnya.', [
            ['Standar final check', 'Pelajari titik pemeriksaan, kriteria OK/NG, dan metode pencatatan hasil final check.'],
            ['Penanganan produk NG', 'Pisahkan produk NG, beri identitas, dan laporkan sesuai alur pengendalian produk tidak sesuai.'],
        ], [
            ['Apa tujuan final check?', 'Memastikan produk memenuhi standar sebelum diteruskan', 'Menambah jumlah produksi', 'Menggantikan proses perakitan', 'Mengurangi pencatatan', 'a'],
            ['Bagaimana penanganan produk NG?', 'Pisahkan dan laporkan sesuai prosedur', 'Dicampur dengan produk OK', 'Dikirim tanpa pemeriksaan', 'Dibiarkan di area kerja', 'a'],
            ['Data apa yang dicatat pada pemeriksaan akhir?', 'Hasil pemeriksaan sesuai format yang berlaku', 'Nama semua operator', 'Waktu istirahat', 'Jumlah alat kerja', 'a'],
        ]);
        $this->seedTraining($skill, 'SETTING-PARAMETER-BATTERY-L2', 2, 'SETTING PARAMETER BOX BATTERY', 'Training Level 2 yang terbuka setelah semua training Level 1 mencapai Balok 2.', [
            ['Parameter proses', 'Kenali parameter yang digunakan pada proses BOX BATTERY dan batas standar yang diizinkan.'],
            ['Tindakan saat parameter tidak sesuai', 'Ikuti WI, hentikan proses bila diperlukan, dan komunikasikan kondisi kepada Leader.'],
        ], [
            ['Kapan parameter proses boleh diubah?', 'Sesuai WI dan arahan Leader', 'Kapan saja oleh operator', 'Saat target belum tercapai', 'Tanpa melihat standar', 'a'],
            ['Apa yang dilakukan bila parameter di luar standar?', 'Lapor kepada Leader dan ikuti prosedur', 'Tetap lanjutkan proses', 'Mengubah catatan hasil', 'Mengabaikan alarm', 'a'],
            ['Mengapa pencatatan parameter penting?', 'Menjaga proses sesuai standar dan mudah ditelusuri', 'Hanya untuk mengisi formulir', 'Menggantikan pemeriksaan visual', 'Agar tidak perlu SOP', 'a'],
        ]);
    }

    /** @param array<int, array{string, string}> $materials @param array<int, array{string, string, string, string, string, string}> $questions */
    private function seedTraining(MSkill $skill, string $code, int $level, string $title, string $description, array $materials, array $questions): void
    {
        $training = Training::updateOrCreate(['kode_training' => $code], ['skill_id' => $skill->id, 'departemen' => 'PRODUKSI PLASTIK', 'level' => $level, 'judul' => $title, 'deskripsi' => $description, 'max_retries' => 3, 'passing_grade' => 80, 'requires_ojt' => true]);
        foreach ($materials as $index => [$materialTitle, $content]) {
            TrainingMaterial::updateOrCreate(['training_id' => $training->id, 'urutan' => $index + 1], ['judul' => $materialTitle, 'tipe_media' => 'text', 'konten_teks' => $content]);
        }
        foreach (['pre_test' => 'Pre-test ' . $title, 'post_test' => 'Post-test ' . $title] as $type => $quizTitle) {
            $quiz = Quiz::updateOrCreate(['training_id' => $training->id, 'tipe' => $type], ['judul' => $quizTitle, 'durasi_menit' => $type === 'pre_test' ? 10 : 15, 'passing_grade' => 80, 'max_retries' => 3, 'cooldown_hours' => 24]);
            $this->seedQuestions($quiz, $questions);
        }
    }

    /** @param array<int, array{string, string, string, string, string, string}> $questions */
    private function seedQuestions(Quiz $quiz, array $questions): void
    {
        foreach ($questions as [$question, $optionA, $optionB, $optionC, $optionD, $answer]) {
            QuizQuestion::updateOrCreate(['quiz_id' => $quiz->id, 'pertanyaan' => $question], ['tipe_media' => 'none', 'opsi_a' => $optionA, 'opsi_b' => $optionB, 'opsi_c' => $optionC, 'opsi_d' => $optionD, 'kunci_jawaban' => $answer]);
        }
    }
}
