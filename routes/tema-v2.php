<?php

use App\Http\Controllers\Identidade\HistoricoDeAcessoController;
use App\Http\Controllers\Identidade\UsuarioController;
use App\Http\Controllers\Rma\HistoricoDeModificacaoController;
use App\Http\Controllers\Parceiros\AssistenciaTecnicaController;
use App\Http\Controllers\Parceiros\ClienteController;
use App\Http\Controllers\Parceiros\FabricanteController;
use App\Http\Controllers\Parceiros\FornecedorController;
use App\Http\Controllers\Rma\RmaController;
use Illuminate\Support\Facades\Route;

/**
 * Fase 8 - rotas do TEMA V2, prefixo `/v2`. Ver `routes/tema-v1.php` para a explicação
 * completa do mecanismo (`tema_forcado` via `ResolverTemaAtivo`) - espelha a mesma
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
            ->names('parceiros.clientes');
        Route::resource('parceiros/fabricantes', FabricanteController::class)
            ->names('parceiros.fabricantes');
        Route::resource('parceiros/fornecedores', FornecedorController::class)
            ->parameters(['fornecedores' => 'fornecedor'])
            ->names('parceiros.fornecedores');
        Route::resource('parceiros/assistencias-tecnicas', AssistenciaTecnicaController::class)
            ->parameters(['assistencias-tecnicas' => 'assistenciaTecnica'])
            ->names('parceiros.assistencias-tecnicas');

        Route::get('/usuarios', [UsuarioController::class, 'index'])->name('identidade.usuarios.index');

        // PAR15-USR-007 - Novo usuario do TEMA V2 (prefixo).
        Route::get('/usuarios/novo', [UsuarioController::class, 'create'])->name('identidade.usuarios.create');
        Route::post('/usuarios', [UsuarioController::class, 'store'])->name('identidade.usuarios.store');

        // PAR15-USR-001 - superficies dedicadas do TEMA V2 espelhadas no prefixo /v2
        // (mesmos Controllers; o POST/PUT continuam canonicos).
        Route::get('/usuarios/{usuario}/permissoes', [UsuarioController::class, 'permissoes'])
            ->name('identidade.usuarios.permissoes');
        Route::get('/usuarios/{usuario}/resetar-senha', [UsuarioController::class, 'resetarSenhaForm'])
            ->name('identidade.usuarios.resetar-senha.form');
        Route::get('/usuarios/{usuario}/apagar', [UsuarioController::class, 'apagar'])
            ->name('identidade.usuarios.apagar');
        Route::get('/perfil', [UsuarioController::class, 'perfil'])->name('identidade.perfil.show');

        // PAR-RES-E-04 - superficies historicas do TEMA V2 separadas (fonte
        // `15.8.1/page/anotacoes.php` e `15.8.1/subp/senha.php`). O /perfil
        // continua existindo como rota moderna/compatibilidade, mas nao e mais
        // a unica tela que mistura perfil+senha+anotacao no V2. O Tema V3 nao
        // recebe esta organizacao.
        Route::get('/anotacoes', [UsuarioController::class, 'anotacoes'])->name('identidade.anotacoes.index');
        Route::get('/perfil/senha', [UsuarioController::class, 'alterarSenha'])->name('identidade.perfil.senha');

        // PAR15-AUD-001/005 - hub Controle do TEMA V2 (prefixo) + superficies do menu historico.
        Route::get('/controle', [\App\Http\Controllers\Identidade\ControleController::class, 'index'])->name('identidade.controle.index');

        // PAR15-REL-001..007 - hub Relatorios do TEMA V2 (item unico do menu historico).
        Route::get('/relatorios', [\App\Http\Controllers\Rma\PainelDeRelatoriosController::class, 'index'])->name('rmas.relatorios.index');
        Route::get('/historico-de-acesso', [HistoricoDeAcessoController::class, 'index'])->name('identidade.historico-de-acesso.index');
        Route::get('/rmas-historico', [HistoricoDeModificacaoController::class, 'index'])->name('rmas.historico.index');
    });
