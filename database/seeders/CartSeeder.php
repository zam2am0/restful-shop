<?php

namespace Database\Seeders;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        // Get or create a user
        $user = User::first(); // Or use User::find($id) for a specific user

        if (!$user) {
            $this->command->info('No user found to associate with the cart.');
            return;
        }

        // Create a cart for the user
        $cart = Cart::create(['user_id' => $user->id]);

        // Add products to the cart (ensure you have products in your database)
        $products = Product::limit(3)->get(); // Fetch some products

        foreach ($products as $product) {
            CartItem::create([
                'cart_id' => $cart->id, // Use 'CartID' instead of 'cart_id'
                'product_id' => $product->id, // Use 'ProductID' instead of 'product_id'
                'quantity' => rand(1, 5) // Random quantity for testing
            ]);
        }

        $this->command->info('Cart and items seeded successfully.');
    
    }
}
