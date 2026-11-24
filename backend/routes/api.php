<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\VehiclePhotoController;
use Illuminate\Support\Facades\Route;

Route::pattern('id', '[0-9]+');
Route::pattern('photoId', '[0-9]+');

Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::prefix('my/vehicles')->group(function () {
        Route::get('/', [VehicleController::class, 'index']);
        Route::post('/', [VehicleController::class, 'store']);
        Route::get('/{id}', [VehicleController::class, 'show']);
        Route::put('/{id}', [VehicleController::class, 'update']);
        Route::delete('/{id}', [VehicleController::class, 'destroy']);
        Route::post('/{id}/publish', [VehicleController::class, 'publish']);
        Route::post('/{id}/pause', [VehicleController::class, 'pause']);
        Route::post('/{id}/sold', [VehicleController::class, 'sold']);

        Route::post('/{id}/photos', [VehiclePhotoController::class, 'store'])->middleware('throttle:30,1');
        Route::put('/{id}/photos/order', [VehiclePhotoController::class, 'order']);
        Route::delete('/{id}/photos/{photoId}', [VehiclePhotoController::class, 'destroy']);
    });
});
