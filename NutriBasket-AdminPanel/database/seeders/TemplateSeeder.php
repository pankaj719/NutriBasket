<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Template;
use App\Models\TemplateItem;
use App\Models\User;
use App\Models\Item;

class TemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create a B2B user
        $user = User::where('user_type', 'b2b')->first();
        if (!$user) {
            $user = User::create([
                'f_name' => 'B2B',
                'l_name' => 'User',
                'email' => 'b2b@example.com',
                'user_type' => 'b2b',
                'password' => bcrypt('password'),
            ]);
        }

        // Get some items
        $items = Item::take(5)->get();
        
        if ($items->count() > 0) {
            // Create sample templates
            $templates = [
                [
                    'name' => 'Weekly Groceries',
                    'description' => 'Essential items for weekly shopping',
                    'user_id' => $user->id,
                    'items' => [
                        ['item_id' => $items->first()->id, 'quantity' => 2, 'notes' => 'Organic preferred'],
                        ['item_id' => $items->skip(1)->first()->id, 'quantity' => 1, 'notes' => 'Large size'],
                    ]
                ],
                [
                    'name' => 'Office Supplies',
                    'description' => 'Regular office supplies order',
                    'user_id' => $user->id,
                    'items' => [
                        ['item_id' => $items->skip(2)->first()->id, 'quantity' => 5, 'notes' => 'Premium quality'],
                    ]
                ],
                [
                    'name' => 'Party Supplies',
                    'description' => 'Items for special events',
                    'user_id' => $user->id,
                    'items' => [
                        ['item_id' => $items->skip(3)->first()->id, 'quantity' => 3, 'notes' => 'Fresh items only'],
                        ['item_id' => $items->last()->id, 'quantity' => 2, 'notes' => 'Local produce'],
                    ]
                ]
            ];

            foreach ($templates as $templateData) {
                $template = Template::create([
                    'user_id' => $templateData['user_id'],
                    'name' => $templateData['name'],
                    'description' => $templateData['description'],
                    'is_active' => true,
                ]);

                foreach ($templateData['items'] as $itemData) {
                    TemplateItem::create([
                        'template_id' => $template->id,
                        'item_id' => $itemData['item_id'],
                        'quantity' => $itemData['quantity'],
                        'notes' => $itemData['notes'],
                    ]);
                }
            }
        }
    }
} 