<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Histórico de acesso — CellSystem RMA</title>
</head>
<body>
    <h1>Histórico de acesso</h1>

    {{-- View mínima, sem fidelidade visual (Fase 8). LEG-RMA-043 — o dado já existe
    desde a Fase 1 (`tentativas_de_acesso`), esta fase só adiciona a tela. --}}
    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>E-mail informado</th>
                <th>Usuário</th>
                <th>IP</th>
                <th>Resultado</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($tentativas as $tentativa)
                <tr>
                    <td>{{ $tentativa->created_at }}</td>
                    <td>{{ $tentativa->email_informado }}</td>
                    <td>{{ $tentativa->user?->name ?? '—' }}</td>
                    <td>{{ $tentativa->ip ?? '—' }}</td>
                    <td>{{ $tentativa->resultado->name }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Nenhuma tentativa registrada.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $tentativas->links() }}
</body>
</html>
