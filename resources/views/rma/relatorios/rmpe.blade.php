<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Relatório de Produtos Encaminhados — CellSystem RMA</title>
</head>
<body>
    <h1>Relatório de Produtos Encaminhados (RMPE)</h1>

    {{-- View mínima, sem fidelidade visual (Fase 8). Intervalo de datas real
    (data_inicio/data_fim obrigatórios) — corrige o intervalo hardcoded para 2014 do
    legado (bug de manutenção, não RN documentada). --}}
    <form method="GET" action="{{ route('rmas.relatorios.rmpe') }}">
        <label>Data início
            <input type="date" name="data_inicio" value="{{ $dataInicio ?? '' }}" required>
        </label>
        <label>Data fim
            <input type="date" name="data_fim" value="{{ $dataFim ?? '' }}" required>
        </label>
        <button type="submit">Filtrar</button>
    </form>

    @if ($registros->isEmpty())
        <p>Nenhum RMA encaminhado no período.</p>
    @else
        <table border="1">
            <thead>
                <tr><th>#</th><th>Descrição</th><th>Encaminhado em</th></tr>
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
</body>
</html>
