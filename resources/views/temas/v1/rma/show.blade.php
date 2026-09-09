@extends('temas.v1.layout')

@section('omitirTituloPadrao')
@endsection

@php
    // PAR-DET-V1-01 - restauracao da gramatica visual do detalhe historico
    // (14.6.1/page/detalhes.php) em leitura: titulo BOLETIM DE DEFEITO, grupos de
    // 4 colunas, celulas densas. Nao vira form editavel: a rota GET edit continua
    // sendo o caminho de edicao. Celulas com dado vazio ficam vazias (sem inventar).
    $l = $legado ?? [];
    $tempo = $registro->createdAt ? (int) $registro->createdAt->diffInDays(now()) : null;
    $fabricanteNome = $fabricante?->nome ?? '';
    $fornecedorNome = $fornecedor?->nome ?? '';
    $clienteNome = $cliente?->nome ?? '';
    $destinatarioNome = $destinatario['nome'] ?? '';
    $solucaoNome = $registro->solucao?->value ?? '';
    $politica = $politicaDeGarantia ?? null;
    $rotuloPolitica = match ($politica['tipo'] ?? null) {
        'destinatario' => 'POLITICA DE GARANTIA DO(A) DESTINATARIO ' . strtoupper($politica['nome'] ?? ''),
        'fabricante' => 'POLITICA DE GARANTIA DO(A) FABRICANTE ' . strtoupper($politica['nome'] ?? ''),
        'fornecedor' => 'POLITICA DE GARANTIA DO(A) FORNECEDOR ' . strtoupper($politica['nome'] ?? ''),
        default => '',
    };
@endphp

@section('conteudo')

<p class="title-icone fl" style="margin-top:8px;">
    <img src="{{ asset('images/tema-v1/bd.png') }}" alt="Boletim de defeito" width="50" height="50">
</p>
<p class="title-comicone fl" style="letter-spacing:2px;">BOLETIM DE DEFEITO</p>
<hr class="both">

