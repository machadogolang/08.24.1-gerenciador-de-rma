@extends('temas.v2.layout')

@section('conteudo')
    @include('temas.v2.identidade._menu_controle', ['subpAtual' => 'logs_de_autenticacao'])
    @include('temas.v2.identidade.historico-de-acesso._conteudo_legado')
@endsection
