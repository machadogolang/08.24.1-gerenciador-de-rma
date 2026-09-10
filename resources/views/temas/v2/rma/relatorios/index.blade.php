@extends('temas.v2.layout')

{{-- PAR15-REL-001..007 - painel estatistico de Relatorios do TEMA V2, fonte
`15.8.1/page/relatorios.php`. Mesma organizacao de blocos (Situacao, Resolucao,
Origem, Fornecedores, Dados relacionados a NF, Dados do Sistema e series
mensais/anuais), com as contagens vindas de `PainelEstatisticoV2` (SQL agregado,
tenant-aware). --}}
@section('conteudo')
    <div class="boxtop-subpage">
        <h4 class="box-subpage fl">Relatorios</h4>
        <div style="clear:both;"></div>
    </div>

    <div style="text-align:left;font-family:'Fira Mono';padding:15px;">Situacao</div>
    @foreach ($painel['situacao'] as $indice => $item)
        <div class="LiRight{{ $indice % 2 === 0 ? 2 : 1 }}">
            <div class="fl">{{ $item['rotulo'] }}</div>
            <div class="fr">{{ $item['valor'] }}</div>
            <div style="clear:both;"></div>
        </div>
    @endforeach

    <div style="text-align:left;font-family:'Fira Mono';padding:15px;">Resolucao</div>
    @foreach ($painel['resolucao'] as $indice => $item)
        <div class="LiRight{{ $indice % 2 === 0 ? 2 : 1 }}">
            <div class="fl">{{ $item['rotulo'] }}</div>
            <div class="fr">{{ $item['valor'] }}</div>
            <div style="clear:both;"></div>
        </div>
    @endforeach

    <div style="text-align:left;font-family:'Fira Mono';padding:15px;">Origem</div>
    @foreach ($painel['origem'] as $indice => $item)
        <div class="LiRight{{ $indice % 2 === 0 ? 2 : 1 }}">
            <div class="fl">{{ $item['rotulo'] }}</div>
            <div class="fr">{{ $item['valor'] }}</div>
            <div style="clear:both;"></div>
        </div>
    @endforeach

    <div style="text-align:left;font-family:'Fira Mono';padding:15px;">Fornecedores</div>
    @foreach ($painel['fornecedores'] as $indice => $item)
        <div class="LiRight{{ $indice % 2 === 0 ? 2 : 1 }}">
            <div class="fl">{{ $item['rotulo'] }}</div>
            <div class="fr">{{ $item['valor'] }}</div>
            <div style="clear:both;"></div>
        </div>
    @endforeach

    <div style="text-align:left;font-family:'Fira Mono';padding:15px;">Dados relacionados a nf</div>
    @foreach ($painel['notas'] as $indice => $item)
        <div class="LiRight{{ $indice % 2 === 0 ? 1 : 2 }}">{{ $item['rotulo'] }}: {{ $item['valor'] }}</div>
    @endforeach

    <div style="text-align:left;font-family:'Fira Mono';padding:15px;">Dados do Sistema</div>
    @foreach ($painel['sistema'] as $indice => $item)
        <div class="LiRight{{ $indice % 2 === 0 ? 2 : 1 }}">{{ $item['rotulo'] }}: {{ $item['valor'] }}</div>
    @endforeach

    @foreach ($painel['anos'] as $serie)
        <div style="text-align:left;font-family:'Fira Mono';padding:15px;">Entrada / {{ $serie['ano'] }}</div>
        @foreach ($serie['entrada'] as $mes => $total)
            <div class="LiRight{{ $mes % 2 === 0 ? 2 : 1 }}">{{ str_pad((string) $mes, 2, '0', STR_PAD_LEFT) }}/{{ $serie['ano'] }}: {{ $total > 0 ? $total : '' }}</div>
        @endforeach

        <div style="text-align:left;font-family:'Fira Mono';padding:15px;">Encaminhado / {{ $serie['ano'] }}</div>
        @foreach ($serie['encaminhado'] as $mes => $total)
            <div class="LiRight{{ $mes % 2 === 0 ? 2 : 1 }}">{{ str_pad((string) $mes, 2, '0', STR_PAD_LEFT) }}/{{ $serie['ano'] }}: {{ $total > 0 ? $total : '' }}</div>
        @endforeach

        <div style="text-align:left;font-family:'Fira Mono';padding:15px;">Concluido / {{ $serie['ano'] }}</div>
        @foreach ($serie['concluido'] as $mes => $total)
            <div class="LiRight{{ $mes % 2 === 0 ? 2 : 1 }}">{{ str_pad((string) $mes, 2, '0', STR_PAD_LEFT) }}/{{ $serie['ano'] }}: {{ $total > 0 ? $total : '' }}</div>
        @endforeach

        <div style="text-align:left;font-family:'Fira Mono';padding:15px;">Total / {{ $serie['ano'] }}</div>
        <div class="LiRight2">Entrada: {{ $serie['totais']['entrada'] }}</div>
        <div class="LiRight1">Recebido: {{ $serie['totais']['recebido'] }}</div>
        <div class="LiRight2">Encaminhado: {{ $serie['totais']['encaminhado'] }}</div>
        <div class="LiRight1">Concluido: {{ $serie['totais']['concluido'] }}</div>
        <div class="LiRight2">Com nf de compra: {{ $serie['totais']['com_nf_compra'] }}</div>
        <div class="LiRight1">Garantia OK: {{ $serie['totais']['garantia_ok'] }}</div>
        <div class="LiRight2">Sem garantia: {{ $serie['totais']['sem_garantia'] }}</div>
    @endforeach

    <div style="clear:both;"></div>
@endsection
