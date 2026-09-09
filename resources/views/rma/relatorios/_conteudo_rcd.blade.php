{{-- FRONT-003/UI-03 — conteúdo do RCD compartilhado pelos dois temas. Sem shell/HTML
próprio: cada tema fornece o wrapper e a impressão limpa via `.relatorio-print`. --}}
<div class="relatorio">
    <h2 class="relatorio-titulo">Relatório de Créditos Disponíveis (RCD)</h2>

    @if ($registros->isEmpty())
        <p class="nenhumencontrado">Nenhum RMA com crédito disponível.</p>
    @else
        <table class="Tabelinha-Table relatorio-tabela">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Descrição</th>
                    <th>Solução</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($registros as $registro)
                    <tr>
                        <td>{{ $registro->id }}</td>
                        <td>{{ $registro->descricao }}</td>
                        <td>{{ $registro->solucao?->value }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
