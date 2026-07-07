<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WastageCategory;

class WastageCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Expired',
                'description' => 'Items that have passed their expiration date',
                'is_active' => true
            ],
            [
                'name' => 'Damaged',
                'description' => 'Items that were damaged during handling or storage',
                'is_active' => true
            ],
            [
                'name' => 'Quality Issues',
                'description' => 'Items that failed quality control standards',
                'is_active' => true
            ],
            [
                'name' => 'Overstock',
                'description' => 'Items that exceeded storage capacity',
                'is_active' => true
            ],
            [
                'name' => 'Temperature Control',
                'description' => 'Items spoiled due to temperature control issues',
                'is_active' => true
            ],
            [
                'name' => 'Transportation Damage',
                'description' => 'Items damaged during transportation',
                'is_active' => true
            ],
            [
                'name' => 'Pest Infestation',
                'description' => 'Items contaminated by pests',
                'is_active' => true
            ],
            [
                'name' => 'Natural Disasters',
                'description' => 'Items lost due to natural disasters',
                'is_active' => true
            ]
        ];

        foreach ($categories as $category) {
            WastageCategory::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
} 