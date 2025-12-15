<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProcesoController; 
use App\Http\Controllers\CambioController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Middleware\Admin;
use App\Services\TwilioService;
use App\Models\Proceso;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

/*Route::match(['get','post'], '/register', function () {
    return redirect('/login'); // o abort(403)
});*/


// Grupo único de rutas protegidas (autenticación requerida)
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
    // CRUD de Clientes
    Route::resource('clientes', ClienteController::class);

    // CRUD de Procesos (NUEVO)
    // Esto habilita las rutas: index, create, store, edit, update, destroy
    Route::prefix('procesos/{proceso}')->group(function() {
    Route::delete('documentos/{documento}', [ProcesoController::class, 'destroyDocumento'])
        ->name('procesos.documentos.destroy');
});


    Route::resource('procesos', ProcesoController::class);

    Route::get('/cambios', [CambioController::class, 'mostrarCambios'])
    ->name('cambios.mostrarTodos');

Route::get('/calendario', [ProcesoController::class, 'calendario'])->name('procesos.calendario');


    
/*
|--------------------------------------------------------------------------
| RUTAS ANIDADAS PARA CAMBIOS DENTRO DE PROCESOS
|--------------------------------------------------------------------------
*/
Route::prefix('procesos/{proceso}/cambios')->group(function () {
    Route::get('', [CambioController::class, 'index'])->name('procesos.cambios.index'); // Lista de cambios para un proceso específico
    Route::get('create', [CambioController::class, 'create'])->name('procesos.cambios.create'); // Formulario para crear un nuevo cambio
    Route::post('', [CambioController::class, 'store'])->name('procesos.cambios.store'); // Almacenar un nuevo cambio
    Route::get('{cambio}', [CambioController::class, 'show'])->name('procesos.cambios.show'); // Mostrar un cambio específico
    Route::get('{cambio}/edit', [CambioController::class, 'edit'])->name('procesos.cambios.edit'); // Formulario para editar un cambio
    Route::put('{cambio}', [CambioController::class, 'update'])->name('procesos.cambios.update'); // Actualizar un cambio existente
    Route::delete('{cambio}', [CambioController::class, 'destroy'])->name('procesos.cambios.destroy');// Eliminar un cambio existente
    Route::delete('{cambio}/documentos/{documento}', [CambioController::class, 'destroyDocumento']) 
        ->name('procesos.cambios.documentos.destroy');// Eliminar un documento específico de un cambio
});

Route::middleware(['auth', Admin::class])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', AdminUserController::class)->except(['show']);
});


});

Route::get('/whatsapp', function() {

    $twilio = new Client(
        config('services.twilio.sid'),
        config('services.twilio.token')
    );

    $twilio->messages->create("whatsapp:+50499999999", [
        "from" => config('services.twilio.whatsapp_from'),
        "body" => "Hola desde Laravel por WhatsApp 🚀"
    ]);

    return "Mensaje enviado por WhatsApp";
});