<table class="Tabelinha-Table">
    <tr class="TRD">
        <th style="width:25%;" class="C2">NUMERO DO BD</th>
        <th style="width:25%;" class="C2">FABRICANTE</th>
        <th style="width:25%;" class="C2">DESCRICAO</th>
        <th style="width:25%;" class="C2">MODELO</th>
    </tr>
    <tr class="formTRDetailD">
        <td class="C1"><span class="TDDX somente-leitura">{{ $numeroExibicao }}</span></td>
        <td class="C1"><span class="TDDX somente-leitura">{{ $fabricanteNome }}</span></td>
        <td class="C1"><span class="TDDX somente-leitura">{{ $registro->descricao }}</span></td>
        <td class="C1"><span class="TDDX somente-leitura">{{ $registro->modelo }}</span></td>
    </tr>

    <tr class="TRD">
        <th style="width:25%;" class="C2">OS</th>
        <th style="width:25%;" class="C2">ORIGEM</th>
        <th style="width:25%;" class="C2">S/N</th>
        <th style="width:25%;" class="C2">EMPRESA</th>
    </tr>
    <tr class="formTRDetailD">
        <td class="C1"><span class="TDDX somente-leitura">{{ $registro->os }}</span></td>
        <td class="C1"><span class="TDDX somente-leitura">{{ $registro->origem }}</span></td>
        <td class="C1"><span class="TDDX somente-leitura">{{ $registro->sn }}</span></td>
        <td class="C1"><span class="TDDX somente-leitura">{{ $registro->empresa }}</span></td>
    </tr>

    <tr class="TRD">
        <th style="width:25%;">P/N</th>
        <th style="width:25%;">ID FCCID SNID AND ETC</th>
        <th style="width:25%;">TEMPO</th>
        <th style="width:25%;">CLIENTE</th>
    </tr>
    <tr class="formTRDetailD">
        <td><span class="TDDX somente-leitura">{{ $registro->pn }}</span></td>
        <td><span class="TDDX somente-leitura">{{ $registro->snid }}</span></td>
        <td><span class="TDDX somente-leitura">{{ $tempo !== null && $tempo > 0 ? $tempo : '' }}</span></td>
        <td><span class="TDDX somente-leitura">{{ $clienteNome }}</span></td>
    </tr>

    <tr class="TRD">
        <th style="width:25%;">NF ENTRADA CLI</th>
        <th style="width:25%;">NF SAIDA CLI</th>
        <th style="width:25%;">RASTREIO ENCAMINHADO</th>
        <th style="width:25%;">RASTREIO RETORNO</th>
    </tr>
    <tr class="formTRDetailD">
        <td><span class="TDDX somente-leitura">{{ $l['nf_entrada_cliente_legado'] ?? '' }}</span></td>
        <td><span class="TDDX somente-leitura">{{ $l['nf_retorno_cliente_legado'] ?? '' }}</span></td>
        <td><span class="TDDX somente-leitura">{{ $l['rastreio_ida'] ?? '' }}</span></td>
        <td><span class="TDDX somente-leitura">{{ $l['rastreio_retorno'] ?? '' }}</span></td>
    </tr>

    <tr class="TRD">
        <th style="width:25%;">NF DE COMPRA</th>
        <th style="width:25%;">DATA</th>
        <th style="width:25%;">NF DE VENDA</th>
        <th style="width:25%;">DATA</th>
    </tr>
    <tr class="formTRDetailD">
        <td><span class="TDDX somente-leitura">{{ $registro->nfcompra }}</span></td>
        <td><span class="TDDX somente-leitura">{{ $registro->nfcompraEmissao?->format('d/m/Y') }}</span></td>
        <td><span class="TDDX somente-leitura">{{ $registro->nfvenda }}</span></td>
        <td><span class="TDDX somente-leitura">{{ $registro->nfvendaEmissao?->format('d/m/Y') }}</span></td>
    </tr>
    <tr class="formTRDetailD">
        <td colspan="2"><span class="TDD_NF somente-leitura">{{ $registro->nfcompraChave }}</span></td>
        <td colspan="2"><span class="TDD_NF somente-leitura">{{ $registro->nfvendaChave }}</span></td>
    </tr>

    <tr class="TRD">
        <th style="width:25%;">NF REMESSA</th>
        <th style="width:25%;">DATA</th>
        <th style="width:25%;">NF RETORNO</th>
        <th style="width:25%;">DATA</th>
    </tr>
    <tr class="formTRDetailD">
        <td><span class="TDDX somente-leitura">{{ $l['nf_remessa'] ?? '' }}</span></td>
        <td><span class="TDDX somente-leitura">{{ $l['nf_remessa_emissao'] ?? '' }}</span></td>
        <td><span class="TDDX somente-leitura">{{ $l['nf_retorno_numero'] ?? '' }}</span></td>
        <td><span class="TDDX somente-leitura">{{ $l['nf_retorno_emissao'] ?? '' }}</span></td>
    </tr>
    <tr class="formTRDetailD">
        <td colspan="2"><span class="TDD_NF somente-leitura">{{ $l['nf_remessa_chave'] ?? '' }}</span></td>
        <td colspan="2"><span class="TDD_NF somente-leitura">{{ $l['nf_retorno_chave'] ?? '' }}</span></td>
    </tr>

    <tr class="TRD">
        <th style="width:25%;" class="C2">DESTINATARIO</th>
        <th style="width:25%;">NF DEVOLUCAO DE VENDA</th>
        <th style="width:25%;">VALOR</th>
        <th style="width:25%;">S/N RETORNO</th>
    </tr>
    <tr class="formTRDetailD">
        <td class="C1"><span class="TDDX somente-leitura">{{ $destinatarioNome }}</span></td>
        <td><span class="TDDX somente-leitura">{{ $l['nf_devolucao_de_venda'] ?? '' }}</span></td>
        <td><span class="TDDX somente-leitura">{{ $registro->valor !== null && $registro->valor > 0 ? number_format($registro->valor, 2, ',', '') : '' }}</span></td>
        <td><span class="TDDX somente-leitura">{{ $registro->snretorno }}</span></td>
    </tr>

    <tr class="TRD">
        <th style="width:25%;">EMAIL DO DESTINATARIO</th>
        <th style="width:25%;">FONE</th>
        <th style="width:25%;" class="C2">PROTOCOLO</th>
        <th style="width:25%;">RESOLUCAO</th>
    </tr>
    <tr class="formTRDetailD">
        <td class="Tabelinha-TDD"><span class="TDDX somente-leitura">{{ $destinatarioEmail }}</span></td>
        <td><span class="TDDX somente-leitura">{{ $destinatarioFone }}</span></td>
        <td class="C1"><span class="TDDX somente-leitura">{{ $registro->protocolo }}</span></td>
        <td><span class="TDDX somente-leitura">{{ $solucaoNome }}</span></td>
    </tr>

    <tr class="TRD">
        <td class="TRD" colspan="4">DEFEITO RECLAMADO</td>
    </tr>
    <tr class="formTRDetailD">
        <td colspan="4" style="text-align:left;">
            <span class="TDD_DEFEITO somente-leitura">{{ $registro->defeito }}</span>
        </td>
    </tr>

    <tr class="TRD">
        <td class="TRD" colspan="4" style="background-color:#45252B;">OBSERVACAO DO BD / BOLETIM DE DEFEITO</td>
    </tr>
    <tr class="formTRDetailD">
        <td colspan="4" style="text-align:left;">
            <span class="TDD_TAOBSERVACAO somente-leitura">{{ $registro->observacao }}</span>
        </td>
    </tr>

    <tr class="TRD">
        <th style="width:25%; @if ($registro->status->name === 'Entrada') background-color:#45252B; @endif">DATA ENTRADA</th>
        <th style="width:25%; @if ($registro->status->name === 'Recebido') background-color:#45252B; @endif">DATA RECEBIDO</th>
        <th style="width:25%; @if ($registro->status->name === 'Encaminhado') background-color:#45252B; @endif">DATA ENCAMINHADO</th>
        <th style="width:25%; @if ($registro->status->name === 'Concluido') background-color:#45252B; @endif">DATA CONCLUIDO</th>
    </tr>
    <tr class="formTRDetailD">
        <td class="Tabelinha-TDD TDD_DATA"><span class="TDDX somente-leitura">{{ $registro->createdAt?->format('d/m/Y') }}</span></td>
        <td class="Tabelinha-TDD TDD_DATA"><span class="TDDX somente-leitura">{{ $registro->recebidoEm?->format('d/m/Y') }}</span></td>
        <td class="Tabelinha-TDD TDD_DATA"><span class="TDDX somente-leitura">{{ $registro->encaminhadoEm?->format('d/m/Y') }}</span></td>
        <td class="Tabelinha-TDD TDD_DATA"><span class="TDDX somente-leitura">{{ $registro->concluidoEm?->format('d/m/Y') }}</span></td>
    </tr>
</table>

<div style="padding:5px 0;clear:both;">
    {{ $registro->marcarestoque ? 'O ITEM E DO ESTOQUE' : 'ITEM NAO E DO ESTOQUE' }}
</div>
<div style="padding:5px 0;clear:both;">
    {{ $registro->creditoDisponivel ? 'CREDITO DISPONIVEL' : 'MARQUE P/ VALIDAR CREDITO' }}
</div>

@if ($politica !== null && ! empty($politica['texto']))
    <table class="Tabelinha-Table" style="margin-top:15px;">
        <tr class="TRD">
            <td class="polinput" colspan="4">{{ $rotuloPolitica }}</td>
        </tr>
        <tr class="formTRDetailD">
            <td colspan="4" style="text-align:left;">
                <span class="TDD_TAPOLITICA somente-leitura" style="white-space:pre-wrap;">{{ $politica['texto'] }}</span>
            </td>
        </tr>
    </table>
@endif

<div class="both"></div>

<div class="acoes-de-transicao detalhe-bd-acoes">
    <p>
        <a href="{{ rota_tema('rmas.edit', ['rma' => $registro->id]) }}" class="acao acao--primaria">Editar</a>
    </p>
    @include('rma._acoes_de_transicao')
</div>
@endsection
