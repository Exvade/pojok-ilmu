<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TrainingProgressSummarySheet implements FromArray, WithColumnWidths, WithEvents, WithTitle
{
    /**
     * @param  array{total: int, not_started: int, in_progress: int, pending_ojt: int, retry_ojt: int, block_two: int}  $summary
     * @param  array{departemen: string, search: string, status: string}  $filters
     */
    public function __construct(
        private readonly array $summary,
        private readonly array $filters,
        private readonly string $generatedAt,
    ) {}

    /** @return array<int, array<int, string|int>> */
    public function array(): array
    {
        return [
            ['LAPORAN PROGRES TRAINING'],
            ['Ringkasan penugasan training sesuai filter yang dipilih.'],
            ['Filter laporan', 'Nilai'],
            ['Departemen', $this->filters['departemen'] !== '' ? $this->filters['departemen'] : 'Semua departemen'],
            ['Status', $this->filters['status'] !== '' ? $this->filters['status'] : 'Semua status'],
            ['Pencarian', $this->filters['search'] !== '' ? $this->filters['search'] : 'Semua data'],
            ['Dibuat pada', $this->generatedAt],
            ['STATUS PENUGASAN', 'JUMLAH'],
            ['Total penugasan', $this->summary['total']],
            ['Belum mulai', $this->summary['not_started']],
            ['Dalam proses', $this->summary['in_progress']],
            ['Menunggu OJT', $this->summary['pending_ojt']],
            ['Perlu OJT ulang', $this->summary['retry_ojt']],
            ['Sudah Balok 2', $this->summary['block_two']],
        ];
    }

    /** @return array<string, int> */
    public function columnWidths(): array
    {
        return ['A' => 30, 'B' => 40];
    }

    public function title(): string
    {
        return 'Ringkasan';
    }

    /** @return array<class-string, callable> */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $sheet->setShowGridlines(false);
                $sheet->mergeCells('A1:B1');
                $sheet->mergeCells('A2:B2');
                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->getStyle('A1:B1')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '14532D']],
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'name' => 'Arial', 'size' => 14],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getStyle('A2:B2')->applyFromArray([
                    'font' => ['italic' => true, 'color' => ['rgb' => '6B7280'], 'name' => 'Arial', 'size' => 10],
                ]);
                $sheet->getStyle('A3:B3')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'DCFCE7']],
                    'font' => ['bold' => true, 'color' => ['rgb' => '14532D'], 'name' => 'Arial', 'size' => 10],
                ]);
                $sheet->getStyle('A8:B8')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '14532D']],
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'name' => 'Arial', 'size' => 10],
                ]);
                $sheet->getStyle('A3:B7')->applyFromArray([
                    'font' => ['name' => 'Arial', 'size' => 10, 'color' => ['rgb' => '1F2937']],
                    'borders' => ['allBorders' => ['style' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']]],
                ]);
                $sheet->getStyle('A8:B14')->applyFromArray([
                    'font' => ['name' => 'Arial', 'size' => 10, 'color' => ['rgb' => '1F2937']],
                    'borders' => ['allBorders' => ['style' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']]],
                ]);
                $sheet->getStyle('A8:B8')->getFont()->getColor()->setRGB('FFFFFF');
                $sheet->getStyle('B9:B14')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('B9:B14')->getNumberFormat()->setFormatCode('#,##0');
            },
        ];
    }
}
