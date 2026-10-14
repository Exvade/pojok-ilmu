<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SpreadsheetTemplateDataSheet implements FromArray, WithColumnWidths, WithEvents, WithHeadings, WithTitle
{
    private const INPUT_ROW_COUNT = 100;

    /** @param array{label: string, description: string, headings: array<int, string>, samples: array<int, array<int, string|int>>, notes: array<int, string>} $definition */
    public function __construct(private readonly array $definition) {}

    /** @return array<int, array<int, string>> */
    public function array(): array
    {
        return array_fill(0, self::INPUT_ROW_COUNT, array_fill(0, count($this->definition['headings']), ''));
    }

    /** @return array<int, string> */
    public function headings(): array
    {
        return $this->definition['headings'];
    }

    /** @return array<string, int> */
    public function columnWidths(): array
    {
        return collect($this->definition['headings'])
            ->mapWithKeys(fn (string $heading, int $index): array => [Coordinate::stringFromColumnIndex($index + 1) => $this->columnWidth($heading)])
            ->all();
    }

    public function title(): string
    {
        return 'Data impor';
    }

    /** @return array<class-string, callable> */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = Coordinate::stringFromColumnIndex(count($this->definition['headings']));
                $lastRow = self::INPUT_ROW_COUNT + 1;
                $headerRange = "A1:{$lastColumn}1";
                $inputRange = "A2:{$lastColumn}{$lastRow}";

                $sheet->setShowGridlines(false);
                $sheet->freezePane('A2');
                $sheet->setAutoFilter($headerRange);
                $sheet->getRowDimension(1)->setRowHeight(28);
                $sheet->getStyle($headerRange)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '14532D']],
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'name' => 'Arial', 'size' => 10],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getStyle($inputRange)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFBEB']],
                    'font' => ['name' => 'Arial', 'size' => 10, 'color' => ['rgb' => '1F2937']],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['bottom' => ['style' => 'thin', 'color' => ['rgb' => 'E5E7EB']]],
                ]);
                $sheet->getStyle("A1:{$lastColumn}{$lastRow}")->getAlignment()->setWrapText(true);

                foreach ($this->definition['headings'] as $index => $heading) {
                    $column = Coordinate::stringFromColumnIndex($index + 1);
                    $sheet->getRowDimension(2)->setRowHeight(22);

                    if (in_array($heading, ['npk', 'kode_training', 'kode_skill', 'kunci_jawaban'], true)) {
                        $sheet->getStyle("{$column}2:{$column}{$lastRow}")->getNumberFormat()->setFormatCode('@');
                    }

                    $this->applyValidation($sheet, $column, $heading, $lastRow);
                }
            },
        ];
    }

    private function columnWidth(string $heading): int
    {
        return match ($heading) {
            'deskripsi', 'konten_teks', 'pertanyaan' => 42,
            'judul', 'judul_materi', 'judul_evaluasi' => 30,
            'url_media' => 48,
            'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d' => 28,
            'departemen', 'kategori' => 26,
            default => 18,
        };
    }

    private function applyValidation(object $sheet, string $column, string $heading, int $lastRow): void
    {
        $allowedValues = match ($heading) {
            'level' => ['1', '2', '3', '4'],
            'tipe_media' => ['text', 'video_youtube', 'video_gdrive', 'image', 'pdf'],
            'kunci_jawaban' => ['a', 'b', 'c', 'd'],
            default => [],
        };

        if ($allowedValues === []) {
            return;
        }

        $validation = $sheet->getCell("{$column}2")->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(true);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setErrorTitle('Nilai tidak valid');
        $validation->setError('Pilih nilai dari daftar yang tersedia.');
        $validation->setPromptTitle('Pilihan tersedia');
        $validation->setPrompt('Gunakan pilihan yang tersedia pada daftar.');
        $validation->setFormula1('"' . implode(',', $allowedValues) . '"');
        $sheet->setDataValidation("{$column}2:{$column}{$lastRow}", $validation);
    }
}
