@extends('temas.v2.layout')

@section('conteudo')
    @include('temas.v2.rma.relatorios._menu_relatorios', ['relatorioAtual' => 'rcd'])
    @include('rma.relatorios._conteudo_rcd')
@endsection
