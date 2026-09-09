{{-- FRONT-003/UI-04 - conteúdo do Painel de Alertas compartilhado pelos temas. --}}
<div class="painel-alertas">
    <h2 class="painel-alertas-titulo">Painel de alertas</h2>

    @foreach ($grupos as $titulo => $rmas)
        <section class="painel-alertas-grupo">
            <h3>{{ $titulo }} ({{ $rmas->count() }})</h3>
            @if ($rmas->isEmpty())
                <p class="nenhumencontrado">Nenhum RMA.</p>
            @else
                <ul>
                    @foreach ($rmas as $registro)
                        <li>
                            <a href="{{ route('rmas.show', $registro->id) }}" class="acao acao--secundaria acao--compacta">
                                #{{ $registro->id }} - {{ $registro->descricao }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    @endforeach
</div>
