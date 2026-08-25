<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>{{ $titulo }} — CellSystem RMA</title>
</head>
<body>
    <h1>{{ $titulo }}</h1>

    @if (session('status'))
        <p>{{ session('status') }}</p>
    @endif

    <p><a href="{{ route('rmas.edit', $registro->id) }}">Editar</a></p>

    <dl>
        <dt>Descrição</dt><dd>{{ $registro->descricao }}</dd>
        <dt>Fabricante</dt><dd>{{ $fabricante?->nome }}</dd>
        <dt>Fornecedor</dt><dd>{{ $fornecedor?->nome }}</dd>
        <dt>Modelo</dt><dd>{{ $registro->modelo }}</dd>
        <dt>SN</dt><dd>{{ $registro->sn }}</dd>
        <dt>OS</dt><dd>{{ $registro->os }}</dd>
        <dt>Origem</dt><dd>{{ $registro->origem }}</dd>
        <dt>Empresa</dt><dd>{{ $registro->empresa }}</dd>
        <dt>Cliente</dt><dd>{{ $cliente?->nome }}</dd>
        <dt>Defeito</dt><dd>{{ $registro->defeito }}</dd>
        <dt>Observação</dt><dd>{{ $registro->observacao }}</dd>
    </dl>
</body>
</html>
