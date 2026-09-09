<?php

namespace App\Http\Controllers\Rma;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Fabricante;
use App\Models\Fornecedor;
use App\Models\Rma as RmaEloquent;
use App\Rma\Aplicacao\VerDetalheDoRma;
use App\Rma\Infraestrutura\CamposDeExibicaoDoRmaEmBanco;
use App\Rma\Aplicacao\Alertas\ListarGruposDeAlertas;
use App\Rma\Aplicacao\BuscarRmas;
use App\Rma\Dominio\CriterioDeBusca;
use App\Rma\Dominio\Solucao;
use App\Rma\Dominio\Status;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

/**
 * Console Operacional Adaptativa - dashboard e listagem de RMAs do TEMA V3.
 * Camada de apresentacao: reusa `BuscarRmas`, `ListarGruposDeAlertas` e o read
 * model Eloquent sem criar regra de negocio nova.
 */
final class V3ConsoleController extends Controller
{
    public function dashboard(ListarGruposDeAlertas $alertas): View
    {
        Gate::authorize('viewAny', RmaEloquent::class);

        $status = [Status::Entrada, Status::Recebido, Status::Encaminhado, Status::Concluido, Status::Arquivado];
        $contagemPorStatus = RmaEloquent::query()
            ->selectRaw('status, COUNT(*) as total')
            ->whereIn('status', $status)
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $contagem = static fn (Status $statusAlvo): int => (int) ($contagemPorStatus[$statusAlvo->name] ?? 0);
        $aguardandoCredito = RmaEloquent::query()
            ->where('solucao', Solucao::PendenteCredito)
            ->count();

        $grupos = $alertas->listar();

        return view_do_tema('dashboard.index', [
            'titulo' => 'Dashboard',
            'filas' => [
                ['slug' => 'entrada', 'rotulo' => 'Entrada', 'contagem' => $contagem(Status::Entrada)],
                ['slug' => 'recebido', 'rotulo' => 'Recebidos', 'contagem' => $contagem(Status::Recebido)],
                ['slug' => 'encaminhado', 'rotulo' => 'Encaminhados', 'contagem' => $contagem(Status::Encaminhado)],
                ['slug' => 'aguardando-credito', 'rotulo' => 'Aguardando credito', 'contagem' => $aguardandoCredito],
                ['slug' => 'concluido', 'rotulo' => 'Concluidos', 'contagem' => $contagem(Status::Concluido)],
                ['slug' => 'arquivado', 'rotulo' => 'Arquivados', 'contagem' => $contagem(Status::Arquivado)],
            ],
            'alertas' => collect($grupos)
                ->map(fn ($registros, $titulo) => ['titulo' => $titulo, 'contagem' => count($registros)])
                ->filter(fn (array $alerta) => $alerta['contagem'] > 0)
                ->values()
                ->all(),
        ]);
    }
    public function detalhe(
        int $rma,
        VerDetalheDoRma $caso,
        CamposDeExibicaoDoRmaEmBanco $camposDeExibicao,
    ): View {
        Gate::authorize('view', RmaEloquent::class);

        $registro = $caso->porId($rma);

        abort_if($registro === null, Response::HTTP_NOT_FOUND);

        $legado = $camposDeExibicao->obter($rma);

        return view_do_tema('rma.show', [
            'titulo' => 'RMA #' . $registro->id,
            'registro' => $registro,
            'legado' => $legado,
            'numeroExibicao' => $legado['numero_legado']
                ?? $legado['numero_da_empresa']
                ?? $registro->id,
            'fabricante' => $registro->fabricanteId ? Fabricante::find($registro->fabricanteId) : null,
            'fornecedor' => $registro->fornecedorId ? Fornecedor::find($registro->fornecedorId) : null,
            'cliente' => $registro->clienteId ? Cliente::find($registro->clienteId) : null,
            // T3-12 - link Editar apenas quando a Policy permite (o update real
            // continua gated no RmaController).
            'podeEditar' => Gate::allows('update', RmaEloquent::class),
        ]);
    }

    public function rmas(Request $request, BuscarRmas $buscarRmas): View
    {
        Gate::authorize('viewAny', RmaEloquent::class);

        $fila = (string) $request->query('fila', 'todos');
        $q = trim((string) $request->query('q', ''));
        $filasValidas = [
            'entrada' => Status::Entrada,
            'recebido' => Status::Recebido,
            'encaminhado' => Status::Encaminhado,
            'concluido' => Status::Concluido,
            'arquivado' => Status::Arquivado,
        ];

        $consulta = RmaEloquent::query()
            ->with(['fabricante:id,nome', 'fornecedor:id,nome', 'cliente:id,nome']);

        if ($q !== '') {
            $ids = array_map(
                static fn ($rma) => $rma->id,
                $buscarRmas->buscar(CriterioDeBusca::porTexto($q)),
            );
            $consulta->whereIn('id', $ids)->orderByDesc('id');
        } elseif ($fila === 'aguardando-credito') {
            $consulta->where('solucao', Solucao::PendenteCredito)->orderByDesc('id');
        } elseif (isset($filasValidas[$fila])) {
            $consulta->where('status', $filasValidas[$fila])->orderByDesc('id');
        } else {
            $consulta->orderByDesc('id');
        }

        $registros = $consulta->get();

        return view_do_tema('rma.index', [
            'titulo' => 'RMAs',
            'registros' => $registros,
            'filaAtual' => $fila,
            'q' => $q,
            'temBusca' => $q !== '',
        ]);
    }
}
