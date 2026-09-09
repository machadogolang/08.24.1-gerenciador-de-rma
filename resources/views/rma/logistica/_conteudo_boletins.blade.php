{{-- FRONT-003/UI-04 - conteúdo de boletins relacionados compartilhado. --}}
<div class="logistica-tabela">
    <h2 class="logistica-tabela-titulo">Boletins relacionados - RMA #{{ $registro->id }}</h2>

    <p><a href="{{ route('rmas.show', $registro->id) }}" class="acao acao--secundaria">Voltar ao RMA #{{ $registro->id }}</a></p>

    <table class="Tabelinha-Table">
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
                    <td>
                        <a href="{{ route('rmas.show', $relacionado->id) }}" class="acao acao--secundaria acao--compacta">
                            #{{ $relacionado->id }}
                        </a>
                    </td>
                    <td>{{ $relacionado->descricao }}</td>
                    <td>{{ $relacionado->status->name }}</td>
                </tr>
            @empty
                <tr><td colspan="3">Nenhum boletim relacionado.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $relacionados->links() }}
</div>
