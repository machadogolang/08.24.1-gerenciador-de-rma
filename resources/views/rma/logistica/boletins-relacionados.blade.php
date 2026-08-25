<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Boletins relacionados — CellSystem RMA</title>
</head>
<body>
    <h1>Boletins relacionados — RMA #{{ $registro->id }}</h1>

    {{-- View mínima, sem fidelidade visual (Fase 8). LEG-RMA-041, paginado. --}}
    <p><a href="{{ route('rmas.show', $registro->id) }}">Voltar ao RMA #{{ $registro->id }}</a></p>

    <table>
        <thead>
            <tr>
                <th>RMA</th>
                <th>Descrição</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($relacionados as $relacionado)
                <tr>
                    <td><a href="{{ route('rmas.show', $relacionado->id) }}">#{{ $relacionado->id }}</a></td>
                    <td>{{ $relacionado->descricao }}</td>
                    <td>{{ $relacionado->status->name }}</td>
                </tr>
            @empty
                <tr><td colspan="3">Nenhum boletim relacionado.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $relacionados->links() }}
</body>
</html>
