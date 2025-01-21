<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BoutiqueController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\PlatController;
use App\Http\Controllers\NotificationController;

Route::prefix('v1')->group(function () {

    Route::prefix('notifications')->group(function () {
        Route::post('/register-token', [NotificationController::class, 'registerToken']);
    });

    Route::prefix('commandes')->group(function () {
        Route::post('/', [CommandeController::class, 'create']);
        Route::get('/', [CommandeController::class, 'index']);
        Route::get('/{id}', [CommandeController::class, 'show']);
        Route::put('/{id}', [CommandeController::class, 'update']);
    });

    Route::prefix('boutiques')->group(function () {
        Route::post('/', [BoutiqueController::class, 'create']);
        Route::get('/', [BoutiqueController::class, 'index']);
        Route::get('/{id}', [BoutiqueController::class, 'show']);
        Route::put('/{id}', [BoutiqueController::class, 'update']);
        Route::delete('/{id}', [BoutiqueController::class, 'destroy']);
    });

    Route::prefix('boutiques/{boutiqueId}/plats')->group(function () {
        Route::post('/', [PlatController::class, 'create']);
        Route::get('/', [PlatController::class, 'index']);
        Route::put('/{platId}', [PlatController::class, 'update']);
        Route::delete('/{platId}', [PlatController::class, 'destroy']);
    });

});