<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(AdminUserSeeder::class);

        if (Product::query()->count() > 0) {
            return;
        }

        $items = [
            [
                'sku' => 'TNM-001',
                'name' => 'Wooden Tea Set',
                'description' => 'Minimal wooden tea set for pretend play.',
                'price' => 1290.00,
                'currency' => 'THB',
                'image_url' => 'https://images.placeholders.dev/?width=640&height=400&text=Tea+Set',
                'stock_qty' => 50,
                'is_enabled' => true,
            ],
            [
                'sku' => 'TNM-002',
                'name' => 'Wooden Building Blocks',
                'description' => 'Classic blocks to support creativity and motor skills.',
                'price' => 990.00,
                'currency' => 'THB',
                'image_url' => 'https://images.placeholders.dev/?width=640&height=400&text=Blocks',
                'stock_qty' => 80,
                'is_enabled' => true,
            ],
            [
                'sku' => 'TNM-003',
                'name' => 'Wooden Vehicle Set',
                'description' => 'Three wooden vehicles for imaginative play.',
                'price' => 750.00,
                'currency' => 'THB',
                'image_url' => 'https://images.placeholders.dev/?width=640&height=400&text=Vehicles',
                'stock_qty' => 30,
                'is_enabled' => true,
            ],
        ];

        foreach ($items as $item) {
            Product::create($item);
        }
    }
}
