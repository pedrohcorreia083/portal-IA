<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ServiceOrderController;
use App\Http\Controllers\TechnicalSheetController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\UserController;
use App\Models\ServiceOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Redirecionar rota raiz para login
Route::get('/', function () {
    return redirect()->route('login');
});

// Rotas de autenticação.
// O cadastro público está desativado: contas são criadas apenas por um administrador.
Auth::routes(['register' => false]);

// Rotas protegidas (apenas para usuários autenticados)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');

    // ========== GESTÃO DE USUÁRIOS ==========
    Route::get('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::resource('users', UserController::class);

    // Rota para exibir a página de upload
    Route::get('/uploads', [UploadController::class, 'index'])->name('uploads.index');
    Route::get('/uploads/imagem/{pasta}/{nome}', [UploadController::class, 'imagem'])
        ->name('uploads.imagem');

    // Rota para processar o upload (POST)
    Route::post('/uploads', [UploadController::class, 'store'])->name('uploads.store');

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

        // A análise técnica foi concentrada nas fichas individuais.
        Route::get('/{serviceOrder}/analysis', function (ServiceOrder $serviceOrder) {
            return redirect()->route('technical-sheets.index', ['order_number' => $serviceOrder->order_number]);
        })->name('service-orders.analysis');

        Route::post('/{serviceOrder}/analysis', function () {
            abort(410, 'A análise deve ser realizada na Ficha técnica.');
        })->name('service-orders.save-analysis');

        Route::patch('/{serviceOrder}/items/update-status', [ServiceOrderController::class, 'updateItemsStatus'])
            ->name('service-orders.items.update-status');
    });

    Route::get('/technical-sheets', [TechnicalSheetController::class, 'index'])->name('technical-sheets.index');
    Route::get('/technical-sheets/{item}', [TechnicalSheetController::class, 'show'])->name('technical-sheets.show');
    Route::patch('/technical-sheets/{item}', [TechnicalSheetController::class, 'update'])->name('technical-sheets.update');
});
