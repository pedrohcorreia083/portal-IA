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
        Route::post('/service-orders/identify', [ServiceOrderController::class, 'identifyProduct'])
            ->name('service-orders.identify');

        Route::get('/service-orders/{serviceOrder}/analysis', [ServiceOrderController::class, 'analysis'])
            ->name('service-orders.analysis')
            ->middleware('can:technical-analysis,serviceOrder');

        // Rota alternativa para teste - REMOVA DEPOIS
        Route::get('/service-orders/{serviceOrder}/analysis-test', [ServiceOrderController::class, 'analysis'])
            ->name('service-orders.analysis-test');

        Route::post('/service-orders/{serviceOrder}/analysis', [ServiceOrderController::class, 'saveAnalysis'])
            ->name('service-orders.save-analysis')
            ->middleware('can:technical-analysis,serviceOrder');
    });

    // Rotas para PDF
    Route::get('/service-orders/{serviceOrder}/pdf', [ServiceOrderController::class, 'pdf'])
        ->name('service-orders.pdf');

    Route::get('/service-orders/{serviceOrder}/download-pdf', [ServiceOrderController::class, 'downloadPdf'])
        ->name('service-orders.download-pdf');


    // Adicione isso no routes/web.php

    Route::get('/debug/my-user', function () {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Não autenticado']);
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role ?? 'N/A',
            'isAdmin' => method_exists($user, 'isAdmin') ? $user->isAdmin() : 'Método não existe',
            'isTechnical' => method_exists($user, 'isTechnical') ? $user->isTechnical() : 'Método não existe',
            'all_attributes' => $user->toArray()
        ]);
    });


    
});

// Se quiser uma landing page pública, pode fazer assim:
// Route::get('/', function () {
//     return view('welcome');
// })->name('welcome');