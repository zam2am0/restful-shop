<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $products = [];

        for ($i = 1; $i <= 10; $i++) {
            $products[] = [
                'Name' => 'Product ' . $i,
                'Description' => 'Description for Product ' . $i,
                'Price' => 10.00 + $i, // Example prices ranging from 11.00 to 20.00
                'StockQuantity' => rand(1, 100), // Random stock quantity between 1 and 100
            ];
        }

        DB::table('products')->insert($products);
    }
}
