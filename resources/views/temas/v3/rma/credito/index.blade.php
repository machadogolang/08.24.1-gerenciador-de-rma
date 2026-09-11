@extends('temas.v3.layout')

@section('conteudo')
    <div class="pagina pagina--dados">
        <div class="pagina__cabecalho">
            <div class="pagina__cabecalho-conteudo">
                <h1 class="pagina__titulo">Gestão de Créditos</h1>
                <p class="pagina__resumo">
                    {{ count($creditos) }} {{ count($creditos) === 1 ? 'registro de crédito' : 'registros de créditos' }}
                </p>
            </div>
            <button type="button" class="botao botao--secundario pagina__cabecalho-acao" onclick="window.print()">
                Imprimir
            </button>
        </div>

        @if (session('status'))
            <div class="cartao" style="border-left: 4px solid #177245; background-color: #f4fbf7; margin-bottom: 20px; padding: 12px 16px; color: #177245;">
                {{ session('status') }}
            </div>
        @endif

        <div class="barra-busca">
            <input class="barra-busca__campo" type="search" id="filtro-credito"
                placeholder="Filtrar créditos por fabricante, modelo ou valor..."
                aria-label="Filtrar créditos" data-v3-filtro-tabela>
        </div>

        @if (empty($creditos))
            <div class="estado-vazio">
                <p>Nenhum produto com crédito pendente no momento.</p>
            </div>
        @else
            <div class="tabela-wrapper">
                <table class="tabela-v3" data-tabela-skinless="true">
                    <thead>
                        <tr>
                            <th scope="col">Data</th>
                            <th scope="col"># RMA</th>
                            <th scope="col">Fabricante</th>
                            <th scope="col">Descrição / Modelo</th>
                            <th scope="col">Destinatário</th>
                            <th scope="col">Valor</th>
                            <th scope="col" style="text-align: right;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($creditos as $registro)
                            <tr data-linha-parceiro>
                                <td>{{ $registro->encaminhadoEm?->format('d/m/Y') ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('v3.rmas.show', ['rma' => $registro->id]) }}">
                                        <strong>{{ $registro->numero_legado ?? $registro->id }}</strong>
                                    </a>
                                </td>
                                <td>{{ $fabricantes[$registro->fabricanteId] ?? '-' }}</td>
                                <td>
                                    <div>{{ $registro->descricao }}</div>
                                    <div style="font-size: 0.8125rem; color: #5b6b7b;">{{ $registro->modelo }}</div>
                                </td>
                                <td>{{ $destinatarios[($registro->destinatarioType ?? '').':'.($registro->destinatarioId ?? '')] ?? '-' }}</td>
                                <td>
                                    @if ((float) $registro->valor > 0)
                                        R$ {{ number_format((float) $registro->valor, 2, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td style="text-align: right;">
                                    <a href="{{ route('v3.rmas.show', ['rma' => $registro->id]) }}"
                                        class="botao botao--secundario botao--compacto">
                                        Ver
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="cartoes-parceiro">
                @foreach ($creditos as $registro)
                    <article class="cartao-parceiro" data-linha-parceiro>
                        <div class="cartao-parceiro__cabecalho">
                            <a href="{{ route('v3.rmas.show', ['rma' => $registro->id]) }}">
                                <strong>#{{ $registro->numero_legado ?? $registro->id }}</strong>
                            </a>
                            @if ((float) $registro->valor > 0)
                                <strong>R$ {{ number_format((float) $registro->valor, 2, ',', '.') }}</strong>
                            @endif
                        </div>
                        <p>{{ $registro->descricao }} - {{ $registro->modelo }}</p>
                        <p style="font-size: 0.8125rem; color: #5b6b7b;">
                            {{ $fabricante[$registro->fabricanteId] ?? '' }} | {{ $registro->encaminhadoEm?->format('d/m/Y') ?? '' }}
                        </p>
                        <div class="cartao-parceiro__acoes">
                            <a href="{{ route('v3.rmas.show', ['rma' => $registro->id]) }}"
                                class="botao botao--secundario botao--compacto">
                                Ver RMA
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
@endsection
