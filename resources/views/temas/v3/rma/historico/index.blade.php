@extends('temas.v3.layout')

@section('conteudo')
    <div class="pagina pagina--dados">
        <div class="pagina__cabecalho">
            <div class="pagina__cabecalho-conteudo">
                <h1 class="pagina__titulo">Histórico de Modificações de RMA</h1>
                <p class="pagina__resumo">
                    Trilha de auditoria das alterações realizadas nos processos de garantia.
                </p>
            </div>
        </div>

        <div class="barra-busca">
            <input class="barra-busca__campo" type="search" id="filtro-historico"
                placeholder="Filtrar por data, número, fabricante ou modelo..."
                aria-label="Filtrar histórico" data-v3-filtro-tabela>
        </div>

        @if ($modificacoes->isEmpty())
            <div class="estado-vazio">
                <p>Nenhum registro de modificação encontrado.</p>
            </div>
        @else
            <div class="tabela-wrapper">
                <table class="tabela-v3" data-tabela-skinless="true">
                    <thead>
                        <tr>
                            <th scope="col">Data</th>
                            <th scope="col"># RMA</th>
                            <th scope="col">Fabricante</th>
                            <th scope="col">Descrição</th>
                            <th scope="col">Modelo</th>
                            <th scope="col" style="text-align: right;">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($modificacoes as $modificacao)
                            @php
                                $estado = $modificacao->estado_apos ?? [];
                                $fabricanteLog = $estado['fabricante'] ?? $modificacao->rma?->fabricante?->nome ?? '-';
                                $descricaoLog = $estado['descricao'] ?? $modificacao->rma?->descricao ?? '-';
                                $modeloLog = $estado['modelo'] ?? $modificacao->rma?->modelo ?? '-';
                                $numeroLog = $modificacao->rma?->numero_legado ?? $modificacao->rma_id;
                            @endphp
                            <tr data-linha-parceiro>
                                <td>{{ $modificacao->created_at?->format('d/m/Y H:i:s') }}</td>
                                <td>
                                    <a href="{{ route('v3.rmas.show', ['rma' => $modificacao->rma_id]) }}">
                                        <strong>{{ $numeroLog }}</strong>
                                    </a>
                                </td>
                                <td>{{ $fabricanteLog }}</td>
                                <td>{{ $descricaoLog }}</td>
                                <td>{{ $modeloLog }}</td>
                                <td style="text-align: right;">
                                    <a href="{{ route('v3.rmas.show', ['rma' => $modificacao->rma_id]) }}"
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
                @foreach ($modificacoes as $modificacao)
                    @php
                        $estado = $modificacao->estado_apos ?? [];
                        $fabricanteLog = $estado['fabricante'] ?? $modificacao->rma?->fabricante?->nome ?? '-';
                        $descricaoLog = $estado['descricao'] ?? $modificacao->rma?->descricao ?? '-';
                        $modeloLog = $estado['modelo'] ?? $modificacao->rma?->modelo ?? '-';
                        $numeroLog = $modificacao->rma?->numero_legado ?? $modificacao->rma_id;
                    @endphp
                    <article class="cartao-parceiro" data-linha-parceiro>
                        <div class="cartao-parceiro__cabecalho">
                            <a href="{{ route('v3.rmas.show', ['rma' => $modificacao->rma_id]) }}">
                                <strong>#{{ $numeroLog }}</strong>
                            </a>
                            <span style="font-size: 0.8125rem; color: #5b6b7b;">
                                {{ $modificacao->created_at?->format('d/m/Y H:i') }}
                            </span>
                        </div>
                        <p><strong>{{ $descricaoLog }}</strong></p>
                        <p style="font-size: 0.875rem; color: #5b6b7b;">{{ $fabricanteLog }} - {{ $modeloLog }}</p>
                        <div class="cartao-parceiro__acoes">
                            <a href="{{ route('v3.rmas.show', ['rma' => $modificacao->rma_id]) }}"
                                class="botao botao--secundario botao--compacto">
                                Ver RMA
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div style="margin-top: 20px;">
                {{ $modificacoes->links() }}
            </div>
        @endif
    </div>
@endsection
