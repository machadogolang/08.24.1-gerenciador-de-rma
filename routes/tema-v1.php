<?php

use App\Http\Controllers\Identidade\UsuarioController;
use App\Http\Controllers\Parceiros\AssistenciaTecnicaController;
use App\Http\Controllers\Parceiros\ClienteController;
use App\Http\Controllers\Parceiros\FabricanteController;
use App\Http\Controllers\Parceiros\FornecedorController;
use App\Http\Controllers\Rma\RmaController;
use Illuminate\Support\Facades\Route;

/**
 * Fase 8 — rotas do TEMA V1, prefixo `/v1`. Mesmos Controllers de `routes/web.php`
 * (nenhuma lógica duplicada) — `tema_forcado` (lido por `ResolverTemaAtivo`) faz
 * `view_do_tema()` sempre resolver `temas.v1.*`, independente de `tema_preferido` do
 * usuário. Usadas por QA visual (comparação com LEGACY-RUNTIME `:8094`) e pelos testes
 * de smoke `RenderizaTemaV1Test`. O fluxo real de navegação pós-login (rota sem
 * prefixo) já resolve o tema certo sozinho via `tema_preferido` — estas rotas
 * existem para permitir visitar o tema explicitamente.
 */
Route::prefix('v1')
    ->name('v1.')
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
