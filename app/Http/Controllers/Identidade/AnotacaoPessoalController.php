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

        // CP9 (fase 2 V1) — o Quadro de Anotações da Página Inicial salva a cada
        // pausa de digitação (`fetch`, sem reload — o Legacy salvava via AJAX
        // próprio, não portado; ver `_form_novo.blade.php`/diário CMP-V1-2-004 pra
        // o motivo de não reimplementar o polling antigo). Resposta JSON só quando
        // pedida — o form tradicional do perfil (`identidade/perfil.blade.php`)
        // continua recebendo o redirect de sempre.
        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return back()->with('status', 'Anotação salva.');
    }
}
