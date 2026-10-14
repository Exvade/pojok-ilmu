<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SpreadsheetTemplateGuideSheet implements FromArray, WithEvents, WithTitle
{
    /** @param array{label: string, description: string, headings: array<int, string>, samples: array<int, array<int, string|int>>, notes: array<int, string>} $definition */
    public function __construct(private readonly array $definition) {}

    /** @return array<int, array<int, string|int>> */
    public function array(): array
    {
        $sample = $this->definition['samples'][0] ?? [];
        $rows = [
            ['Panduan template impor ' . ucfirst($this->definition['label'])],
            [$this->definition['description']],
            [],
            ['Langkah pengisian'],
            ['1. Isi hanya pada sheet Data impor mulai baris 2.'],
            ['2. Jangan mengubah nama header pada baris pertama.'],
            ['3. Kolom berwarna kuning adalah area yang dapat diisi.'],
            ['4. Hapus baris kosong tidak diperlukan sebelum mengimpor.'],
            [],
            ['Kolom', 'Wajib diisi', 'Cara pengisian', 'Contoh pengisian'],
        ];

        foreach ($this->definition['headings'] as $index => $heading) {
            $rows[] = [$heading, $this->isRequired($heading) ? 'Ya' : 'Tidak', $this->fieldGuidance($heading), $sample[$index] ?? ''];
        }

        $rows[] = [];
        $rows[] = ['Catatan'];
        foreach ($this->definition['notes'] as $note) {
            $rows[] = [$note];
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Petunjuk';
    }

    /** @return array<class-string, callable> */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $sheet->setShowGridlines(false);
                $sheet->mergeCells('A1:D1');
                $sheet->mergeCells('A2:D2');
                $sheet->getColumnDimension('A')->setWidth(28);
                $sheet->getColumnDimension('B')->setWidth(16);
                $sheet->getColumnDimension('C')->setWidth(42);
                $sheet->getColumnDimension('D')->setWidth(52);
                $sheet->getRowDimension(1)->setRowHeight(28);
                $sheet->getStyle('A1:D1')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '14532D']],
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'name' => 'Arial', 'size' => 14],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getStyle('A2:D2')->applyFromArray([
                    'font' => ['italic' => true, 'color' => ['rgb' => '4B5563'], 'name' => 'Arial', 'size' => 10],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                ]);
                $sheet->getStyle('A4:D4')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '14532D'], 'name' => 'Arial', 'size' => 11],
                ]);
                $sheet->getStyle('A10:D10')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '14532D']],
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'name' => 'Arial', 'size' => 10],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getStyle('A11:D' . (10 + count($this->definition['headings'])))->applyFromArray([
                    'font' => ['name' => 'Arial', 'size' => 10],
                    'borders' => ['bottom' => ['style' => 'thin', 'color' => ['rgb' => 'E5E7EB']]],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                ]);
                $notesRow = 12 + count($this->definition['headings']);
                $sheet->getStyle("A{$notesRow}:D{$notesRow}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'DCFCE7']],
                    'font' => ['bold' => true, 'color' => ['rgb' => '14532D'], 'name' => 'Arial', 'size' => 10],
                ]);
                $sheet->getStyle('A1:D' . ($notesRow + count($this->definition['notes'])))->getAlignment()->setWrapText(true);
            },
        ];
    }

    private function isRequired(string $heading): bool
    {
        return ! in_array($heading, ['deskripsi', 'konten_teks', 'url_media'], true);
    }

    private function fieldGuidance(string $heading): string
    {
        return match ($heading) {
            'departemen' => 'Gunakan nama HaRis. Pisahkan beberapa departemen dengan ; atau isi SEMUA untuk training umum.',
            'level' => 'Pilih angka 1 sampai 4.',
            'nilai_lulus' => 'Masukkan angka 50 sampai 100.',
            'batas_percobaan', 'durasi_menit', 'masa_tunggu_jam', 'urutan' => 'Masukkan angka bulat positif.',
            'tipe_media' => 'Pilih dari daftar: text, video_youtube, video_gdrive, image, atau pdf.',
            'url_media' => 'Wajib untuk video, gambar, atau PDF. Kosongkan untuk materi teks.',
            'kunci_jawaban' => 'Pilih satu huruf: a, b, c, atau d.',
            'npk' => 'Gunakan NPK yang sama persis dengan data HaRis.',
            default => 'Isi sesuai data yang ingin diimpor.',
        };
    }
}
