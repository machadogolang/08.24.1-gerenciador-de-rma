{{-- FRONT-003/UI-04 - conteúdo do histórico de acesso compartilhado. --}}
<div class="historico-tabela">
    <h2 class="historico-tabela-titulo">Histórico de acesso</h2>

    <table class="Tabelinha-Table">
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
                    <td>{{ $tentativa->user?->name ?? '-' }}</td>
                    <td>{{ $tentativa->ip ?? '-' }}</td>
                    <td>{{ $tentativa->resultado->name }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Nenhuma tentativa registrada.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $tentativas->links() }}
</div>
