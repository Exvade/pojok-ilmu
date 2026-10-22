<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TrainingProgressReportExport implements Export, WithMultipleSheets
{
    /**
     * @param  array<int, array<int, string|int>>  $rows
     * @param  array{total: int, not_started: int, in_progress: int, pending_ojt: int, retry_ojt: int, block_two: int}  $summary
     * @param  array{departemen: string, search: string, status: string}  $filters
     */
    public function __construct(
        private readonly array $rows,
        private readonly array $summary = ['total' => 0, 'not_started' => 0, 'in_progress' => 0, 'pending_ojt' => 0, 'retry_ojt' => 0, 'block_two' => 0],
        private readonly array $filters = ['departemen' => '', 'search' => '', 'status' => ''],
        private readonly string $generatedAt = '',
    ) {}

    /** @return array<int, TrainingProgressSummarySheet|TrainingProgressDetailSheet> */
    public function sheets(): array
    {
        return [
            new TrainingProgressSummarySheet($this->summary, $this->filters, $this->generatedAt),
            new TrainingProgressDetailSheet($this->rows),
        ];
    }
}
