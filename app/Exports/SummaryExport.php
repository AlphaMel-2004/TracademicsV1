<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SummaryExport implements FromArray, WithHeadings
{
    public function __construct(private array $rows)
    {
    }

    public function array(): array
    {
        return $this->rows ?: [
            ['Faculty','Program','Department','Term','Compiled','Not Compiled','Not Applicable'],
        ];
    }

    public function headings(): array
    {
        return ['Faculty','Program','Department','Term','Compiled','Not Compiled','Not Applicable'];
    }
}


