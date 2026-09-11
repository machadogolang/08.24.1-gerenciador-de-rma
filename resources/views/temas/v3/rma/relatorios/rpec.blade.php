@extends('temas.v3.layout')

@section('conteudo')
    <div class="pagina pagina--dados">
        <div class="pagina__cabecalho">
            <div class="pagina__cabecalho-conteudo">
                <h1 class="pagina__titulo">{{ $titulo }}</h1>
                <p class="pagina__resumo">
                    {{ $registros->count() }} {{ $registros->count() === 1 ? 'item para contagem' : 'itens para contagem' }}
                </p>
            </div>
            <button type="button" class="botao botao--secundario pagina__cabecalho-acao" onclick="window.print()">
                Imprimir relatório
            </button>
        </div>

        @include('temas.v3.rma.relatorios._menu_relatorios', ['relatorioAtual' => 'rpec'])

        @if (session('status'))
            <div class="cartao" style="border-left: 4px solid #177245; background-color: #f4fbf7; margin-bottom: 20px; padding: 12px 16px; color: #177245;">
                {{ session('status') }}
            </div>
        @endif

        <form method="GET" action="{{ rota_tema('rmas.relatorios.rpec') }}" class="barra-busca" style="display: flex; gap: 8px; align-items: center; margin-bottom: 20px;">
            <label style="font-size: 0.875rem; font-weight: 600; color: #5b6b7b;">Filtrar por Status:</label>
            <select name="status" class="campo__controle" style="max-width: 200px;">
                <option value="">Todos</option>
                @foreach (\App\Rma\Dominio\Status::cases() as $caso)
                    <option value="{{ $caso->name }}" @selected(($status?->name ?? '') === $caso->name)>{{ $caso->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="botao botao--secundario">Filtrar</button>
            @if ($status !== null)
                <a href="{{ rota_tema('rmas.relatorios.rpec') }}" class="botao botao--secundario">Limpar</a>
            @endif
        </form>

        @if ($registros->isEmpty())
            <div class="estado-vazio">
                <p>Nenhum RMA marcado para contagem de estoque encontrado.</p>
            </div>
        @else
            <div class="tabela-wrapper">
                <table class="tabela-v3">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Descrição</th>
                            <th scope="col">Status</th>
                            <th scope="col">Data</th>
                            <th scope="col" style="text-align: right;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($registros as $registro)
                            <tr data-linha-parceiro>
                                <td>
                                    <a href="{{ route('v3.rmas.show', ['rma' => $registro->id]) }}">
                                        <strong>{{ $registro->numero_da_empresa ?? $registro->id }}</strong>
                                    </a>
                                </td>
                                <td>{{ $registro->descricao }}</td>
                                <td>
                                    <span class="status-badge status-badge--{{ strtolower($registro->status->name) }}">
                                        {{ $registro->status->name }}
                                    </span>
                                </td>
                                <td>{{ $registro->created_at?->format('d/m/Y') }}</td>
                                <td style="text-align: right;">
                                    <a href="{{ route('v3.rmas.show', ['rma' => $registro->id]) }}"
                                        class="botao botao--secundario botao--compacto">
                                        Ver RMA
                                    </a>
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
                            <a href="{{ route('v3.rmas.show', ['rma' => $registro->id]) }}">
                                <strong>{{ $registro->numero_da_empresa ?? $registro->id }}</strong>
                            </a>
                            <span class="status-badge status-badge--{{ strtolower($registro->status->name) }}">
                                {{ $registro->status->name }}
                            </span>
                        </div>
                        <p>{{ $registro->descricao }}</p>
                        <p style="font-size: 0.875rem; color: #5b6b7b;">{{ $registro->created_at?->format('d/m/Y') }}</p>
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

        {{-- Informações adicionais do relatório --}}
        @if (isset($relatorio['informacaoAdicional']))
            <section class="cartao" style="margin-top: 24px;">
                <h2 style="font-size: 1rem; margin: 0 0 12px;">Informação Adicional do Relatório</h2>
                <form method="POST" action="{{ rota_tema('rmas.relatorios.informacao-adicional.update', ['codigo' => 'RPEC']) }}">
                    @csrf
                    @method('PUT')
                    <textarea name="informacao_adicional" rows="3" class="campo__controle" style="width: 100%; margin-bottom: 12px;"
                        placeholder="Observações complementares para este relatório...">{{ $relatorio['informacao_adicional'] ?? '' }}</textarea>
                    <button type="submit" class="botao botao--secundario botao--compacto">
                        Salvar informação adicional
                    </button>
                </form>
            </section>
        @endif
    </div>
@endsection
