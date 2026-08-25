<?php

use App\Http\Controllers\Identidade\UsuarioController;
use App\Http\Controllers\Parceiros\AssistenciaTecnicaController;
use App\Http\Controllers\Parceiros\ClienteController;
use App\Http\Controllers\Parceiros\FabricanteController;
use App\Http\Controllers\Parceiros\FornecedorController;
use App\Http\Controllers\Rma\RmaController;
use Illuminate\Support\Facades\Route;

/**
 * Fase 8 — rotas do TEMA V2, prefixo `/v2`. Ver `routes/tema-v1.php` para a explicação
 * completa do mecanismo (`tema_forcado` via `ResolverTemaAtivo`) — espelha a mesma
 * árvore, mesmos Controllers, só troca o tema forçado.
 */
Route::prefix('v2')
    ->name('v2.')
    ->middleware('auth')
    ->group(function (): void {
        Route::resource('rma', RmaController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'show'])
            ->parameters(['rma' => 'rma'])
            ->names('rmas');

        Route::resource('parceiros/clientes', ClienteController::class)
            ->except(['show'])
            ->names('parceiros.clientes');
        Route::resource('parceiros/fabricantes', FabricanteController::class)
            ->except(['show'])
            ->names('parceiros.fabricantes');
        Route::resource('parceiros/fornecedores', FornecedorController::class)
            ->except(['show'])
            ->parameters(['fornecedores' => 'fornecedor'])
            ->names('parceiros.fornecedores');
        Route::resource('parceiros/assistencias-tecnicas', AssistenciaTecnicaController::class)
            ->except(['show'])
            ->parameters(['assistencias-tecnicas' => 'assistenciaTecnica'])
            ->names('parceiros.assistencias-tecnicas');

        Route::get('/usuarios', [UsuarioController::class, 'index'])->name('identidade.usuarios.index');
        Route::get('/perfil', [UsuarioController::class, 'perfil'])->name('identidade.perfil.show');
    });
