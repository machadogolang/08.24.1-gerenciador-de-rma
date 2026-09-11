@extends('errors.layout')

@section('codigo', '500')
@section('titulo', 'Instabilidade Temporária')
@section('mensagem')
    Ocorreu uma falha no processamento da solicitação pelo servidor. Por favor, tente novamente em instantes.
@endsection
@section('acao_secundaria')
    <button type="button" class="botao botao--secundario" onclick="window.location.reload()">Recarregar Página</button>
@endsection
