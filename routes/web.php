<?php

use App\Http\Controllers\Identidade\AnotacaoPessoalController;
use App\Http\Controllers\Identidade\SessaoController;
use App\Http\Controllers\Identidade\TemaPreferidoController;
use App\Http\Controllers\Identidade\UsuarioController;
use App\Http\Controllers\Parceiros\AssistenciaTecnicaController;
use App\Http\Controllers\Parceiros\ClienteController;
use App\Http\Controllers\Parceiros\FabricanteController;
use App\Http\Controllers\Parceiros\FornecedorController;
use App\Http\Controllers\Rma\CicloDeVidaController;
use App\Http\Controllers\Rma\RmaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Sessão (login/logout) — território comum aos dois temas, fora de qualquer prefixo.
Route::middleware('guest')->group(function () {
    Route::get('/login', [SessaoController::class, 'create'])->name('login');
    Route::post('/login', [SessaoController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [SessaoController::class, 'destroy'])->name('logout');

    Route::post('/tema/alternar', [TemaPreferidoController::class, 'update'])->name('tema.alternar');

    // Gestão de usuários (LEG-RMA-003/005) — autorização checada dentro do controller.
    Route::get('/usuarios', [UsuarioController::class, 'index'])->name('identidade.usuarios.index');
    Route::put('/usuarios/{usuario}', [UsuarioController::class, 'update'])->name('identidade.usuarios.update');
    Route::post('/usuarios/{usuario}/resetar-senha', [UsuarioController::class, 'resetarSenha'])
        ->name('identidade.usuarios.resetar-senha');

    // Perfil do próprio usuário (troca de senha, LEG-RMA-004; anotação pessoal, LEG-RMA-042).
    Route::get('/perfil', [UsuarioController::class, 'perfil'])->name('identidade.perfil.show');
    Route::put('/perfil/senha', [UsuarioController::class, 'atualizarSenha'])->name('identidade.perfil.senha.update');
    Route::put('/perfil/anotacao', [AnotacaoPessoalController::class, 'update'])->name('identidade.perfil.anotacao.update');

    // Parceiros (LEG-RMA-030 a 033) — cadastro de cliente/fabricante/fornecedor/
    // assistência técnica. Autorização checada dentro de cada controller via Policy.
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

    // Rma núcleo (LEG-RMA-007/008/009/010) — criação, busca, detalhe, edição.
    // Parâmetro de rota é o id puro (int), não Eloquent model binding: o objeto de
    // domínio `Dominio\Rma` é puro e o Eloquent model interno nunca sai da
    // infraestrutura (ver `app/Rma/Infraestrutura/RmasEmBanco.php`).
    Route::resource('rmas', RmaController::class)->except(['destroy']);

    // Ciclo de vida do RMA (LEG-RMA-011 a 015, LEG-RMA-017) — Fase 4.
    Route::post('/rmas/{rma}/receber', [CicloDeVidaController::class, 'receber'])->name('rmas.receber');
    Route::post('/rmas/{rma}/encaminhar', [CicloDeVidaController::class, 'encaminhar'])->name('rmas.encaminhar');
    Route::post('/rmas/{rma}/concluir', [CicloDeVidaController::class, 'concluir'])->name('rmas.concluir');
    Route::post('/rmas/{rma}/arquivar', [CicloDeVidaController::class, 'arquivar'])->name('rmas.arquivar');
    Route::post('/rmas/{rma}/reverter', [CicloDeVidaController::class, 'reverter'])->name('rmas.reverter');
    Route::post('/rmas/{rma}/solucao', [CicloDeVidaController::class, 'registrarSolucao'])->name('rmas.solucao');
});
