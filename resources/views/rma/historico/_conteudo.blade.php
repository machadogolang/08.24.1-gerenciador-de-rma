{{-- FRONT-003/UI-04 — conteúdo do histórico de modificações compartilhado. --}}
<div class="historico-tabela">
    <h2 class="historico-tabela-titulo">Histórico de modificações de RMA</h2>

    <table class="Tabelinha-Table">
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
                        <a href="{{ route('rmas.show', $modificacao->rma_id) }}" class="acao acao--secundaria acao--compacta">
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
</div>
