@extends('temas.v3.layout')

@section('conteudo')
    <div class="pagina pagina--dados">
        <div class="pagina__cabecalho">
            <div class="pagina__cabecalho-conteudo">
                <h1 class="pagina__titulo">Painel de Alertas</h1>
                <p class="pagina__resumo">
                    Acompanhamento prioritário de RMAs com inconformidades, prazos estourados e sem garantia.
                </p>
            </div>
        </div>

        @include('rma.alertas._conteudo_painel')
    </div>
@endsection
