@extends('temas.v1.layout')

@section('conteudo')
    @if (session('status'))
        <p class="centrodeavisos">{{ session('status') }}</p>
    @endif

    <p><a href="{{ rota_tema('rmas.edit', ['rma' => $registro->id]) }}">Editar</a></p>

    @include('rma._acoes_de_transicao')

    <table class="Tabelinha-Table">
        <tr><td>Status</td><td>{{ $registro->status->name }}</td></tr>
        <tr><td>Descrição</td><td>{{ $registro->descricao }}</td></tr>
        <tr><td>Fabricante</td><td>{{ $fabricante?->nome }}</td></tr>
        <tr><td>Fornecedor</td><td>{{ $fornecedor?->nome }}</td></tr>
        <tr><td>Modelo</td><td>{{ $registro->modelo }}</td></tr>
        <tr><td>SN</td><td>{{ $registro->sn }}</td></tr>
        <tr><td>OS</td><td>{{ $registro->os }}</td></tr>
        <tr><td>Origem</td><td>{{ $registro->origem }}</td></tr>
        <tr><td>Empresa</td><td>{{ $registro->empresa }}</td></tr>
        <tr><td>Cliente</td><td>{{ $cliente?->nome }}</td></tr>
        <tr><td>Defeito</td><td>{{ $registro->defeito }}</td></tr>
        <tr><td>Observação</td><td>{{ $registro->observacao }}</td></tr>
        <tr><td>Recebido em</td><td>{{ $registro->recebidoEm }}</td></tr>
        <tr><td>Encaminhado em</td><td>{{ $registro->encaminhadoEm }}</td></tr>
        <tr><td>Concluído em</td><td>{{ $registro->concluidoEm }}</td></tr>
        <tr><td>Arquivado em</td><td>{{ $registro->arquivadoEm }}</td></tr>
        <tr><td>Protocolo</td><td>{{ $registro->protocolo }}</td></tr>
        <tr><td>Solução</td><td>{{ $registro->solucao?->value }}</td></tr>
        <tr><td>SN de retorno</td><td>{{ $registro->snretorno }}</td></tr>
    </table>
@endsection
