{{-- UF-08 (GAP-V2-01..03) - menu/breadcrumb de navegacao de Relatorios do TEMA V2.
Permite alternar entre o Painel Estatistico e os relatorios RCD, RPEC e RMPE
mantendo o item unico "Relatorios" no menu principal do 15.8.1. --}}
@php
    $relatorioAtual = $relatorioAtual ?? 'painel';

    $itens = [
        ['id' => 'painel', 'rotulo' => 'Estatisticas Gerais', 'url' => rota_tema('rmas.relatorios.index')],
        ['id' => 'rcd', 'rotulo' => 'RCD (Creditos)', 'url' => rota_tema('rmas.relatorios.rcd')],
        ['id' => 'rpec', 'rotulo' => 'RPEC (Estoque)', 'url' => rota_tema('rmas.relatorios.rpec')],
        ['id' => 'rmpe', 'rotulo' => 'RMPE (Movimentacao)', 'url' => rota_tema('rmas.relatorios.rmpe')],
    ];
@endphp

<div class="menu-subp">
    <ol class="breadcrumb">
        @foreach ($itens as $item)
            <li @if ($relatorioAtual === $item['id']) class="active" @endif>
                <a href="{{ $item['url'] }}" @if ($relatorioAtual === $item['id']) class="active" @endif>{{ $item['rotulo'] }}</a>
            </li>
        @endforeach
    </ol>
</div>
