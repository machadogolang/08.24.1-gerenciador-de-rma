@extends('temas.v2.layout')

@section('conteudo')
    @if ($errors->any())
        <ul class="text-danger">
            @foreach ($errors->all() as $erro)
                <li>{{ $erro }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ $registro->exists ? rota_tema('parceiros.' . $tipo . '.update', $registro) : rota_tema('parceiros.' . $tipo . '.store') }}" class="form-horizontal">
        @csrf
        @if ($registro->exists)
            @method('PUT')
        @endif

        <div class="form-group">
            <label class="col-sm-2 control-label">Nome</label>
            <div class="col-sm-6"><input type="text" class="form-control" name="nome" value="{{ old('nome', $registro->nome) }}" required></div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Representante</label>
            <div class="col-sm-6"><input type="text" class="form-control" name="representante" value="{{ old('representante', $registro->representante) }}"></div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">CPF/CNPJ</label>
            <div class="col-sm-6"><input type="text" class="form-control" name="cpf_cnpj" value="{{ old('cpf_cnpj', $registro->cpf_cnpj) }}"></div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">E-mail</label>
            <div class="col-sm-6"><input type="email" class="form-control" name="email" value="{{ old('email', $registro->email) }}"></div>
        </div>
        @if ($comEnderecoEContato)
            <div class="form-group">
                <label class="col-sm-2 control-label">E-mail secundário</label>
                <div class="col-sm-6"><input type="email" class="form-control" name="email_secundario" value="{{ old('email_secundario', $registro->email_secundario) }}"></div>
            </div>
        @endif
        <div class="form-group">
            <label class="col-sm-2 control-label">Telefone</label>
            <div class="col-sm-6"><input type="text" class="form-control" name="telefone" value="{{ old('telefone', $registro->telefone) }}"></div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Telefone 2</label>
            <div class="col-sm-6"><input type="text" class="form-control" name="telefone2" value="{{ old('telefone2', $registro->telefone2) }}"></div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">CEP</label>
            <div class="col-sm-6"><input type="text" class="form-control" name="cep" value="{{ old('cep', $registro->cep) }}"></div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Logradouro</label>
            <div class="col-sm-6"><input type="text" class="form-control" name="logradouro" value="{{ old('logradouro', $registro->logradouro) }}"></div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Número</label>
            <div class="col-sm-6"><input type="text" class="form-control" name="numero" value="{{ old('numero', $registro->numero) }}"></div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Complemento</label>
            <div class="col-sm-6"><input type="text" class="form-control" name="complemento" value="{{ old('complemento', $registro->complemento) }}"></div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Bairro</label>
            <div class="col-sm-6"><input type="text" class="form-control" name="bairro" value="{{ old('bairro', $registro->bairro) }}"></div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">Cidade</label>
            <div class="col-sm-6"><input type="text" class="form-control" name="cidade" value="{{ old('cidade', $registro->cidade) }}"></div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">UF</label>
            <div class="col-sm-6">
                <select name="uf" class="form-control formSelect">
                    <option value="">—</option>
                    @foreach (\App\Compartilhado\Uf::cases() as $uf)
                        <option value="{{ $uf->value }}" @selected(old('uf', $registro->uf?->value) === $uf->value)>
                            {{ $uf->value }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        @if ($comEnderecoEContato)
            <div class="form-group">
                <label class="col-sm-2 control-label">Site</label>
                <div class="col-sm-6"><input type="text" class="form-control" name="www" value="{{ old('www', $registro->www) }}"></div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">Frete</label>
                <div class="col-sm-6"><input type="text" class="form-control" name="frete" value="{{ old('frete', $registro->frete) }}"></div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">CFOP</label>
                <div class="col-sm-6"><input type="text" class="form-control" name="cfop" value="{{ old('cfop', $registro->cfop) }}"></div>
            </div>
        @endif
        <div class="form-group">
            <label class="col-sm-2 control-label">Observação</label>
            <div class="col-sm-6"><textarea class="form-control" name="observacao">{{ old('observacao', $registro->observacao) }}</textarea></div>
        </div>
        @if ($comEnderecoEContato)
            <div class="form-group">
                <label class="col-sm-2 control-label">Política de garantia</label>
                <div class="col-sm-6"><textarea class="form-control" name="politica_de_garantia">{{ old('politica_de_garantia', $registro->politica_de_garantia) }}</textarea></div>
            </div>
        @endif

        <button type="submit" class="btn formSubmit">Salvar</button>
    </form>
@endsection
