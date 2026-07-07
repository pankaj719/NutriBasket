<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StockDifferenceExport implements FromArray, WithHeadings, WithMapping
{
    protected $array;

    public function __construct(array $array)
    {
        $this->array = $array;
    }

    public function array(): array
    {
        return $this->array;
    }

    public function map($row): array
    {
        return [
            $row['#'],
            $row['Item ID'],
            $row['Item Name'],
            $row['Unit'],
            $this->formatNumber($row['Ordered Quantity']),
            $this->formatNumber($row['In Stock']),
            $this->formatNumber($row['Extra Needed']),
        ];
    }

    private function formatNumber($value)
    {
        if ($value === null || $value === '' || $value === 0) {
            return '0.0';
        }
        if (is_numeric($value)) {
            $floatValue = (float)$value;
            return $floatValue == 0 ? '0.0' : (string)$floatValue;
        }
        return $value;
    }

    public function headings(): array
    {
        return [
            '#',
            'Item ID',
            'Item Name',
            'Unit',
            'Ordered Quantity',
            'In Stock',
            'Extra Needed',
        ];
    }
}