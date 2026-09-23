<?php

namespace Database\Seeders;

use App\Models\MManpower;
use App\Models\MSkill;
use App\Models\MSkillTargetLevel;
use App\Models\OperatorSkillMatrix;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Training;
use App\Models\TrainingEnrollment;
use App\Models\TrainingMaterial;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Manpower & Users
        $adminMp = MManpower::create([
            'mp_id' => 'ADMIN001',
            'nik' => '990001',
            'nama' => 'Administrator MWT',
            'departemen' => 'IT & HRD',
            'bagian' => 'System Administrator',
            'status' => true,
        ]);

        User::create([
            'user_id' => 'ADMIN001',
            'name' => 'Administrator MWT',
            'email' => 'admin@mada.co.id',
            'password' => Hash::make('2025'),
            'role' => 1, // Superadmin/Admin
            'is_superadmin' => true,
        ]);

        $hrMp = MManpower::create([
            'mp_id' => 'HR001',
            'nik' => '990002',
            'nama' => 'Siti Supervisor HR',
            'departemen' => 'HRD',
            'bagian' => 'Training & Development',
            'status' => true,
        ]);

        User::create([
            'user_id' => 'HR001',
            'name' => 'Siti Supervisor HR',
            'email' => 'hr@mada.co.id',
            'password' => Hash::make('123456'),
            'role' => 2, // Management/HR
            'is_superadmin' => false,
        ]);

        // Operator 1: Budi Trainee (OP001)
        $budiMp = MManpower::create([
            'mp_id' => 'OP001',
            'nik' => '100001',
            'nama' => 'BUDI TRAINEE',
            'departemen' => 'PRODUKSI PLASTIK',
            'bagian' => 'ASSEMBLY',
            'status' => true,
        ]);

        User::create([
            'user_id' => 'OP001',
            'name' => 'BUDI TRAINEE',
            'email' => 'budi@mada.co.id',
            'password' => null, // Operator login scan/NPK
            'role' => 0, // Operator
            'is_superadmin' => false,
        ]);

        // Operator 2: Ani Operator (OP002)
        $aniMp = MManpower::create([
            'mp_id' => 'OP002',
            'nik' => '100002',
            'nama' => 'ANI OPERATOR',
            'departemen' => 'PRODUKSI PLASTIK',
            'bagian' => 'INJECTION',
            'status' => true,
        ]);

        User::create([
            'user_id' => 'OP002',
            'name' => 'ANI OPERATOR',
            'email' => 'ani@mada.co.id',
            'password' => null,
            'role' => 0,
            'is_superadmin' => false,
        ]);

        // 2. Seed Skills
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

        // 3. Seed Target Levels
        MSkillTargetLevel::create([
            'departemen' => 'PRODUKSI PLASTIK',
            'bagian' => 'ASSEMBLY',
            'skill_id' => $skill1->id,
            'target_level' => 2,
        ]);

        MSkillTargetLevel::create([
            'departemen' => 'PRODUKSI PLASTIK',
            'bagian' => 'ASSEMBLY',
            'skill_id' => $skill2->id,
            'target_level' => 2,
        ]);

        MSkillTargetLevel::create([
            'departemen' => 'PRODUKSI PLASTIK',
            'bagian' => 'ASSEMBLY',
            'skill_id' => $skill3->id,
            'target_level' => 3,
        ]);

        // 4. Initial Skill Matrix Budi Trainee
        $matrix1 = OperatorSkillMatrix::create([
            'manpower_id' => $budiMp->id,
            'skill_id' => $skill1->id,
            'current_level' => 0,
            'target_level' => 2,
            'status_gap' => 'Gap -2 Level',
            'last_updated_at' => now(),
        ]);

        $matrix2 = OperatorSkillMatrix::create([
            'manpower_id' => $budiMp->id,
            'skill_id' => $skill2->id,
            'current_level' => 0,
            'target_level' => 2,
            'status_gap' => 'Gap -2 Level',
            'last_updated_at' => now(),
        ]);

        $matrix3 = OperatorSkillMatrix::create([
            'manpower_id' => $budiMp->id,
            'skill_id' => $skill3->id,
            'current_level' => 1,
            'target_level' => 3,
            'status_gap' => 'Gap -2 Level',
            'last_updated_at' => now(),
        ]);

        // 5. Seed Training Programs & Materials
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

        // 6. Auto-Enroll Budi Trainee to TNA Training 1
        TrainingEnrollment::create([
            'manpower_id' => $budiMp->id,
            'training_id' => $training1->id,
            'status' => 'enrolled',
            'current_retry_count' => 0,
        ]);
    }
}
