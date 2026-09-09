@extends('temas.v1.layout')

@section('omitirTituloPadrao')
@endsection

@section('conteudo')
    @include('rma.relatorios._conteudo_rpec')
@endsection
