@extends('temas.v1.layout')

@section('omitirTituloPadrao')
@endsection

@php
    // PAR-DET-V1-EDIT-01 - detalhe V1 volta a ser formulario de edicao inline como
    // no Legacy 14.6.1. A arquitetura permanece V3: POST+PUT na rota rmas.update,
    // Policy, CSRF e casos de uso; nenhum post monolitico do Legacy.
    $l = $legado ?? [];
    $fabricanteNome = $fabricante?->nome ?? '';
    $clienteNome = $cliente?->nome ?? '';
    $tempo = $registro->createdAt ? (int) $registro->createdAt->diffInDays(now()) : null;
    $solucaoNome = $registro->solucao?->value ?? '';
    $politica = $politicaDeGarantia ?? null;
    $rotuloPolitica = match ($politica['tipo'] ?? null) {
        'destinatario' => 'POLITICA DE GARANTIA DO(A) DESTINATARIO ' . strtoupper($politica['nome'] ?? ''),
        'fabricante' => 'POLITICA DE GARANTIA DO(A) FABRICANTE ' . strtoupper($politica['nome'] ?? ''),
        'fornecedor' => 'POLITICA DE GARANTIA DO(A) FORNECEDOR ' . strtoupper($politica['nome'] ?? ''),
        default => '',
    };
    $tipoDestinoAtual = $destinatario['type'] ?? '';
    $slugDestinoAtual = match (true) {
        str_ends_with($tipoDestinoAtual, 'AssistenciaTecnica') => 'assistencia_tecnica',
        str_ends_with($tipoDestinoAtual, 'Fabricante') => 'fabricante',
        str_ends_with($tipoDestinoAtual, 'Fornecedor') => 'fornecedor',
        default => '',
    };
    $valorDestinoAtual = $slugDestinoAtual !== '' && ! empty($destinatario['id'])
        ? $slugDestinoAtual . ':' . $destinatario['id']
        : '';
@endphp

@section('conteudo')

<p class="title-icone fl" style="margin-top:8px;">
    <img src="{{ asset('images/tema-v1/bd.png') }}" alt="Boletim de defeito" width="50" height="50">
</p>
<p class="title-comicone fl" style="letter-spacing:2px;">BOLETIM DE DEFEITO</p>
<hr class="both">

