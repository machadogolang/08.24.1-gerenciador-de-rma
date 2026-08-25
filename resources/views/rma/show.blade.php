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

    @include('rma._acoes_de_transicao')

    <dl>
        <dt>Status</dt><dd>{{ $registro->status->name }}</dd>
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
        <dt>Recebido em</dt><dd>{{ $registro->recebidoEm }}</dd>
        <dt>Encaminhado em</dt><dd>{{ $registro->encaminhadoEm }}</dd>
        <dt>Concluído em</dt><dd>{{ $registro->concluidoEm }}</dd>
        <dt>Arquivado em</dt><dd>{{ $registro->arquivadoEm }}</dd>
        <dt>Protocolo</dt><dd>{{ $registro->protocolo }}</dd>
        <dt>Solução</dt><dd>{{ $registro->solucao?->value }}</dd>
        <dt>SN de retorno</dt><dd>{{ $registro->snretorno }}</dd>
    </dl>
</body>
</html>
