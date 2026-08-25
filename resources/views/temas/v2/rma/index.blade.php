@extends('temas.v2.layout')

@php
    // Achado confirmado no LEGACY-RUNTIME (design.md "Mecanismo de navegação por
    // tema"): os 7 painéis (#inicio, #pesquisar, #novo_rma, #entrada, #recebido,
    // #encaminhado, #concluido) vêm TODOS renderizados no mesmo HTML; a troca é o
    // plugin de abas nativo do Bootstrap 3 (`data-toggle="tab"`), sem AJAX/reload.
    // Fonte de dados: a MESMA busca já resolvida pelo `RmaController@index` (Fase 3) —
    // nenhuma regra de negócio nova. Os painéis por status particionam o resultado já
    // buscado (`$rmas`) por `$registro->status`; quando não há termo de busca (`$rmas`
    // vazio), os painéis mostram o estado "nenhum encontrado", mesmo comportamento do
    // painel #pesquisar.
    $porStatus = fn (\App\Rma\Dominio\Status $status) => array_values(array_filter($rmas, fn ($r) => $r->status === $status));
@endphp

@section('conteudo')
    {{-- CP17 (`docs/produto/plano-execucao-paridade-v2.md`) — a `<ul class="nav
    nav-tabs">` histórica virou parte do header único (`temas/v2/layout.blade.php`),
    fonte real `legacy-source/15.8.1/inc/menu.php`: os 9 itens (Inicio…Logout) são um
    componente do layout inteiro, não desta tela — mesmo tratamento já dado ao TEMA
    V1. --}}
    <div class="tab-content">
        <div id="inicio" class="tab-pane fade in active">
            {{-- `page/inicio.php` = `include("page/pesquisar.php")` por inteiro, depois
            separador + Centro de Avisos — não é uma versão simplificada, é a MESMA
            composição da aba #pesquisar (achado confirmado lendo o PHP fonte
            completo nesta sessão; a suposição anterior de "busca simplificada" nesta
            aba estava errada, corrigida no CP20). --}}
            @include('temas.v2.rma._pesquisar_conteudo')

            <div style="clear:both;"></div>
            <img src="{{ asset('images/tema-v2/separador2.png') }}" alt="Separador" title="Separador" height="40" style="margin-top:50px;float:right;">
            <div style="clear:both;"></div>

            <div class="centrodeavisos">
                <img class="fl" src="{{ asset('images/tema-v2/lembrete.png') }}" alt="Lembrete" title="Lembrete" width="40">
                <h5 class="fl">CENTRO DE AVISOS E RELATORIOS</h5>
                <div style="clear:both;height:10px;"></div>
                <hr class="hrup">
            </div>
            <div style="clear:both;"></div>

            @include('rma._centro_de_avisos', ['grupos' => $grupos])
        </div>

        <div id="pesquisar" class="tab-pane fade">
            @include('temas.v2.rma._pesquisar_conteudo')
        </div>

        <div id="novo_rma" class="tab-pane fade">
            <p><a href="{{ rota_tema('rmas.create') }}" class="btn formSubmit">Abrir novo RMA</a></p>
        </div>

        <div id="entrada" class="tab-pane fade">
            @include('temas.v2.rma._tabela', ['registros' => $porStatus(\App\Rma\Dominio\Status::Entrada)])
        </div>

        <div id="recebido" class="tab-pane fade">
            @include('temas.v2.rma._tabela', ['registros' => $porStatus(\App\Rma\Dominio\Status::Recebido)])
        </div>

        <div id="encaminhado" class="tab-pane fade">
            @include('temas.v2.rma._tabela', ['registros' => $porStatus(\App\Rma\Dominio\Status::Encaminhado)])
        </div>

        <div id="concluido" class="tab-pane fade">
            @include('temas.v2.rma._tabela', ['registros' => $porStatus(\App\Rma\Dominio\Status::Concluido)])
        </div>
    </div>
@endsection
