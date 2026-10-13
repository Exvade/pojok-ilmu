<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SpreadsheetTemplateExport implements FromCollection, WithHeadings
{
    /** @param array<int, string> $headings @param array<int, array<int, string|int|null>> $rows */
    public function __construct(private readonly array $headings, private readonly array $rows) {}

    public function collection(): Collection
    {
        return collect($this->rows);
    }

    public function headings(): array
    {
        return $this->headings;
    }
}
