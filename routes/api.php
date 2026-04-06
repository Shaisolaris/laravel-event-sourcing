<?php

use App\Http\Controllers\Api\OrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('orders')->group(function () {
    Route::get('/', [OrderController::class, 'index']);
    Route::post('/', [OrderController::class, 'store']);
    Route::get('/analytics', [OrderController::class, 'analytics']);
    Route::get('/{uuid}', [OrderController::class, 'show']);
    Route::get('/{uuid}/events', [OrderController::class, 'events']);
    Route::post('/{uuid}/confirm', [OrderController::class, 'confirm']);
    Route::post('/{uuid}/ship', [OrderController::class, 'ship']);
    Route::post('/{uuid}/deliver', [OrderController::class, 'deliver']);
    Route::post('/{uuid}/cancel', [OrderController::class, 'cancel']);
    Route::post('/{uuid}/refund', [OrderController::class, 'refund']);
    Route::post('/{uuid}/notes', [OrderController::class, 'addNote']);
});
// Demo mode: seed data available via php artisan db:seed
