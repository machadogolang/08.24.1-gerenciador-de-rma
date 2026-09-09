{{-- FRONT-003/UI-03 — conteúdo do RMPE compartilhado pelos dois temas. Filtro usa o
contrato de ações; impressão limpa via `.relatorio-print`. --}}
<div class="relatorio">
    <h2 class="relatorio-titulo">Relatório de Produtos Encaminhados (RMPE)</h2>

    <form method="GET" action="{{ route('rmas.relatorios.rmpe') }}" class="relatorio-filtro">
        <label class="acao-label">Data início
            <input type="date" name="data_inicio" class="form-control" value="{{ $dataInicio ?? '' }}" required>
        </label>
        <label class="acao-label">Data fim
            <input type="date" name="data_fim" class="form-control" value="{{ $dataFim ?? '' }}" required>
        </label>
        <button type="submit" class="acao acao--secundaria">Filtrar</button>
    </form>

    @if ($registros->isEmpty())
        <p class="nenhumencontrado">Nenhum RMA encaminhado no período.</p>
    @else
        <table class="Tabelinha-Table relatorio-tabela">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Descrição</th>
                    <th>Encaminhado em</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($registros as $registro)
                    <tr>
                        <td>{{ $registro->id }}</td>
                        <td>{{ $registro->descricao }}</td>
                        <td>{{ $registro->encaminhado_em }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
