<?php

namespace App\Http\Controllers\Rma;

use App\Http\Controllers\Controller;
use App\Models\AssistenciaTecnica;
use App\Models\Cliente;
use App\Models\Fabricante;
use App\Models\Fornecedor;
use App\Models\Rma as RmaEloquent;
use App\Models\User;
use App\Rma\Aplicacao\Alertas\ListarGruposDeAlertas;
use App\Rma\Aplicacao\ArquivarRma;
use App\Rma\Aplicacao\BuscarRmas;
use App\Rma\Aplicacao\ConcluirRma;
use App\Rma\Aplicacao\CriarRma;
use App\Rma\Aplicacao\Destinatarios\OpcoesDeDestinatario;
use App\Rma\Aplicacao\EditarRma;
use App\Rma\Aplicacao\EncaminharRma;
use App\Rma\Aplicacao\ReceberRma;
use App\Rma\Aplicacao\RegistrarSolucao;
use App\Rma\Aplicacao\ReverterRmaParaEntrada;
use App\Rma\Aplicacao\VerDetalheDoRma;
use App\Rma\Dominio\CriterioDeBusca;
use App\Rma\Dominio\PainelDeStatus;
use App\Rma\Dominio\RepositorioDeRmas;
use App\Rma\Dominio\Rma;
use App\Rma\Dominio\Solucao;
use App\Rma\Dominio\Status;
use App\Rma\Infraestrutura\CamposDeExibicaoDoRmaEmBanco;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class RmaController extends Controller
{
    public function index(
        Request $request,
        BuscarRmas $caso,
        ListarGruposDeAlertas $listarGruposDeAlertas,
        RepositorioDeRmas $repositorio,
    ): View {
        Gate::authorize('viewAny', RmaEloquent::class);

        $tipo = $request->query('tipo', 'texto');
        $valor = (string) $request->query('valor', '');

        // CP7 (fase 2 V1) - painel Localizar histórico (`menujs-top/localizar.php`)
        // manda `campo` (13 opções + TUDO) em vez de `tipo`; mapeado aqui, na camada
        // de apresentação, para os tipos que `CriterioDeBusca`/`RmasEmBanco::buscar()`
        // aceitam. ARQ-004 (2026-09-09): `NF`→`nota_fiscal` (campos fiscais reais,
        // fonte `page/localizar.php:9`) e `os`→`os` (coluna própria, mesma fonte,
        // ramo `else`); `SNPNSNID`→serial. Os demais campos sem coluna direta no
        // agregado (`fabricante`/`cliente`/`destinatario`/`protocolo`/`numero` etc.)
        // continuam no fallback `texto` - `[GAP]` documentado, coberto por PAR-RMA-003.
        if ($request->has('campo')) {
            $tipo = match ($request->query('campo')) {
                'NF' => 'nota_fiscal',
                'os' => 'os',
                'SNPNSNID' => 'serial',
                'numero' => 'numero',
                default => 'texto',
            };
        }

        $solucaoQuery = (string) $request->query('solucao', '');
        $solucao = Solucao::tryFrom($solucaoQuery);

        $criterio = match ($tipo) {
            'serial' => CriterioDeBusca::porSerial($valor, $solucao),
            'nota_fiscal' => CriterioDeBusca::porNotaFiscal($valor, $solucao),
            'os' => CriterioDeBusca::porOs($valor, $solucao),
            'numero' => CriterioDeBusca::porNumero($valor, $solucao),
            default => CriterioDeBusca::porTexto($valor, $solucao),
        };

        $rmas = ($valor !== '' || $solucao !== null) ? $caso->buscar($criterio) : [];

        // CP23 (paridade visual V2) - as abas Entrada/Recebido/Encaminhado/Concluído
        // (`15.8.1/page/{entrada,recebido,encaminhado,concluido}.php`) são listagens
        // próprias por status, sempre cheias - NÃO um recorte do resultado de busca
        // (achado: a implementação anterior filtrava `$rmas`, que só tem conteúdo
        // quando há termo de busca, deixando essas 4 abas vazias por padrão).
        $porStatusV2 = [
            'entrada' => $repositorio->listarPorPainel(PainelDeStatus::EntradaSomente),
            'recebido' => $repositorio->listarPorPainel(PainelDeStatus::RecebidoSomente),
            'encaminhado' => $repositorio->listarPorPainel(PainelDeStatus::Encaminhados),
            'concluido' => $repositorio->listarPorPainel(PainelDeStatus::Concluidos),
        ];
        $todosOsRegistrosDasAbas = array_merge($rmas, ...array_values($porStatusV2));
        $grupos = $listarGruposDeAlertas->listar();
        $todosOsRegistrosDaPagina = array_merge(
            $todosOsRegistrosDasAbas,
            ...array_map(static fn ($grupo) => $grupo->all(), array_values($grupos)),
        );

        return view_do_tema('rma.index', [
            'titulo' => 'RMAs',
            'rmas' => $rmas,
            'tipo' => $tipo,
            'valor' => $valor,
            // CP7 (fase 2 V1) - reflete a última busca nos selects de
            // `_form_localizar.blade.php` (o Legacy não fazia isso, `<option
            // selected>` estático - melhoria de UX sem custo de fidelidade visual
            // estática, mesmo critério já usado para o autosave de Anotações).
            'campo' => $request->query('campo', 'TUDO'),
            'solucao' => $solucaoQuery !== '' ? $solucaoQuery : '%',
            'porStatusV2' => $porStatusV2,
            // CP20/CP23 (paridade visual V2) - as tabelas históricas mostram nome de
            // fabricante/destinatário, não só o id; mesmo padrão de
            // `ListagensPorStatusController::mapaDeFabricantes()`/
            // `mapaDeDestinatarios()`, agora cobrindo busca + as 4 abas por status.
            'fabricantes' => $this->mapaDeFabricantes($todosOsRegistrosDaPagina),
            'fornecedores' => $this->mapaDeFornecedores($todosOsRegistrosDaPagina),
            'destinatarios' => $this->mapaDeDestinatarios($todosOsRegistrosDaPagina),
            // "CENTRO DE AVISOS E RELATORIOS" (correção de fidelidade Fase 8,
            // 2026-08-25) - a aba "Início"/"Pág. Inicial" dos dois temas mostra as
            // mesmas 10 regras da Fase 5 (`PainelDeAlertasController`), sempre
            // presente no HTML (mesmo mecanismo de abas client-side documentado no
            // design.md). `ListarGruposDeAlertas` é a mesma composição usada por
            // `PainelDeAlertasController` - nenhuma regra de negócio nova, nenhuma
            // duplicação de lógica entre as duas telas.
            'grupos' => $grupos,
            // Sidebar "contadores por solução" - só consumida pelo TEMA V1
            // (`14.6.1/index.php`, achado confirmado por captura de referência),
            // fonte real: contagem de RMAs por `status`/`solucao`. Consulta de
            // composição direta (não é caso de uso/regra de negócio nova).
            'contadores' => $this->contadoresDoPainel(),
            // PAR-V2-NOVO-01 - listas Eloquent para o formulario inline da aba Novo
            // (a index ja busca mapas de nomes para as tabelas; aqui sao listas
            // para selects do formulario, mesmo padrao de `RmaController::create`).
            'fabricantesParaNovo' => Fabricante::query()->orderBy('nome')->get(),
            'fornecedoresParaNovo' => Fornecedor::query()->orderBy('nome')->get(),
        ]);
    }

    /**
     * @param  Rma[]  $registros
     * @return array<int, string>
     */
    private function mapaDeFabricantes(array $registros): array
    {
        $ids = array_unique(array_filter(array_map(
            fn ($r) => $r instanceof Rma ? $r->fabricanteId : $r->fabricante_id,
            $registros,
        )));

        return $ids === [] ? [] : Fabricante::query()->whereIn('id', $ids)->pluck('nome', 'id')->all();
    }

    /**
     * CP12-05 (fase 2 V1) - as tabelas históricas do Centro de Avisos mostram o
     * nome do fornecedor. Resolve ids na apresentação sem acoplar o domínio ao
     * Eloquent e incluindo os registros dos grupos, que não necessariamente estão
     * nas quatro abas carregadas na mesma requisição.
     *
     * @param  Rma[]  $registros
     * @return array<int, string>
     */
    private function mapaDeFornecedores(array $registros): array
    {
        $ids = array_unique(array_filter(array_map(
            fn ($r) => $r instanceof Rma ? $r->fornecedorId : $r->fornecedor_id,
            $registros,
        )));

        return $ids === [] ? [] : Fornecedor::query()->whereIn('id', $ids)->pluck('nome', 'id')->all();
    }

    /**
     * `destinatarioType`/`destinatarioId` são polimórficos, sem `morphMap` - mesmo
     * padrão de `ListagensPorStatusController::mapaDeDestinatarios()`.
     *
     * @param  Rma[]  $registros
     * @return array<string, string>
     */
    private function mapaDeDestinatarios(array $registros): array
    {
        $idsPorTipo = [];
        foreach ($registros as $registro) {
            $tipo = $registro instanceof Rma ? $registro->destinatarioType : $registro->destinatario_type;
            $id = $registro instanceof Rma ? $registro->destinatarioId : $registro->destinatario_id;
            if ($tipo !== null && $id !== null) {
                $idsPorTipo[$tipo][] = $id;
            }
        }

        $mapa = [];
        foreach ($idsPorTipo as $tipo => $ids) {
            if (! class_exists($tipo)) {
                continue;
            }

            foreach ($tipo::query()->whereIn('id', array_unique($ids))->get(['id', 'nome']) as $model) {
                $mapa[$tipo.'#'.$model->id] = $model->nome;
            }
        }

        return $mapa;
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
            // T3-12 - listas completas para o formulario V3 em secoes.
            'assistenciasTecnicas' => AssistenciaTecnica::query()->orderBy('nome')->get(),
        ]);
    }

    public function store(Request $request, CriarRma $caso): RedirectResponse
    {
        Gate::authorize('create', RmaEloquent::class);

        $dados = $this->validarDados($request);
        // Checkbox HTML: ausente na requisição quando desmarcado (mesma semântica do
        // legado, `isset($_POST['marcarestoque'])` - ver `post/novo.php`).
        $dados['marcarestoque'] = $request->boolean('marcarestoque');

        $rma = $caso->criar($dados);

        return redirect(rota_tema('rmas.show', ['rma' => $rma->id]))->with('status', 'RMA criado.');
    }

    public function show(
        int $rma,
        VerDetalheDoRma $caso,
        CamposDeExibicaoDoRmaEmBanco $camposDeExibicao,
    ): View {
        Gate::authorize('view', RmaEloquent::class);

        $registro = $caso->porId($rma);

        abort_if($registro === null, Response::HTTP_NOT_FOUND);

        $legado = $camposDeExibicao->obter($rma);
        $fabricante = $registro->fabricanteId ? Fabricante::find($registro->fabricanteId) : null;
        $fornecedor = $registro->fornecedorId ? Fornecedor::find($registro->fornecedorId) : null;
        $cliente = $registro->clienteId ? Cliente::find($registro->clienteId) : null;

        return view_do_tema('rma.show', [
            'titulo' => 'RMA #'.$registro->id,
            'registro' => $registro,
            'fabricante' => $fabricante,
            'fornecedor' => $fornecedor,
            'cliente' => $cliente,
            // PAR-DET-V1-EDIT-01 - listas para edicao inline do detalhe V1 (selects
            // com nomes reais, sem resolver por texto arbitrario no servidor).
            'fabricantesLista' => Fabricante::query()->orderBy('nome')->get(),
            'fornecedoresLista' => Fornecedor::query()->orderBy('nome')->get(),
            'assistenciasTecnicasLista' => AssistenciaTecnica::query()->orderBy('nome')->get(),
            // PAR-DET-V1-01/PAR-DET-V2-01 - campos historicos de apresentacao
            // (colunas preservadas pela Fase 9; leitura exclusiva desta tela).
            'legado' => $legado,
            'numeroExibicao' => $legado['numero_legado']
                ?? $legado['numero_da_empresa']
                ?? $registro->id,
            'clienteEmail' => $legado['cliente_email_legado'] ?: $cliente?->email,
            'destinatarioEmail' => $legado['destinatario_email_legado'],
            'destinatarioFone' => $legado['destinatario_fone_legado'],
            'destinatario' => $legado['destinatario_nome'] !== null && $legado['destinatario_nome'] !== ''
                ? [
                    'type' => $legado['destinatario_type'],
                    'id' => $legado['destinatario_id'],
                    'nome' => $legado['destinatario_nome'],
                ]
                : null,
            'politicaDeGarantia' => $this->politicaDeGarantiaParaDetalhe($legado, $registro, $fabricante, $fornecedor),
            // PAR-V2-DETAIL-02 - a view usa a Policy para liberar/desabilitar os
            // controles; a autorizacao real de escrita continua no update.
            'podeEditar' => Gate::allows('update', RmaEloquent::class),
        ]);
    }

    /**
     * PAR-DET-V1-01/PAR-DET-V2-01 - selecao historica da politica exibida no detalhe
     * (14.6.1/page/detalhes.php e 15.8.1/page/rma.php): destinatario quando existe;
     * sem destinatario, fabricante e depois fornecedor. Nenhuma regra de ciclo de
     * vida e alterada; e somente escolha de leitura para a tela.
     *
     * @param  array<string, mixed>  $legado
     * @return array{tipo: string, nome: string, texto: string}|null
     */
    private function politicaDeGarantiaParaDetalhe(
        array $legado,
        Rma $registro,
        ?Fabricante $fabricante,
        ?Fornecedor $fornecedor,
    ): ?array {
        $politicaDe = function (?string $tipo, ?int $id): ?array {
            if ($tipo === null || $id === null || ! class_exists($tipo)) {
                return null;
            }

            $entidade = $tipo::query()->find($id);
            if ($entidade === null || empty($entidade->politica_de_garantia)) {
                return null;
            }

            return [
                'tipo' => 'destinatario',
                'nome' => $entidade->nome,
                'texto' => $entidade->politica_de_garantia,
            ];
        };

        if ($legado['destinatario_type'] !== null || $legado['destinatario_id'] !== null) {
            $politica = $politicaDe($legado['destinatario_type'], $legado['destinatario_id']);
            if ($politica !== null) {
                return $politica;
            }
        }

        if ($fabricante !== null && ! empty($fabricante->politica_de_garantia)) {
            return [
                'tipo' => 'fabricante',
                'nome' => $fabricante->nome,
                'texto' => $fabricante->politica_de_garantia,
            ];
        }

        if ($fornecedor !== null && ! empty($fornecedor->politica_de_garantia)) {
            return [
                'tipo' => 'fornecedor',
                'nome' => $fornecedor->nome,
                'texto' => $fornecedor->politica_de_garantia,
            ];
        }

        return null;
    }

    public function edit(int $rma, VerDetalheDoRma $caso): View
    {
        Gate::authorize('update', RmaEloquent::class);

        $registro = $caso->porId($rma);

        abort_if($registro === null, Response::HTTP_NOT_FOUND);

        return view_do_tema('rma.edit', [
            'titulo' => 'Editar RMA #'.$registro->id,
            'numeroExibicao' => $registro->id,
            'registro' => $registro,
            'fabricantes' => Fabricante::query()->orderBy('nome')->get(),
            'fornecedores' => Fornecedor::query()->orderBy('nome')->get(),
            // T3-12 - formulario V3 em secoes precisa de todas as listas e do
            // nome do cliente para popular os controles.
            'assistenciasTecnicas' => AssistenciaTecnica::query()->orderBy('nome')->get(),
            'clienteNome' => $registro->clienteId ? (Cliente::find($registro->clienteId)?->nome ?? '') : '',
        ]);
    }

    public function update(
        Request $request,
        int $rma,
        EditarRma $caso,
        RegistrarSolucao $registrarSolucao,
        ReceberRma $receberRma,
        EncaminharRma $encaminharRma,
        ConcluirRma $concluirRma,
        ReverterRmaParaEntrada $reverterRma,
        ArquivarRma $arquivarRma,
    ): RedirectResponse {
        Gate::authorize('update', RmaEloquent::class);

        $dados = $this->validarDados($request);

        $registro = $caso->editar($rma, $dados);

        // PAR-DET-V1-EDIT-01 - o detalhe V1 envia o select de solucao junto com os
        // demais campos; a gravacao usa o caso de uso moderno (RegistrarSolucao),
        // nao SQL no blade nem endpoint monolitico do Legacy.
        if ($request->filled('solucao')) {
            $solucao = Solucao::tryFrom((string) $request->input('solucao'));
            abort_if($solucao === null, 422);
            $registro = $registrarSolucao->registrar($this->usuario(), $registro, $solucao);
        }

        $this->executarAcaoDoDetalhe(
            (string) $request->input('acao', 'salvar'),
            $request,
            $registro,
            $receberRma,
            $encaminharRma,
            $concluirRma,
            $reverterRma,
            $arquivarRma,
        );

        $mensagens = [
            'receber' => 'RMA recebido.',
            'encaminhar' => 'RMA encaminhado.',
            'concluir' => 'RMA concluido.',
            'reverter' => 'RMA revertido para Entrada.',
            'arquivar' => 'RMA arquivado.',
            'salvar' => 'RMA atualizado.',
        ];

        return redirect(rota_tema('rmas.show', ['rma' => $registro->id]))
            ->with('status', $mensagens[(string) $request->input('acao', 'salvar')] ?? 'RMA atualizado.');
    }

    private function usuario(): User
    {
        /** @var User $usuario */
        $usuario = auth()->user();

        return $usuario;
    }

    private function executarAcaoDoDetalhe(
        string $acao,
        Request $request,
        Rma $registro,
        ReceberRma $receberRma,
        EncaminharRma $encaminharRma,
        ConcluirRma $concluirRma,
        ReverterRmaParaEntrada $reverterRma,
        ArquivarRma $arquivarRma,
    ): void {
        if ($acao === 'salvar') {
            return;
        }

        $dados = match ($acao) {
            'concluir' => $request->validate([
                'solucao' => ['required', 'string'],
            ]),
            default => [],
        };

        if ($acao === 'encaminhar') {
            // UX-003/P7 - mesmo resolvedor validado do V1 (tipo permitido, existencia e
            // tenant) em vez de `explode` cru; a relacao polimorfica guarda o FQCN.
            [$destinatarioType, $destinatarioId] = app(OpcoesDeDestinatario::class)
                ->resolver((string) $request->input('destinatario_tipo'));

            $encaminharRma->encaminhar($this->usuario(), $registro, $destinatarioType, $destinatarioId);

            return;
        }

        match ($acao) {
            'receber' => $receberRma->receber($this->usuario(), $registro),
            'concluir' => $concluirRma->concluir(
                $this->usuario(),
                $registro,
                Solucao::from($dados['solucao']),
            ),
            'reverter' => $reverterRma->reverter($this->usuario(), $registro),
            'arquivar' => $arquivarRma->arquivar($this->usuario(), $registro),
            default => abort(422),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function validarDados(Request $request): array
    {
        // CP8 (fase 2 V1) - `menujs-top/novo.php` usa `type="text"
        // placeholder="00/00/2015"` pras datas, não `type="date"` (o TEMA V2 e o
        // fallback `/rmas/create` continuam com o date picker nativo, que já manda
        // ISO `Y-m-d`) - normaliza só quando o valor bate com `dd/mm/aaaa`, mantendo
        // o `Y-m-d` do TEMA V2 intocado.
        foreach (['nfcompra_emissao', 'nfvenda_emissao'] as $campo) {
            $valor = $request->input($campo);
            if (is_string($valor) && preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $valor, $partes) === 1) {
                $request->merge([$campo => sprintf('%s-%02d-%02d', $partes[3], (int) $partes[2], (int) $partes[1])]);
            }
        }

        $dados = $request->validate([
            'acao' => ['nullable', 'string', 'in:salvar,receber,encaminhar,concluir,reverter,arquivar'],
            'descricao' => ['required', 'string', 'max:255'],
            'fabricante_id' => ['nullable', 'integer', 'exists:fabricantes,id'],
            'fabricante_nome' => ['nullable', 'string', 'max:255'],
            'fornecedor_id' => ['nullable', 'integer', 'exists:fornecedores,id'],
            'fornecedor_nome' => ['nullable', 'string', 'max:255'],
            'modelo' => ['nullable', 'string', 'max:255'],
            'sn' => ['nullable', 'string', 'max:255'],
            'os' => ['nullable', 'string', 'max:255'],
            'origem' => ['nullable', 'string', 'max:255'],
            'empresa' => ['nullable', 'string', 'max:255'],
            'cliente_nome' => ['nullable', 'string', 'max:255'],
            'defeito' => ['required', 'string', 'max:255'],
            'observacao' => ['nullable', 'string'],
            'pn' => ['nullable', 'string', 'max:255'],
            'snid' => ['nullable', 'string', 'max:255'],
            'protocolo' => ['nullable', 'string', 'max:255'],
            'valor' => ['nullable', 'string', 'max:20'],
            'snretorno' => ['nullable', 'string', 'max:255'],
            'marcarestoque' => ['sometimes', 'boolean'],
            'credito_disponivel' => ['sometimes', 'boolean'],
            'prioridade' => ['nullable', 'string', 'in:baixa,media,alta,Baixa,Media,Alta,Normal,normal'],
            'lancadoretorno' => ['nullable', 'string', 'in:pendente,nf_devolucao,sem_movimentacao,nao,sim'],
            'solucao' => ['nullable', 'string', 'in:'.implode(',', array_column(Solucao::cases(), 'value'))],
            'nfcompra' => ['nullable', 'string', 'max:255'],
            'nfcompra_emissao' => ['nullable', 'date'],
            'nfcompra_chave' => ['nullable', 'string', 'max:500'],
            'nfvenda' => ['nullable', 'string', 'max:255'],
            'nfvenda_emissao' => ['nullable', 'date'],
            'nfvenda_chave' => ['nullable', 'string', 'max:500'],
            'nf_entrada_cliente_legado' => ['nullable', 'string', 'max:255'],
            'nf_retorno_cliente_legado' => ['nullable', 'string', 'max:255'],
            'nf_devolucao_de_venda' => ['nullable', 'string', 'max:255'],
            'nf_remessa' => ['nullable', 'string', 'max:255'],
            'nf_remessa_emissao' => ['nullable', 'string', 'max:30'],
            'nf_remessa_chave' => ['nullable', 'string', 'max:500'],
            'nf_retorno_numero' => ['nullable', 'string', 'max:255'],
            'nf_retorno_emissao' => ['nullable', 'string', 'max:30'],
            'nf_retorno_chave' => ['nullable', 'string', 'max:500'],
            'rastreio_ida' => ['nullable', 'string', 'max:255'],
            'rastreio_retorno' => ['nullable', 'string', 'max:255'],
            'cliente_email_legado' => ['nullable', 'string', 'max:255'],
            'destinatario_email_legado' => ['nullable', 'string', 'max:255'],
            'destinatario_fone_legado' => ['nullable', 'string', 'max:255'],
            'destinatario_nome_legado' => ['nullable', 'string', 'max:255'],
            'destinatario_tipo' => ['nullable', 'string', 'regex:/^[a-z_]+:\d+$/'],
        ]);

        return $dados;
    }
}
