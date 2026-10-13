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
        // Akun berasal dari HaRis. Jalankan haris:sync-manpower lalu
        // pojok-ilmu:bootstrap-superadmin untuk menetapkan superadmin.

        // 1. Seed Skills
        $skill1 = MSkill::create([
            'kode_skill' => 'SKILL-KZN-01',
            'nama_skill' => 'Pengenalan Kaizen',
            'kategori' => 'Continues Improvement (CI)',
            'deskripsi' => 'Dasar-dasar filosofi Kaizen dan pemahaman budaya 5S di lingkungan kerja.',
        ]);

        $skill2 = MSkill::create([
            'kode_skill' => 'SKILL-KZN-02',
            'nama_skill' => 'Tata Cara Kaizen',
            'kategori' => 'Continues Improvement (CI)',
            'deskripsi' => 'Penerapan ide perbaikan mandiri di stasiun kerja.',
        ]);

        $skill3 = MSkill::create([
            'kode_skill' => 'SKILL-PRT-01',
            'nama_skill' => 'Kualifikasi Part COVER FUEL TANK (Product Knowledge)',
            'kategori' => 'Kualifikasi Part & Mesin',
            'deskripsi' => 'Pemahaman standar mutu, penanganan part COVER FUEL TANK, dan deteksi cacat visual.',
        ]);

        // 3. Seed Training Programs & Materials
        $training1 = Training::create([
            'kode_training' => 'TRN-KZN-L1',
            'skill_id' => $skill1->id,
            'level' => 1,
            'judul' => 'Modul 1: Konsep Dasar Kaizen & Eliminasi 7 Waste',
            'deskripsi' => 'Modul pembelajaran mandiri pengenalan budaya Kaizen dan eliminasi pemborosan di area produksi.',
            'max_retries' => 3,
            'passing_grade' => 80,
        ]);

        // Materials for Training 1 (Text, Image, Video GDrive)
        TrainingMaterial::create([
            'training_id' => $training1->id,
            'urutan' => 1,
            'judul' => 'Pengantar Kaizen & Budaya Kerja MWT',
            'tipe_media' => 'text',
            'konten_teks' => 'Kaizen berarti perbaikan berkesinambungan yang melibatkan seluruh karyawan dari manajemen hingga operator. Di PT MWT, Kaizen merupakan pilar utama keberhasilan produksi.',
        ]);

        TrainingMaterial::create([
            'training_id' => $training1->id,
            'urutan' => 2,
            'judul' => 'Video Demonstrasi: Penerapan Kaizen di Line Assembly',
            'tipe_media' => 'video_gdrive',
            'konten_teks' => 'Simak video berdurasi singkat berikut mengenai contoh riil perbaikan metode kerja di line assembly.',
            'media_url' => 'https://drive.google.com/file/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs/view', // Sample GDrive ID link
        ]);

        // Quiz for Training 1
        $quiz1 = Quiz::create([
            'training_id' => $training1->id,
            'judul' => 'Assessment Level 1: Pengenalan Kaizen',
            'durasi_menit' => 10,
            'passing_grade' => 80,
            'max_retries' => 3,
        ]);

        // Quiz Questions
        QuizQuestion::create([
            'quiz_id' => $quiz1->id,
            'pertanyaan' => 'Apa arti kata "Kaizen" secara harfiah dalam sistem manajemen mutu MWT?',
            'tipe_media' => 'none',
            'opsi_a' => 'Perubahan yang drastis dan mahal',
            'opsi_b' => 'Perbaikan berkesinambungan untuk menjadi lebih baik',
            'opsi_c' => 'Pengantian mesin lama dengan mesin baru',
            'opsi_d' => 'Penambahan jam kerja lembur',
            'kunci_jawaban' => 'b',
        ]);

        QuizQuestion::create([
            'quiz_id' => $quiz1->id,
            'pertanyaan' => 'Manakah di bawah ini yang BUKAN merupakan jenis pemborosan (Waste) di area produksi?',
            'tipe_media' => 'none',
            'opsi_a' => 'Overproduction (Produksi Berlebih)',
            'opsi_b' => 'Waiting (Waktu Menunggu)',
            'opsi_c' => 'Standardization (Standardisasi Proses)',
            'opsi_d' => 'Defect (Produk Cacat)',
            'kunci_jawaban' => 'c',
        ]);

        QuizQuestion::create([
            'quiz_id' => $quiz1->id,
            'pertanyaan' => 'Berdasarkan video pembelajaran Kaizen yang telah disimak, apa langkah awal sebelum melakukan perbaikan stasiun kerja?',
            'tipe_media' => 'video_gdrive',
            'media_url' => 'https://drive.google.com/file/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs/view',
            'opsi_a' => 'Mengidentifikasi masalah dan memotret area kerja',
            'opsi_b' => 'Membuang seluruh perkakas lama',
            'opsi_c' => 'Menghentikan seluruh line produksi selama seminggu',
            'opsi_d' => 'Menunggu instruksi langsung dari direksi',
            'kunci_jawaban' => 'a',
        ]);
    }
}
