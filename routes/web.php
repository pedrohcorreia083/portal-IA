<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ServiceOrderController;


// Redirecionar rota raiz para login
Route::get('/', function () {
    return redirect()->route('login');
});

// Rotas de autenticação
Auth::routes();

// Rotas protegidas (apenas para usuários autenticados)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');

    // Adicione aqui outras rotas protegidas

    // Gestão de Usuários
    Route::get('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::resource('users', UserController::class);

    Route::middleware(['auth'])->group(function () {
        // Ordens de Serviço
        Route::resource('service-orders', ServiceOrderController::class);
        // Atualização rápida de status
        Route::patch('/service-orders/{serviceOrder}/update-status', [ServiceOrderController::class, 'updateStatus'])->name('service-orders.update-status');
    });
    
    // Rotas para PDF
    Route::get('/service-orders/{serviceOrder}/pdf', [ServiceOrderController::class, 'pdf'])
        ->name('service-orders.pdf');

    Route::get('/service-orders/{serviceOrder}/download-pdf', [ServiceOrderController::class, 'downloadPdf'])
        ->name('service-orders.download-pdf');
});

// Se quiser uma landing page pública, pode fazer assim:
// Route::get('/', function () {
//     return view('welcome');
// })->name('welcome');