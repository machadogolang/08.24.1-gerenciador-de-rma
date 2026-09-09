@extends('temas.v3.layout')

@section('conteudo')
    <div class="pagina">
        <div class="pagina__cabecalho">
            <h1 class="pagina__titulo">Dashboard</h1>
            <p class="pagina__resumo">O que voce vai atender agora?</p>
        </div>

        <form class="barra-busca" method="GET" action="{{ route('v3.rmas.index') }}" role="search">
            <label for="busca-rapida" class="sr-only">Buscar RMA</label>
            <input id="busca-rapida" class="barra-busca__campo" type="search" name="q"
                placeholder="Numero, serial, descricao, cliente..." autocomplete="off">
            <button type="submit" class="botao">Buscar</button>
        </form>

        <section aria-labelledby="filas-titulo">
            <h2 id="filas-titulo" class="cartao__titulo">Filas operacionais</h2>
            <div class="grade-filas">
                @foreach ($filas as $fila)
                    <a class="cartao cartao--fila" href="{{ route('v3.rmas.index', ['fila' => $fila['slug']]) }}">
                        <span class="cartao__titulo">{{ $fila['rotulo'] }}</span>
                        <span class="cartao__valor">{{ $fila['contagem'] }}</span>
                    </a>
                @endforeach
            </div>
        </section>

        <section class="painel-alertas" aria-labelledby="alertas-titulo">
            <div class="cartao">
                <h2 id="alertas-titulo" class="cartao__titulo">Alertas</h2>
                @if (count($alertas) === 0)
                    <p class="painel-alertas__vazio">Nenhum alerta ativo.</p>
                @else
                    <ul class="painel-alertas__lista">
                        @foreach ($alertas as $alerta)
                            <li class="painel-alertas__item">
                                <span>{{ $alerta['titulo'] }}</span>
                                <strong>{{ $alerta['contagem'] }}</strong>
                            </li>
                        @endforeach
                    </ul>
                    <p style="margin:16px 0 0;">
                        <a class="botao" href="{{ route('rmas.alertas') }}">Ver painel de alertas</a>
                    </p>
                @endif
            </div>
        </section>
    </div>
@endsection
