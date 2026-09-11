@extends('temas.v2.layout')

@section('conteudo')
    @if ($errors->any())
        <ul class="text-danger">
            @foreach ($errors->all() as $erro)
                <li>{{ $erro }}</li>
            @endforeach
        </ul>
    @endif

    @php
        $existe = $registro->exists;
        $completo = $comEnderecoEContato ?? false;
        $nomeSingular = match ($tipo) {
            'clientes' => 'cliente',
            'fornecedores' => 'fornecedor',
            'fabricantes' => 'fabricante',
            'assistencias-tecnicas' => 'assistência técnica',
            default => 'parceiro',
        };
    @endphp

    @if ($existe)
        {{-- PAR15-PART-002..005 - Contrato visual de EDICAO (subp/ver_*.php):
        Breadcrumb "ID / Nome", grade de 4 col-md-3, RG/IE exposto, Informacao adicional rows=20 e botao SALVAR --}}
        <ol class="breadcrumb submenutitulo">
            <li class="fl" style="margin-top:0px;">{{ $registro->id }} / {{ $registro->nome }}</li>
            <li style="clear:both;"></li>
        </ol>

        <div class="formNovo form-parceiro-v2 form-parceiro-v2--edicao" style="margin:-15px;">
            <form method="POST" action="{{ rota_tema('parceiros.' . $tipo . '.update', $registro) }}" role="form">
                @csrf
                @method('PUT')

                <div class="col-md-3">
                    <div class="form-group form-parceiro-v2__campo">
                        <label class="formLabel form-parceiro-v2__rotulo" for="nome">Nome</label>
                        <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="nome" id="nome" required value="{{ old('nome', $registro->nome) }}">
                    </div>
                    <div class="form-group form-parceiro-v2__campo">
                        <label class="formLabel form-parceiro-v2__rotulo" for="representante">Representante</label>
                        <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="representante" id="representante" value="{{ old('representante', $registro->representante) }}">
                    </div>
                    <div class="form-group form-parceiro-v2__campo">
                        <label class="formLabel form-parceiro-v2__rotulo" for="telefone">Fone</label>
                        <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="telefone" id="telefone" value="{{ old('telefone', $registro->telefone) }}">
                    </div>
                    <div class="form-group form-parceiro-v2__campo">
                        <label class="formLabel form-parceiro-v2__rotulo" for="telefone2">Fone / 2</label>
                        <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="telefone2" id="telefone2" value="{{ old('telefone2', $registro->telefone2) }}">
                    </div>
                    <div class="form-group form-parceiro-v2__campo">
                        <label class="formLabel form-parceiro-v2__rotulo" for="email">E-mail</label>
                        <input type="email" class="form-control Input1 cb form-parceiro-v2__controle" name="email" id="email" value="{{ old('email', $registro->email) }}">
                    </div>
                    @if ($completo)
                        <div class="form-group form-parceiro-v2__campo">
                            <label class="formLabel form-parceiro-v2__rotulo" for="email_secundario">E-mail / 2</label>
                            <input type="email" class="form-control Input1 cb form-parceiro-v2__controle" name="email_secundario" id="email_secundario" value="{{ old('email_secundario', $registro->email_secundario) }}">
                        </div>
                    @endif
                </div>

                <div class="col-md-3">
                    <div class="form-group form-parceiro-v2__campo">
                        <label class="formLabel form-parceiro-v2__rotulo" for="cpf_cnpj">CPF / CNPJ</label>
                        <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="cpf_cnpj" id="cpf_cnpj" value="{{ old('cpf_cnpj', $registro->cpf_cnpj) }}">
                    </div>
                    <div class="form-group form-parceiro-v2__campo">
                        <label class="formLabel form-parceiro-v2__rotulo" for="rgie">RG / IE</label>
                        <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="rgie" id="rgie" value="{{ old('rgie', $registro->rgie) }}">
                    </div>
                    @if ($completo)
                        <div class="form-group form-parceiro-v2__campo">
                            <label class="formLabel form-parceiro-v2__rotulo" for="www">WWW</label>
                            <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="www" id="www" value="{{ old('www', $registro->www) }}">
                        </div>
                        <div class="form-group form-parceiro-v2__campo">
                            <label class="formLabel form-parceiro-v2__rotulo" for="frete">Frete</label>
                            <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="frete" id="frete" value="{{ old('frete', $registro->frete) }}">
                        </div>
                        <div class="form-group form-parceiro-v2__campo">
                            <label class="formLabel form-parceiro-v2__rotulo" for="cfop">CFOP</label>
                            <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="cfop" id="cfop" value="{{ old('cfop', $registro->cfop) }}">
                        </div>
                    @endif
                </div>

                <div class="col-md-3">
                    <div class="form-group form-parceiro-v2__campo">
                        <label class="formLabel form-parceiro-v2__rotulo" for="cep">CEP</label>
                        <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="cep" id="cep" value="{{ old('cep', $registro->cep) }}">
                    </div>
                    <div class="form-group form-parceiro-v2__campo">
                        <label class="formLabel form-parceiro-v2__rotulo" for="logradouro">Logradouro</label>
                        <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="logradouro" id="logradouro" value="{{ old('logradouro', $registro->logradouro) }}">
                    </div>
                    <div class="form-group form-parceiro-v2__campo">
                        <label class="formLabel form-parceiro-v2__rotulo" for="numero">Nº</label>
                        <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="numero" id="numero" value="{{ old('numero', $registro->numero) }}">
                    </div>
                    <div class="form-group form-parceiro-v2__campo">
                        <label class="formLabel form-parceiro-v2__rotulo" for="complemento">Complemento</label>
                        <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="complemento" id="complemento" value="{{ old('complemento', $registro->complemento) }}">
                    </div>
                    <div class="form-group form-parceiro-v2__campo">
                        <label class="formLabel form-parceiro-v2__rotulo" for="bairro">Bairro</label>
                        <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="bairro" id="bairro" value="{{ old('bairro', $registro->bairro) }}">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group form-parceiro-v2__campo" style="width:90%;">
                        <label class="formLabel form-parceiro-v2__rotulo" for="cidade">Cidade</label>
                        <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="cidade" id="cidade" value="{{ old('cidade', $registro->cidade) }}">
                    </div>
                    <div class="form-group form-parceiro-v2__campo" style="width:90%;">
                        <label class="formLabel form-parceiro-v2__rotulo" for="uf">UF</label>
                        <select name="uf" id="uf" class="form-control Input1 cb form-parceiro-v2__controle form-parceiro-v2__controle--curto">
                            <option value="">-</option>
                            @foreach (\App\Compartilhado\Uf::cases() as $uf)
                                <option value="{{ $uf->value }}" @selected(old('uf', $registro->uf?->value) === $uf->value)>{{ $uf->value }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div style="clear:both;height:15px;"></div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="formLabelTextArea Input4" style="float:left;width:100%;" for="observacao">Informação adicional</label>
                        <textarea class="form-control formInputTextArea" rows="20" name="observacao" id="observacao">{{ old('observacao', $registro->observacao) }}</textarea>
                    </div>
                    @if ($completo)
                        <div class="form-group">
                            <label class="formLabelTextArea Input4" style="float:left;width:100%;" for="politica_de_garantia">Política de Garantia</label>
                            <textarea class="form-control formInputTextArea" rows="10" name="politica_de_garantia" id="politica_de_garantia">{{ old('politica_de_garantia', $registro->politica_de_garantia) }}</textarea>
                        </div>
                    @endif
                    <button type="submit" class="btn btn-default formButtonCadastrar2">Salvar</button>
                    <div style="clear:both;height:15px;"></div>
                </div>
            </form>
        </div>
        <div style="clear:both;"></div>
    @else
        {{-- Contrato visual de CRIACAO (15.8.1/inc/novo_*.php):
        Breadcrumb com icone, label "Quem voce quer cadastrar?" e botao CADASTRAR --}}
        <ol class="breadcrumb submenutitulo">
            <li class="fl">
                <img alt="Novo {{ $nomeSingular }}" style="margin-top:-2px;" title="Novo {{ $nomeSingular }}" src="{{ asset('images/rma/novo_cliente.png') }}" width="20" height="20"/>
            </li>
            <li class="fl" style="margin-top:0px;">Novo {{ $nomeSingular }}</li>
            <li style="clear:both;"></li>
        </ol>

        <div class="formNovo form-parceiro-v2 form-parceiro-v2--criacao" style="margin:-15px;">
            <form method="POST" action="{{ rota_tema('parceiros.' . $tipo . '.store') }}" role="form">
                @csrf

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group form-parceiro-v2__campo">
                            <label class="formLabel form-parceiro-v2__rotulo" for="nome">
                                <img style="margin-top:-2px;" src="{{ asset('images/rma/nome.png') }}" width="18"/>
                                Quem voce quer cadastrar?
                            </label>
                            <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="nome" id="nome" required value="{{ old('nome', $registro->nome) }}">
                        </div>
                        <div class="form-group form-parceiro-v2__campo">
                            <label class="formLabel form-parceiro-v2__rotulo" for="representante">Representante</label>
                            <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="representante" id="representante" value="{{ old('representante', $registro->representante) }}">
                        </div>
                        <div class="form-group form-parceiro-v2__campo">
                            <label class="formLabel form-parceiro-v2__rotulo" for="cpf_cnpj">CPF / CNPJ</label>
                            <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="cpf_cnpj" id="cpf_cnpj" value="{{ old('cpf_cnpj', $registro->cpf_cnpj) }}">
                        </div>
                        <div class="form-group form-parceiro-v2__campo">
                            <label class="formLabel form-parceiro-v2__rotulo" for="rgie">RG / IE</label>
                            <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="rgie" id="rgie" value="{{ old('rgie', $registro->rgie) }}">
                        </div>
                        <div class="form-group form-parceiro-v2__campo">
                            <label class="formLabel form-parceiro-v2__rotulo" for="email">E-mail</label>
                            <input type="email" class="form-control Input1 cb form-parceiro-v2__controle" name="email" id="email" value="{{ old('email', $registro->email) }}">
                        </div>
                        <div class="form-group form-parceiro-v2__campo">
                            <label class="formLabel form-parceiro-v2__rotulo" for="telefone">Fone</label>
                            <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="telefone" id="telefone" value="{{ old('telefone', $registro->telefone) }}">
                        </div>
                        @if ($completo)
                            <div class="form-group form-parceiro-v2__campo">
                                <label class="formLabel form-parceiro-v2__rotulo" for="email_secundario">E-mail / 2</label>
                                <input type="email" class="form-control Input1 cb form-parceiro-v2__controle" name="email_secundario" id="email_secundario" value="{{ old('email_secundario', $registro->email_secundario) }}">
                            </div>
                        @endif
                        <div class="form-group form-parceiro-v2__campo">
                            <label class="formLabel form-parceiro-v2__rotulo" for="telefone2">Fone / 2</label>
                            <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="telefone2" id="telefone2" value="{{ old('telefone2', $registro->telefone2) }}">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group form-parceiro-v2__campo">
                            <label class="formLabel form-parceiro-v2__rotulo" for="cep">CEP</label>
                            <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="cep" id="cep" value="{{ old('cep', $registro->cep) }}">
                        </div>
                        <div class="form-group form-parceiro-v2__campo">
                            <label class="formLabel form-parceiro-v2__rotulo" for="logradouro">Logradouro</label>
                            <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="logradouro" id="logradouro" value="{{ old('logradouro', $registro->logradouro) }}">
                        </div>
                        <div class="form-group form-parceiro-v2__campo">
                            <label class="formLabel form-parceiro-v2__rotulo" for="numero">Nº</label>
                            <input type="text" class="form-control Input1 cb form-parceiro-v2__controle form-parceiro-v2__controle--curto" name="numero" id="numero" value="{{ old('numero', $registro->numero) }}">
                        </div>
                        <div class="form-group form-parceiro-v2__campo">
                            <label class="formLabel form-parceiro-v2__rotulo" for="complemento">Complemento</label>
                            <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="complemento" id="complemento" value="{{ old('complemento', $registro->complemento) }}">
                        </div>
                        <div class="form-group form-parceiro-v2__campo">
                            <label class="formLabel form-parceiro-v2__rotulo" for="bairro">Bairro</label>
                            <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="bairro" id="bairro" value="{{ old('bairro', $registro->bairro) }}">
                        </div>
                        <div class="form-group form-parceiro-v2__campo">
                            <label class="formLabel form-parceiro-v2__rotulo" for="cidade">Cidade</label>
                            <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="cidade" id="cidade" value="{{ old('cidade', $registro->cidade) }}">
                        </div>
                        <div class="form-group form-parceiro-v2__campo">
                            <label class="formLabel form-parceiro-v2__rotulo" for="uf">UF</label>
                            <select name="uf" id="uf" class="form-control Input1 cb form-parceiro-v2__controle form-parceiro-v2__controle--curto">
                                <option value="">-</option>
                                @foreach (\App\Compartilhado\Uf::cases() as $uf)
                                    <option value="{{ $uf->value }}" @selected(old('uf', $registro->uf?->value) === $uf->value)>{{ $uf->value }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        @if ($completo)
                            <div class="form-group form-parceiro-v2__campo">
                                <label class="formLabel form-parceiro-v2__rotulo" for="www">WWW</label>
                                <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="www" id="www" value="{{ old('www', $registro->www) }}">
                            </div>
                            <div class="form-group form-parceiro-v2__campo">
                                <label class="formLabel form-parceiro-v2__rotulo" for="frete">Frete</label>
                                <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="frete" id="frete" value="{{ old('frete', $registro->frete) }}">
                            </div>
                            <div class="form-group form-parceiro-v2__campo">
                                <label class="formLabel form-parceiro-v2__rotulo" for="cfop">CFOP</label>
                                <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="cfop" id="cfop" value="{{ old('cfop', $registro->cfop) }}">
                            </div>
                        @endif
                    </div>
                </div>

                <div style="clear:both;height:15px;"></div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="formLabelTextArea form-parceiro-v2__rotulo" for="observacao">Informação adicional</label>
                            <textarea class="form-control formInputTextArea form-parceiro-v2__controle--texto" name="observacao" id="observacao" rows="8">{{ old('observacao', $registro->observacao) }}</textarea>
                        </div>
                    </div>
                    @if ($completo)
                        <div class="col-md-8">
                            <div class="form-group">
                                <label class="formLabelTextArea form-parceiro-v2__rotulo" for="politica_de_garantia">Política de Garantia</label>
                                <textarea class="form-control formInputTextArea form-parceiro-v2__controle--texto" name="politica_de_garantia" id="politica_de_garantia" rows="8">{{ old('politica_de_garantia', $registro->politica_de_garantia) }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-default formButtonCadastrar2 form-parceiro-v2__salvar">Cadastrar</button>
                        </div>
                    @else
                        <div class="col-md-8">
                            <button type="submit" class="btn btn-default formButtonCadastrar2 form-parceiro-v2__salvar">Cadastrar</button>
                        </div>
                    @endif
                </div>
            </form>
        </div>
    @endif
@endsection
