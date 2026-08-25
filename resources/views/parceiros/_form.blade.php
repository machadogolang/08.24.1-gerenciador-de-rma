<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>{{ $titulo }} — CellSystem RMA</title>
</head>
<body>
    <h1>{{ $titulo }}</h1>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $erro)
                <li>{{ $erro }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ $registro->exists ? route('parceiros.' . $tipo . '.update', $registro) : route('parceiros.' . $tipo . '.store') }}">
        @csrf
        @if ($registro->exists)
            @method('PUT')
        @endif

        <label>Nome
            <input type="text" name="nome" value="{{ old('nome', $registro->nome) }}" required>
        </label>

        <label>Representante
            <input type="text" name="representante" value="{{ old('representante', $registro->representante) }}">
        </label>

        <label>CPF/CNPJ
            <input type="text" name="cpf_cnpj" value="{{ old('cpf_cnpj', $registro->cpf_cnpj) }}">
        </label>

        <label>E-mail
            <input type="email" name="email" value="{{ old('email', $registro->email) }}">
        </label>

        @if ($comEnderecoEContato)
            <label>E-mail secundário
                <input type="email" name="email_secundario" value="{{ old('email_secundario', $registro->email_secundario) }}">
            </label>
        @endif

        <label>Telefone
            <input type="text" name="telefone" value="{{ old('telefone', $registro->telefone) }}">
        </label>

        <label>Telefone 2
            <input type="text" name="telefone2" value="{{ old('telefone2', $registro->telefone2) }}">
        </label>

        <label>CEP
            <input type="text" name="cep" value="{{ old('cep', $registro->cep) }}">
        </label>

        <label>Logradouro
            <input type="text" name="logradouro" value="{{ old('logradouro', $registro->logradouro) }}">
        </label>

        <label>Número
            <input type="text" name="numero" value="{{ old('numero', $registro->numero) }}">
        </label>

        <label>Complemento
            <input type="text" name="complemento" value="{{ old('complemento', $registro->complemento) }}">
        </label>

        <label>Bairro
            <input type="text" name="bairro" value="{{ old('bairro', $registro->bairro) }}">
        </label>

        <label>Cidade
            <input type="text" name="cidade" value="{{ old('cidade', $registro->cidade) }}">
        </label>

        <label>UF
            <select name="uf">
                <option value="">—</option>
                @foreach (\App\Compartilhado\Uf::cases() as $uf)
                    <option value="{{ $uf->value }}" @selected(old('uf', $registro->uf?->value) === $uf->value)>
                        {{ $uf->value }}
                    </option>
                @endforeach
            </select>
        </label>

        @if ($comEnderecoEContato)
            <label>Site
                <input type="text" name="www" value="{{ old('www', $registro->www) }}">
            </label>

            <label>Frete
                <input type="text" name="frete" value="{{ old('frete', $registro->frete) }}">
            </label>

            <label>CFOP
                <input type="text" name="cfop" value="{{ old('cfop', $registro->cfop) }}">
            </label>
        @endif

        <label>Observação
            <textarea name="observacao">{{ old('observacao', $registro->observacao) }}</textarea>
        </label>

        @if ($comEnderecoEContato)
            <label>Política de garantia
                <textarea name="politica_de_garantia">{{ old('politica_de_garantia', $registro->politica_de_garantia) }}</textarea>
            </label>
        @endif

        <button type="submit">Salvar</button>
    </form>
</body>
</html>
