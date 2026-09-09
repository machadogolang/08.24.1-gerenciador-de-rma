{{-- FRONT-003/UI-04 — conteúdo do frete Porto Alegre compartilhado. --}}
<div class="logistica-tabela">
    <h2 class="logistica-tabela-titulo">Frete consolidado — Porto Alegre</h2>

    <table class="Tabelinha-Table">
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
                    <td>
                        <a href="{{ route('rmas.show', $registro->id) }}" class="acao acao--secundaria acao--compacta">
                            #{{ $registro->id }}
                        </a>
                    </td>
                    <td>{{ $registro->descricao }}</td>
                    <td>{{ $registro->status->name }}</td>
                </tr>
            @empty
                <tr><td colspan="3">Nenhum RMA com frete de Porto Alegre pendente.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
