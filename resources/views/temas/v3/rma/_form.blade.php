@php
    $registroFormV3 = $registro ?? null;
    $fabricantesFormV3 = $fabricantes ?? collect();
    $fornecedoresFormV3 = $fornecedores ?? collect();
    $assistenciasFormV3 = $assistenciasTecnicas ?? collect();
    $origensFormV3 = ['Unknown', 'Loja', 'Casa', 'Cliente', 'Licitação', 'Leilão', 'Mercado Livre', 'Credito', 'AC'];
    $tipoDestinoFormV3 = $registroFormV3?->destinatarioType ?? '';
    $slugDestinoFormV3 = match (true) {
        str_ends_with($tipoDestinoFormV3, 'AssistenciaTecnica') => 'assistencia_tecnica',
        str_ends_with($tipoDestinoFormV3, 'Fabricante') => 'fabricante',
        str_ends_with($tipoDestinoFormV3, 'Fornecedor') => 'fornecedor',
        default => '',
    };
    $valorDestinoFormV3 = $slugDestinoFormV3 !== '' && $registroFormV3?->destinatarioId !== null
        ? $slugDestinoFormV3 . ':' . $registroFormV3->destinatarioId
        : '';
    $rotulosPrioridadeFormV3 = [
        'baixa' => 'Baixa',
        'media' => 'Normal',
        'alta' => 'Alta',
    ];
    $prioridadeAtualFormV3 = $registroFormV3?->prioridade?->name ?? 'Media';
    $prioridadePadraoFormV3 = match ($prioridadeAtualFormV3) {
        'Alta' => 'alta',
        'Baixa' => 'baixa',
        default => 'media',
    };
    $prioridadeSelecionadaFormV3 = match (mb_strtolower(trim((string) old('prioridade', $prioridadePadraoFormV3)))) {
        'normal' => 'media',
        default => mb_strtolower(trim((string) old('prioridade', $prioridadePadraoFormV3))),
    };
    $valorAtualFormV3 = $registroFormV3?->valor;
@endphp

