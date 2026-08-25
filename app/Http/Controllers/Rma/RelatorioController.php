<?php

namespace App\Http\Controllers\Rma;

use App\Http\Controllers\Controller;
use App\Models\Rma as RmaEloquent;
use App\Rma\Aplicacao\Relatorios\RelatorioCreditosDisponiveis;
use App\Rma\Aplicacao\Relatorios\RelatorioProdutosEmEstoqueParaContagem;
use App\Rma\Aplicacao\Relatorios\RelatorioProdutosEncaminhados;
use App\Rma\Dominio\Status;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * 3 relatórios fiscais/contábeis (`LEG-RMA-037/038/039`) — consultas de leitura sobre
 * `Rma` já maduro depois da Fase 5, não módulo próprio (`INV-RMA-05` §3). Sem PDF real
 * (`EVO-REL-001`, backlog evolutivo) — impressão via `Ctrl+P`, igual ao legado. Views
 * mínimas, sem fidelidade visual (Fase 8).
 */
class RelatorioController extends Controller
{
    public function creditosDisponiveis(RelatorioCreditosDisponiveis $relatorio): View
    {
        Gate::authorize('viewAny', RmaEloquent::class);

        return view('rma.relatorios.rcd', [
            'registros' => $relatorio->listar(),
        ]);
    }

    /**
     * RPEC — status é filtro configurável pelo usuário (query string opcional
     * `status`), não hardcoded como no legado.
     */
    public function produtosEmEstoqueParaContagem(Request $request, RelatorioProdutosEmEstoqueParaContagem $relatorio): View
    {
        Gate::authorize('viewAny', RmaEloquent::class);

        $dados = $request->validate([
            'status' => ['nullable', 'string', 'in:' . implode(',', array_column(Status::cases(), 'name'))],
        ]);

        $status = isset($dados['status'])
            ? collect(Status::cases())->first(fn (Status $caso) => $caso->name === $dados['status'])
            : null;

        return view('rma.relatorios.rpec', [
            'registros' => $relatorio->listar($status),
            'status' => $status,
        ]);
    }

    /**
     * RMPE — intervalo de datas real via Form Request (`data_inicio`/`data_fim`
     * obrigatórios), substitui o intervalo hardcoded para "2014" do legado (bug de
     * manutenção, não RN documentada).
     */
    public function produtosEncaminhados(Request $request, RelatorioProdutosEncaminhados $relatorio): View
    {
        Gate::authorize('viewAny', RmaEloquent::class);

        $dados = $request->validate([
            'data_inicio' => ['required', 'date'],
            'data_fim' => ['required', 'date', 'after_or_equal:data_inicio'],
        ]);

        $registros = $relatorio->listar(
            new \DateTimeImmutable($dados['data_inicio']),
            new \DateTimeImmutable($dados['data_fim'] . ' 23:59:59'),
        );

        return view('rma.relatorios.rmpe', [
            'registros' => $registros,
            'dataInicio' => $dados['data_inicio'],
            'dataFim' => $dados['data_fim'],
        ]);
    }
}
