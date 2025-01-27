<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PlatController;
use App\Http\Controllers\NotificationController;

Route::prefix('v1')->group(function () {

    Route::prefix('notifications')->group(function () {
        Route::post('/register-token', [NotificationController::class, 'registerToken']);
    });

    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::get('/auth/profile', [AuthController::class, 'profile']);

    Route::prefix('commandes')->group(function () {
        Route::post('/', [CommandeController::class, 'create']);
        Route::get('/', [CommandeController::class, 'index']);
        Route::get('/{id}', [CommandeController::class, 'show']);
        Route::put('/{id}', [CommandeController::class, 'update']);
    });

    Route::get('specialities', [PlatController::class,'specialities']);

    Route::prefix('/plats')->group(function () {
        Route::post('/', [PlatController::class, 'create']);
        Route::get('/', [PlatController::class, 'index']);
        Route::put('/{platId}', [PlatController::class, 'update']);
        Route::delete('/{platId}', [PlatController::class, 'destroy']);
    });

});