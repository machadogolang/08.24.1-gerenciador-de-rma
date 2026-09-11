{{-- PAR14-REL-RPEC/RCD/RMPE - folha de relatorio do TEMA V1 no contrato do Legacy
`14.6.1/page/relatorios.php`: titulo + texto explicativo, tabela com as colunas
historicas, totalizadores, "INFORMACAO ADICIONAL" persistida e a nota de impressao.
Especifica (nao compartilhada com o V2): cada tema preserva o proprio contrato. --}}
<div class="relatorio">
    <p class="title-icone fl" style="margin-left:0px;margin-top:8px;">
        <img src="{{ asset('images/tema-v1/bd.png') }}" alt="Relatorios" width="50" height="50">
    </p>
    <h1 class="title-comicone fl" style="font-size:18px;">{{ $relatorio['titulo'] }}</h1>
    <p style="clear:both;margin-bottom:15px;font-size:14px;padding-top:10px;">{{ $relatorio['subtitulo'] }}</p>
    <hr class="both">

    <table class="Tabelinha-Table" data-tabela-skinless="true">
        <thead>
            <tr class="TableListarFPEF-TR">
                @foreach ($relatorio['colunas'] as $coluna)
                    <th style="width:{{ $coluna['largura'] }}%">{{ $coluna['rotulo'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($relatorio['linhas'] as $indice => $linha)
                <tr class="{{ $indice % 2 === 0 ? 'Tabelinha-TR1' : 'Tabelinha-TR2' }}">
                    @foreach ($relatorio['colunas'] as $coluna)
                        <td class="Tabelinha-TD"><div>{{ $linha[$coluna['chave']] ?? '' }}</div></td>
                    @endforeach
                </tr>
            @empty
                <tr><td class="Tabelinha-TD" colspan="{{ count($relatorio['colunas']) }}">Nenhum item foi encontrado</td></tr>
            @endforelse
        </tbody>
    </table>

    <hr style="clear:both;">
    <h2 style="float:right;font-family:Arial;margin-top:0px;letter-spacing:2px;">Valor Total: R$ {{ $relatorio['totais']['valor'] }}</h2>
    <p style="font-size:15px;">DATA DO RELATORIO: {{ $relatorio['totais']['data'] }}</p>
    <p style="font-size:15px;">Quantidade Total de produtos: {{ $relatorio['totais']['quantidade'] }}</p>
    <p style="font-size:15px;">Quantidade dos produtos a cima que nao participaram da contagem monetario: {{ $relatorio['totais']['sem_valor'] }}</p>
    <hr style="margin-top:20px;">

    <h3>INFORMACAO ADICIONAL: </h3>
    <form method="POST" action="{{ rota_tema('rmas.relatorios.informacao-adicional.update', ['codigo' => $relatorio['codigo']]) }}">
        @csrf
        @method('PUT')
        <div style="height:75px;">
            <textarea style="height:100%;width:100%;font-size:18px;color:#FFF;" name="informacao_adicional">{{ $relatorio['informacao_adicional'] }}</textarea>
        </div>
        <div style="margin-top:10px;float:left;">
            <button style="width:200px;font-size:11px;margin-top:10px;" type="submit">SALVAR INFORMACAO ADICIONAL</button>
        </div>
    </form>
    <div style="height:10px;margin-top:40px;float:right;font-size:11px;">PARA GERAR O RELATORIO FACA A IMPRESSAO COM CTRL + P USANDO O DOPDF</div>
    <div style="clear:both;height:10px;"></div>
</div>
