<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TnmshopDemoProductsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tnmshop_products')->insert([
            [
                'sku' => 'TNM-001',
                'name' => 'Wooden Coffee Set',
                'description' => 'Minimal wooden coffee set for pretend play.',
                'price' => 1290.00,
                'currency' => 'THB',
                'stock_qty' => 20,
                'is_enabled' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sku' => 'TNM-002',
                'name' => 'Kitchen Starter Pack',
                'description' => 'Starter kitchen toys set. Safe and eco-friendly.',
                'price' => 1590.00,
                'currency' => 'THB',
                'stock_qty' => 15,
                'is_enabled' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'sku' => 'TNM-003',
                'name' => 'Farm Animal Set',
                'description' => 'A set of wooden farm animals for storytelling.',
                'price' => 990.00,
                'currency' => 'THB',
                'stock_qty' => 30,
                'is_enabled' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
