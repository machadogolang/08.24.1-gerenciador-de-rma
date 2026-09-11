<?php

use App\Http\Controllers\Identidade\UsuarioController;
use App\Http\Controllers\Parceiros\AssistenciaTecnicaController;
use App\Http\Controllers\Parceiros\ClienteController;
use App\Http\Controllers\Parceiros\FabricanteController;
use App\Http\Controllers\Parceiros\FornecedorController;
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

        // T3-13 - Parceiros no Tema V3: mesmos controllers de V1/V2/web.php,
        // gerando v3.parceiros.{clientes,fabricantes,fornecedores,assistencias-tecnicas}.*
        Route::get('/parceiros', fn () => redirect()->route('v3.parceiros.clientes.index'))
            ->name('parceiros.index');
        Route::resource('parceiros/clientes', ClienteController::class)
            ->names('parceiros.clientes');
        Route::resource('parceiros/fabricantes', FabricanteController::class)
            ->names('parceiros.fabricantes');
        Route::resource('parceiros/fornecedores', FornecedorController::class)
            ->parameters(['fornecedores' => 'fornecedor'])
            ->names('parceiros.fornecedores');
        Route::resource('parceiros/assistencias-tecnicas', AssistenciaTecnicaController::class)
            ->parameters(['assistencias-tecnicas' => 'assistenciaTecnica'])
            ->names('parceiros.assistencias-tecnicas');

        // T3-14 - Usuarios/admin no Tema V3: mesmo UsuarioController de V1/V2
        Route::get('/usuarios', [UsuarioController::class, 'index'])->name('identidade.usuarios.index');
        Route::get('/usuarios/novo', [UsuarioController::class, 'create'])->name('identidade.usuarios.create');
        Route::post('/usuarios', [UsuarioController::class, 'store'])->name('identidade.usuarios.store');
        Route::put('/usuarios/{usuario}', [UsuarioController::class, 'update'])->name('identidade.usuarios.update');
        Route::post('/usuarios/{usuario}/resetar-senha', [UsuarioController::class, 'resetarSenha'])
            ->name('identidade.usuarios.resetar-senha');
    });
