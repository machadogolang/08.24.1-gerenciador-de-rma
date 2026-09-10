{{-- PAR-V2-DETAIL-02 - formulario operacional do detalhe RMA V2, fonte visual
`15.8.1/page/rma.php` por cima da arquitetura moderna (PUT + CSRF + Policy +
casos de uso). Campos que eram editaveis no Legacy voltam a ser editaveis;
usuario sem Policy de escrita ve os mesmos controles desabilitados. Nenhuma regra
de negocio nova nesta view. --}}
@php
    $numeroExibicaoDetalheV2 = $numeroExibicaoV2 ?? $registro->id;
    $podeGravarDetalheV2 = $podeEditar ?? false;
    $disabled = $podeGravarDetalheV2 ? '' : 'disabled';
    $tipoDestinoDetalheV2 = $destinatario['type'] ?? '';
    $slugDestinoDetalheV2 = match (true) {
        str_ends_with($tipoDestinoDetalheV2, 'AssistenciaTecnica') => 'assistencia_tecnica',
        str_ends_with($tipoDestinoDetalheV2, 'Fabricante') => 'fabricante',
        str_ends_with($tipoDestinoDetalheV2, 'Fornecedor') => 'fornecedor',
        default => '',
    };
    $valorDestinoDetalheV2 = $slugDestinoDetalheV2 !== '' && ! empty($destinatario['id'])
        ? $slugDestinoDetalheV2 . ':' . $destinatario['id']
        : '';
    $rotulosPrioridade = [
        \App\Rma\Dominio\Prioridade::Baixa->name => 'Baixa',
        \App\Rma\Dominio\Prioridade::Media->name => 'Normal',
        \App\Rma\Dominio\Prioridade::Alta->name => 'Alta',
    ];
    $rotulosLancamento = [
        'pendente' => 'PENDENTE',
        'nf_devolucao' => 'NF DE DEVOLUCAO',
        'sem_movimentacao' => 'SEM MOVIMENTACAO',
        'nao' => 'NAO',
        'sim' => 'SIM',
    ];
    $opcoesOrigem = [
        'Unknown', 'Loja', 'Casa', 'Cliente', 'Licitação', 'Leilão', 'Mercado Livre', 'Credito', 'AC',
    ];
@endphp

