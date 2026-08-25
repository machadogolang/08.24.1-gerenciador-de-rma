<?php

namespace App\Http\Controllers\Identidade;

use App\Http\Controllers\Controller;
use App\Models\TentativaDeAcesso;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * `LEG-RMA-043` — dado (`tentativas_de_acesso`) já existe desde a Fase 1, esta fase só
 * adiciona a tela de consulta. Mesma regra de autorização de `HistoricoDeModificacaoController`
 * (`Papel::podeGerenciarUsuarios()`, tela administrativa).
 */
class HistoricoDeAcessoController extends Controller
{
    public function index(): View
    {
        Gate::authorize('gerenciar', User::class);

        $tentativas = TentativaDeAcesso::query()
            ->with('user')
            ->latest()
            ->paginate(20);

        return view('identidade.historico-de-acesso.index', [
            'titulo' => 'Histórico de acesso',
            'tentativas' => $tentativas,
        ]);
    }
}
