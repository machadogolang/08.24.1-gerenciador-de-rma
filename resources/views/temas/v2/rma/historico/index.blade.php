@extends('temas.v2.layout')

@section('conteudo')
    @include('temas.v2.identidade._menu_controle', ['subpAtual' => 'logs_de_modificacao'])
    @include('temas.v2.rma.historico._conteudo_legado')
@endsection
