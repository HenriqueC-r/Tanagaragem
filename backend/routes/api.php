<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PlanController;
use App\Http\Controllers\Api\PublicVehicleController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\VehiclePhotoController;
use Illuminate\Support\Facades\Route;

Route::pattern('id', '[0-9]+');
Route::pattern('photoId', '[0-9]+');

// O terceiro valor do throttle é um prefixo: cada grupo ganha o seu
// próprio contador, em vez de todos dividirem o mesmo por IP.

// Vitrine pública (sem login)
Route::middleware('throttle:120,1,public')->group(function () {
    Route::get('/vehicles', [PublicVehicleController::class, 'index']);
    Route::get('/vehicles/featured', [PublicVehicleController::class, 'featured']);
    Route::get('/vehicles/{id}', [PublicVehicleController::class, 'show']);
    Route::get('/categories', [PublicVehicleController::class, 'categories']);
    Route::get('/plans', [PlanController::class, 'index']);
});

Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1,register');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1,login');

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

        Route::post('/{id}/photos', [VehiclePhotoController::class, 'store'])->middleware('throttle:30,1,photos');
        Route::put('/{id}/photos/order', [VehiclePhotoController::class, 'order']);
        Route::delete('/{id}/photos/{photoId}', [VehiclePhotoController::class, 'destroy']);
    });
});
