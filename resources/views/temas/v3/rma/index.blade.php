@extends('temas.v3.layout')

@php
    $filtros = [
        ['slug' => 'todos', 'rotulo' => 'Todos'],
        ['slug' => 'entrada', 'rotulo' => 'Entrada'],
        ['slug' => 'recebido', 'rotulo' => 'Recebidos'],
        ['slug' => 'encaminhado', 'rotulo' => 'Encaminhados'],
        ['slug' => 'aguardando-credito', 'rotulo' => 'Aguardando credito'],
        ['slug' => 'concluido', 'rotulo' => 'Concluidos'],
        ['slug' => 'arquivado', 'rotulo' => 'Arquivados'],
    ];
    $parceiro = static function ($registro) {
        if ($registro->fabricante) return $registro->fabricante->nome;
        if ($registro->fornecedor) return $registro->fornecedor->nome;
        if ($registro->cliente) return $registro->cliente->nome;
        return $registro->origem;
    };
    $statusClasse = static function ($registro) {
        return 'status-badge status-badge--' . strtolower($registro->status->name);
    };
@endphp

@section('conteudo')
    <div class="pagina pagina--dados">
        <div class="pagina__cabecalho">
            <div class="pagina__cabecalho-conteudo">
                <h1 class="pagina__titulo">RMAs</h1>
                <p class="pagina__resumo">
                @if ($temBusca)
                    Resultados para "{{ $q }}"
                @elseif ($filaAtual === 'aguardando-credito')
                    Aguardando credito
                @elseif ($filaAtual !== 'todos')
                    Fila: {{ ucfirst($filaAtual) }}
                @else
                    Todas as filas
                @endif
                </p>
            </div>
            @can('create', \App\Models\Rma::class)
                <a class="botao pagina__cabecalho-acao" href="{{ route('v3.rmas.create') }}">Novo RMA</a>
            @endcan
        </div>

        <form class="barra-busca" method="GET" action="{{ route('v3.rmas.index') }}" role="search">
            <input class="barra-busca__campo" type="search" name="q" value="{{ $q }}"
                placeholder="Buscar RMA..." aria-label="Buscar RMA">
            <button type="submit" class="botao">Buscar</button>
        </form>

        <nav class="segmentos" aria-label="Filtros por fila">
            @foreach ($filtros as $filtro)
                <a class="segmento"
                    href="{{ route('v3.rmas.index', array_filter(['fila' => $filtro['slug'] !== 'todos' ? $filtro['slug'] : null])) }}"
                    @if ($filtro['slug'] === $filaAtual && !$temBusca) aria-current="true" @endif>
                    {{ $filtro['rotulo'] }}
                </a>
            @endforeach
        </nav>

        @if ($registros->isEmpty())
            <div class="estado-vazio">
                <p>Nenhum RMA encontrado.</p>
                <p><a href="{{ route('v3.rmas.index') }}">Limpar filtros</a></p>
            </div>
        @else
            <div class="tabela-wrapper">
            <table class="tabela-v3">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Status</th>
                        <th scope="col">Descricao</th>
                        <th scope="col">Parceiro / Origem</th>
                        <th scope="col">Modelo</th>
                        <th scope="col">Data</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($registros as $registro)
                        <tr>
                            <td><a href="{{ route('v3.rmas.show', ['rma' => $registro->id]) }}">{{ $registro->numero_da_empresa ?? $registro->id }}</a></td>
                            <td><span class="{{ $statusClasse($registro) }}">{{ $registro->status->name }}</span></td>
                            <td>{{ $registro->descricao }}</td>
                            <td>{{ $parceiro($registro) }}</td>
                            <td>{{ $registro->modelo }}</td>
                            <td>{{ $registro->created_at?->format('d/m/Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>

            <div class="cartoes-rma">
                @foreach ($registros as $registro)
                    <article class="cartao-rma">
                        <div class="cartao-rma__cabecalho">
                            <a href="{{ route('v3.rmas.show', ['rma' => $registro->id]) }}"><strong>{{ $registro->numero_da_empresa ?? $registro->id }}</strong></a>
                            <span class="{{ $statusClasse($registro) }}">{{ $registro->status->name }}</span>
                        </div>
                        <p>{{ $registro->descricao }}</p>
                        <p>{{ $parceiro($registro) }} - {{ $registro->modelo }}</p>
                        <p>{{ $registro->created_at?->format('d/m/Y') }}</p>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
@endsection
