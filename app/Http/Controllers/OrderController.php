<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OrdersExport;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    /**
     * Display a listing of the orders (for the authenticated user).
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $orders = $user->orders;

        if ($orders->isEmpty()) {
            return response()->json([
                'success' => false,
                'description' => 'No orders found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'description' => 'Orders retrieved successfully',
            'data' => $orders
        ], 200);
    }

    /**
     * Store a newly created order.
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();
        $cart = $user->cart;

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json([
                'success' => false,
                'description' => 'Cart is empty. Cannot place an order.',
                'data' => null
            ], 400);
        }

        // Calculate total amount
        $totalAmount = $cart->items->sum(function($item) {
            return $item->product->Price * $item->quantity;
        });

        try {
            // Create a new order
            $order = Order::create([
                'UserID' => $user->id,
                'TotalAmount' => $totalAmount,
                'PaymentStatus' => 'Pending'
            ]);

            // Move items from cart to order
            foreach ($cart->items as $cartItem) {
                OrderItem::create([
                    'OrderID' => $order->id,
                    'ProductID' => $cartItem->product->id,
                    'Quantity' => $cartItem->quantity,
                    'Price' => $cartItem->product->Price
                ]);
            }

            // Clear the user's cart after placing the order
            $cart->items()->delete();

            return response()->json([
                'success' => true,
                'description' => 'Order placed successfully',
                'data' => $order
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'description' => 'Order creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified order.
     */
    public function show($id): JsonResponse
    {
        $user = Auth::user();
        $order = $user->orders()->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'description' => 'Order not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'description' => 'Order retrieved successfully',
            'data' => $order
        ], 200);
    }

    /**
     * Download an Excel file of all orders.
     */
    public function export(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(new OrdersExport, 'orders.xlsx');
    }

    /**
     * Remove the specified order from storage.
     */
    public function destroy($id): JsonResponse
    {
        $user = Auth::user();
        $order = $user->orders()->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'description' => 'Order not found',
                'data' => null
            ], 404);
        }

        try {
            $order->delete();
            return response()->json([
                'success' => true,
                'description' => 'Order deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'description' => 'Order deletion failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
