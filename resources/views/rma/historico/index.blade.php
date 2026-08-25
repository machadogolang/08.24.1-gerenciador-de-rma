<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Histórico de modificações — CellSystem RMA</title>
</head>
<body>
    <h1>Histórico de modificações de RMA</h1>

    {{-- View mínima, sem fidelidade visual (Fase 8). LEG-RMA-044. --}}
    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>RMA</th>
                <th>Usuário</th>
                <th>Ação</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($modificacoes as $modificacao)
                <tr>
                    <td>{{ $modificacao->created_at }}</td>
                    <td>
                        <a href="{{ route('rmas.show', $modificacao->rma_id) }}">
                            #{{ $modificacao->rma_id }}
                        </a>
                    </td>
                    <td>{{ $modificacao->user?->name ?? '—' }}</td>
                    <td>{{ $modificacao->acao->name }}</td>
                    <td>{{ $modificacao->ip ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Nenhuma modificação registrada.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $modificacoes->links() }}
</body>
</html>
