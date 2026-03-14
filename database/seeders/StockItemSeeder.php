<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StockItem;

class StockItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'item_name' => 'Pneumatic Drill',
                'sku' => 'PDR-001',
                'category' => 'Tools',
                'quantity' => 15,
                'unit_price' => 89.99,
            ],
            [
                'item_name' => 'Safety Helmet',
                'sku' => 'SH-102',
                'category' => 'Safety',
                'quantity' => 45,
                'unit_price' => 34.50,
            ],
            [
                'item_name' => 'Stainless Steel Bolts (M10)',
                'sku' => 'BOLT-M10',
                'category' => 'Hardware',
                'quantity' => 250,
                'unit_price' => 0.75,
            ],
            [
                'item_name' => 'LED Work Light',
                'sku' => 'LED-WL-200',
                'category' => 'Equipment',
                'quantity' => 8,
                'unit_price' => 45.00,
            ],
            [
                'item_name' => 'Rubber Work Gloves (Pair)',
                'sku' => 'GLOVE-RUB-L',
                'category' => 'Supplies',
                'quantity' => 120,
                'unit_price' => 3.99,
            ],
            [
                'item_name' => 'Adjustable Wrench',
                'sku' => 'WRENCH-12',
                'category' => 'Tools',
                'quantity' => 22,
                'unit_price' => 12.50,
            ],
            [
                'item_name' => 'Paint Roller Set',
                'sku' => 'PAINT-ROLLER',
                'category' => 'Supplies',
                'quantity' => 0,
                'unit_price' => 17.99,
            ],
        ];

        foreach ($items as $item) {
            StockItem::create($item);
        }
    }
}
