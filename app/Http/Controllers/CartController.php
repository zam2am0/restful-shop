<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    /**
     * Display a listing of the cart items (for authenticated user).
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        
        // Check if the user has a cart
        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart) {
            return response()->json([
                'success' => false,
                'description' => 'No cart found for the user',
                'data' => null
            ], 404);
        }

        // Check if the cart has items
        if ($cart->cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'description' => 'Cart is empty',
                'data' => null
            ], 404);
        }

        // Calculate the total cost of items
        $totalCost = $cart->cartItems->sum(function($item) {
            return $item->product->Price * $item->Quantity;
        });

        return response()->json([
            'success' => true,
            'description' => 'Cart retrieved successfully',
            'data' => [
                'items' => $cart->cartItems,
                'total_cost' => $totalCost
            ]
        ], 200);
    }



    /**
     * Add product to the cart.
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();
        $cart = $user->cart;

        // If user does not have a cart, create one
        if (!$cart) {
            $cart = Cart::create(['UserID' => $user->id]);
        }

        // Validate product ID and quantity
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $product = Product::find($request->product_id);

        if (!$product || $product->StockQuantity < $request->quantity) {
            return response()->json([
                'success' => false,
                'description' => 'Product out of stock or insufficient quantity',
                'data' => null
            ], 400);
        }

        // Add product to cart or update quantity if already in cart
        $cartItem = CartItem::updateOrCreate(
            ['cart_id' => $cart->id, 'product_id' => $request->product_id],
            ['quantity' => $request->quantity]
        );

        return response()->json([
            'success' => true,
            'description' => 'Product added to cart successfully',
            'data' => $cartItem
        ], 200);
    }

    /**
     * Checkout (deduct the total cost from wallet and adjust product stock).
     */
    public function update(Request $request, $id): JsonResponse
    {
        $user = Auth::user();
        $cart = $user->cart;

        if (!$cart || $cart->cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'description' => 'Cart is empty',
                'data' => null
            ], 404);
        }

        $totalCost = $cart->cartItems->sum(function($item) {
            return $item->product->Price * $item->quantity;
        });

        $wallet = $user->wallet;

        if ($wallet->Balance < $totalCost) {
            return response()->json([
                'success' => false,
                'description' => 'Insufficient funds in wallet',
                'data' => null
            ], 400);
        }

        try {
            // Deduct the total cost from the wallet
            $wallet->Balance -= $totalCost;
            $wallet->save();

            // Adjust the stock of each product in the cart
            foreach ($cart->cartItems as $item) {
                $product = $item->product;
                $product->StockQuantity -= $item->quantity;
                $product->save();
            }

            // Empty the cart after checkout
            $cart->items()->delete();

            return response()->json([
                'success' => true,
                'description' => 'Checkout successful',
                'data' => [
                    'remaining_balance' => $wallet->Balance
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'description' => 'Checkout failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove product from the cart.
     */
    public function destroy($id): JsonResponse
    {
        $user = Auth::user();
        $cart = $user->cart;
        $cartItem = $cart->items()->find($id);

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'description' => 'Product not found in cart',
                'data' => null
            ], 404);
        }

        $cartItem->delete();

        return response()->json([
            'success' => true,
            'description' => 'Product removed from cart successfully',
            'data' => null
        ], 200);
    }
}
