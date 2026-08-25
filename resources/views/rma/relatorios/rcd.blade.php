<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Relatório de Créditos Disponíveis — CellSystem RMA</title>
</head>
<body>
    <h1>Relatório de Créditos Disponíveis (RCD)</h1>

    {{-- View mínima, sem fidelidade visual (Fase 8). Sem PDF real (EVO-REL-001) —
    impressão via Ctrl+P, igual ao legado. --}}
    @if ($registros->isEmpty())
        <p>Nenhum RMA com crédito disponível.</p>
    @else
        <table border="1">
            <thead>
                <tr><th>#</th><th>Descrição</th><th>Solução</th></tr>
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
</body>
</html>
