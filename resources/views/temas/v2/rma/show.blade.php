@extends('temas.v2.layout')

@php
    // PAR-DET-V2-01 - leitura organizada segundo 15.8.1/page/rma.php: cabecalho do
    // RMA (NUMERO DO BD + descricao/fabricante/modelo) e grupos em colunas
    // (produto/origem, estoque/datas, fiscal, destinatario/logistica, solucao).
    // Show e edit continuam separados; nenhuma regra de ciclo de vida muda.
    $l = $legado ?? [];
    $fabricanteNome = $fabricante?->nome ?? '';
    $fornecedorNome = $fornecedor?->nome ?? '';
    $clienteNome = $cliente?->nome ?? '';
    $destinatarioNome = $destinatario['nome'] ?? '';
    $solucaoNome = $registro->solucao?->value ?? '';
    $prioridadeNome = match ($registro->prioridade) {
        \App\Rma\Dominio\Prioridade::Alta => 'Alta',
        \App\Rma\Dominio\Prioridade::Media => 'Normal',
        default => 'Baixa',
    };
    $lancamentoNome = match ($registro->lancadoretorno) {
        \App\Rma\Dominio\StatusDeLancamento::Pendente => 'PENDENTE',
        \App\Rma\Dominio\StatusDeLancamento::NfDevolucao => 'NF DE DEVOLUCAO',
        \App\Rma\Dominio\StatusDeLancamento::SemMovimentacao => 'SEM MOVIMENTACAO',
        \App\Rma\Dominio\StatusDeLancamento::Nao => 'NAO',
        \App\Rma\Dominio\StatusDeLancamento::Sim => 'SIM',
        default => '',
    };
    $politica = $politicaDeGarantia ?? null;
    $rotuloPolitica = match ($politica['tipo'] ?? null) {
        'destinatario' => 'Politica de Garantia com ' . ($politica['nome'] ?? '') . ' (Destinatario)',
        'fabricante' => 'Politica de Garantia com ' . ($politica['nome'] ?? '') . ' (Fabricante)',
        'fornecedor' => 'Politica de Garantia com ' . ($politica['nome'] ?? '') . ' (Fornecedor)',
        default => '',
    };
    $numeroExibicaoV2 = $numeroExibicao ?? $registro->id;
@endphp