<form method="POST" action="{{ $formAction }}" class="form-v3" novalidate>
    @csrf
    @if ($registroFormV3 !== null)
        @method('PUT')
    @endif

    <div class="form-v3__secoes">
        <section class="cartao form-v3__secao" aria-labelledby="form-secao-identificacao">
            <h2 id="form-secao-identificacao" class="form-v3__titulo">Identificacao</h2>
            <div class="form-v3__grade">
                <div class="campo">
                    <label class="campo__rotulo" for="descricao">Descricao</label>
                    <input class="campo__controle" id="descricao" name="descricao" required maxlength="255"
                        value="{{ old('descricao', $registroFormV3?->descricao) }}">
                </div>
                <div class="campo">
                    <label class="campo__rotulo" for="modelo">Modelo</label>
                    <input class="campo__controle" id="modelo" name="modelo" maxlength="255"
                        value="{{ old('modelo', $registroFormV3?->modelo) }}">
                </div>
                <div class="campo">
                    <label class="campo__rotulo" for="sn">S/N</label>
                    <input class="campo__controle" id="sn" name="sn" maxlength="255"
                        value="{{ old('sn', $registroFormV3?->sn) }}">
                </div>
                <div class="campo">
                    <label class="campo__rotulo" for="pn">P/N</label>
                    <input class="campo__controle" id="pn" name="pn" maxlength="255"
                        value="{{ old('pn', $registroFormV3?->pn) }}">
                </div>
                <div class="campo">
                    <label class="campo__rotulo" for="snid">SNID</label>
                    <input class="campo__controle" id="snid" name="snid" maxlength="255"
                        value="{{ old('snid', $registroFormV3?->snid) }}">
                </div>
                <div class="campo">
                    <label class="campo__rotulo" for="os">OS</label>
                    <input class="campo__controle" id="os" name="os" maxlength="255"
                        value="{{ old('os', $registroFormV3?->os) }}">
                </div>
            </div>
        </section>

        <section class="cartao form-v3__secao" aria-labelledby="form-secao-origem">
            <h2 id="form-secao-origem" class="form-v3__titulo">Origem e parceiros</h2>
            <div class="form-v3__grade">
                <div class="campo">
                    <label class="campo__rotulo" for="origem">Origem</label>
                    <select class="campo__controle" id="origem" name="origem">
                        <option value=""></option>
                        @foreach ($origensFormV3 as $origem)
                            <option value="{{ $origem }}" @selected(old('origem', $registroFormV3?->origem) === $origem)>{{ $origem }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="campo">
                    <label class="campo__rotulo" for="empresa">Empresa</label>
                    <input class="campo__controle" id="empresa" name="empresa" maxlength="255"
                        value="{{ old('empresa', $registroFormV3?->empresa) }}">
                </div>
                <div class="campo">
                    <label class="campo__rotulo" for="cliente_nome">Cliente</label>
                    <input class="campo__controle" id="cliente_nome" name="cliente_nome" maxlength="255"
                        value="{{ old('cliente_nome', $clienteNome ?? '') }}">
                </div>
                <div class="campo">
                    <label class="campo__rotulo" for="fabricante_id">Fabricante</label>
                    <select class="campo__controle" id="fabricante_id" name="fabricante_id">
                        <option value="">-</option>
                        @foreach ($fabricantesFormV3 as $fabricante)
                            <option value="{{ $fabricante->id }}" @selected((int) old('fabricante_id', $registroFormV3?->fabricanteId) === $fabricante->id)>
                                {{ $fabricante->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="campo">
                    <label class="campo__rotulo" for="fornecedor_id">Fornecedor</label>
                    <select class="campo__controle" id="fornecedor_id" name="fornecedor_id">
                        <option value="">-</option>
                        @foreach ($fornecedoresFormV3 as $fornecedor)
                            <option value="{{ $fornecedor->id }}" @selected((int) old('fornecedor_id', $registroFormV3?->fornecedorId) === $fornecedor->id)>
                                {{ $fornecedor->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </section>

        <section class="cartao form-v3__secao form-v3__secao--larga" aria-labelledby="form-secao-fiscal">
            <h2 id="form-secao-fiscal" class="form-v3__titulo">Fiscal</h2>
            <div class="form-v3__grade form-v3__grade--fiscal">
                <div class="campo">
                    <label class="campo__rotulo" for="nfvenda">NF de venda</label>
                    <input class="campo__controle" id="nfvenda" name="nfvenda" maxlength="255"
                        value="{{ old('nfvenda', $registroFormV3?->nfvenda) }}">
                </div>
                <div class="campo">
                    <label class="campo__rotulo" for="nfvenda_emissao">Data de emissao (venda)</label>
                    <input class="campo__controle" id="nfvenda_emissao" name="nfvenda_emissao" placeholder="dd/mm/aaaa"
                        value="{{ old('nfvenda_emissao', $registroFormV3?->nfvendaEmissao?->format('d/m/Y')) }}">
                </div>
                <div class="campo">
                    <label class="campo__rotulo" for="nfvenda_chave">DANFE venda</label>
                    <input class="campo__controle campo__controle--mono" id="nfvenda_chave" name="nfvenda_chave" maxlength="500"
                        value="{{ old('nfvenda_chave', $registroFormV3?->nfvendaChave) }}">
                </div>
                <div class="campo">
                    <label class="campo__rotulo" for="nfcompra">NF de compra</label>
                    <input class="campo__controle" id="nfcompra" name="nfcompra" maxlength="255"
                        value="{{ old('nfcompra', $registroFormV3?->nfcompra) }}">
                </div>
                <div class="campo">
                    <label class="campo__rotulo" for="nfcompra_emissao">Data de emissao (compra)</label>
                    <input class="campo__controle" id="nfcompra_emissao" name="nfcompra_emissao" placeholder="dd/mm/aaaa"
                        value="{{ old('nfcompra_emissao', $registroFormV3?->nfcompraEmissao?->format('d/m/Y')) }}">
                </div>
                <div class="campo">
                    <label class="campo__rotulo" for="nfcompra_chave">DANFE compra</label>
                    <input class="campo__controle campo__controle--mono" id="nfcompra_chave" name="nfcompra_chave" maxlength="500"
                        value="{{ old('nfcompra_chave', $registroFormV3?->nfcompraChave) }}">
                </div>
            </div>
        </section>

        <section class="cartao form-v3__secao" aria-labelledby="form-secao-operacao">
            <h2 id="form-secao-operacao" class="form-v3__titulo">Operacao</h2>
            <div class="form-v3__grade">
                <div class="campo">
                    <label class="campo__rotulo" for="prioridade">Prioridade</label>
                    <select class="campo__controle" id="prioridade" name="prioridade">
                        @foreach ($rotulosPrioridadeFormV3 as $valor => $rotulo)
                            <option value="{{ $valor }}" @selected($prioridadeSelecionadaFormV3 === $valor)>{{ $rotulo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="campo">
                    <label class="campo__rotulo" for="protocolo">Protocolo</label>
                    <input class="campo__controle" id="protocolo" name="protocolo" maxlength="255"
                        value="{{ old('protocolo', $registroFormV3?->protocolo) }}">
                </div>
                <div class="campo">
                    <label class="campo__rotulo" for="valor">Valor do produto</label>
                    <input class="campo__controle" id="valor" name="valor" inputmode="decimal"
                        value="{{ old('valor', $valorAtualFormV3 !== null && $valorAtualFormV3 > 0 ? number_format($valorAtualFormV3, 2, '.', '') : '') }}">
                </div>
                <div class="campo">
                    <label class="campo__rotulo" for="solucao">Solucao</label>
                    <select class="campo__controle" id="solucao" name="solucao">
                        <option value=""></option>
                        @foreach (\App\Rma\Dominio\Solucao::cases() as $solucao)
                            <option value="{{ $solucao->value }}" @selected(old('solucao', $registroFormV3?->solucao?->value) === $solucao->value)>
                                {{ $solucao->value }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="campo campo--caixa">
                    <input type="hidden" name="marcarestoque" value="0">
                    <input class="campo__controle" type="checkbox" id="marcarestoque" name="marcarestoque" value="1"
                        @checked(old('marcarestoque', $registroFormV3?->marcarestoque ?? true))>
                    <label class="campo__rotulo campo__rotulo--inline" for="marcarestoque">Item do estoque</label>
                </div>
                <div class="campo campo--caixa">
                    <input type="hidden" name="credito_disponivel" value="0">
                    <input class="campo__controle" type="checkbox" id="credito_disponivel" name="credito_disponivel" value="1"
                        @checked(old('credito_disponivel', $registroFormV3?->creditoDisponivel))>
                    <label class="campo__rotulo campo__rotulo--inline" for="credito_disponivel">Credito disponivel</label>
                </div>
                <div class="campo">
                    <label class="campo__rotulo" for="destinatario_tipo">Destinatario</label>
                    <select class="campo__controle" id="destinatario_tipo" name="destinatario_tipo">
                        <option value="">-</option>
                        @if ($assistenciasFormV3->isNotEmpty())
                            <optgroup label="Assistencias tecnicas">
                                @foreach ($assistenciasFormV3 as $assistencia)
                                    <option value="assistencia_tecnica:{{ $assistencia->id }}" @selected($valorDestinoFormV3 === 'assistencia_tecnica:' . $assistencia->id)>
                                        {{ $assistencia->nome }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                        @if ($fabricantesFormV3->isNotEmpty())
                            <optgroup label="Fabricantes">
                                @foreach ($fabricantesFormV3 as $fabricante)
                                    <option value="fabricante:{{ $fabricante->id }}" @selected($valorDestinoFormV3 === 'fabricante:' . $fabricante->id)>
                                        {{ $fabricante->nome }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                        @if ($fornecedoresFormV3->isNotEmpty())
                            <optgroup label="Fornecedores">
                                @foreach ($fornecedoresFormV3 as $fornecedor)
                                    <option value="fornecedor:{{ $fornecedor->id }}" @selected($valorDestinoFormV3 === 'fornecedor:' . $fornecedor->id)>
                                        {{ $fornecedor->nome }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>
                </div>
            </div>
        </section>

        <section class="cartao form-v3__secao" aria-labelledby="form-secao-observacoes">
            <h2 id="form-secao-observacoes" class="form-v3__titulo">Observacoes</h2>
            <div class="form-v3__grade">
                <div class="campo">
                    <label class="campo__rotulo" for="defeito">Defeito reclamado</label>
                    <textarea class="campo__controle" id="defeito" name="defeito" rows="3" maxlength="255" required>{{ old('defeito', $registroFormV3?->defeito) }}</textarea>
                </div>
                <div class="campo">
                    <label class="campo__rotulo" for="observacao">Observacao</label>
                    <textarea class="campo__controle" id="observacao" name="observacao" rows="6">{{ old('observacao', $registroFormV3?->observacao) }}</textarea>
                </div>
            </div>
        </section>
    </div>

    <div class="form-v3__acoes">
        <a class="botao botao--secundario" href="{{ $voltarUrl }}">Cancelar</a>
        <button type="submit" class="botao">Salvar</button>
    </div>
</form>
