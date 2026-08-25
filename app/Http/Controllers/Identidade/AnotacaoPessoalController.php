<?php

namespace App\Http\Controllers\Identidade;

use App\Http\Controllers\Controller;
use App\Identidade\Aplicacao\AtualizarAnotacaoPessoal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AnotacaoPessoalController extends Controller
{
    /**
     * Salva o bloco de notas pessoal do próprio usuário autenticado (LEG-RMA-042).
     */
    public function update(Request $request, AtualizarAnotacaoPessoal $atualizarAnotacaoPessoal): RedirectResponse
    {
        $dados = $request->validate([
            'anotacao' => ['nullable', 'string'],
        ]);

        $atualizarAnotacaoPessoal->atualizar($request->user(), $dados['anotacao'] ?? null);

        return back()->with('status', 'Anotação salva.');
    }
}
