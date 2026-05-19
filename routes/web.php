<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ServiceOrderController;
use App\Http\Controllers\ServiceOrderItemController;

// Redirecionar rota raiz para login
Route::get('/', function () {
    return redirect()->route('login');
});

// Rotas de autenticação
Auth::routes();

// Rotas protegidas (apenas para usuários autenticados)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');

    // ========== GESTÃO DE USUÁRIOS ==========
    Route::get('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::resource('users', UserController::class);

    // ========== ORDENS DE SERVIÇO ==========
    Route::prefix('service-orders')->group(function () {

        // Exportação
        Route::get('/export', [ServiceOrderController::class, 'export'])->name('service-orders.export');

        // PDFs
        Route::get('/{serviceOrder}/pdf', [ServiceOrderController::class, 'pdf'])->name('service-orders.pdf');
        Route::get('/{serviceOrder}/download-pdf', [ServiceOrderController::class, 'downloadPdf'])->name('service-orders.download-pdf');

        // Identificação por IA
        Route::post('/identify', [ServiceOrderController::class, 'identifyProduct'])->name('service-orders.identify');

        // CRUD principal
        Route::get('/', [ServiceOrderController::class, 'index'])->name('service-orders.index');
        Route::get('/create', [ServiceOrderController::class, 'create'])->name('service-orders.create');
        Route::post('/', [ServiceOrderController::class, 'store'])->name('service-orders.store');
        Route::get('/{serviceOrder}', [ServiceOrderController::class, 'show'])->name('service-orders.show');
        Route::get('/{serviceOrder}/edit', [ServiceOrderController::class, 'edit'])->name('service-orders.edit');
        Route::put('/{serviceOrder}', [ServiceOrderController::class, 'update'])->name('service-orders.update');
        Route::delete('/{serviceOrder}', [ServiceOrderController::class, 'destroy'])->name('service-orders.destroy');

        // Atualização rápida de status da OS
        Route::patch('/{serviceOrder}/update-status', [ServiceOrderController::class, 'updateStatus'])->name('service-orders.update-status');

        // ========== ANÁLISE TÉCNICA DA OS ==========
        Route::get('/{serviceOrder}/analysis', [ServiceOrderController::class, 'analysis'])
            ->name('service-orders.analysis')
            ->middleware('can:technical-analysis,serviceOrder');

        Route::post('/{serviceOrder}/analysis', [ServiceOrderController::class, 'saveAnalysis'])
            ->name('service-orders.save-analysis')
            ->middleware('can:technical-analysis,serviceOrder');

        // ========== ITEMS (Controller específico) ==========
        Route::prefix('{serviceOrder}/items')->controller(ServiceOrderController::class)->group(function () {

            // Análise individual do item
            Route::post('/{item}/analyze', 'analyze')
                ->name('service-orders.items.analyze')
                ->middleware('can:technical-analysis,serviceOrder');

            // Status do item
            Route::patch('/{item}/status', 'updateStatus')
                ->name('service-orders.items.status')
                ->middleware('can:technical-analysis,serviceOrder');

            // Fluxo do item
            Route::post('/{item}/start-repair', 'startRepair')
                ->name('service-orders.items.start-repair');

            Route::post('/{item}/complete-repair', 'completeRepair')
                ->name('service-orders.items.complete-repair');

            Route::post('/{item}/approve-quality', 'approveQuality')
                ->name('service-orders.items.approve-quality');

            Route::post('/{item}/reject-quality', 'rejectQuality')
                ->name('service-orders.items.reject-quality');

            // CRUD do item
            Route::get('/{item}/edit', 'edit')
                ->name('service-orders.items.edit');

            Route::put('/{item}', 'update')
                ->name('service-orders.items.update');

            Route::delete('/{item}', 'destroy')
                ->name('service-orders.items.destroy');
        });
    });
});
