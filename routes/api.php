<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\CartApiController;
use App\Http\Controllers\Api\PaymentApiController;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Api\ChatApiController;

use App\Http\Controllers\PenjualController;
use App\Http\Controllers\SuperadminController;
use App\Http\Controllers\XenditWebhookController;

// ================= AUTH =================

Route::post('/login', [AuthApiController::class, 'login']);

Route::post('/register', [AuthApiController::class, 'register']);

Route::post('/logout', [AuthApiController::class, 'logout']);

// ================= XENDIT =================

Route::post('/xendit/webhook', [XenditWebhookController::class, 'handle']);

// ================= USER TOKEN =================

Route::get('/me-token', function (Request $request) {

    $token = $request->query('token');

    if (!$token) {
        return response()->json(null);
    }

    $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);

    if (!$accessToken) {
        return response()->json(null);
    }

    $user = $accessToken->tokenable;

    if (!$user) {
        return response()->json(null);
    }

    $customer = \App\Models\Customers::where('users_id', $user->id)->first();

    return response()->json([
        'nama_lengkap' => $customer->nama_lengkap ?? 'User'
    ]);
});

// ================= ME =================

Route::get('/me', function (Request $request) {

    $token = $request->bearerToken()
        ?? $request->query('token');

    if (!$token) {
        return response()->json([
            'success' => false,
            'message' => 'Token tidak ditemukan'
        ], 401);
    }

    $accessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);

    if (!$accessToken) {
        return response()->json([
            'success' => false,
            'message' => 'Token tidak valid'
        ], 401);
    }

    $user = $accessToken->tokenable;

    return response()->json([
        'success' => true,
        'user' => $user
    ]);
});

// ================= PENJUAL =================

Route::prefix('penjual')->group(function () {

    Route::get('/dashboard', [PenjualController::class, 'index']);
    Route::get('/profile', [PenjualController::class, 'profile']);
    Route::put('/profile/update', [PenjualController::class, 'profileUpdate']);
    Route::get('/notifications', [PenjualController::class, 'notifications']);
    Route::get('/orders', [OrderApiController::class, 'penjualOrders']);
    Route::get('/orders/{id}', [OrderApiController::class, 'penjualOrderShow']);
    Route::post('/orders/{id}/process', [OrderApiController::class, 'penjualOrderProcess']);
    Route::post('/orders/{id}/ready', [OrderApiController::class, 'penjualOrderReady']);
    Route::post('/orders/{id}/complete', [OrderApiController::class, 'penjualOrderComplete']);
    Route::post('/orders/{id}/cancel', [OrderApiController::class, 'penjualOrderCancel']);
});

// ================= PRODUCTS =================

Route::prefix('products')->group(function () {

    Route::get('/', [ProductApiController::class, 'index']);

    Route::get('/{id}', [ProductApiController::class, 'show']);

    Route::post('/store', [ProductApiController::class, 'store']);

    Route::post('/update/{id}', [ProductApiController::class, 'update']);

    Route::delete('/delete/{id}', [ProductApiController::class, 'destroy']);
});

// ================= CART ap.php =================

Route::prefix('cart')->group(function () {

    // GET CART
    Route::get('/', [CartApiController::class, 'index']);

    // ADD TO CART
    Route::post('/add', [CartApiController::class, 'add']);

    // UPDATE CART
    Route::post('/update', [CartApiController::class, 'update']);

    // DELETE CART
    Route::delete('/remove', [CartApiController::class, 'remove']);

    // SUMMARY CART
    Route::get('/summary', [CartApiController::class, 'summary']);

    // CHECKOUT
    Route::post('/checkout', [CartApiController::class, 'checkout']);
});
Route::prefix('payment')->group(function () {
    Route::get('/qris', [PaymentApiController::class, 'qris']);           // ?order_id=...
    Route::post('/qris', [PaymentApiController::class, 'qris']);          // { order_id: ... }
    Route::get('/qris/status', [PaymentApiController::class, 'checkQrisStatus']);
});
Route::prefix('customer')->group(function () {
    Route::get('/orders', [OrderApiController::class, 'customerOrders']);
    Route::get('/orders/{id}', [OrderApiController::class, 'customerOrderShow']);
});

Route::prefix('chat')->group(function () {
    Route::get('/users', [ChatApiController::class, 'users']);
    Route::get('/room/{userId}', [ChatApiController::class, 'room']);
    Route::post('/send', [ChatApiController::class, 'send']);
    Route::delete('/{id}', [ChatApiController::class, 'delete']);

    Route::get('/unread-count', [ChatApiController::class, 'unreadCount']);
    Route::get('/unread-by-user', [ChatApiController::class, 'unreadByUser']);
});


// ================= SUPERADMIN =================

Route::prefix('superadmin')->group(function () {

    Route::get('/penjual', [SuperadminController::class, 'apiIndexPenjual']);

    Route::post('/penjual', [SuperadminController::class, 'apiStorePenjual']);
});