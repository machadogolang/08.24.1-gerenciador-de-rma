<?php

use App\Http\Controllers\Identidade\AnotacaoPessoalController;
use App\Http\Controllers\Identidade\SessaoController;
use App\Http\Controllers\Identidade\TemaPreferidoController;
use App\Http\Controllers\Identidade\UsuarioController;
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
});
