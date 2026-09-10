@extends('temas.v1.layout')

@section('conteudo')
    @include('temas.v1.rma.relatorios._conteudo_v1', ['relatorio' => $relatorio])
@endsection
