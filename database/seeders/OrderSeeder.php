<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Product;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        // Retrieve some users and products to use in the orders
        $users = User::all();
        $products = Product::all();

        // Check if there are any users or products available
        if ($users->isEmpty() || $products->isEmpty()) {
            $this->command->info('No users or products found. Please seed users and products first.');
            return;
        }

        foreach ($users as $user) {
            // Create a new order for each user
            $order = Order::create([
                'user_id' => $user->id,
                'TotalAmount' => $this->generateTotalAmount($products),
                'PaymentStatus' => 'Pending'
            ]);

            // Add items to the order
            foreach ($this->getRandomProducts($products) as $product) {
                OrderItem::create([
                    'OrderID' => $order->id,
                    'ProductID' => $product->id,
                    'Quantity' => rand(1, 5), // Random quantity between 1 and 5
                    'Price' => $product->Price
                ]);
            }
        }
    }

    /**
     * Generate a total amount for an order based on selected products.
     */
    private function generateTotalAmount($products)
    {
        $totalAmount = 0;
        foreach ($this->getRandomProducts($products) as $product) {
            $totalAmount += $product->Price * rand(1, 5); // Random quantity between 1 and 5
        }
        return $totalAmount;
    }

        /**
         * Get a random selection of products.
         */
        private function getRandomProducts($products)
        {
            return $products->random(rand(1, 3)); // Randomly select between 1 and 3 products
        }
    
}
