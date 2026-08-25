<?php

namespace App\Http\Controllers\Rma;

use App\Http\Controllers\Controller;
use App\Models\Rma as RmaEloquent;
use App\Models\User;
use App\Rma\Dominio\Status;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;

/**
 * VIS-V1-010 — painel "Controle" do TEMA V1 (`legacy-source/14.6.1/page/controle.php`,
 * idêntico a `menujs-right/controle.php`: 7 ações administrativas). Não é o "Controle"
 * do TEMA V2 legado (`15.8.1/page/controle.php`, logs de modificação — esse continua em
 * `HistoricoDeModificacaoController`/`rmas.historico.index`, inalterado).
 *
 * Reaproveita ações V3 já existentes (arquivar RMA, cadastro de parceiro, troca da
 * própria senha) em vez de duplicar caso de uso. `VIS-V1-011`/`VIS-V1-012` (hard delete
 * de RMA/usuário) não têm rota V3 — decisão de produto pendente, não implementadas por
 * inferência; a view só registra a pendência.
 */
class ControlePainelController extends Controller
{
    public function index(): View
    {
        Gate::authorize('gerenciar', User::class);

        // VIS-V1-013 — "LISTAR SOLICITACOES DE RMA ARQUIVADAS", construída sobre
        // `Status::Arquivado` (já existe no domínio via `rmas.arquivar`/`rmas.reverter`),
        // sem decisão de produto nova.
        $arquivados = RmaEloquent::query()
            ->with('fabricante')
            ->where('status', Status::Arquivado)
            ->orderByDesc('updated_at')
            ->get();

        return view('temas.v1.rma.controle', [
            'titulo' => 'Controle',
            'arquivados' => $arquivados,
        ]);
    }
}
