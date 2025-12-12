<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EventOrderController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\ClienteApiController;
use App\Http\Controllers\Api\ItemApiController;
use App\Http\Controllers\Api\BundleApiController;
use App\Http\Controllers\Api\EventPaymentController;
use App\Http\Controllers\Api\PettyCashPosController;
use App\Http\Controllers\Api\PosCatalogController;

// Login
Route::post('/login', [AuthApiController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthApiController::class, 'logout']);

    // Clientes
    Route::get('/clients', [ClienteApiController::class, 'index']);
    Route::post('/clients', [ClienteApiController::class, 'store']);

    // Órdenes de evento (POS)
    Route::get('/event-orders', [EventOrderController::class, 'index']);
    Route::get('/event-orders/{eventOrder}', [EventOrderController::class, 'show']);
    //Agregar partidas a la orden desde historial
     Route::post('/event-orders/{eventOrder}/lines', [EventOrderController::class, 'addLines']);
     // Actualizar TODAS las líneas de la orden (usado por el POS detalle)
    Route::put('/event-orders/{eventOrder}/lines', [EventOrderController::class, 'updateLines']);

    Route::post('/event-orders', [EventOrderController::class, 'store']);
    Route::patch('/event-orders/{eventOrder}/status', [EventOrderController::class, 'updateStatus']);

    Route::patch('event-orders/{eventOrder}/loans', [EventOrderController::class, 'updateLoans']);

    // Pagos de órdenes
    Route::post('/event-orders/{eventOrder}/payments', [EventPaymentController::class, 'store']);

    // Catálogo items / bundles
    Route::get('/items', [ItemApiController::class, 'index']);
    Route::get('/bundles', [BundleApiController::class, 'index']);

    // Caja chica POS
    Route::prefix('pos')->group(function () {
        Route::get('/cash-session', [PettyCashPosController::class, 'currentSession']);
        Route::get('/cash-movements', [PettyCashPosController::class, 'indexMovements']);
        Route::post('/cash-movements', [PettyCashPosController::class, 'storeMovement']);
        Route::get('/expense-categories', [PettyCashPosController::class, 'categories']);
        Route::get('/cost-centers', [PettyCashPosController::class, 'costCenters']);
        Route::get('/items', [PosCatalogController::class, 'items']);
    });
});
