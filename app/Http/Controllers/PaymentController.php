<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    /**
     * Handle the payment process for an order.
     */
    public function pay(Request $request, $orderId): JsonResponse
    {
        $user = Auth::user();
        $order = Order::where('UserID', $user->id)->find($orderId);

        if (!$order) {
            return response()->json([
                'success' => false,
                'description' => 'Order not found',
                'data' => null
            ], 404);
        }

        if ($order->PaymentStatus !== 'Pending') {
            return response()->json([
                'success' => false,
                'description' => 'Order has already been paid or canceled',
                'data' => null
            ], 400);
        }

        // Call PayThawani API
        return $this->payThawani($order);
    }

    /**
     * Handle the payment callback from PayThawani.
     */
    public function thawaniCallback(Request $request): JsonResponse
    {
        $status = $request->input('status'); // Assuming status is sent in the callback

        return response()->json([
            'success' => true,
            'description' => 'Payment status: ' . $status
        ], 200);
    }

    /**
     * Make a payment request to PayThawani.
     */
    private function payThawani(Order $order): JsonResponse
    {
        $data = [
            "client_reference_id" => $order->id,
            "mode" => "payment",
            "products" => [
                [
                    "name" => "Order #" . $order->id,
                    "quantity" => 1,
                    "unit_amount" => intval($order->TotalAmount) * 100 
                ]
            ],
            "success_url" => url('/payment/success'),
            "cancel_url" => url('/payment/failed'),
            "metadata" => [
                "customer_name" => Auth::user()->name,
                "email" => Auth::user()->email,
            ]
        ];

        $response = Http::withHeaders([
            'thawani-api-key' => 'rRQ26GcsZzoEhbrP2HZvLYDbn9C9et'
        ])->post('https://uatcheckout.thawani.om/api/v1/checkout/session', $data);

        if ($response->successful()) {
            $sessionId = $response->json('data.session_id');
            $publishableKey = "HGvTMLDssJghr9tlN9gr4DVYt0qyBy";

            return response()->json([
                'success' => true,
                'description' => 'Redirecting to payment gateway',
                'redirect_url' => "https://uatcheckout.thawani.om/pay/$sessionId?key=$publishableKey"
            ], 200);
        }

        return response()->json([
            'success' => false,
            'description' => 'Payment request failed',
            'error' => $response->json('error')
        ], 500);
    }
}
