@extends('temas.v1.layout')

@section('omitirTituloPadrao')
@endsection

@section('conteudo')
    @include('rma.logistica._conteudo_frete')
@endsection
