<?php

namespace App\Http\Controllers\Rma;

use App\Http\Controllers\Controller;
use App\Models\Rma as RmaEloquent;
use App\Rma\Aplicacao\Alertas\AguardandoCredito;
use App\Rma\Aplicacao\MarcarCreditoDisponivel;
use App\Rma\Aplicacao\VerDetalheDoRma;
use App\Rma\Dominio\RepositorioDeRmas;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fluxo único de crédito (`LEG-RMA-036`, ver `proposal.md` - reconstrói só a intenção
 * do módulo de créditos quebrado em TEMA V2, `LEG-RMA-048`, não as 3 sub-rotas
 * `pendentes/usados/disponíveis`). View mínima, sem fidelidade visual (Fase 8).
 */
class CreditoController extends Controller
{
    public function index(AguardandoCredito $aguardandoCredito, RepositorioDeRmas $repositorio): View
    {
        Gate::authorize('viewAny', RmaEloquent::class);

        // PAR15-CREDIT-001 - o V2 reproduz a tabela real do `15.8.1/page/credito.php`;
        // o V1 mantem o painel de relatorios do `14.6.1/menujs-right/creditos.php`.
        $creditos = $repositorio->listarCreditosDisponiveis();

        return view_do_tema('rma.credito.index', [
            'titulo' => 'Creditos',
            'creditos' => $creditos,
            'fabricantes' => $this->mapaDeFabricantes($creditos),
            'destinatarios' => $this->mapaDeDestinatarios($creditos),
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

    /**
     * @param  \App\Rma\Dominio\Rma[]  $registros
     * @return array<int, string>
     */
    private function mapaDeFabricantes(array $registros): array
    {
        $ids = collect($registros)->pluck('fabricanteId')->filter()->unique()->values()->all();

        return \App\Models\Fabricante::query()->whereIn('id', $ids)->pluck('nome', 'id')->all();
    }

    /**
     * @param  \App\Rma\Dominio\Rma[]  $registros
     * @return array<string, string>
     */
    private function mapaDeDestinatarios(array $registros): array
    {
        $mapa = [];

        collect($registros)
            ->filter(fn ($registro) => $registro->destinatarioType !== null && $registro->destinatarioId !== null)
            ->groupBy(fn ($registro) => $registro->destinatarioType)
            ->each(function ($grupo, $tipo) use (&$mapa): void {
                $ids = $grupo->pluck('destinatarioId')->unique()->all();

                foreach ($tipo::query()->whereIn('id', $ids)->pluck('nome', 'id') as $id => $nome) {
                    $mapa[$tipo.':'.$id] = $nome;
                }
            });

        return $mapa;
    }

    private function usuario(): \App\Models\User
    {
        /** @var \App\Models\User $usuario */
        $usuario = auth()->user();

        return $usuario;
    }
}
