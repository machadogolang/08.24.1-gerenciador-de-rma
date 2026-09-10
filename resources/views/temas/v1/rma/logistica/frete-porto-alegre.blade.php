@extends('temas.v1.layout')

{{-- UF-16 (GAP-V1-11 / CAP-LOG-001) - Transporte Porto Alegre na linguagem visual do 14.6.1. --}}
@section('omitirTituloPadrao')
@endsection

@section('conteudo')
<div>
    <p class="title-icone fl" style="margin-left:0px;margin-top:8px;">
        <img src="{{ asset('images/rma/retornou.png') }}" alt="" width="50" height="50">
    </p>
    <h1 class="title-comicone fl" style="font-size:18px;">Transporte para Porto Alegre</h1>
    <hr class="both">

    @include('rma.logistica._conteudo_frete')
</div>
@endsection
