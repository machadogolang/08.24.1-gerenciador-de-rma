{{-- Formulário "Novo RMA"/edição — HTML <table> autoral (`.tablenovo`), confirmado em
`14.6.1/index.php` (painel "Novo"). TEMA V1 não usa nenhum framework CSS/grid. --}}
<table class="tablenovo">
    <tr>
        <td>Descrição</td>
        <td><input class="novo_formInput" type="text" name="descricao" value="{{ old('descricao', $registro?->descricao) }}" required></td>
    </tr>
    <tr>
        <td>Fabricante</td>
        <td>
            <select name="fabricante_id" class="formSelect">
                <option value="">—</option>
                @foreach ($fabricantes as $fabricante)
                    <option value="{{ $fabricante->id }}" @selected(old('fabricante_id', $registro?->fabricanteId) == $fabricante->id)>
                        {{ $fabricante->nome }}
                    </option>
                @endforeach
            </select>
        </td>
    </tr>
    <tr>
        <td>Fornecedor</td>
        <td>
            <select name="fornecedor_id" class="formSelect">
                <option value="">—</option>
                @foreach ($fornecedores as $fornecedor)
                    <option value="{{ $fornecedor->id }}" @selected(old('fornecedor_id', $registro?->fornecedorId) == $fornecedor->id)>
                        {{ $fornecedor->nome }}
                    </option>
                @endforeach
            </select>
        </td>
    </tr>
    <tr>
        <td>Modelo</td>
        <td><input class="novo_formInput" type="text" name="modelo" value="{{ old('modelo', $registro?->modelo) }}"></td>
    </tr>
    <tr>
        <td>SN</td>
        <td><input class="novo_formInput" type="text" name="sn" value="{{ old('sn', $registro?->sn) }}"></td>
    </tr>
    <tr>
        <td>OS</td>
        <td><input class="novo_formInput" type="text" name="os" value="{{ old('os', $registro?->os) }}"></td>
    </tr>
    <tr>
        <td>Origem</td>
        <td><input class="novo_formInput" type="text" name="origem" value="{{ old('origem', $registro?->origem) }}"></td>
    </tr>
    <tr>
        <td>Empresa</td>
        <td><input class="novo_formInput" type="text" name="empresa" value="{{ old('empresa', $registro?->empresa) }}"></td>
    </tr>
    <tr>
        <td>Cliente</td>
        <td><input class="novo_formInput" type="text" name="cliente_nome" value="{{ old('cliente_nome') }}"></td>
    </tr>
    <tr>
        <td>Defeito</td>
        <td><input class="novo_formInput" type="text" name="defeito" value="{{ old('defeito', $registro?->defeito) }}" required></td>
    </tr>
    <tr>
        <td>Observação</td>
        <td><textarea name="observacao">{{ old('observacao', $registro?->observacao) }}</textarea></td>
    </tr>
</table>
