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
            <div class="painel-inicio-fundo-escuro">
                {{-- Achado CP20 (16 do prompt original): "Bem-vindo(a), usuário."
                não existe em `15.8.1/index.php`/`page/inicio.php` — removido.
                Busca simplificada da aba "Início" — fonte real: `15.8.1/index.php`
                (campo único "Pesquisar:"/"Enviar pesquisa", sem o seletor de tipo da aba
                "Pesquisar"). MESMA rota/Controller da aba #pesquisar — só um subconjunto
                da UI, nenhuma regra de negócio nova. --}}
                <form method="GET" action="{{ rota_tema('rmas.index') }}" class="form-inline">
                    <input type="hidden" name="tipo" value="texto">
                    <label for="inicio-pesquisar" class="control-label" style="margin-right:10px;">Pesquisar:</label>
                    <div class="form-group">
                        <input id="inicio-pesquisar" type="text" name="valor" class="form-control" placeholder="Search" value="{{ $valor }}">
                    </div>
                    <button type="submit" class="btn formSubmit">Enviar pesquisa</button>
                </form>

                <img src="{{ asset('images/rma/separador2.png') }}" alt="Separador" class="separador-alerta">

                @include('rma._centro_de_avisos', ['grupos' => $grupos])
            </div>
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
