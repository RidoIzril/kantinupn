<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\PenjualController;
use App\Http\Controllers\SuperadminController;
use App\Http\Controllers\XenditWebhookController;

    
    Route::post('/login', [AuthApiController::class, 'login']);
    Route::post('/register', [AuthApiController::class, 'register']);
    Route::post('/xendit/webhook', [XenditWebhookController::class, 'handle']);

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
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthApiController::class, 'logout']);

        Route::get('/me', function (Request $request) {
            return response()->json([
                'success' => true,
                'user' => $request->user()
            ]);
        });

    Route::get('/penjual/dashboard', [PenjualController::class, 'index']);
    Route::get('/penjual/profile', [PenjualController::class, 'profile']);
    Route::put('/penjual/profile/update', [PenjualController::class, 'profileUpdate']);
    Route::get('/penjual/notifications', [PenjualController::class, 'notifications']);

    Route::prefix('products')->group(function () {

    Route::get('/', [ProductApiController::class, 'index']);

    Route::get('/{id}', [ProductApiController::class, 'show']);

    Route::post('/store', [ProductApiController::class, 'store']);

    Route::post('/update/{id}', [ProductApiController::class, 'update']);

    Route::delete('/delete/{id}', [ProductApiController::class, 'destroy']);
});
    Route::prefix('superadmin')->group(function () {
    Route::get('/penjual', [SuperadminController::class, 'apiIndexPenjual']);
    Route::post('/penjual', [SuperadminController::class, 'apiStorePenjual']);
    });

    
});