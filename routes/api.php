<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

// Публичные маршруты
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Для авторизованных пользователей
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);

    // Только для администратора
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/ping', function () {
            return response()->json(['message' => 'Вы администратор']);
        });
    });
});