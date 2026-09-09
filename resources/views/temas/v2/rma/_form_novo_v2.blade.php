{{-- PAR-V2-NOVO-01 - formulario Novo RMA inline do Tema V2, composicao 15.8.1
(page/novo_rma.php): 3 colunas, condicional NF Venda/Compra por Origem, estoque e
CRIAR BD. Rotas/store modernos (POST + CSRF + CriarRma), sem SQL/JS legado. --}}
@php
    $fabricantesNovoV2 = $fabricantesParaNovo ?? ($fabricantes ?? collect());
    $fornecedoresNovoV2 = $fornecedoresParaNovo ?? ($fornecedores ?? collect());
    $empresasNovoV2 = ['', 'Cellsystem', 'Expert', 'Registros Ativos', 'R A', 'Informatica', 'T A'];
@endphp

<div class="form-novo-v2">
    <ol class="breadcrumb submenutitulo">
        <li class="fl" style="color:#FC9E6B;">Preencha os dados abaixo para inserir um novo BD</li>
        <li style="clear:both;"></li>
    </ol>

    <form method="POST" action="{{ rota_tema('rmas.store') }}" role="form">
        @csrf

        <div class="row formgroupnf form-novo-v2__grade">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="form-novo-v2__rotulo">Que produto é?</label>
                    <input type="text" class="form-control" name="descricao" maxlength="255" required>
                </div>
                <div class="form-group">
                    <label class="form-novo-v2__rotulo">Fabricante</label>
                    <select name="fabricante_id" class="form-control formSelect">
                        <option value="">-</option>
                        @foreach ($fabricantesNovoV2 as $fabricante)
                            <option value="{{ $fabricante->id }}">{{ $fabricante->nome }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-novo-v2__rotulo">Modelo</label>
                    <input type="text" class="form-control" name="modelo" maxlength="255">
                </div>
                <div class="form-group">
                    <label class="form-novo-v2__rotulo" for="sn">S/N</label>
                    <input type="text" class="form-control" name="sn" id="sn" maxlength="255">
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label class="form-novo-v2__rotulo" for="os">OS</label>
                    <input type="text" class="form-control form-novo-v2__os" name="os" id="os" maxlength="255">
                </div>
                <div class="form-group">
                    <label class="form-novo-v2__rotulo" for="origem">Origem</label>
                    <select name="origem" id="origem" class="form-control formSelect form-novo-v2__select-largo" required>
                        <option value=""></option>
                        <option value="Unknown">Unknown</option>
                        <option value="Loja">Loja</option>
                        <option value="Casa">Casa</option>
                        <option value="Cliente">Cliente</option>
                        <option value="Licitação">Licitação</option>
                        <option value="Leilão">Leilão</option>
                        <option value="Mercado Livre">Mercado Livre</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-novo-v2__rotulo">Empresa</label>
                    <select name="empresa" class="form-control formSelect form-novo-v2__select-largo">
                        @foreach ($empresasNovoV2 as $empresa)
                            <option value="{{ $empresa }}" @selected(old('empresa', '') === $empresa)>{{ $empresa ?: '-' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-novo-v2__rotulo" for="prioridade">Prioridade</label>
                    <select name="prioridade" id="prioridade" class="form-control formSelect form-novo-v2__select-largo">
                        <option value="baixa">Baixa</option>
                        <option value="media" selected>Normal</option>
                        <option value="alta">Alta</option>
                    </select>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-novo-v2__bloco-cli" id="cli" style="display:none;">
                    <div class="form-group">
                        <label class="form-novo-v2__rotulo">NF de Venda</label>
                        <input type="text" class="form-control form-novo-v2__nf-linha" name="nfvenda" maxlength="255">
                        <label class="form-novo-v2__rotulo form-novo-v2__rotulo--data">Data de emissao</label>
                        <input type="text" class="form-control form-novo-v2__nf-linha" name="nfvenda_emissao" placeholder="dd/mm/aaaa" maxlength="10">
                    </div>
                    <div class="form-group">
                        <label class="form-novo-v2__rotulo">DANFE</label>
                        <input type="text" class="form-control" name="nfvenda_chave" maxlength="500">
                    </div>
                    <div class="form-group">
                        <label class="form-novo-v2__rotulo">Quem é o Cliente?</label>
                        <input type="text" class="form-control" name="cliente_nome" maxlength="255">
                    </div>
                </div>

                <div class="form-novo-v2__bloco-outra-origem" id="outraorigem" style="display:none;">
                    <div class="form-group">
                        <label class="form-novo-v2__rotulo">NF de Compra</label>
                        <input type="text" class="form-control form-novo-v2__nf-linha" name="nfcompra" maxlength="255">
                        <label class="form-novo-v2__rotulo form-novo-v2__rotulo--data">Data de emissao</label>
                        <input type="text" class="form-control form-novo-v2__nf-linha" name="nfcompra_emissao" placeholder="dd/mm/aaaa" maxlength="10">
                    </div>
                    <div class="form-group">
                        <label class="form-novo-v2__rotulo">DANFE</label>
                        <input type="text" class="form-control" name="nfcompra_chave" maxlength="500">
                    </div>
                    <div class="form-group">
                        <label class="form-novo-v2__rotulo">Qual o Fornecedor?</label>
                        <select name="fornecedor_id" class="form-control formSelect">
                            <option value="">-</option>
                            @foreach ($fornecedoresNovoV2 as $fornecedor)
                                <option value="{{ $fornecedor->id }}">{{ $fornecedor->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div style="clear:both;height:15px;"></div>

        <div class="row formgroupnf">
            <div class="col-md-8">
                <div class="form-group">
                    <label class="form-novo-v2__rotulo" for="defeito">Defeito reclamado</label>
                    <textarea class="form-control form-novo-v2__textarea-curto" name="defeito" id="defeito" rows="2" maxlength="255" required></textarea>
                </div>
                <div class="form-group">
                    <label class="form-novo-v2__rotulo" for="observacao">OBSERVACAO</label>
                    <textarea class="form-control form-novo-v2__textarea-curto" name="observacao" id="observacao" rows="5"></textarea>
                </div>
                <div class="form-novo-v2__estoque">
                    <input type="hidden" name="marcarestoque" value="0">
                    <input type="checkbox" id="checkbox3" name="marcarestoque" value="1" checked>
                    <label for="checkbox3">O ITEM E DO ESTOQUE</label>
                </div>
                <button type="submit" class="btn btn-default formSubmit form-novo-v2__criar">CRIAR BD</button>
            </div>
            <div class="col-md-4"></div>
        </div>
    </form>
</div>
