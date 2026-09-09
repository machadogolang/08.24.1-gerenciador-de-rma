<?php

use App\Http\Controllers\Rma\RmaController;
use App\Http\Controllers\Rma\V3ConsoleController;
use Illuminate\Support\Facades\Route;

/**
 * T3-08 - rotas do TEMA V3, prefixo `/v3`, nome `v3.*`. QA oculto: nenhum
 * usuario normal recebe este tema via `tema_preferido`; `ResolverTemaAtivo`
 * so forca V3 quando o nome da rota comeca por `v3.`.
 *
 * Mesmos Controllers/casos de uso das rotas sem prefixo - nenhuma regra de
 * negocio duplicada. Escopo desta tranche: dashboard + listagem de RMAs.
 */
Route::prefix('v3')
    ->name('v3.')
    ->middleware('auth')
    ->group(function (): void {
        Route::get('/', [V3ConsoleController::class, 'dashboard'])->name('dashboard');
        Route::get('/rmas', [V3ConsoleController::class, 'rmas'])->name('rmas.index');
        Route::get('/rma/{rma}', [V3ConsoleController::class, 'detalhe'])->name('rmas.show');

        // T3-12 - formularios de RMA no Tema V3: mesmo RmaController/casos de uso
        // de V1/V2; so a resolucao de view/rota muda pelo prefixo v3.
        Route::get('/rmas/novo', [RmaController::class, 'create'])->name('rmas.create');
        Route::post('/rmas', [RmaController::class, 'store'])->name('rmas.store');
        Route::get('/rma/{rma}/editar', [RmaController::class, 'edit'])->name('rmas.edit');
        Route::match(['put', 'patch'], '/rma/{rma}', [RmaController::class, 'update'])
            ->name('rmas.update');
    });
