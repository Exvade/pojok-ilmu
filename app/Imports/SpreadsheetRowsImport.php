<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SpreadsheetRowsImport implements ToCollection, WithHeadingRow, WithMultipleSheets
{
    /** @var Collection<int, Collection<string, mixed>> */
    public Collection $rows;

    public function __construct()
    {
        $this->rows = collect();
    }

    /** @param Collection<int, Collection<string, mixed>> $rows */
    public function collection(Collection $rows): void
    {
        $this->rows = $rows;
    }

    public function sheets(): array
    {
        return [0 => $this];
    }
}