@section('conteudo')
    @if (session('status'))
        <p class="centrodeavisos">{{ session('status') }}</p>
    @endif

    <div class="detalhe-rma-v2">
        <ol class="breadcrumb submenutitulo">
            <li class="fl numerodobd">
                NUMERO DO BD
                {{ $numeroExibicaoV2 }}
                /
                {{ $registro->descricao }}
                {{ $fabricanteNome }}
                {{ $registro->modelo }}
            </li>
            <li style="clear:both;"></li>
        </ol>

        <div class="fr detalhe-rma-v2__acao-cabecalho">
            <a href="{{ rota_tema('rmas.edit', ['rma' => $registro->id]) }}" class="acao acao--primaria btn formSubmit">Editar</a>
        </div>
        <div style="clear:both;"></div>

        <div class="row formgroupnf">
            <div class="col-md-3">
                <div class="form-group">
                    <label class="Label1 importante">Que produto é?</label>
                    <p class="detalhe-v2__valor">{{ $registro->descricao }}</p>
                </div>
                <div class="form-group">
                    <label class="Label1 importante">Modelo</label>
                    <p class="detalhe-v2__valor">{{ $registro->modelo }}</p>
                </div>
                <div class="form-group">
                    <label class="Label1 importante">Fabricante</label>
                    <p class="detalhe-v2__valor">{{ $fabricanteNome }}</p>
                </div>
                <div class="form-group">
                    <label class="Label1 importante" for="sn">S/N</label>
                    <p class="detalhe-v2__valor">{{ $registro->sn }}</p>
                </div>
                <div class="form-group">
                    <label class="Label1" for="snid">SNID</label>
                    <p class="detalhe-v2__valor">{{ $registro->snid }}</p>
                </div>
                <div class="form-group">
                    <label class="Label1" for="pn">P/N</label>
                    <p class="detalhe-v2__valor">{{ $registro->pn }}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label class="Label1 {{ $registro->os ? 'importante' : '' }}" for="os">OS</label>
                    <p class="detalhe-v2__valor detalhe-v2__valor--os">{{ $registro->os }}</p>
                </div>
                <div class="form-group">
                    <label class="Label1 importante" for="origem">Origem</label>
                    <p class="detalhe-v2__valor">{{ $registro->origem }}</p>
                </div>
                <div class="form-group">
                    <label class="Label1 {{ $registro->prioridade === \App\Rma\Dominio\Prioridade::Alta ? 'importante' : '' }}" for="prioridade">Prioridade</label>
                    <p class="detalhe-v2__valor">{{ $prioridadeNome }}</p>
                </div>
                <div class="form-group">
                    <label class="Label1 importante">PROTOCOLO</label>
                    <p class="detalhe-v2__valor">{{ $registro->protocolo }}</p>
                </div>
                <div class="form-group">
                    <label class="formlabeldefeito importante">Defeito reclamado</label>
                    <p class="detalhe-v2__valor">{{ $registro->defeito }}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label class="Label1 importante">E um produto do estoque ?</label>
                    <p class="detalhe-v2__valor">{{ $registro->marcarestoque ? 'Sim' : 'Nao' }}</p>
                </div>
                <div class="form-group">
                    <label class="Label1 importante">Empresa</label>
                    <p class="detalhe-v2__valor">{{ $registro->empresa }}</p>
                </div>
                <div class="form-group">
                    <label class="Label1">E credito disponivel ?</label>
                    <p class="detalhe-v2__valor">{{ $registro->creditoDisponivel ? 'Sim' : 'Nao' }}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label class="Label1">Entrada</label>
                    <p class="detalhe-v2__valor">{{ $registro->createdAt?->format('d/m/Y') }}</p>
                </div>
                <div class="form-group">
                    <label class="Label1">Recebido</label>
                    <p class="detalhe-v2__valor">{{ $registro->recebidoEm?->format('d/m/Y') }}</p>
                </div>
                <div class="form-group">
                    <label class="Label1">Encaminhado</label>
                    <p class="detalhe-v2__valor">{{ $registro->encaminhadoEm?->format('d/m/Y') }}</p>
                </div>
                <div class="form-group">
                    <label class="Label1">Concluido</label>
                    <p class="detalhe-v2__valor">{{ $registro->concluidoEm?->format('d/m/Y') }}</p>
                </div>
                <div class="form-group">
                    <label class="Label1 importante">Tempo</label>
                    <p class="detalhe-v2__valor">{{ $registro->createdAt ? (int) $registro->createdAt->diffInDays(now()) : '' }}</p>
                </div>
            </div>
        </div>

        <div class="row formgroupnf">
            <div class="col-md-3">
                <div class="form-group">
                    <label class="Label1 importante">NF de Venda</label>
                    <p class="detalhe-v2__valor">{{ $registro->nfvenda }}</p>
                </div>
                <div class="form-group">
                    <label class="Label1 importante">Data</label>
                    <p class="detalhe-v2__valor">{{ $registro->nfvendaEmissao?->format('d/m/Y') }}</p>
                </div>
                <div class="form-group">
                    <label class="Label1">DANFE</label>
                    <p class="detalhe-v2__valor">{{ $registro->nfvendaChave }}</p>
                </div>
                <div class="form-group">
                    <label class="Label1 importante">Quem e o Cliente ?</label>
                    <p class="detalhe-v2__valor">{{ $clienteNome }}</p>
                </div>
                <div class="form-group">
                    <label class="Label1">E-mail (cliente)</label>
                    <p class="detalhe-v2__valor">{{ $clienteEmail }}</p>
                </div>
                <div class="form-group">
                    <label class="Label1">NF de Devolucao de Venda</label>
                    <p class="detalhe-v2__valor">{{ $l['nf_devolucao_de_venda'] ?? '' }}</p>
                </div>
                <div class="form-group">
                    <label class="Label1">NF de Entrada p/ Conserto</label>
                    <p class="detalhe-v2__valor">{{ $l['nf_entrada_cliente_legado'] ?? '' }}</p>
                </div>
                <div class="form-group">
                    <label class="Label1">NF de Retorno p/ Cliente</label>
                    <p class="detalhe-v2__valor">{{ $l['nf_retorno_cliente_legado'] ?? '' }}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label class="Label1 importante">NF de Compra</label>
                    <p class="detalhe-v2__valor">{{ $registro->nfcompra }}</p>
                </div>
                <div class="form-group">
                    <label class="Label1 importante">Data</label>
                    <p class="detalhe-v2__valor">{{ $registro->nfcompraEmissao?->format('d/m/Y') }}</p>
                </div>
                <div class="form-group">
                    <label class="Label1">DANFE</label>
                    <p class="detalhe-v2__valor">{{ $registro->nfcompraChave }}</p>
                </div>
                <div class="form-group">
                    <label class="Label1 importante">Qual o Fornecedor ?</label>
                    <p class="detalhe-v2__valor">{{ $fornecedorNome }}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label class="Label1 importante">NF de Remessa</label>
                    <p class="detalhe-v2__valor">{{ $l['nf_remessa'] ?? '' }}</p>
                </div>
                <div class="form-group">
                    <label class="Label1">Data</label>
                    <p class="detalhe-v2__valor">{{ $l['nf_remessa_emissao'] ?? '' }}</p>
                </div>
                <div class="form-group">
                    <label class="Label1">DANFE</label>
                    <p class="detalhe-v2__valor">{{ $l['nf_remessa_chave'] ?? '' }}</p>
                </div>
                <div class="form-group">
                    <label class="Label1">Valor do produto</label>
                    <p class="detalhe-v2__valor">{{ $registro->valor !== null && $registro->valor > 0 ? number_format($registro->valor, 2, ',', '') : '' }}</p>
                </div>
                <div class="form-group">
                    <label class="Label1 importante">Qual o destinatario ?</label>
                    <p class="detalhe-v2__valor">{{ $destinatarioNome }}</p>
                </div>
                <div class="form-group">
                    <label class="Label1">Fone</label>
                    <p class="detalhe-v2__valor">{{ $destinatarioFone }}</p>
                </div>
                <div class="form-group">
                    <label class="Label1">E-mail</label>
                    <p class="detalhe-v2__valor">{{ $destinatarioEmail }}</p>
                </div>
                <div class="form-group">
                    <label class="Label1">Codigo de rastreio</label>
                    <p class="detalhe-v2__valor">{{ $l['rastreio_ida'] ?? '' }}</p>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    <label class="Label1 importante">NF de Retorno</label>
                    <p class="detalhe-v2__valor">{{ $l['nf_retorno_numero'] ?? '' }}</p>
                </div>
                <div class="form-group">
                    <label class="Label1">Data</label>
                    <p class="detalhe-v2__valor">{{ $l['nf_retorno_emissao'] ?? '' }}</p>
                </div>
                <div class="form-group">
                    <label class="Label1">DANFE</label>
                    <p class="detalhe-v2__valor">{{ $l['nf_retorno_chave'] ?? '' }}</p>
                </div>
                <div class="form-group">
                    <label class="Label1">NF lancada no estoque ?</label>
                    <p class="detalhe-v2__valor">{{ $lancamentoNome }}</p>
                </div>
                <div class="form-group">
                    <label class="Label1">Codigo de rastreio</label>
                    <p class="detalhe-v2__valor">{{ $l['rastreio_retorno'] ?? '' }}</p>
                </div>
                <div class="form-group">
                    <label class="Label1">O que foi feito / resultado ?</label>
                    <p class="detalhe-v2__valor">{{ $solucaoNome }}</p>
                </div>
            </div>
        </div>

        <div class="row formgroupnf">
            <div class="col-md-12">
                <div class="form-group">
                    <label class="formLabelTextArea" for="observacao">Informacao adicional</label>
                    <p class="detalhe-v2__texto-longo">{{ $registro->observacao }}</p>
                </div>
            </div>
        </div>

        @if ($politica !== null && ! empty($politica['texto']))
            <div class="row formgroupnf">
                <div class="col-md-12">
                    <div class="form-group">
                        <label class="formLabelTextArea Input4 detalhe-v2__politica-titulo">{{ $rotuloPolitica }}</label>
                        <p class="detalhe-v2__texto-longo detalhe-v2__politica-texto">{{ $politica['texto'] }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="row detalhe-rma-v2__acoes-finais">
            <div class="fr">
                @include('rma._acoes_de_transicao')
            </div>
        </div>
    </div>
@endsection
