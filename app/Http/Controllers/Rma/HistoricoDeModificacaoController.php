<?php

namespace App\Http\Controllers\Rma;

use App\Http\Controllers\Controller;
use App\Models\ModificacaoDeRma;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * `LEG-RMA-044` — reproduz `subp/logs_de_modificacao.php`, exige
 * `Papel::podeGerenciarUsuarios()` (mesma Policy/Gate `'gerenciar'` de
 * `UsuarioController`, tela administrativa).
 */
class HistoricoDeModificacaoController extends Controller
{
    public function index(): View
    {
        Gate::authorize('gerenciar', User::class);

        $modificacoes = ModificacaoDeRma::query()
            ->with(['rma', 'user'])
            ->latest()
            ->paginate(20);

        return view('rma.historico.index', [
            'titulo' => 'Histórico de modificações de RMA',
            'modificacoes' => $modificacoes,
        ]);
    }
}
