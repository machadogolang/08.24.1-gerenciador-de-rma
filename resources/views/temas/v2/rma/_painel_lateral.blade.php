{{-- CP19 (paridade visual V2) — fonte real `legacy-source/15.8.1/inc/rightmenu.php`:
14 seções colapsáveis, cabeçalho alternando `LRTOP1`/`LRTOP2`, linhas alternando
`LiRight1`/`LiRight2`. Dado já vem pronto de `ListarPainelLateral` (via
`View::composer`, `AppServiceProvider`) — nenhum cálculo/SQL aqui. --}}
@php $topoAlternado = true; @endphp
@foreach ($painelLateral as $chave => $secao)
    @php $topoAlternado = ! $topoAlternado; @endphp
    <div class="{{ $topoAlternado ? 'LRTOP1' : 'LRTOP2' }}" data-pmo-alvo="#{{ $chave }}">
        <div>{{ $secao['titulo'] }}</div>
    </div>
    <div id="{{ $chave }}" style="display:none">
        @if (count($secao['registros']) === 0)
            <div class="fl" style="color:#FFF;padding:5px;font-size:12px;">Nenhum encontrado</div>
        @else
            @php $linhaAlternada = true; @endphp
            @foreach ($secao['registros'] as $item)
                @php $linhaAlternada = ! $linhaAlternada; @endphp
                @if ($secao['tipo'] === 'lista')
                    <a href="{{ route('rmas.show', ['rma' => $item['id']]) }}">
                        <div class="{{ $linhaAlternada ? 'LiRight1' : 'LiRight2' }}" style="min-height:28px;padding:6px;">
                            <div class="fl">{{ $item['nome'] }}</div>
                            <div class="fr">{{ $item['valor'] }}</div>
                        </div>
                    </a>
                @else
                    <div class="{{ $linhaAlternada ? 'LiRight1' : 'LiRight2' }}" style="min-height:28px;padding:6px 5px;">
                        <div class="fl">{{ $item['nome'] }}</div>
                        <div class="fr">{{ $item['valor'] }}</div>
                    </div>
                @endif
            @endforeach
        @endif
    </div>
    <div class="cb"></div>
@endforeach
