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
    @endphp

    {{-- PAR-RES-D-01..04 - composicao historica V2 (15.8.1/inc/novo_*.php): grade
    de colunas, controles escuros e textareas de Observacao/Política separadas;
    seguranca/CSRF/casos de uso continuam identicos. --}}
    <div class="form-parceiro-v2">
        <form method="POST"
            action="{{ $existe ? rota_tema('parceiros.' . $tipo . '.update', $registro) : rota_tema('parceiros.' . $tipo . '.store') }}"
            role="form">
            @csrf
            @if ($existe)
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group form-parceiro-v2__campo">
                        <label class="formLabel form-parceiro-v2__rotulo" for="nome">Quem voce quer cadastrar?</label>
                        <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="nome" id="nome" required value="{{ old('nome', $registro->nome) }}">
                    </div>
                    <div class="form-group form-parceiro-v2__campo">
                        <label class="formLabel form-parceiro-v2__rotulo" for="representante">Representante</label>
                        <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="representante" value="{{ old('representante', $registro->representante) }}">
                    </div>
                    <div class="form-group form-parceiro-v2__campo">
                        <label class="formLabel form-parceiro-v2__rotulo" for="cpf_cnpj">CPF / CNPJ</label>
                        <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="cpf_cnpj" value="{{ old('cpf_cnpj', $registro->cpf_cnpj) }}">
                    </div>
                    <div class="form-group form-parceiro-v2__campo">
                        <label class="formLabel form-parceiro-v2__rotulo" for="email">E-mail</label>
                        <input type="email" class="form-control Input1 cb form-parceiro-v2__controle" name="email" value="{{ old('email', $registro->email) }}">
                    </div>
                    <div class="form-group form-parceiro-v2__campo">
                        <label class="formLabel form-parceiro-v2__rotulo" for="telefone">Fone</label>
                        <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="telefone" value="{{ old('telefone', $registro->telefone) }}">
                    </div>
                    @if ($completo)
                        <div class="form-group form-parceiro-v2__campo">
                            <label class="formLabel form-parceiro-v2__rotulo" for="email_secundario">E-mail / 2</label>
                            <input type="email" class="form-control Input1 cb form-parceiro-v2__controle" name="email_secundario" value="{{ old('email_secundario', $registro->email_secundario) }}">
                        </div>
                    @endif
                    <div class="form-group form-parceiro-v2__campo">
                        <label class="formLabel form-parceiro-v2__rotulo" for="telefone2">Fone / 2</label>
                        <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="telefone2" value="{{ old('telefone2', $registro->telefone2) }}">
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group form-parceiro-v2__campo">
                        <label class="formLabel form-parceiro-v2__rotulo" for="cep">CEP</label>
                        <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="cep" value="{{ old('cep', $registro->cep) }}">
                    </div>
                    <div class="form-group form-parceiro-v2__campo">
                        <label class="formLabel form-parceiro-v2__rotulo" for="logradouro">Logradouro</label>
                        <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="logradouro" value="{{ old('logradouro', $registro->logradouro) }}">
                    </div>
                    <div class="form-group form-parceiro-v2__campo">
                        <label class="formLabel form-parceiro-v2__rotulo" for="numero">Numero</label>
                        <input type="text" class="form-control Input1 cb form-parceiro-v2__controle form-parceiro-v2__controle--curto" name="numero" value="{{ old('numero', $registro->numero) }}">
                    </div>
                    <div class="form-group form-parceiro-v2__campo">
                        <label class="formLabel form-parceiro-v2__rotulo" for="complemento">Complemento</label>
                        <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="complemento" value="{{ old('complemento', $registro->complemento) }}">
                    </div>
                    <div class="form-group form-parceiro-v2__campo">
                        <label class="formLabel form-parceiro-v2__rotulo" for="bairro">Bairro</label>
                        <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="bairro" value="{{ old('bairro', $registro->bairro) }}">
                    </div>
                    <div class="form-group form-parceiro-v2__campo">
                        <label class="formLabel form-parceiro-v2__rotulo" for="cidade">Cidade</label>
                        <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="cidade" value="{{ old('cidade', $registro->cidade) }}">
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
                            <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="www" value="{{ old('www', $registro->www) }}">
                        </div>
                        <div class="form-group form-parceiro-v2__campo">
                            <label class="formLabel form-parceiro-v2__rotulo" for="frete">Frete</label>
                            <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="frete" value="{{ old('frete', $registro->frete) }}">
                        </div>
                        <div class="form-group form-parceiro-v2__campo">
                            <label class="formLabel form-parceiro-v2__rotulo" for="cfop">CFOP</label>
                            <input type="text" class="form-control Input1 cb form-parceiro-v2__controle" name="cfop" value="{{ old('cfop', $registro->cfop) }}">
                        </div>
                    @endif
                </div>
            </div>

            <div style="clear:both;height:15px;"></div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="formLabelTextArea form-parceiro-v2__rotulo" for="observacao">Informacao adicional</label>
                        <textarea class="form-control formInputTextArea form-parceiro-v2__controle--texto" name="observacao" id="observacao" rows="8">{{ old('observacao', $registro->observacao) }}</textarea>
                    </div>
                </div>
                @if ($completo)
                    <div class="col-md-8">
                        <div class="form-group">
                            <label class="formLabelTextArea form-parceiro-v2__rotulo" for="politica_de_garantia">Politica de Garantia</label>
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
@endsection
