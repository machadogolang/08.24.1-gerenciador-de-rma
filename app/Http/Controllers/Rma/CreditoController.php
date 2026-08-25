<?php

namespace App\Http\Controllers\Rma;

use App\Http\Controllers\Controller;
use App\Models\Rma as RmaEloquent;
use App\Rma\Aplicacao\Alertas\AguardandoCredito;
use App\Rma\Aplicacao\MarcarCreditoDisponivel;
use App\Rma\Aplicacao\VerDetalheDoRma;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fluxo único de crédito (`LEG-RMA-036`, ver `proposal.md` — reconstrói só a intenção
 * do módulo de créditos quebrado em TEMA V2, `LEG-RMA-048`, não as 3 sub-rotas
 * `pendentes/usados/disponíveis`). View mínima, sem fidelidade visual (Fase 8).
 */
class CreditoController extends Controller
{
    public function index(AguardandoCredito $aguardandoCredito): View
    {
        Gate::authorize('viewAny', RmaEloquent::class);

        return view('rma.credito.index', [
            'aguardandoCredito' => $aguardandoCredito->listar(),
        ]);
    }

    public function marcar(Request $request, VerDetalheDoRma $buscar, MarcarCreditoDisponivel $caso): RedirectResponse
    {
        Gate::authorize('update', RmaEloquent::class);

        $dados = $request->validate([
            'rma_id' => ['required', 'integer'],
        ]);

        $registro = $buscar->porId((int) $dados['rma_id']);

        abort_if($registro === null, Response::HTTP_NOT_FOUND);

        $caso->marcar($this->usuario(), $registro);

        return redirect()->route('rmas.credito.index')->with('status', 'Crédito marcado como disponível.');
    }

    private function usuario(): \App\Models\User
    {
        /** @var \App\Models\User $usuario */
        $usuario = auth()->user();

        return $usuario;
    }
}
