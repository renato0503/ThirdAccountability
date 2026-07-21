<?php

use App\Http\Controllers\Api\ChatIaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('api/chat-ia')->group(function () {
    Route::post('/processar',       [ChatIaController::class, 'processar'])->name('api.chat-ia.processar');
    Route::post('/selecionar',      [ChatIaController::class, 'selecionar'])->name('api.chat-ia.selecionar');
    Route::post('/orcamento-manual',[ChatIaController::class, 'orcamentoManual'])->name('api.chat-ia.orcamento-manual');
    Route::get('/status/{pesquisa_id}', [ChatIaController::class, 'status'])->name('api.chat-ia.status');
});
