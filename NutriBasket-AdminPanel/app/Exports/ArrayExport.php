<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ArrayExport implements FromArray, WithHeadings
{
    protected $array;

    public function __construct(array $array)
    {
        $this->array = $array;
    }

    public function array(): array
    {
        // Ensure 0 values are displayed as 0.0, not blank
        return array_map(function($row) {
            return array_map(function($value) {
                // Handle null, empty string, or 0 values
                if ($value === null || $value === '' || $value === 0) {
                    return 0.0;
                }
                // Convert numeric values to float for proper display
                if (is_numeric($value)) {
                    $floatValue = (float)$value;
                    // Ensure 0.0 is returned for zero values
                    return $floatValue == 0 ? 0.0 : $floatValue;
                }
                return $value;
            }, $row);
        }, $this->array);
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