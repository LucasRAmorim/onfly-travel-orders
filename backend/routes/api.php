<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TravelOrderController;
use App\Http\Controllers\Api\AirportController;
use App\Http\Controllers\Api\NotificationController;


Route::post('/login', [AuthController::class, 'login']);
Route::get('/airports', [AirportController::class, 'index']);



Route::middleware('auth:sanctum')->group(function () {
    Route::get('/travel-orders', [TravelOrderController::class, 'index']);
    Route::post('/travel-orders', [TravelOrderController::class, 'store']);
    Route::get('/travel-orders/{travelOrder}', [TravelOrderController::class, 'show']);
    Route::patch('/travel-orders/{travelOrder}/status', [TravelOrderController::class, 'updateStatus']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{notificationId}/read', [NotificationController::class, 'markAsRead']);
});
