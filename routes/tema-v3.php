<?php

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
    });
