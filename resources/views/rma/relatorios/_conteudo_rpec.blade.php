{{-- FRONT-003/UI-03 - conteúdo do RPEC compartilhado pelos dois temas. Filtro usa o
contrato de ações (`.acao--secundaria`); impressão limpa via `.relatorio-print`. --}}
<div class="relatorio">
    <h2 class="relatorio-titulo">Relatório de Produtos em Estoque para Contagem (RPEC)</h2>

    <form method="GET" action="{{ route('rmas.relatorios.rpec') }}" class="relatorio-filtro">
        <label class="acao-label">Status
            <select name="status" class="formSelect">
                <option value="">Todos</option>
                @foreach (\App\Rma\Dominio\Status::cases() as $caso)
                    <option value="{{ $caso->name }}" @selected($status === $caso)>{{ $caso->name }}</option>
                @endforeach
            </select>
        </label>
        <button type="submit" class="acao acao--secundaria">Filtrar</button>
    </form>

    @if ($registros->isEmpty())
        <p class="nenhumencontrado">Nenhum RMA marcado para contagem de estoque.</p>
    @else
        <table class="Tabelinha-Table relatorio-tabela">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Descrição</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($registros as $registro)
                    <tr>
                        <td>{{ $registro->id }}</td>
                        <td>{{ $registro->descricao }}</td>
                        <td>{{ $registro->status->name }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
