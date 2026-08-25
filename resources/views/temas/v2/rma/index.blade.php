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
    <ul class="nav nav-tabs">
        <li class="active"><a href="#inicio" data-toggle="tab">Início</a></li>
        <li><a href="#pesquisar" data-toggle="tab">Pesquisar</a></li>
        <li><a href="#novo_rma" data-toggle="tab">Novo RMA</a></li>
        <li><a href="#entrada" data-toggle="tab">Entrada</a></li>
        <li><a href="#recebido" data-toggle="tab">Recebido</a></li>
        <li><a href="#encaminhado" data-toggle="tab">Encaminhado</a></li>
        <li><a href="#concluido" data-toggle="tab">Concluído</a></li>
    </ul>

    <div class="tab-content">
        <div id="inicio" class="tab-pane fade in active">
            <p class="centrodeavisos">Bem-vindo(a), {{ auth()->user()?->name }}. Use as abas acima para pesquisar
                ou abrir um novo RMA.</p>
        </div>

        <div id="pesquisar" class="tab-pane fade">
            <form method="GET" action="{{ rota_tema('rmas.index') }}" class="form-inline" style="margin-bottom:10px;">
                <div class="form-group">
                    <select name="tipo" class="form-control formSelect">
                        <option value="texto" @selected($tipo === 'texto')>Texto</option>
                        <option value="serial" @selected($tipo === 'serial')>Serial</option>
                        <option value="nota_fiscal" @selected($tipo === 'nota_fiscal')>Nota fiscal</option>
                    </select>
                </div>
                <div class="form-group">
                    <input type="text" name="valor" class="form-control" value="{{ $valor }}">
                </div>
                <button type="submit" class="btn formSubmit">Buscar</button>
            </form>

            @include('temas.v2.rma._tabela', ['registros' => $rmas])
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
