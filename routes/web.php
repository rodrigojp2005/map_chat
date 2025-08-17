<?php

use App\Http\Controllers\ProfileController;
// use App\Http\Controllers\GincanaController; // legacy removed
use App\Http\Controllers\MapchatController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\ComentarioController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Rota principal - AGORA SEM O MIDDLEWARE 'guest' PARA FUNCIONAR PARA TODOS
Route::get('/', [MapchatController::class, 'welcome'])->name('home');
// Endpoint público para listar chats ativos em JSON (usado pelo mapa lateral)
Route::get('/mapchat-ativos.json', [MapchatController::class, 'ativosJson'])->name('mapchat.ativos.json');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Rotas legacy "gincana.*" agora apontam para MapchatController (compatibilidade)
    // Route::get('/gincana', [MapchatController::class, 'index'])->name('gincana.index');
    // Route::get('/gincana/create', [MapchatController::class, 'create'])->name('gincana.create');
    // Route::post('/gincana', [MapchatController::class, 'store'])->name('gincana.store');
    // Route::get('/gincana/disponiveis', [MapchatController::class, 'disponiveis'])->name('gincana.disponiveis');
    // Route::get('/gincana/{mapchat}', [MapchatController::class, 'show'])->name('gincana.show');
    // Route::get('/gincana/{mapchat}/jogar', [MapchatController::class, 'jogar'])->name('gincana.jogar');
    // Route::get('/gincana/{mapchat}/edit', [MapchatController::class, 'edit'])->name('gincana.edit');
    // Route::put('/gincana/{mapchat}', [MapchatController::class, 'update'])->name('gincana.update');
    // Route::delete('/gincana/{mapchat}', [MapchatController::class, 'destroy'])->name('gincana.destroy');
    
    // Rotas do jogo removidas

    // Rotas paralelas MapChat (alias das rotas gincana.*) para migração gradual
    Route::prefix('mapchat')->name('mapchat.')->group(function () {
        Route::get('/', [MapchatController::class, 'index'])->name('index');
        Route::get('/create', [MapchatController::class, 'create'])->name('create');
        Route::post('/', [MapchatController::class, 'store'])->name('store');
        Route::get('/disponiveis', [MapchatController::class, 'disponiveis'])->name('disponiveis');
        Route::get('/{mapchat}', [MapchatController::class, 'show'])->name('show');
    Route::get('/{mapchat}/jogar', [MapchatController::class, 'jogar'])->name('jogar');
        Route::get('/{mapchat}/edit', [MapchatController::class, 'edit'])->name('edit');
        Route::put('/{mapchat}', [MapchatController::class, 'update'])->name('update');
        Route::delete('/{mapchat}', [MapchatController::class, 'destroy'])->name('destroy');
    });
    
    // Rankings removidos
    
    // Rotas para comentários
    Route::post('/comentarios', [ComentarioController::class, 'store'])->name('comentarios.store');
    Route::get('/comentarios/{mapchat_id}', [ComentarioController::class, 'index'])->name('comentarios.index');

    // Push subscription
    Route::post('/push/subscribe', [\App\Http\Controllers\PushSubscriptionController::class, 'store'])->name('push.subscribe');
    Route::post('/push/unsubscribe', [\App\Http\Controllers\PushSubscriptionController::class, 'destroy'])->name('push.unsubscribe');

    // Notificações internas
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index']);
    Route::post('/notifications/read', [\App\Http\Controllers\NotificationController::class, 'markRead']);
});

// Rotas do Google OAuth
Route::get('auth/google', [SocialiteController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [SocialiteController::class, 'handleGoogleCallback']);

require __DIR__.'/auth.php';
