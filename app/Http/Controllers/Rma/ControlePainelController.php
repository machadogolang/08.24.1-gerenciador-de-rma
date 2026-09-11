<?php

namespace App\Http\Controllers\Rma;

use App\Http\Controllers\Controller;
use App\Models\AssistenciaTecnica;
use App\Models\Fabricante;
use App\Models\Fornecedor;
use App\Models\Rma as RmaEloquent;
use App\Models\User;
use App\Rma\Aplicacao\ArquivarRma;
use App\Rma\Aplicacao\VerDetalheDoRma;
use App\Rma\Dominio\Status;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/**
 * VIS-V1-010 / UI-V1-CONTROLE-01 - painel "Controle" do TEMA V1 (`legacy-source/14.6.1/page/controle.php`,
 * idêntico a `menujs-right/controle.php`: 7 ações administrativas). Não é o "Controle"
 * do TEMA V2 legado (`15.8.1/page/controle.php`, logs de modificação - esse continua em
 * `HistoricoDeModificacaoController`/`rmas.historico.index`, inalterado).
 *
 * Reaproveita ações e casos de uso do domínio de forma segura e atômica:
 * - Adicionar Representante: endpoint único que valida tipo em allow-list e delega ao Model com tenant.
 * - Arquivar RMA: endpoint específico que valida número no tenant, autoriza por Gate e invoca ArquivarRma.
 * - Mudar senha: reaproveita o perfil com segurança moderna.
 * - Deletar RMA/usuário: permanecem indisponíveis por decisão de produto / segurança pendente.
 */
class ControlePainelController extends Controller
{
    public function index(): View
    {
        Gate::authorize('gerenciar', User::class);

        // VIS-V1-013 - "LISTAR SOLICITACOES DE RMA ARQUIVADAS", construída sobre
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

    public function storeRepresentante(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'tipo' => ['required', Rule::in(['assistencia_tecnica', 'fornecedor', 'fabricante'])],
        ]);

        $nome = $dados['nome'];
        $tipo = $dados['tipo'];

        switch ($tipo) {
            case 'assistencia_tecnica':
                Gate::authorize('create', AssistenciaTecnica::class);
                AssistenciaTecnica::create(['nome' => $nome]);
                $mensagem = 'Assistência técnica cadastrada com sucesso.';
                break;
            case 'fornecedor':
                Gate::authorize('create', Fornecedor::class);
                Fornecedor::create(['nome' => $nome]);
                $mensagem = 'Fornecedor cadastrado com sucesso.';
                break;
            case 'fabricante':
                Gate::authorize('create', Fabricante::class);
                Fabricante::create(['nome' => $nome]);
                $mensagem = 'Fabricante cadastrado com sucesso.';
                break;
        }

        return redirect()->route('rmas.controle.index')
            ->with('status', $mensagem)
            ->with('painel_aberto', 'representante');
    }

    public function arquivarRma(Request $request, VerDetalheDoRma $buscar, ArquivarRma $caso): RedirectResponse
    {
        $dados = $request->validate([
            'numero' => ['required', 'numeric'],
        ]);

        $numero = (int) $dados['numero'];
        $model = RmaEloquent::query()->where('id', $numero)->first();

        $contexto = app(\App\Compartilhado\Tenant\ContextoDeTenant::class);
        $tenantInvalido = $contexto->temEmpresa() && $model !== null && $model->tenant_id !== $contexto->empresaId();

        if ($model === null || $tenantInvalido) {
            return redirect()->route('rmas.controle.index')
                ->withErrors(['numero' => "RMA {$numero} não encontrado."])
                ->with('painel_aberto', 'arquivar');
        }

        $registro = $buscar->porId($numero);
        if ($registro === null) {
            return redirect()->route('rmas.controle.index')
                ->withErrors(['numero' => "RMA {$numero} não encontrado."])
                ->with('painel_aberto', 'arquivar');
        }

        Gate::authorize('update', RmaEloquent::class);
        $caso->arquivar(auth()->user(), $registro);

        return redirect()->route('rmas.controle.index')
            ->with('status', "RMA {$numero} arquivado com sucesso.")
            ->with('painel_aberto', 'arquivar');
    }
}
