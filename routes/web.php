<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
// Importamos ambos controladores aquí
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProcesoController; 
use App\Http\Controllers\CambioController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Grupo único de rutas protegidas (autenticación requerida)
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // CRUD de Clientes
    Route::resource('clientes', ClienteController::class);

    // CRUD de Procesos (NUEVO)
    // Esto habilita las rutas: index, create, store, edit, update, destroy
    Route::resource('procesos', ProcesoController::class);

    Route::get('/cambios', [CambioController::class, 'mostrarCambios'])
    ->name('cambios.mostrarTodos');

    
// CRUD de Cambios (anidado a un Proceso)
Route::prefix('procesos/{proceso}/cambios')->group(function () {
    Route::get('', [CambioController::class, 'index'])->name('procesos.cambios.index');
    Route::get('create', [CambioController::class, 'create'])->name('procesos.cambios.create');
    Route::post('', [CambioController::class, 'store'])->name('procesos.cambios.store');
    Route::get('{cambio}/edit', [CambioController::class, 'edit'])->name('procesos.cambios.edit');
    Route::put('{cambio}', [CambioController::class, 'update'])->name('procesos.cambios.update');
    Route::delete('{cambio}', [CambioController::class, 'destroy'])->name('procesos.cambios.destroy');

    // Ruta para eliminar un documento específico de un cambio
    Route::delete('{cambio}/documentos/{documento}', [CambioController::class, 'destroyDocumento'])
        ->name('procesos.cambios.documentos.destroy');
});


});