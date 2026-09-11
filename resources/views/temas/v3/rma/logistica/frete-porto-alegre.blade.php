@extends('temas.v3.layout')

@section('conteudo')
    <div class="pagina pagina--dados">
        <div class="pagina__cabecalho">
            <div class="pagina__cabecalho-conteudo">
                <h1 class="pagina__titulo">Transporte para Porto Alegre</h1>
                <p class="pagina__resumo">
                    Consolidação de frete e transporte logístico de Porto Alegre.
                </p>
            </div>
        </div>

        @include('rma.logistica._conteudo_frete')
    </div>
@endsection
