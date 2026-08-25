@extends('temas.v1.layout')

@section('conteudo')
    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $erro)
                <li>{{ $erro }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ $registro->exists ? rota_tema('parceiros.' . $tipo . '.update', $registro) : rota_tema('parceiros.' . $tipo . '.store') }}">
        @csrf
        @if ($registro->exists)
            @method('PUT')
        @endif

        <table class="tablenovo">
            <tr>
                <td>Nome</td>
                <td><input class="novo_formInput" type="text" name="nome" value="{{ old('nome', $registro->nome) }}" required></td>
            </tr>
            <tr>
                <td>Representante</td>
                <td><input class="novo_formInput" type="text" name="representante" value="{{ old('representante', $registro->representante) }}"></td>
            </tr>
            <tr>
                <td>CPF/CNPJ</td>
                <td><input class="novo_formInput" type="text" name="cpf_cnpj" value="{{ old('cpf_cnpj', $registro->cpf_cnpj) }}"></td>
            </tr>
            <tr>
                <td>E-mail</td>
                <td><input class="novo_formInput" type="email" name="email" value="{{ old('email', $registro->email) }}"></td>
            </tr>
            @if ($comEnderecoEContato)
                <tr>
                    <td>E-mail secundário</td>
                    <td><input class="novo_formInput" type="email" name="email_secundario" value="{{ old('email_secundario', $registro->email_secundario) }}"></td>
                </tr>
            @endif
            <tr>
                <td>Telefone</td>
                <td><input class="novo_formInput" type="text" name="telefone" value="{{ old('telefone', $registro->telefone) }}"></td>
            </tr>
            <tr>
                <td>Telefone 2</td>
                <td><input class="novo_formInput" type="text" name="telefone2" value="{{ old('telefone2', $registro->telefone2) }}"></td>
            </tr>
            <tr>
                <td>CEP</td>
                <td><input class="novo_formInput" type="text" name="cep" value="{{ old('cep', $registro->cep) }}"></td>
            </tr>
            <tr>
                <td>Logradouro</td>
                <td><input class="novo_formInput" type="text" name="logradouro" value="{{ old('logradouro', $registro->logradouro) }}"></td>
            </tr>
            <tr>
                <td>Número</td>
                <td><input class="novo_formInput" type="text" name="numero" value="{{ old('numero', $registro->numero) }}"></td>
            </tr>
            <tr>
                <td>Complemento</td>
                <td><input class="novo_formInput" type="text" name="complemento" value="{{ old('complemento', $registro->complemento) }}"></td>
            </tr>
            <tr>
                <td>Bairro</td>
                <td><input class="novo_formInput" type="text" name="bairro" value="{{ old('bairro', $registro->bairro) }}"></td>
            </tr>
            <tr>
                <td>Cidade</td>
                <td><input class="novo_formInput" type="text" name="cidade" value="{{ old('cidade', $registro->cidade) }}"></td>
            </tr>
            <tr>
                <td>UF</td>
                <td>
                    <select name="uf" class="formSelect">
                        <option value="">—</option>
                        @foreach (\App\Compartilhado\Uf::cases() as $uf)
                            <option value="{{ $uf->value }}" @selected(old('uf', $registro->uf?->value) === $uf->value)>
                                {{ $uf->value }}
                            </option>
                        @endforeach
                    </select>
                </td>
            </tr>
            @if ($comEnderecoEContato)
                <tr>
                    <td>Site</td>
                    <td><input class="novo_formInput" type="text" name="www" value="{{ old('www', $registro->www) }}"></td>
                </tr>
                <tr>
                    <td>Frete</td>
                    <td><input class="novo_formInput" type="text" name="frete" value="{{ old('frete', $registro->frete) }}"></td>
                </tr>
                <tr>
                    <td>CFOP</td>
                    <td><input class="novo_formInput" type="text" name="cfop" value="{{ old('cfop', $registro->cfop) }}"></td>
                </tr>
            @endif
            <tr>
                <td>Observação</td>
                <td><textarea name="observacao">{{ old('observacao', $registro->observacao) }}</textarea></td>
            </tr>
            @if ($comEnderecoEContato)
                <tr>
                    <td>Política de garantia</td>
                    <td><textarea name="politica_de_garantia">{{ old('politica_de_garantia', $registro->politica_de_garantia) }}</textarea></td>
                </tr>
            @endif
        </table>

        <button type="submit" class="buttonSave">Salvar</button>
    </form>
@endsection
