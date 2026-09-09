{{--
    "CENTRO DE AVISOS E RELATORIOS" — correção de fidelidade Fase 8 (2026-08-25).
    Fonte real confirmada por captura autenticada de `http://localhost:8094/15.8.1/` e
    `http://localhost:8094/14.6.1/` (as 10 regras da Fase 5, mesma composição de
    `PainelDeAlertasController::index()`, `$grupos` no mesmo formato
    `titulo => Collection<Rma>`). Compartilhado pelos DOIS temas (elemento idêntico nos
    dois, só a folha de estilo em volta muda) — por isso vive em `resources/views/rma/`,
    não em `temas/{v1,v2}/`. Ícones vendorizados de `legacy-source/images/` (mesmos
    bytes do legado: `lembrete.png`, `retornou.png`, `separador.png`).
--}}
<div class="centro-de-avisos">
    <img class="centro-de-avisos-icone" src="{{ asset('images/rma/lembrete.png') }}" alt="Lembrete" width="40">
    <h5 class="centro-de-avisos-titulo">CENTRO DE AVISOS E RELATORIOS</h5>
    <div class="both"></div>
    <hr>
</div>

@foreach ($grupos as $titulo => $rmas)
    @php
        $alvo = 'centro-de-avisos-dados-' . $loop->index;
        $configuracaoDaTabela = match ($titulo) {
            'PRODUTOS COM MAIOR PRIORIDADE SEM ENCAMINHAMENTO' => [
                'tipo' => 'prioridade-alta-sem-encaminhar',
                'rotuloData' => 'ENTRADA',
                'campoData' => 'created_at',
                'abreviarMercadoLivre' => true,
            ],
            'PROTOCOLO ESTA ABERTO E O PRODUTO NAO ENCAMINHADO' => [
                'tipo' => 'protocolo-aberto-nao-encaminhado',
                'rotuloData' => 'RECEBIDO',
                'campoData' => 'recebido_em',
                'abreviarMercadoLivre' => false,
            ],
            'NECESSARIO IDENTIFICAR O S/N' => [
                'tipo' => 'sem-numero-de-serie',
                'rotuloData' => 'RECEBIDO',
                'campoData' => 'recebido_em',
                'abreviarMercadoLivre' => false,
                'mensagemVazio' => 'Nenhum item foi encontrado sem identificação',
            ],
            'SEM NF DE COMPRA E NF DE VENDA' => [
                'tipo' => 'sem-nota-fiscal',
                'partial' => 'rma.alertas._sem_nota',
                'mensagemVazio' => 'Nenhum item foi encontrado',
            ],
            'O DESTINATARIO ESTOUROU O PRAZO DE 30 DIAS PARA RETORNAR' => [
                'tipo' => 'prazo-destinatario-estourado',
                'partial' => 'rma.alertas._prazo_destinatario',
                'mensagemVazio' => 'Nenhum item foi encontrado',
            ],
            'RECEBIDO A MAIS DE 30 DIAS E NAO ENCAMINHADO' => [
                'tipo' => 'recebidos-sem-encaminhar-30-dias',
                'partial' => 'rma.alertas._sem_nota',
                'abreviarMercadoLivre' => false,
                'mensagemVazio' => 'Nenhum item foi encontrado',
            ],
            'NAO VAI DAR GARANTIA' => [
                'tipo' => 'nao-vai-dar-garantia',
                'partial' => 'rma.alertas._nao_vai_dar_garantia',
                'mensagemVazio' => 'Nenhum item foi encontrado',
            ],
            'PRODUTOS COM PENDENCIA DE LANCAR NF DO RETORNO' => [
                'tipo' => 'nf-retorno-pendente-de-lancar',
                'partial' => 'rma.alertas._nf_retorno_pendente',
                'mensagemVazio' => 'Nenhum item foi encontrado',
            ],
            'PRAZO DE GARANTIA COM O FORNECEDOR EXPIRADO MAIS DE 1 ANO' => [
                'tipo' => 'garantia-fornecedor-expirada',
                'partial' => 'rma.alertas._garantia_fornecedor_expirada',
                'mensagemVazio' => 'Nenhum item foi encontrado',
            ],
            'FALTA MENOS DE 30 DIAS PARA EXPIRAR GARANTIA DE 1 ANO COM O FORNECEDOR' => [
                'tipo' => 'garantia-fornecedor-expirando-30-dias',
                'partial' => 'rma.alertas._garantia_fornecedor_expirando',
                'mensagemVazio' => 'Nenhum item foi encontrado',
            ],
            default => null,
        };
        $partialDaTabela = $configuracaoDaTabela === null
            ? null
            : ($configuracaoDaTabela['partial'] ?? 'rma.alertas._abertos_nao_encaminhados');
    @endphp
    <div class="regra-de-alerta" @if ($configuracaoDaTabela) data-alerta-tipo="{{ $configuracaoDaTabela['tipo'] }}" @endif>
        <div class="regra-de-alerta-cabecalho">
            <img src="{{ asset('images/rma/retornou.png') }}" alt="" width="20" height="20">
            <span class="regra-de-alerta-titulo">{{ Illuminate\Support\Str::upper($titulo) }}:</span>
        </div>
        <span class="pmo" data-pmo-alvo="#{{ $alvo }}" aria-controls="{{ $alvo }}" aria-expanded="false">Mostrar</span>
        <div style="display:none;" id="{{ $alvo }}" class="regra-de-alerta-dados">
            @if ($rmas->isEmpty())
                <p class="nenhumencontrado">{{ $configuracaoDaTabela['mensagemVazio'] ?? 'Nenhum item foi encontrado' }}</p>
            @elseif ($partialDaTabela !== null)
                @include($partialDaTabela, ['rmas' => $rmas, ...$configuracaoDaTabela])
            @else
                <ul>
                    @foreach ($rmas as $registro)
                        <li>
                            <a href="{{ rota_tema('rmas.show', ['rma' => $registro->id]) }}">
                                #{{ $registro->id }} - {{ $registro->descricao }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
    <img src="{{ asset('images/rma/separador.png') }}" alt="Separador" class="separador-alerta">
@endforeach
