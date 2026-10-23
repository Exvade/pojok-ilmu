<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TrainingProgressDetailSheet implements FromArray, WithColumnWidths, WithEvents, WithHeadings, WithTitle
{
    /** @param array<int, array<int, string|int>> $rows */
    public function __construct(private readonly array $rows) {}

    /** @return array<int, array<int, string|int>> */
    public function array(): array
    {
        return $this->rows;
    }

    /** @return array<int, string> */
    public function headings(): array
    {
        return ['NIK', 'Nama karyawan', 'Kelompok departemen', 'Departemen HaRis', 'Bagian', 'Kode training', 'Training', 'Level', 'Status', 'Lulus post-test', 'Penilaian OJT', 'Diperbarui'];
    }

    /** @return array<string, int> */
    public function columnWidths(): array
    {
        return ['A' => 18, 'B' => 30, 'C' => 28, 'D' => 28, 'E' => 28, 'F' => 22, 'G' => 34, 'H' => 10, 'I' => 20, 'J' => 22, 'K' => 22, 'L' => 22];
    }

    public function title(): string
    {
        return 'Detail penugasan';
    }

    /** @return array<class-string, callable> */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $lastRow = count($this->rows) + 1;
                $sheet->setShowGridlines(false);
                $sheet->freezePane('A2');
                $sheet->setAutoFilter("A1:L{$lastRow}");
                $sheet->getRowDimension(1)->setRowHeight(28);
                $sheet->getStyle('A1:L1')->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '14532D']],
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'name' => 'Arial', 'size' => 10],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                if ($lastRow > 1) {
                    $sheet->getStyle("A2:L{$lastRow}")->applyFromArray([
                        'font' => ['name' => 'Arial', 'size' => 10, 'color' => ['rgb' => '1F2937']],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                        'borders' => ['allBorders' => ['style' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']]],
                    ]);
                    $sheet->getStyle("A2:L{$lastRow}")->getAlignment()->setWrapText(true);
                }
            },
        ];
    }
}
