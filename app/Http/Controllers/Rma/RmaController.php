<?php

namespace App\Http\Controllers\Rma;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Fabricante;
use App\Models\Fornecedor;
use App\Models\Rma as RmaEloquent;
use App\Rma\Aplicacao\Alertas\ListarGruposDeAlertas;
use App\Rma\Aplicacao\BuscarRmas;
use App\Rma\Aplicacao\CriarRma;
use App\Rma\Aplicacao\EditarRma;
use App\Rma\Aplicacao\VerDetalheDoRma;
use App\Rma\Dominio\CriterioDeBusca;
use App\Rma\Dominio\Solucao;
use App\Rma\Dominio\Status;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class RmaController extends Controller
{
    public function index(Request $request, BuscarRmas $caso, ListarGruposDeAlertas $listarGruposDeAlertas): View
    {
        Gate::authorize('viewAny', RmaEloquent::class);

        $tipo = $request->query('tipo', 'texto');
        $valor = (string) $request->query('valor', '');

        $criterio = match ($tipo) {
            'serial' => CriterioDeBusca::porSerial($valor),
            'nota_fiscal' => CriterioDeBusca::porNotaFiscal($valor),
            default => CriterioDeBusca::porTexto($valor),
        };

        return view_do_tema('rma.index', [
            'titulo' => 'RMAs',
            'rmas' => $valor !== '' ? $caso->buscar($criterio) : [],
            'tipo' => $tipo,
            'valor' => $valor,
            // "CENTRO DE AVISOS E RELATORIOS" (correção de fidelidade Fase 8,
            // 2026-08-25) — a aba "Início"/"Pág. Inicial" dos dois temas mostra as
            // mesmas 11 regras da Fase 5 (`PainelDeAlertasController`), sempre
            // presente no HTML (mesmo mecanismo de abas client-side documentado no
            // design.md). `ListarGruposDeAlertas` é a mesma composição usada por
            // `PainelDeAlertasController` — nenhuma regra de negócio nova, nenhuma
            // duplicação de lógica entre as duas telas.
            'grupos' => $listarGruposDeAlertas->listar(),
            // Sidebar "contadores por solução" — só consumida pelo TEMA V1
            // (`14.6.1/index.php`, achado confirmado por captura de referência),
            // fonte real: contagem de RMAs por `status`/`solucao`. Consulta de
            // composição direta (não é caso de uso/regra de negócio nova).
            'contadores' => $this->contadoresDoPainel(),
        ]);
    }

    /**
     * @return array<string, int>
     */
    private function contadoresDoPainel(): array
    {
        return [
            'ENTRADA' => RmaEloquent::query()->where('status', Status::Entrada)->count(),
            'PENDENTE CREDITO' => RmaEloquent::query()->where('solucao', Solucao::PendenteCredito)->count(),
            'ENCAMINHADO' => RmaEloquent::query()->where('status', Status::Encaminhado)->count(),
            'CONCLUIDO' => RmaEloquent::query()->where('status', Status::Concluido)->count(),
            'SEM GARANTIA' => RmaEloquent::query()->where('solucao', Solucao::SemGarantia)->count(),
            'GERADO CREDITO' => RmaEloquent::query()->where('solucao', Solucao::GeradoCredito)->count(),
            'REPARO' => RmaEloquent::query()->where('solucao', Solucao::Reparo)->count(),
            'TROCA DO PRODUTO' => RmaEloquent::query()->where('solucao', Solucao::TrocaDoProduto)->count(),
            'TROCA DE PECA INTERNA' => RmaEloquent::query()->where('solucao', Solucao::TrocaDePecaInterna)->count(),
            'DEVOLUCAO DO PRODUTO' => RmaEloquent::query()->where('solucao', Solucao::DevolucaoDoProduto)->count(),
            'REEMBOLSO DO DINHEIRO' => RmaEloquent::query()->where('solucao', Solucao::ReembolsoDoDinheiro)->count(),
            'REPARO PELO RMA' => RmaEloquent::query()->where('solucao', Solucao::ReparoPeloRma)->count(),
            'TESTADO TUDO OK' => RmaEloquent::query()->where('solucao', Solucao::TestadoTudoOk)->count(),
            'ORCAMENTO PAGO' => RmaEloquent::query()->where('solucao', Solucao::OrcamentoPago)->count(),
            'PROCON' => RmaEloquent::query()->where('solucao', Solucao::Procon)->count(),
            'QUANTIDADE TOTAL DE ITENS' => RmaEloquent::query()->count(),
        ];
    }

    public function create(): View
    {
        Gate::authorize('create', RmaEloquent::class);

        return view_do_tema('rma.create', [
            'titulo' => 'Novo RMA',
            'fabricantes' => Fabricante::query()->orderBy('nome')->get(),
            'fornecedores' => Fornecedor::query()->orderBy('nome')->get(),
        ]);
    }

    public function store(Request $request, CriarRma $caso): RedirectResponse
    {
        Gate::authorize('create', RmaEloquent::class);

        $dados = $this->validarDados($request);
        // Checkbox HTML: ausente na requisição quando desmarcado (mesma semântica do
        // legado, `isset($_POST['marcarestoque'])` — ver `post/novo.php`).
        $dados['marcarestoque'] = $request->boolean('marcarestoque');

        $rma = $caso->criar($dados);

        return redirect(rota_tema('rmas.show', ['rma' => $rma->id]))->with('status', 'RMA criado.');
    }

    public function show(int $rma, VerDetalheDoRma $caso): View
    {
        Gate::authorize('view', RmaEloquent::class);

        $registro = $caso->porId($rma);

        abort_if($registro === null, Response::HTTP_NOT_FOUND);

        return view_do_tema('rma.show', [
            'titulo' => 'RMA #' . $registro->id,
            'registro' => $registro,
            'fabricante' => $registro->fabricanteId ? Fabricante::find($registro->fabricanteId) : null,
            'fornecedor' => $registro->fornecedorId ? Fornecedor::find($registro->fornecedorId) : null,
            'cliente' => $registro->clienteId ? Cliente::find($registro->clienteId) : null,
        ]);
    }

    public function edit(int $rma, VerDetalheDoRma $caso): View
    {
        Gate::authorize('update', RmaEloquent::class);

        $registro = $caso->porId($rma);

        abort_if($registro === null, Response::HTTP_NOT_FOUND);

        return view_do_tema('rma.edit', [
            'titulo' => 'Editar RMA #' . $registro->id,
            'registro' => $registro,
            'fabricantes' => Fabricante::query()->orderBy('nome')->get(),
            'fornecedores' => Fornecedor::query()->orderBy('nome')->get(),
        ]);
    }

    public function update(Request $request, int $rma, EditarRma $caso): RedirectResponse
    {
        Gate::authorize('update', RmaEloquent::class);

        $dados = $this->validarDados($request);

        $registro = $caso->editar($rma, $dados);

        return redirect(rota_tema('rmas.show', ['rma' => $registro->id]))->with('status', 'RMA atualizado.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validarDados(Request $request): array
    {
        $dados = $request->validate([
            'descricao' => ['required', 'string', 'max:255'],
            'fabricante_id' => ['nullable', 'integer', 'exists:fabricantes,id'],
            'fornecedor_id' => ['nullable', 'integer', 'exists:fornecedores,id'],
            'modelo' => ['nullable', 'string', 'max:255'],
            'sn' => ['nullable', 'string', 'max:255'],
            'os' => ['nullable', 'string', 'max:255'],
            'origem' => ['nullable', 'string', 'max:255'],
            'empresa' => ['nullable', 'string', 'max:255'],
            'cliente_nome' => ['nullable', 'string', 'max:255'],
            'defeito' => ['required', 'string', 'max:255'],
            'observacao' => ['nullable', 'string'],
            // VIS-V1-003 (Grupo A) — já existiam no agregado (`App\Rma\Dominio\Rma`) e
            // na coluna, mas nunca chegavam validados até `CriarRma`. `pn`/`snid`
            // promovidos de coluna histórica para campo de primeira classe (ver
            // docblock do construtor de `Rma`).
            'nfcompra' => ['nullable', 'string', 'max:255'],
            'nfcompra_emissao' => ['nullable', 'date'],
            'nfvenda' => ['nullable', 'string', 'max:255'],
            'nfvenda_emissao' => ['nullable', 'date'],
            'pn' => ['nullable', 'string', 'max:255'],
            'snid' => ['nullable', 'string', 'max:255'],
        ]);

        return $dados;
    }
}
