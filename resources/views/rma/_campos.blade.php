<label>Descrição
    <input type="text" name="descricao" value="{{ old('descricao', $registro?->descricao) }}" required>
</label>

<label>Fabricante
    <select name="fabricante_id">
        <option value="">—</option>
        @foreach ($fabricantes as $fabricante)
            <option value="{{ $fabricante->id }}" @selected(old('fabricante_id', $registro?->fabricanteId) == $fabricante->id)>
                {{ $fabricante->nome }}
            </option>
        @endforeach
    </select>
</label>

<label>Fornecedor
    <select name="fornecedor_id">
        <option value="">—</option>
        @foreach ($fornecedores as $fornecedor)
            <option value="{{ $fornecedor->id }}" @selected(old('fornecedor_id', $registro?->fornecedorId) == $fornecedor->id)>
                {{ $fornecedor->nome }}
            </option>
        @endforeach
    </select>
</label>

<label>Modelo
    <input type="text" name="modelo" value="{{ old('modelo', $registro?->modelo) }}">
</label>

<label>SN
    <input type="text" name="sn" value="{{ old('sn', $registro?->sn) }}">
</label>

<label>OS
    <input type="text" name="os" value="{{ old('os', $registro?->os) }}">
</label>

<label>Origem
    <input type="text" name="origem" value="{{ old('origem', $registro?->origem) }}">
</label>

<label>Empresa
    <input type="text" name="empresa" value="{{ old('empresa', $registro?->empresa) }}">
</label>

<label>Cliente
    <input type="text" name="cliente_nome" value="{{ old('cliente_nome') }}">
</label>

<label>Defeito
    <input type="text" name="defeito" value="{{ old('defeito', $registro?->defeito) }}" required>
</label>

<label>Observação
    <textarea name="observacao">{{ old('observacao', $registro?->observacao) }}</textarea>
</label>
