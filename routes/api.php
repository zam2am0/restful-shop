<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Fetch authenticated user details
Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});


// User registration and authentication routes
Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
//Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth:sanctum');
Route::middleware('auth:sanctum')->post('/logout', [AuthenticatedSessionController::class, 'destroy']);


// Password reset routes
Route::post('/forgot-password', [PasswordResetLinkController::class, 'store']);
Route::post('/reset-password', [NewPasswordController::class, 'store']);

// Product CRUD routes
Route::resource('products', ProductController::class)->middleware('auth:sanctum'); //index,show,store,update,destroy

// Wallet routes
Route::resource('wallet', WalletController::class)->middleware('auth:sanctum');

// Cart routes
Route::resource('cart', CartController::class)->middleware('auth:sanctum');


// Order routes
Route::resource('orders', OrderController::class)->middleware('auth:sanctum');


Route::get('/orders/export', [OrderController::class, 'export'])->middleware('auth:sanctum');
