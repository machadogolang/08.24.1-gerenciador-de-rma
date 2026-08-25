<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Frete Porto Alegre — CellSystem RMA</title>
</head>
<body>
    <h1>Frete consolidado — Porto Alegre</h1>

    {{-- View mínima, sem fidelidade visual (Fase 8). RN-16, LEG-RMA-040. Cidade
    "PORTO ALEGRE" hardcoded (comportamento documentado do legado). --}}
    <table>
        <thead>
            <tr>
                <th>RMA</th>
                <th>Descrição</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rmas as $registro)
                <tr>
                    <td><a href="{{ route('rmas.show', $registro->id) }}">#{{ $registro->id }}</a></td>
                    <td>{{ $registro->descricao }}</td>
                    <td>{{ $registro->status->name }}</td>
                </tr>
            @empty
                <tr><td colspan="3">Nenhum RMA com frete de Porto Alegre pendente.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
