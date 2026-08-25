<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Relatório de Produtos em Estoque para Contagem — CellSystem RMA</title>
</head>
<body>
    <h1>Relatório de Produtos em Estoque para Contagem (RPEC)</h1>

    {{-- View mínima, sem fidelidade visual (Fase 8). Status é filtro configurável pelo
    usuário (não hardcoded como no legado). --}}
    <form method="GET" action="{{ route('rmas.relatorios.rpec') }}">
        <label>Status
            <select name="status">
                <option value="">Todos</option>
                @foreach (\App\Rma\Dominio\Status::cases() as $caso)
                    <option value="{{ $caso->name }}" @selected($status === $caso)>{{ $caso->name }}</option>
                @endforeach
            </select>
        </label>
        <button type="submit">Filtrar</button>
    </form>

    @if ($registros->isEmpty())
        <p>Nenhum RMA marcado para contagem de estoque.</p>
    @else
        <table border="1">
            <thead>
                <tr><th>#</th><th>Descrição</th><th>Status</th></tr>
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
</body>
</html>