<form method="POST" action="{{ rota_tema('rmas.update', ['rma' => $registro->id]) }}" class="detalhe-bd-form">
    @csrf
    @method('PUT')

    <table class="Tabelinha-Table">
        <tr class="TRD">
            <th style="width:25%;" class="C2">NUMERO DO BD</th>
            <th style="width:25%;" class="C2">FABRICANTE</th>
            <th style="width:25%;" class="C2">DESCRICAO</th>
            <th style="width:25%;" class="C2">MODELO</th>
        </tr>
        <tr class="formTRDetailD">
            <td class="C1"><input class="TDDX" value="{{ $numeroExibicao }}" disabled></td>
            <td class="C1">
                <select name="fabricante_id" class="TDDX">
                    <option value="">-</option>
                    @foreach ($fabricantesLista ?? [] as $opcaoFabricante)
                        <option value="{{ $opcaoFabricante->id }}" @selected($registro->fabricanteId === $opcaoFabricante->id)>
                            {{ $opcaoFabricante->nome }}
                        </option>
                    @endforeach
                </select>
            </td>
            <td class="C1"><input class="TDDX" name="descricao" value="{{ $registro->descricao }}" maxlength="255" required></td>
            <td class="C1"><input class="TDDX" name="modelo" value="{{ $registro->modelo }}" maxlength="255"></td>
        </tr>

        <tr class="TRD">
            <th style="width:25%;" class="C2">OS</th>
            <th style="width:25%;" class="C2">ORIGEM</th>
            <th style="width:25%;" class="C2">S/N</th>
            <th style="width:25%;" class="C2">EMPRESA</th>
        </tr>
        <tr class="formTRDetailD">
            <td class="C1"><input class="TDDX" name="os" value="{{ $registro->os }}" maxlength="255"></td>
            <td class="C1"><input class="TDDX" name="origem" value="{{ $registro->origem }}" maxlength="255"></td>
            <td class="C1"><input class="TDDX" name="sn" value="{{ $registro->sn }}" maxlength="255"></td>
            <td class="C1"><input class="TDDX" name="empresa" value="{{ $registro->empresa }}" maxlength="255"></td>
        </tr>

        <tr class="TRD">
            <th style="width:25%;">P/N</th>
            <th style="width:25%;">ID FCCID SNID AND ETC</th>
            <th style="width:25%;">TEMPO</th>
            <th style="width:25%;">CLIENTE</th>
        </tr>
        <tr class="formTRDetailD">
            <td><input class="TDDX" name="pn" value="{{ $registro->pn }}" maxlength="255"></td>
            <td><input class="TDDX" name="snid" value="{{ $registro->snid }}" maxlength="255"></td>
            <td><input class="TDDX" value="{{ $tempo !== null && $tempo > 0 ? $tempo : '' }}" disabled></td>
            <td><input class="TDDX" name="cliente_nome" value="{{ $clienteNome }}" maxlength="255"></td>
        </tr>

        <tr class="TRD">
            <th style="width:25%;">NF ENTRADA CLI</th>
            <th style="width:25%;">NF SAIDA CLI</th>
            <th style="width:25%;">RASTREIO ENCAMINHADO</th>
            <th style="width:25%;">RASTREIO RETORNO</th>
        </tr>
        <tr class="formTRDetailD">
            <td><input class="TDDX" name="nf_entrada_cliente_legado" value="{{ $l['nf_entrada_cliente_legado'] ?? '' }}"></td>
            <td><input class="TDDX" name="nf_retorno_cliente_legado" value="{{ $l['nf_retorno_cliente_legado'] ?? '' }}"></td>
            <td><input class="TDDX" name="rastreio_ida" value="{{ $l['rastreio_ida'] ?? '' }}"></td>
            <td><input class="TDDX" name="rastreio_retorno" value="{{ $l['rastreio_retorno'] ?? '' }}"></td>
        </tr>

        <tr class="TRD">
            <th style="width:25%;">NF DE COMPRA</th>
            <th style="width:25%;">DATA</th>
            <th style="width:25%;">NF DE VENDA</th>
            <th style="width:25%;">DATA</th>
        </tr>
        <tr class="formTRDetailD">
            <td><input class="TDDX" name="nfcompra" value="{{ $registro->nfcompra }}"></td>
            <td><input class="TDDX" name="nfcompra_emissao" value="{{ $registro->nfcompraEmissao?->format('d/m/Y') }}"></td>
            <td><input class="TDDX" name="nfvenda" value="{{ $registro->nfvenda }}"></td>
            <td><input class="TDDX" name="nfvenda_emissao" value="{{ $registro->nfvendaEmissao?->format('d/m/Y') }}"></td>
        </tr>
        <tr class="formTRDetailD">
            <td colspan="2"><input class="TDD_NF" name="nfcompra_chave" value="{{ $registro->nfcompraChave }}"></td>
            <td colspan="2"><input class="TDD_NF" name="nfvenda_chave" value="{{ $registro->nfvendaChave }}"></td>
        </tr>

        <tr class="TRD">
            <th style="width:25%;">NF REMESSA</th>
            <th style="width:25%;">DATA</th>
            <th style="width:25%;">NF RETORNO</th>
            <th style="width:25%;">DATA</th>
        </tr>
        <tr class="formTRDetailD">
            <td><input class="TDDX" name="nf_remessa" value="{{ $l['nf_remessa'] ?? '' }}"></td>
            <td><input class="TDDX" name="nf_remessa_emissao" value="{{ $l['nf_remessa_emissao'] ?? '' }}"></td>
            <td><input class="TDDX" name="nf_retorno_numero" value="{{ $l['nf_retorno_numero'] ?? '' }}"></td>
            <td><input class="TDDX" name="nf_retorno_emissao" value="{{ $l['nf_retorno_emissao'] ?? '' }}"></td>
        </tr>
        <tr class="formTRDetailD">
            <td colspan="2"><input class="TDD_NF" name="nf_remessa_chave" value="{{ $l['nf_remessa_chave'] ?? '' }}"></td>
            <td colspan="2"><input class="TDD_NF" name="nf_retorno_chave" value="{{ $l['nf_retorno_chave'] ?? '' }}"></td>
        </tr>

        <tr class="TRD">
            <th style="width:25%;" class="C2">DESTINATARIO</th>
            <th style="width:25%;">NF DEVOLUCAO DE VENDA</th>
            <th style="width:25%;">VALOR</th>
            <th style="width:25%;">S/N RETORNO</th>
        </tr>
        <tr class="formTRDetailD">
            <td class="C1">
                <select name="destinatario_tipo" class="TDDX">
                    <option value="">-</option>
                    <optgroup label="Assistencias tecnicas">
                        @foreach ($assistenciasTecnicasLista ?? [] as $assistencia)
                            <option value="assistencia_tecnica:{{ $assistencia->id }}" @selected($valorDestinoAtual === 'assistencia_tecnica:' . $assistencia->id)>
                                {{ $assistencia->nome }}
                            </option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Fabricantes">
                        @foreach ($fabricantesLista ?? [] as $opcaoFabricante)
                            <option value="fabricante:{{ $opcaoFabricante->id }}" @selected($valorDestinoAtual === 'fabricante:' . $opcaoFabricante->id)>
                                {{ $opcaoFabricante->nome }}
                            </option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Fornecedores">
                        @foreach ($fornecedoresLista ?? [] as $opcaoFornecedor)
                            <option value="fornecedor:{{ $opcaoFornecedor->id }}" @selected($valorDestinoAtual === 'fornecedor:' . $opcaoFornecedor->id)>
                                {{ $opcaoFornecedor->nome }}
                            </option>
                        @endforeach
                    </optgroup>
                </select>
            </td>
            <td><input class="TDDX" name="nf_devolucao_de_venda" value="{{ $l['nf_devolucao_de_venda'] ?? '' }}"></td>
            <td><input class="TDDX" name="valor" value="{{ $registro->valor !== null ? number_format($registro->valor, 2, '.', '') : '' }}"></td>
            <td><input class="TDDX" name="snretorno" value="{{ $registro->snretorno }}"></td>
        </tr>

        <tr class="TRD">
            <th style="width:25%;">EMAIL DO DESTINATARIO</th>
            <th style="width:25%;">FONE</th>
            <th style="width:25%;" class="C2">PROTOCOLO</th>
            <th style="width:25%;">RESOLUCAO</th>
        </tr>
        <tr class="formTRDetailD">
            <td class="Tabelinha-TDD"><input class="TDDX" name="destinatario_email_legado" value="{{ $destinatarioEmail ?? '' }}"></td>
            <td><input class="TDDX" name="destinatario_fone_legado" value="{{ $destinatarioFone ?? '' }}"></td>
            <td class="C1"><input class="TDDX" name="protocolo" value="{{ $registro->protocolo }}"></td>
            <td>
                <select name="solucao" class="formSelectView">
                    <option value=""></option>
                    @foreach (\App\Rma\Dominio\Solucao::cases() as $opcaoSolucao)
                        <option value="{{ $opcaoSolucao->value }}" @selected($registro->solucao === $opcaoSolucao)>
                            {{ $opcaoSolucao->value }}
                        </option>
                    @endforeach
                </select>
            </td>
        </tr>

        <tr class="TRD">
            <td class="TRD" colspan="4">DEFEITO RECLAMADO</td>
        </tr>
        <tr class="formTRDetailD">
            <td colspan="4" style="text-align:left;">
                <input class="TDD_DEFEITO" name="defeito" value="{{ $registro->defeito }}" maxlength="150" required>
            </td>
        </tr>

        <tr class="TRD">
            <td class="TRD" colspan="4" style="background-color:#45252B;">OBSERVACAO DO BD / BOLETIM DE DEFEITO</td>
        </tr>
        <tr class="formTRDetailD">
            <td colspan="4" style="text-align:left;">
                <textarea class="TDD_TAOBSERVACAO" name="observacao" rows="8">{{ $registro->observacao }}</textarea>
            </td>
        </tr>

        <tr class="TRD">
            <th style="width:25%; @if ($registro->status->name === 'Entrada') background-color:#45252B; @endif">DATA ENTRADA</th>
            <th style="width:25%; @if ($registro->status->name === 'Recebido') background-color:#45252B; @endif">DATA RECEBIDO</th>
            <th style="width:25%; @if ($registro->status->name === 'Encaminhado') background-color:#45252B; @endif">DATA ENCAMINHADO</th>
            <th style="width:25%; @if ($registro->status->name === 'Concluido') background-color:#45252B; @endif">DATA CONCLUIDO</th>
        </tr>
        <tr class="formTRDetailD">
            <td class="Tabelinha-TDD TDD_DATA"><input class="TDDX" value="{{ $registro->createdAt?->format('d/m/Y') }}" disabled></td>
            <td class="Tabelinha-TDD TDD_DATA"><input class="TDDX" value="{{ $registro->recebidoEm?->format('d/m/Y') }}" disabled></td>
            <td class="Tabelinha-TDD TDD_DATA"><input class="TDDX" value="{{ $registro->encaminhadoEm?->format('d/m/Y') }}" disabled></td>
            <td class="Tabelinha-TDD TDD_DATA"><input class="TDDX" value="{{ $registro->concluidoEm?->format('d/m/Y') }}" disabled></td>
        </tr>
    </table>

    @if ($politica !== null && ! empty($politica['texto']))
        <table class="Tabelinha-Table" style="margin-top:15px;">
            <tr class="TRD">
                <td class="polinput" colspan="4">{{ $rotuloPolitica }}</td>
            </tr>
            <tr class="formTRDetailD">
                <td colspan="4" style="text-align:left;">
                    <textarea class="TDD_TAPOLITICA" rows="6" disabled>{{ $politica['texto'] }}</textarea>
                </td>
            </tr>
        </table>
    @endif

    <div class="detalhe-bd-rodape">
        <div class="detalhe-bd-rodape__esquerda fl">
            <input type="hidden" name="marcarestoque" value="0">
            <input type="checkbox" id="checkboxEstoqueDetalheV1" name="marcarestoque" value="1" @checked($registro->marcarestoque)>
            <label for="checkboxEstoqueDetalheV1" class="checkbox-v1-detalhe" data-text-true="O ITEM E DO ESTOQUE" data-text-false="ITEM NAO E DO ESTOQUE"><i></i></label>
            <input type="hidden" name="credito_disponivel" value="0">
            <input type="checkbox" id="checkboxCreditoDetalheV1" name="credito_disponivel" value="1" @checked($registro->creditoDisponivel)>
            <label for="checkboxCreditoDetalheV1" class="checkbox-v1-detalhe" data-text-true="CREDITO DISPONIVEL" data-text-false="MARQUE P/ VALIDAR CREDITO"><i></i></label>
        </div>

        <div class="detalhe-bd-rodape__direita fr">
            <select name="acao" class="formSelect">
                <option value="salvar">SALVAR</option>
                @if ($registro->status->podeReceber())
                    <option value="receber">RECEBER</option>
                @endif
                @if ($registro->status->podeEncaminhar())
                    <option value="encaminhar">ENCAMINHAR</option>
                @endif
                @if ($registro->status->podeConcluir())
                    <option value="concluir">CONCLUIR</option>
                @endif
                @if ($registro->status->podeArquivar())
                    <option value="arquivar">ARQUIVAR</option>
                @endif
                @if ($registro->status->podeReverterParaEntrada())
                    <option value="reverter">RETORNAR P/ ENTRADA</option>
                @endif
            </select>
            <button type="submit" class="acao acao--primaria buttonSave">OK</button>
        </div>
        <div style="clear:both;"></div>
    </div>

    <p style="margin-top:10px;">
        <a href="{{ rota_tema('rmas.edit', ['rma' => $registro->id]) }}" class="acao acao--secundaria">Editar</a>
    </p>
</form>

{{-- Acoes especificas/ciclo de vida: preservadas em bloco recolhivel para os testes
de contrato e para acesso avancado, sem reverter o rodape historico a uma pilha
vertical no topo. --}}
<details class="detalhe-bd-acoes-avancadas">
    <summary>Mais acoes de ciclo de vida</summary>
    @include('rma._acoes_de_transicao')
</details>
@endsection
