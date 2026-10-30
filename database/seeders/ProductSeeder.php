<?php

namespace Database\Seeders;

use App\Models\Product\ProductModel;
use Illuminate\Database\Seeder;

/** Sample products, so the table has something to page, search and sort. */
class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // [name, category, price, stock]
            ['Wireless Mouse', 'Electronics', 450, 120],
            ['Mechanical Keyboard', 'Electronics', 2850, 35],
            ['27" Monitor', 'Electronics', 9800, 12],
            ['USB-C Hub', 'Electronics', 1250, 60],
            ['Webcam 1080p', 'Electronics', 1990, 0],
            ['Noise-Cancelling Headset', 'Electronics', 3450, 18],
            ['Office Chair', 'Furniture', 6500, 8],
            ['Standing Desk', 'Furniture', 15900, 4],
            ['Filing Cabinet', 'Furniture', 4200, 10],
            ['Bookshelf', 'Furniture', 3100, 0],
            ['Bond Paper A4 (ream)', 'Office Supplies', 245, 300],
            ['Ballpen (box of 12)', 'Office Supplies', 96, 150],
            ['Stapler', 'Office Supplies', 180, 75],
            ['Sticky Notes', 'Office Supplies', 65, 220],
            ['Whiteboard Marker Set', 'Office Supplies', 140, 90],
            ['Folder (pack of 10)', 'Office Supplies', 120, 0],
            ['Ground Coffee 500g', 'Food & Beverage', 380, 40],
            ['Bottled Water (case)', 'Food & Beverage', 210, 55],
            ['Green Tea (box)', 'Food & Beverage', 150, 30],
            ['Crackers (pack)', 'Food & Beverage', 85, 80],
            ['Dishwashing Liquid', 'Cleaning', 95, 65],
            ['Floor Mop', 'Cleaning', 320, 15],
            ['Trash Bags (roll)', 'Cleaning', 110, 100],
            ['Disinfectant Spray', 'Cleaning', 260, 45],
            ['Microfiber Cloth (5 pcs)', 'Cleaning', 175, 0],
        ];

        foreach ($products as $index => [$name, $category, $price, $stock]) {
            $code = sprintf('PRD-%04d', $index + 1);

            ProductModel::updateOrCreate(['code' => $code], [
                'name' => $name,
                'category' => $category,
                'price' => $price,
                'stock' => $stock,
                'description' => null,
                'is_active' => $index % 7 !== 6, // a few inactive ones, to see the status column
            ]);
        }
    }
}
