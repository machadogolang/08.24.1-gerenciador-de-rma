@extends('temas.v3.layout')

@php
    $abas = [
        ['rotulo' => 'Clientes', 'tipo' => 'clientes', 'rota' => 'parceiros.clientes.index'],
        ['rotulo' => 'Fornecedores', 'tipo' => 'fornecedores', 'rota' => 'parceiros.fornecedores.index'],
        ['rotulo' => 'Fabricantes', 'tipo' => 'fabricantes', 'rota' => 'parceiros.fabricantes.index'],
        ['rotulo' => 'Assistências técnicas', 'tipo' => 'assistencias-tecnicas', 'rota' => 'parceiros.assistencias-tecnicas.index'],
    ];

    $nomeSingular = match ($tipo) {
        'clientes' => 'cliente',
        'fornecedores' => 'fornecedor',
        'fabricantes' => 'fabricante',
        'assistencias-tecnicas' => 'assistência técnica',
        default => 'parceiro',
    };
@endphp

@section('conteudo')
    <div class="pagina pagina--dados">
        <div class="pagina__cabecalho">
            <div class="pagina__cabecalho-conteudo">
                <h1 class="pagina__titulo">Parceiros - {{ $titulo }}</h1>
                <p class="pagina__resumo">
                    {{ $registros->count() }} {{ $registros->count() === 1 ? 'registro cadastrado' : 'registros cadastrados' }}
                </p>
            </div>
            @can('create', $modelo)
                <a class="botao pagina__cabecalho-acao" href="{{ rota_tema('parceiros.' . $tipo . '.create') }}">
                    Novo {{ $nomeSingular }}
                </a>
            @endcan
        </div>

        <nav class="segmentos" aria-label="Categorias de parceiros">
            @foreach ($abas as $aba)
                <a class="segmento"
                    href="{{ rota_tema($aba['rota']) }}"
                    @if ($aba['tipo'] === $tipo) aria-current="true" @endif>
                    {{ $aba['rotulo'] }}
                </a>
            @endforeach
        </nav>

        <div class="barra-busca">
            <input class="barra-busca__campo" type="search" id="filtro-parceiros"
                placeholder="Filtrar por nome, representante, contato ou cidade..."
                aria-label="Filtrar parceiros" data-v3-filtro-tabela>
        </div>

        @if ($registros->isEmpty())
            <div class="estado-vazio">
                <p>Nenhum {{ $nomeSingular }} cadastrado nesta categoria.</p>
                @can('create', $modelo)
                    <p>
                        <a href="{{ rota_tema('parceiros.' . $tipo . '.create') }}" class="botao botao--secundario">
                            Cadastrar primeiro {{ $nomeSingular }}
                        </a>
                    </p>
                @endcan
            </div>
        @else
            <div class="tabela-wrapper">
                <table class="tabela-v3" data-v3-tabela-parceiros>
                    <thead>
                        <tr>
                            <th scope="col">Nome</th>
                            <th scope="col">Representante</th>
                            <th scope="col">Contato</th>
                            <th scope="col">Cidade / UF</th>
                            <th scope="col" style="text-align: right;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($registros as $registro)
                            <tr data-linha-parceiro>
                                <td>
                                    <a href="{{ rota_tema('parceiros.' . $tipo . '.show', $registro) }}">
                                        <strong>{{ $registro->nome }}</strong>
                                    </a>
                                </td>
                                <td>{{ $registro->representante ?: '-' }}</td>
                                <td>
                                    @if (! empty($registro->telefone))
                                        <div>{{ $registro->telefone }}</div>
                                    @endif
                                    @if (! empty($registro->email))
                                        <div style="font-size: 0.875rem; color: #5b6b7b;">{{ $registro->email }}</div>
                                    @endif
                                    @if (empty($registro->telefone) && empty($registro->email))
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if (! empty($registro->cidade))
                                        {{ $registro->cidade }}{{ $registro->uf ? '/' . $registro->uf->value : '' }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td style="text-align: right;">
                                    <div class="tabela-v3__acoes" style="justify-content: flex-end;">
                                        <a href="{{ rota_tema('parceiros.' . $tipo . '.show', $registro) }}"
                                            class="botao botao--secundario botao--compacto">
                                            Ver
                                        </a>
                                        @can('update', $registro)
                                            <a href="{{ rota_tema('parceiros.' . $tipo . '.edit', $registro) }}"
                                                class="botao botao--secundario botao--compacto">
                                                Editar
                                            </a>
                                        @endcan
                                        @can('delete', $registro)
                                            <form method="POST" action="{{ rota_tema('parceiros.' . $tipo . '.destroy', $registro) }}"
                                                style="display: inline;" data-confirmar-remocao="Remover este registro?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="botao botao--perigo botao--compacto">
                                                    Remover
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="cartoes-parceiro">
                @foreach ($registros as $registro)
                    <article class="cartao-parceiro" data-linha-parceiro>
                        <div class="cartao-parceiro__cabecalho">
                            <a href="{{ rota_tema('parceiros.' . $tipo . '.show', $registro) }}">
                                <strong>{{ $registro->nome }}</strong>
                            </a>
                            @if (! empty($registro->cidade))
                                <span style="font-size: 0.875rem; color: #5b6b7b;">
                                    {{ $registro->cidade }}{{ $registro->uf ? '/' . $registro->uf->value : '' }}
                                </span>
                            @endif
                        </div>
                        @if (! empty($registro->representante))
                            <p><strong>Representante:</strong> {{ $registro->representante }}</p>
                        @endif
                        @if (! empty($registro->telefone) || ! empty($registro->email))
                            <p>
                                {{ $registro->telefone }}
                                @if (! empty($registro->telefone) && ! empty($registro->email)) - @endif
                                {{ $registro->email }}
                            </p>
                        @endif
                        <div class="cartao-parceiro__acoes">
                            <a href="{{ rota_tema('parceiros.' . $tipo . '.show', $registro) }}"
                                class="botao botao--secundario botao--compacto">
                                Ver
                            </a>
                            @can('update', $registro)
                                <a href="{{ rota_tema('parceiros.' . $tipo . '.edit', $registro) }}"
                                    class="botao botao--secundario botao--compacto">
                                    Editar
                                </a>
                            @endcan
                            @can('delete', $registro)
                                <form method="POST" action="{{ rota_tema('parceiros.' . $tipo . '.destroy', $registro) }}"
                                    style="display: inline;" data-confirmar-remocao="Remover este registro?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="botao botao--perigo botao--compacto">
                                        Remover
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
@endsection
