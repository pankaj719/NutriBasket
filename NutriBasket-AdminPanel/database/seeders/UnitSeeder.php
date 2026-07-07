<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $units = [
            'KG',
            'Piece',
            'Liter',
            'Gram',
            'Milliliter',
            'Pack',
            'Box',
            'Bottle',
            'Can',
            'Bag',
            'Dozen',
            'Unit',
            'Meter',
            'Centimeter',
            'Inch',
            'Foot',
            'Yard',
            'Mile',
            'Kilometer',
            'Pound',
            'Ounce',
            'Ton',
            'Gallon',
            'Quart',
            'Pint',
            'Cup',
            'Tablespoon',
            'Teaspoon',
            'Pair',
            'Set',
            'Bundle',
            'Roll',
            'Sheet',
            'Block',
            'Slice',
            'Portion',
            'Serving',
            'Container',
            'Jar',
            'Tub',
            'Carton'
        ];

        foreach ($units as $unit) {
            Unit::firstOrCreate(['unit' => $unit]);
        }
    }
} 