<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CashController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BundleController;
use App\Http\Controllers\UnidadController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CostCenterController;
use App\Http\Controllers\EventOrderController;
use App\Http\Controllers\ItemCategoriaController;
use App\Http\Controllers\EventOrderPrintController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\PettyCashSessionController;
use App\Http\Controllers\PettyCashMovementController;
use App\Http\Controllers\InventoryAdjustmentController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

//Módulo Usuarios


Route::middleware(['auth','role:superadmin|administrador'])->group(function () {
    Route::resource('users', UserController::class)->except(['show']);
    Route::patch('users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');
});

//modulo Gastos

Route::middleware(['auth','active'])->group(function () {
    // Listar
    Route::get('expenses', [ExpenseController::class,'index'])
        ->middleware('permission:gastos.ver')
        ->name('expenses.index');

    // Crear
    Route::get('expenses/create', [ExpenseController::class,'create'])
        ->middleware('permission:gastos.crear')
        ->name('expenses.create');

    Route::post('expenses', [ExpenseController::class,'store'])
        ->middleware('permission:gastos.crear')
        ->name('expenses.store');

    // Editar/Actualizar/Eliminar (la Policy ya limita a dueño o managers)
    Route::get('expenses/{expense}/edit', [ExpenseController::class,'edit'])
        ->middleware('permission:gastos.editar')
        ->name('expenses.edit');

    Route::put('expenses/{expense}', [ExpenseController::class,'update'])
        ->middleware('permission:gastos.editar')
        ->name('expenses.update');

    Route::delete('expenses/{expense}', [ExpenseController::class,'destroy'])
        ->middleware('permission:gastos.eliminar')
        ->name('expenses.destroy');

    // Aprobar/Rechazar: SOLO finanzas/admin/superadmin
    Route::post('expenses/{expense}/approve', [ExpenseController::class,'approve'])
        ->middleware('permission:gastos.aprobar')
        ->name('expenses.approve');

    Route::post('expenses/{expense}/reject', [ExpenseController::class,'reject'])
        ->middleware('permission:gastos.aprobar')
        ->name('expenses.reject');

    //show
    Route::get('expenses/{expense}', [ExpenseController::class,'show'])
        ->middleware('permission:gastos.ver')
        ->name('expenses.show');

});

//Categorías de Gastos y Centro de Costos

Route::middleware(['auth','active', 'role:superadmin|administrador'])->group(function () {

    // ... tus rutas de expenses aquí ...

    // Categorías de gastos
    Route::resource('expense-categories', ExpenseCategoryController::class)
        ->except('show');

    // Centros de costo
    Route::resource('cost-centers', CostCenterController::class)
        ->except('show');
});

//sesiones de caja chica

Route::middleware(['auth','active', 'role:superadmin|administrador|finanzas'])->group(function () {

    Route::resource('petty-cash-sessions', PettyCashSessionController::class)
        ->only(['index', 'create', 'store', 'show']);

        
    // Movimientos de caja (se registran sobre una sesión)
    Route::post('petty-cash-sessions/{session}/movements', [PettyCashMovementController::class, 'store'])
        ->name('petty-cash-movements.store');

     // Cierre de sesión de caja
    Route::get('petty-cash-sessions/{pettyCashSession}/close', [PettyCashSessionController::class, 'closeForm'])
        ->name('petty-cash-sessions.close-form');

    Route::post('petty-cash-sessions/{pettyCashSession}/close', [PettyCashSessionController::class, 'close'])
        ->name('petty-cash-sessions.close');

    Route::get('petty-cash-movements/{movement}/receipt', [PettyCashMovementController::class, 'showReceipt'])
    ->name('petty-cash-movements.receipt');

});



// Categorias y Unidades de medida

Route::middleware(['auth', 'active', 'role:superadmin|administrador|finanzas'])->group(function () {

    // Categorías de ítems
    Route::resource('item-categorias', ItemCategoriaController::class)
        ->except(['show', 'destroy']);

    Route::patch('item-categorias/{itemCategoria}/toggle', [ItemCategoriaController::class, 'toggle'])
        ->name('item-categorias.toggle');

    // Unidades
    Route::resource('unidades', UnidadController::class)
        ->except(['show', 'destroy']);

    Route::patch('unidades/{unidad}/toggle', [UnidadController::class, 'toggle'])
        ->name('unidades.toggle');
});
//Items catalogos 

Route::middleware(['auth', 'active', 'role:superadmin|administrador'])->group(function () {

    // 🔹 Import / Export – SIEMPRE antes del resource
    Route::get('items/import', [ItemController::class, 'showImportForm'])
        ->name('items.import.form');

    Route::post('items/import', [ItemController::class, 'import'])
        ->name('items.import');

    Route::get('items-export', [ItemController::class, 'export'])
        ->name('items.export');

    // 🔹 Resource COMPLETO (incluye show)
    Route::resource('items', ItemController::class);

    // 🔹 Ajustes de inventario
    Route::get('items/{item}/ajustes', [InventoryAdjustmentController::class, 'index'])
        ->name('items.ajustes.index');

    Route::get('items/{item}/ajustes/create', [InventoryAdjustmentController::class, 'create'])
        ->name('items.ajustes.create');

    Route::post('items/{item}/ajustes', [InventoryAdjustmentController::class, 'store'])
        ->name('items.ajustes.store');

    Route::get('items/{item}/qr/download', [ItemController::class, 'downloadQr'])
        ->name('items.qr.download');

    // 🔹 Paquetes (bundles)
    Route::resource('bundles', BundleController::class)
        ->except(['show', 'destroy']);

    Route::patch('bundles/{bundle}/toggle', [BundleController::class, 'toggle'])
        ->name('bundles.toggle');

    //descargar plantilla de importacion    
    Route::get('items/import/template', [ItemController::class, 'downloadImportTemplate'])
    ->name('items.import.template');

});



//clientes

Route::middleware(['auth', 'active', 'role:superadmin|administrador|ventas'])->group(function () {
    Route::resource('clientes', ClienteController::class)->except(['show']);
    Route::patch('clientes/{cliente}/toggle', [ClienteController::class, 'toggle'])
        ->name('clientes.toggle');
});


//ordenes de evento

Route::middleware(['auth','active', 'role:superadmin|administrador|vendedor-pos'])->group(function () {
    Route::get('/ordenes-evento', [EventOrderController::class, 'index'])
        ->name('event-orders.index');
    Route::get('/event-orders/{eventOrder}/edit', [EventOrderController::class, 'edit'])
    ->name('event_orders.edit');

Route::put('/event-orders/{eventOrder}', [EventOrderController::class, 'update'])
    ->name('event_orders.update');
   

    Route::get('/ordenes-evento/{eventOrder}', [EventOrderController::class, 'show'])
        ->name('event-orders.show');
        Route::get('event-orders/{eventOrder}/print', [EventOrderPrintController::class, 'show'])
            ->name('event-orders.print');


});


// Perfil de usuario
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
