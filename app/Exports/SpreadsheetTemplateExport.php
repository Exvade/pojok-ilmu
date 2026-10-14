<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SpreadsheetTemplateExport implements Export, WithMultipleSheets
{
    /** @param array{label: string, description: string, headings: array<int, string>, samples: array<int, array<int, string|int>>, notes: array<int, string>} $definition */
    public function __construct(private readonly array $definition) {}

    /** @return array<int, object> */
    public function sheets(): array
    {
        return [
            new SpreadsheetTemplateDataSheet($this->definition),
            new SpreadsheetTemplateGuideSheet($this->definition),
        ];
    }
}
