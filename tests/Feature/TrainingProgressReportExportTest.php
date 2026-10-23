<?php

use App\Exports\TrainingProgressDetailSheet;
use App\Exports\TrainingProgressReportExport;
use App\Exports\TrainingProgressSummarySheet;
use Maatwebsite\Excel\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

test('training progress export provides shareable summary and detail sheets', function () {
    $export = new TrainingProgressReportExport(
        [['NIK-001', 'Dani Rusdani', 'PRODUKSI', 'PRODUKSI PLASTIK A', 'OPERATOR', 'TR-001', 'BOX BATTERY', 1, 'Menunggu OJT', '22 Okt 2026, 08:00', '-', '22 Okt 2026, 08:15']],
        ['total' => 6, 'not_started' => 1, 'in_progress' => 2, 'pending_ojt' => 1, 'retry_ojt' => 1, 'block_two' => 1],
        ['departemen' => 'PRODUKSI', 'search' => '', 'status' => 'Menunggu OJT'],
        '22 Okt 2026, 10:00',
    );

    $sheets = $export->sheets();

    expect($sheets)->toHaveCount(2)
        ->and($sheets[0])->toBeInstanceOf(TrainingProgressSummarySheet::class)
        ->and($sheets[0]->title())->toBe('Ringkasan')
        ->and($sheets[0]->array())->toContain(['Total penugasan', 6])
        ->and($sheets[1])->toBeInstanceOf(TrainingProgressDetailSheet::class)
        ->and($sheets[1]->title())->toBe('Detail penugasan')
        ->and($sheets[1]->headings())->toContain('NIK')
        ->and($sheets[1]->headings())->toContain('Status');

    $temporaryPath = tempnam(sys_get_temp_dir(), 'training-progress-');
    file_put_contents($temporaryPath, app(Excel::class)->raw($export, Excel::XLSX));

    try {
        $workbook = IOFactory::load($temporaryPath);

        expect($workbook->getSheetNames())->toBe(['Ringkasan', 'Detail penugasan'])
            ->and($workbook->getSheetByName('Ringkasan')->getCell('A1')->getValue())->toBe('LAPORAN PROGRES TRAINING')
            ->and($workbook->getSheetByName('Ringkasan')->getCell('B9')->getValue())->toBe(6)
            ->and($workbook->getSheetByName('Detail penugasan')->getCell('I2')->getValue())->toBe('Menunggu OJT');
    } finally {
        unlink($temporaryPath);
    }
});