<form method="POST" action="{{ rota_tema('rmas.update', ['rma' => $registro->id]) }}" class="detalhe-rma-v2__form">
    @csrf
    @method('PUT')

    <ol class="breadcrumb submenutitulo">
        <li class="fl numerodobd">
            NUMERO DO BD
            {{ $numeroExibicaoDetalheV2 }}
            /
            {{ $registro->descricao }}
            {{ $fabricanteNome }}
            {{ $registro->modelo }}
        </li>
        <li style="clear:both;"></li>
    </ol>

    <div class="fr detalhe-rma-v2__acao-cabecalho">
        @if ($podeGravarDetalheV2)
            @include('temas.v2.rma._acoes_do_ciclo', ['sufixoAcao' => 'up', 'classeSelectAcao' => 'formSelect formSelect3'])
        @else
            <span class="detalhe-rma-v2__somente-leitura">Somente leitura</span>
        @endif
    </div>
    <div style="clear:both;"></div>

    <div class="row formgroupnf">
        <div class="col-md-3">
            <div class="form-group">
                <label class="Label1 importante">Que produto é?</label>
                <input type="text" class="form-control detalhe-rma-v2__controle" name="descricao"
                    value="{{ $registro->descricao }}" maxlength="255" required {{ $disabled }}>
            </div>
            <div class="form-group">
                <label class="Label1 importante">Modelo</label>
                <input type="text" class="form-control detalhe-rma-v2__controle" name="modelo"
                    value="{{ $registro->modelo }}" maxlength="255" {{ $disabled }}>
            </div>
            <div class="form-group">
                <label class="Label1 importante">Fabricante</label>
                <select name="fabricante_id" class="form-control formSelect detalhe-rma-v2__controle" {{ $disabled }}>
                    <option value="">-</option>
                    @foreach ($fabricantesLista ?? [] as $opcaoFabricante)
                        <option value="{{ $opcaoFabricante->id }}" @selected($registro->fabricanteId === $opcaoFabricante->id)>
                            {{ $opcaoFabricante->nome }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="Label1 importante" for="sn">S/N</label>
                <input type="text" class="form-control detalhe-rma-v2__controle" name="sn"
                    value="{{ $registro->sn }}" maxlength="255" {{ $disabled }}>
            </div>
            <div class="form-group">
                <label class="Label1" for="snid">SNID</label>
                <input type="text" class="form-control detalhe-rma-v2__controle" name="snid"
                    value="{{ $registro->snid }}" maxlength="255" {{ $disabled }}>
            </div>
            <div class="form-group">
                <label class="Label1" for="pn">P/N</label>
                <input type="text" class="form-control detalhe-rma-v2__controle" name="pn"
                    value="{{ $registro->pn }}" maxlength="255" {{ $disabled }}>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label class="Label1 {{ $registro->os ? 'importante' : '' }}" for="os">OS</label>
                <input type="text" class="form-control detalhe-rma-v2__controle detalhe-rma-v2__controle--os"
                    name="os" value="{{ $registro->os }}" maxlength="255" {{ $disabled }}>
            </div>
            <div class="form-group">
                <label class="Label1 importante" for="origem">Origem</label>
                <select name="origem" id="origem" class="form-control formSelect detalhe-rma-v2__controle" {{ $disabled }}>
                    <option value=""></option>
                    @foreach ($opcoesOrigem as $origemOpcao)
                        <option value="{{ $origemOpcao }}" @selected($registro->origem === $origemOpcao)>{{ $origemOpcao }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="Label1 {{ $registro->prioridade === \App\Rma\Dominio\Prioridade::Alta ? 'importante' : '' }}" for="prioridade">Prioridade</label>
                <select name="prioridade" id="prioridade" class="form-control formSelect detalhe-rma-v2__controle" {{ $disabled }}>
                    @foreach ($rotulosPrioridade as $case => $rotulo)
                        <option value="{{ strtolower($rotulo) }}" @selected($registro->prioridade?->name === $case)>{{ $rotulo }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="Label1 importante">PROTOCOLO</label>
                <input type="text" class="form-control detalhe-rma-v2__controle" name="protocolo"
                    value="{{ $registro->protocolo }}" maxlength="255" {{ $disabled }}>
            </div>
            <div class="form-group">
                <label class="formlabeldefeito importante">Defeito reclamado</label>
                <textarea class="form-control detalhe-rma-v2__textarea" rows="4" name="defeito"
                    maxlength="255" required {{ $disabled }}>{{ $registro->defeito }}</textarea>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label class="Label1 importante">E um produto do estoque ?</label>
                <select name="marcarestoque" class="form-control formSelect detalhe-rma-v2__controle" {{ $disabled }}>
                    <option value="0" @selected(! $registro->marcarestoque)>Nao</option>
                    <option value="1" @selected($registro->marcarestoque)>Sim</option>
                </select>
            </div>
            <div class="form-group">
                <label class="Label1 importante">Empresa</label>
                <input type="text" class="form-control detalhe-rma-v2__controle" name="empresa"
                    value="{{ $registro->empresa }}" maxlength="255" {{ $disabled }}>
            </div>
            <div class="form-group">
                <label class="Label1">E credito disponivel ?</label>
                <select name="credito_disponivel" class="form-control formSelect detalhe-rma-v2__controle" {{ $disabled }}>
                    <option value="0" @selected(! $registro->creditoDisponivel)>Nao</option>
                    <option value="1" @selected($registro->creditoDisponivel)>Sim</option>
                </select>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label class="Label1">Entrada</label>
                <input type="text" class="form-control detalhe-rma-v2__controle" value="{{ $registro->createdAt?->format('d/m/Y') }}" disabled>
            </div>
            <div class="form-group">
                <label class="Label1">Recebido</label>
                <input type="text" class="form-control detalhe-rma-v2__controle" value="{{ $registro->recebidoEm?->format('d/m/Y') }}" disabled>
            </div>
            <div class="form-group">
                <label class="Label1">Encaminhado</label>
                <input type="text" class="form-control detalhe-rma-v2__controle" value="{{ $registro->encaminhadoEm?->format('d/m/Y') }}" disabled>
            </div>
            <div class="form-group">
                <label class="Label1">Concluido</label>
                <input type="text" class="form-control detalhe-rma-v2__controle" value="{{ $registro->concluidoEm?->format('d/m/Y') }}" disabled>
            </div>
            <div class="form-group">
                <label class="Label1 importante">Tempo</label>
                <input type="text" class="form-control detalhe-rma-v2__controle"
                    value="{{ $registro->createdAt ? (int) $registro->createdAt->diffInDays(now()) : '' }}" disabled>
            </div>
        </div>
    </div>

    <div class="row formgroupnf">
        <div class="col-md-3">
            <div class="form-group">
                <label class="Label1 importante">NF de Venda</label>
                <input type="text" class="form-control detalhe-rma-v2__controle" name="nfvenda"
                    value="{{ $registro->nfvenda }}" maxlength="255" {{ $disabled }}>
            </div>
            <div class="form-group">
                <label class="Label1 importante">Data</label>
                <input type="text" class="form-control detalhe-rma-v2__controle" name="nfvenda_emissao"
                    value="{{ $registro->nfvendaEmissao?->format('d/m/Y') }}" placeholder="dd/mm/aaaa" {{ $disabled }}>
            </div>
            <div class="form-group">
                <label class="Label1">DANFE</label>
                <input type="text" class="form-control detalhe-rma-v2__controle" name="nfvenda_chave"
                    value="{{ $registro->nfvendaChave }}" maxlength="500" {{ $disabled }}>
            </div>
            <div class="form-group">
                <label class="Label1 importante">Quem e o Cliente ?</label>
                <input type="text" class="form-control detalhe-rma-v2__controle" name="cliente_nome"
                    value="{{ $clienteNome }}" maxlength="255" {{ $disabled }}>
            </div>
            <div class="form-group">
                <label class="Label1">E-mail (cliente)</label>
                <input type="text" class="form-control detalhe-rma-v2__controle" name="cliente_email_legado"
                    value="{{ $clienteEmail }}" maxlength="255" {{ $disabled }}>
            </div>
            <div class="form-group">
                <label class="Label1">NF de Devolucao de Venda</label>
                <input type="text" class="form-control detalhe-rma-v2__controle" name="nf_devolucao_de_venda"
                    value="{{ $l['nf_devolucao_de_venda'] ?? '' }}" maxlength="255" {{ $disabled }}>
            </div>
            <div class="form-group">
                <label class="Label1">NF de Entrada p/ Conserto</label>
                <input type="text" class="form-control detalhe-rma-v2__controle" name="nf_entrada_cliente_legado"
                    value="{{ $l['nf_entrada_cliente_legado'] ?? '' }}" maxlength="255" {{ $disabled }}>
            </div>
            <div class="form-group">
                <label class="Label1">NF de Retorno p/ Cliente</label>
                <input type="text" class="form-control detalhe-rma-v2__controle" name="nf_retorno_cliente_legado"
                    value="{{ $l['nf_retorno_cliente_legado'] ?? '' }}" maxlength="255" {{ $disabled }}>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label class="Label1 importante">NF de Compra</label>
                <input type="text" class="form-control detalhe-rma-v2__controle" name="nfcompra"
                    value="{{ $registro->nfcompra }}" maxlength="255" {{ $disabled }}>
            </div>
            <div class="form-group">
                <label class="Label1 importante">Data</label>
                <input type="text" class="form-control detalhe-rma-v2__controle" name="nfcompra_emissao"
                    value="{{ $registro->nfcompraEmissao?->format('d/m/Y') }}" placeholder="dd/mm/aaaa" {{ $disabled }}>
            </div>
            <div class="form-group">
                <label class="Label1">DANFE</label>
                <input type="text" class="form-control detalhe-rma-v2__controle" name="nfcompra_chave"
                    value="{{ $registro->nfcompraChave }}" maxlength="500" {{ $disabled }}>
            </div>
            <div class="form-group">
                <label class="Label1 importante">Qual o Fornecedor ?</label>
                <select name="fornecedor_id" class="form-control formSelect detalhe-rma-v2__controle" {{ $disabled }}>
                    <option value="">-</option>
                    @foreach ($fornecedoresLista ?? [] as $opcaoFornecedor)
                        <option value="{{ $opcaoFornecedor->id }}" @selected($registro->fornecedorId === $opcaoFornecedor->id)>
                            {{ $opcaoFornecedor->nome }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label class="Label1 importante">NF de Remessa</label>
                <input type="text" class="form-control detalhe-rma-v2__controle" name="nf_remessa"
                    value="{{ $l['nf_remessa'] ?? '' }}" maxlength="255" {{ $disabled }}>
            </div>
            <div class="form-group">
                <label class="Label1">Data</label>
                <input type="text" class="form-control detalhe-rma-v2__controle" name="nf_remessa_emissao"
                    value="{{ $l['nf_remessa_emissao'] ?? '' }}" maxlength="30" {{ $disabled }}>
            </div>
            <div class="form-group">
                <label class="Label1">DANFE</label>
                <input type="text" class="form-control detalhe-rma-v2__controle" name="nf_remessa_chave"
                    value="{{ $l['nf_remessa_chave'] ?? '' }}" maxlength="500" {{ $disabled }}>
            </div>
            <div class="form-group">
                <label class="Label1">Valor do produto</label>
                <input type="text" class="form-control detalhe-rma-v2__controle" name="valor"
                    value="{{ $registro->valor !== null && $registro->valor > 0 ? number_format($registro->valor, 2, '.', '') : '' }}" maxlength="20" {{ $disabled }}>
            </div>
            <div class="form-group">
                <label class="Label1 importante">Qual o destinatario ?</label>
                <select name="destinatario_tipo" class="form-control formSelect detalhe-rma-v2__controle" {{ $disabled }}>
                    <option value="">-</option>
                    <optgroup label="Assistencias tecnicas">
                        @foreach ($assistenciasTecnicasLista ?? [] as $assistencia)
                            <option value="assistencia_tecnica:{{ $assistencia->id }}" @selected($valorDestinoDetalheV2 === 'assistencia_tecnica:' . $assistencia->id)>
                                {{ $assistencia->nome }}
                            </option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Fabricantes">
                        @foreach ($fabricantesLista ?? [] as $opcaoFabricante)
                            <option value="fabricante:{{ $opcaoFabricante->id }}" @selected($valorDestinoDetalheV2 === 'fabricante:' . $opcaoFabricante->id)>
                                {{ $opcaoFabricante->nome }}
                            </option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Fornecedores">
                        @foreach ($fornecedoresLista ?? [] as $opcaoFornecedor)
                            <option value="fornecedor:{{ $opcaoFornecedor->id }}" @selected($valorDestinoDetalheV2 === 'fornecedor:' . $opcaoFornecedor->id)>
                                {{ $opcaoFornecedor->nome }}
                            </option>
                        @endforeach
                    </optgroup>
                </select>
            </div>
            <div class="form-group">
                <label class="Label1">Fone</label>
                <input type="text" class="form-control detalhe-rma-v2__controle" name="destinatario_fone_legado"
                    value="{{ $destinatarioFone }}" maxlength="255" {{ $disabled }}>
            </div>
            <div class="form-group">
                <label class="Label1">E-mail</label>
                <input type="text" class="form-control detalhe-rma-v2__controle" name="destinatario_email_legado"
                    value="{{ $destinatarioEmail }}" maxlength="255" {{ $disabled }}>
            </div>
            <div class="form-group">
                <label class="Label1">Codigo de rastreio</label>
                <input type="text" class="form-control detalhe-rma-v2__controle" name="rastreio_ida"
                    value="{{ $l['rastreio_ida'] ?? '' }}" maxlength="255" {{ $disabled }}>
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label class="Label1 importante">NF de Retorno</label>
                <input type="text" class="form-control detalhe-rma-v2__controle" name="nf_retorno_numero"
                    value="{{ $l['nf_retorno_numero'] ?? '' }}" maxlength="255" {{ $disabled }}>
            </div>
            <div class="form-group">
                <label class="Label1">Data</label>
                <input type="text" class="form-control detalhe-rma-v2__controle" name="nf_retorno_emissao"
                    value="{{ $l['nf_retorno_emissao'] ?? '' }}" maxlength="30" {{ $disabled }}>
            </div>
            <div class="form-group">
                <label class="Label1">DANFE</label>
                <input type="text" class="form-control detalhe-rma-v2__controle" name="nf_retorno_chave"
                    value="{{ $l['nf_retorno_chave'] ?? '' }}" maxlength="500" {{ $disabled }}>
            </div>
            <div class="form-group">
                <label class="Label1">NF lancada no estoque ?</label>
                <select name="lancadoretorno" class="form-control formSelect detalhe-rma-v2__controle" {{ $disabled }}>
                    <option value=""></option>
                    @foreach ($rotulosLancamento as $case => $rotulo)
                        <option value="{{ $case }}" @selected($registro->lancadoretorno?->value === $case)>{{ $rotulo }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="Label1">Codigo de rastreio</label>
                <input type="text" class="form-control detalhe-rma-v2__controle" name="rastreio_retorno"
                    value="{{ $l['rastreio_retorno'] ?? '' }}" maxlength="255" {{ $disabled }}>
            </div>
            <div class="form-group">
                <label class="Label1">O que foi feito / resultado ?</label>
                <select name="solucao" class="form-control formSelect detalhe-rma-v2__controle" {{ $disabled }}>
                    <option value=""></option>
                    @foreach (\App\Rma\Dominio\Solucao::cases() as $solucaoOpcao)
                        <option value="{{ $solucaoOpcao->value }}" @selected($registro->solucao === $solucaoOpcao)>
                            {{ $solucaoOpcao->value }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="row formgroupnf">
        <div class="col-md-12">
            <div class="form-group">
                <label class="formLabelTextArea" for="observacao">Informacao adicional</label>
                <textarea class="form-control detalhe-rma-v2__textarea detalhe-rma-v2__textarea--grande"
                    name="observacao" id="observacao" rows="8" {{ $disabled }}>{{ $registro->observacao }}</textarea>
            </div>
        </div>
    </div>

    @if ($politica !== null && ! empty($politica['texto']))
        <div class="row formgroupnf">
            <div class="col-md-12">
                <div class="form-group">
                    <label class="formLabelTextArea Input4 detalhe-v2__politica-titulo">{{ $rotuloPolitica }}</label>
                    <textarea class="form-control detalhe-rma-v2__textarea detalhe-v2__politica-texto"
                        rows="6" disabled>{{ $politica['texto'] }}</textarea>
                </div>
            </div>
        </div>
    @endif

    @if ($podeGravarDetalheV2)
        <div class="row detalhe-rma-v2__acoes-finais">
            <div class="fr">
                @include('temas.v2.rma._acoes_do_ciclo', ['sufixoAcao' => 'down', 'classeSelectAcao' => 'formSelect formSelect2'])
            </div>
        </div>
    @endif

</form>
