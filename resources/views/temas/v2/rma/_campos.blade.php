{{-- `.form-group`/`.form-control` do Bootstrap 3, confirmado como dependência real do
TEMA V2 (design.md "Organização Vite/Sass por tema"). --}}
<div class="form-group">
    <label class="col-sm-2 control-label">Descrição</label>
    <div class="col-sm-6">
        <input type="text" name="descricao" class="form-control" value="{{ old('descricao', $registro?->descricao) }}" required>
    </div>
</div>
<div class="form-group">
    <label class="col-sm-2 control-label">Fabricante</label>
    <div class="col-sm-6">
        <select name="fabricante_id" class="form-control formSelect">
            <option value="">—</option>
            @foreach ($fabricantes as $fabricante)
                <option value="{{ $fabricante->id }}" @selected(old('fabricante_id', $registro?->fabricanteId) == $fabricante->id)>
                    {{ $fabricante->nome }}
                </option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-group">
    <label class="col-sm-2 control-label">Fornecedor</label>
    <div class="col-sm-6">
        <select name="fornecedor_id" class="form-control formSelect">
            <option value="">—</option>
            @foreach ($fornecedores as $fornecedor)
                <option value="{{ $fornecedor->id }}" @selected(old('fornecedor_id', $registro?->fornecedorId) == $fornecedor->id)>
                    {{ $fornecedor->nome }}
                </option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-group">
    <label class="col-sm-2 control-label">Modelo</label>
    <div class="col-sm-6"><input type="text" name="modelo" class="form-control" value="{{ old('modelo', $registro?->modelo) }}"></div>
</div>
<div class="form-group">
    <label class="col-sm-2 control-label">SN</label>
    <div class="col-sm-6"><input type="text" name="sn" class="form-control" value="{{ old('sn', $registro?->sn) }}"></div>
</div>
<div class="form-group">
    <label class="col-sm-2 control-label">OS</label>
    <div class="col-sm-6"><input type="text" name="os" class="form-control" value="{{ old('os', $registro?->os) }}"></div>
</div>
<div class="form-group">
    <label class="col-sm-2 control-label">Origem</label>
    <div class="col-sm-6"><input type="text" name="origem" class="form-control" value="{{ old('origem', $registro?->origem) }}"></div>
</div>
<div class="form-group">
    <label class="col-sm-2 control-label">Empresa</label>
    <div class="col-sm-6"><input type="text" name="empresa" class="form-control" value="{{ old('empresa', $registro?->empresa) }}"></div>
</div>
<div class="form-group">
    <label class="col-sm-2 control-label">Cliente</label>
    <div class="col-sm-6"><input type="text" name="cliente_nome" class="form-control" value="{{ old('cliente_nome') }}"></div>
</div>
<div class="form-group">
    <label class="col-sm-2 control-label">Defeito</label>
    <div class="col-sm-6"><input type="text" name="defeito" class="form-control" value="{{ old('defeito', $registro?->defeito) }}" required></div>
</div>
<div class="form-group">
    <label class="col-sm-2 control-label">Observação</label>
    <div class="col-sm-6"><textarea name="observacao" class="form-control">{{ old('observacao', $registro?->observacao) }}</textarea></div>
</div>
