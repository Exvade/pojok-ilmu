<?php

namespace App\Console\Commands;

use App\Models\MSkill;
use App\Models\Training;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PerbaruiDataSimulasi extends Command
{
    protected $signature = 'pojok-ilmu:perbarui-simulasi {--jalankan : Simpan perubahan setelah memeriksa hasil pratinjau}';

    protected $description = 'Perbarui nama data seeder pelatihan lama tanpa mengubah progres.';

    public function handle(): int
    {
        try {
            DB::transaction(function (): void {
                foreach ([1 => 'Pengenalan Produk', 2 => 'Proses Produk', 3 => 'Pemeriksaan Produk'] as $level => $title) {
                    $oldCode = 'DEMO-HARIS-L' . $level;
                    $newCode = 'TRN-PRODUK-L' . $level;
                    $skill = MSkill::withTrashed()->where('kode_skill', $oldCode)->first();
                    if (! $skill) {
                        continue;
                    }
                    $training = Training::withTrashed()->where('skill_id', $skill->id)->get();
                    if ($skill->trashed() || $training->count() !== 1 || $training->first()->trashed()) {
                        throw new RuntimeException("Kompetensi {$oldCode} terhapus atau digunakan oleh lebih dari satu training. Periksa data terlebih dahulu.");
                    }
                    $training = $training->first();
                    if (! in_array($training->kode_training, [$oldCode, $newCode], true) || (int) $training->level !== $level) {
                        throw new RuntimeException("Training pada kompetensi {$oldCode} telah berubah. Periksa data terlebih dahulu.");
                    }
                    if (MSkill::withTrashed()->where('kode_skill', $newCode)->where('id', '!=', $skill->id)->exists()
                        || Training::withTrashed()->where('kode_training', $newCode)->where('id', '!=', $training->id)->exists()) {
                        throw new RuntimeException("Kode {$newCode} sudah digunakan. Periksa data terlebih dahulu.");
                    }
                    $this->line("{$training->kode_training} → {$newCode}: {$title}");
                    if (! $this->option('jalankan')) {
                        continue;
                    }
                    $oldTitle = 'DEMO - ' . $title;
                    $skill->update([
                        'kode_skill' => $newCode,
                        'nama_skill' => $skill->nama_skill === $oldTitle ? $title : $skill->nama_skill,
                        'kategori' => $skill->kategori === 'DEMO PELATIHAN' ? 'Product Knowledge' : $skill->kategori,
                    ]);
                    $training->update([
                        'kode_training' => $newCode,
                        'judul' => $training->judul === $oldTitle ? $title : $training->judul,
                        'deskripsi' => $training->deskripsi === 'Data contoh. Tambahkan materi video sebelum digunakan untuk demonstrasi.'
                            ? 'Pelatihan pemahaman produk, proses kerja, dan pemeriksaan sesuai tingkat kompetensi.' : $training->deskripsi,
                    ]);
                    foreach (['pre_test' => 'Pre-test', 'post_test' => 'Post-test'] as $type => $label) {
                        $training->quizzes()->where('tipe', $type)->where('judul', $label . ' ' . $oldTitle)
                            ->update(['judul' => $label . ' ' . $title]);
                    }
                }
            });
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info($this->option('jalankan') ? 'Pembaruan selesai.' : 'Pratinjau selesai. Tambahkan --jalankan untuk menyimpan perubahan.');

        return self::SUCCESS;
    }
}
