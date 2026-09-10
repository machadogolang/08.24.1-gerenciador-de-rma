{{-- CP19 (paridade visual V2) - fonte real `legacy-source/15.8.1/inc/rightmenu.php`:
14 secoes colapsaveis. Dado ja vem pronto de `ListarPainelLateral` (via
`View::composer`, `AppServiceProvider`) - nenhum calculo/SQL aqui.

PAR-RES-A-01 (ONDA A) - o cabecalho NAO e alternancia simples: em
`inc/rightmenu.php` a sequencia e
`LRTOP1, LRTOP2, LRTOP1, LRTOP2, LRTOP1, LRTOP1, LRTOP1, LRTOP2, LRTOP1, LRTOP2,
LRTOP1, LRTOP2, LRTOP1, LRTOP2` (DESTINATARIOS/PORTO A/URGENTE sao TRES `LRTOP1`
seguidos). O toggle anterior gerava paridade invertida e errava o miolo. As linhas
tambem comecam em `LiRight1` em TODA secao (o legado inicializa `$x = 1`). --}}
@php
    $classeDoTopo = [
        'entrada_r' => 'LRTOP1',
        'recebido_r' => 'LRTOP2',
        'encaminhado_r' => 'LRTOP1',
        'concluido_r' => 'LRTOP2',
        'destinatarios_r' => 'LRTOP1',
        'portoalegre_r' => 'LRTOP1',
        'urgente_r' => 'LRTOP1',
        'pendentecredito_r' => 'LRTOP2',
        'creditodisponivel_r' => 'LRTOP1',
        'fabricantes_r' => 'LRTOP2',
        'fornecedores_r' => 'LRTOP1',
        'clientes_r' => 'LRTOP2',
        'produtosdecliente_r' => 'LRTOP1',
        'todosprodutos_r' => 'LRTOP2',
    ];
@endphp
@foreach ($painelLateral as $chave => $secao)
    <div class="{{ $classeDoTopo[$chave] ?? 'LRTOP1' }}" data-pmo-alvo="#{{ $chave }}">
        <div>{{ $secao['titulo'] }}</div>
    </div>
    <div id="{{ $chave }}" style="display:none">
        @if (count($secao['registros']) === 0)
            <div class="fl" style="color:#FFF;padding:5px;font-size:12px;">Nenhum encontrado</div>
        @else
            @php $linhaAlternada = false; @endphp
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
