<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class WalletController extends Controller
{
    /**
     * Display a listing of the resource (get wallet balance for the authenticated user).
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $wallet = $user->wallet;

        if (!$wallet) {
            return response()->json([
                'success' => false,
                'description' => 'Wallet not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'description' => 'Wallet retrieved successfully',
            'data' => $wallet->Balance
        ], 200);
    }

    /**
     * Store a newly created resource in storage (add funds to the wallet).
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();
        //$wallet = $user->wallet;
        $wallet = Wallet::where('user_id', $user->id)->first(); // Use the correct column name

        // Validate the amount being added
        $request->validate([
            'amount' => 'required|numeric|min:1'
        ]);

        $amount = $request->input('amount');

        if (!$wallet) {
            return response()->json([
                'success' => false,
                'description' => 'Wallet not found',
                'data' => null
            ], 404);
        }

        try {
            // Update the wallet balance
            $wallet->Balance += $amount;
            $wallet->save();

            return response()->json([
                'success' => true,
                'description' => 'Funds added successfully',
                'data' => $wallet->Balance
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'description' => 'Failed to add funds',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource (show the current balance of the wallet).
     */
    public function show($id): JsonResponse
    {
        $wallet = Wallet::find($id);

        if (!$wallet) {
            return response()->json([
                'success' => false,
                'description' => 'Wallet not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'description' => 'Wallet balance retrieved successfully',
            'data' => $wallet->Balance
        ], 200);
    }

    /**
     * Update the specified resource in storage (make a purchase from the wallet).
     */
    public function update(Request $request, $id): JsonResponse
    {
        $wallet = Wallet::find($id);

        if (!$wallet) {
            return response()->json([
                'success' => false,
                'description' => 'Wallet not found',
                'data' => null
            ], 404);
        }

        // Validate the purchase amount
        $request->validate([
            'amount' => 'required|numeric|min:1'
        ]);

        $purchaseAmount = $request->input('amount');

        if ($wallet->Balance < $purchaseAmount) {
            return response()->json([
                'success' => false,
                'description' => 'Insufficient funds',
                'data' => $wallet->Balance
            ], 400);
        }

        try {
            // Deduct the purchase amount from the balance
            $wallet->Balance -= $purchaseAmount;
            $wallet->save();

            return response()->json([
                'success' => true,
                'description' => 'Purchase made successfully',
                'data' => $wallet->Balance
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'description' => 'Failed to make purchase',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage (reset wallet balance).
     */
    public function destroy($id): JsonResponse
    {
        $wallet = Wallet::find($id);

        if (!$wallet) {
            return response()->json([
                'success' => false,
                'description' => 'Wallet not found',
                'data' => null
            ], 404);
        }

        try {
            // Reset wallet balance
            $wallet->Balance = 0;
            $wallet->save();

            return response()->json([
                'success' => true,
                'description' => 'Wallet balance reset successfully',
                'data' => $wallet->Balance
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'description' => 'Failed to reset wallet',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
