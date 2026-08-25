@extends('temas.v1.layout')

@section('omitirTituloPadrao')
@endsection

@section('conteudo')
    {{-- VIS-V1-001/CP3D — fonte real `legacy-source/14.6.1/page/aguardandocredito.php`:
    `solucao='PENDENTE CREDITO'` (não é filtro por `status`). Distinta de
    `rmas.credito.index` (`/rmas-credito`, item "Creditos" do MENU administrativo):
    aquela é o fluxo de marcar crédito disponível; esta é a listagem read-only do
    atalho do header. Coluna "NF R" (`nfremessa`) sem campo equivalente no domínio
    atual, mesma decisão de `encaminhados.blade.php` — mantida vazia, não simulada.
    Achado 4: esta tela usa só `Tabelinha-TR1`/`Tabelinha-TR2` (zebra compacta de
    30px), nunca a família `TrZebrada`/`classe_css_de_alerta()` — o legado não tem
    nenhuma regra de destaque aqui, só alternância. --}}
    <p class="title-icone title-icone-status-v1 fl">
        <img src="{{ asset('images/tema-v1/pendente.png') }}" alt="" width="50" height="50">
    </p>
    <p class="title-comicone fl">{{ $descricao }}</p>
    <hr class="both">

    @if (count($registros) === 0)
        <p class="nenhumencontrado">Nenhum encontrado</p>
    @else
        <table class="Tabelinha-Table">
            <colgroup>
                @foreach ([8, 5, 12, 13, 9, 18, 5, 8, 6, 12, 4] as $largura)
                    <col style="width:{{ $largura }}%">
                @endforeach
            </colgroup>
            <thead>
                <tr class="TableListarFPEF-TR">
                    <th>ENTRADA</th>
                    <th>NF C</th>
                    <th>FABRICANTE</th>
                    <th>DESCRICAO</th>
                    <th>ORIGEM</th>
                    <th>MODELO</th>
                    <th>NF R</th>
                    <th>PROTOCOLO</th>
                    <th>VALOR</th>
                    <th>DESTINATARIO</th>
                    <th>OS</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $indiceZebra = 0;
                @endphp
                @foreach ($registros as $registro)
                    @php
                        // [DÚVIDA] mesmo padrão registrado em `concluidos.blade.php`: o PHP
                        // isolado do legado começa a alternância em `Tabelinha-TR2` (`$TR1`
                        // não setado na primeira iteração), mas o runtime observado começa em
                        // TR1. Determinístico aqui por índice, começando em TR1.
                        $classeLinha = $indiceZebra++ % 2 === 0 ? 'Tabelinha-TR1' : 'Tabelinha-TR2';
                        $origemExibida = origem_abreviada_v1($registro->origem);
                        $urlDetalhe = route('rmas.show', ['rma' => $registro->id]);
                    @endphp
                    <tr class="{{ $classeLinha }}">
                        <td class="Tabelinha-TD"><a href="{{ $urlDetalhe }}"><div>{{ $registro->createdAt?->format('d/m/Y') }}</div></a></td>
                        <td class="Tabelinha-TD"><a href="{{ $urlDetalhe }}"><div>{{ (float) $registro->nfcompra > 0 ? $registro->nfcompra : '' }}</div></a></td>
                        <td class="Tabelinha-TD"><a href="{{ $urlDetalhe }}"><div>{{ $fabricantes[$registro->fabricanteId] ?? '' }}</div></a></td>
                        <td class="Tabelinha-TD"><a href="{{ $urlDetalhe }}"><div>{{ $registro->descricao }}</div></a></td>
                        <td class="Tabelinha-TD"><a href="{{ $urlDetalhe }}"><div>{{ $origemExibida }}</div></a></td>
                        <td class="Tabelinha-TD"><a href="{{ $urlDetalhe }}"><div>{{ $registro->modelo }}</div></a></td>
                        <td class="Tabelinha-TD"><a href="{{ $urlDetalhe }}"><div></div></a></td>
                        <td class="Tabelinha-TD"><div>{{ $registro->protocolo }}</div></td>
                        <td class="Tabelinha-TD"><div>{{ $registro->valor > 0 ? number_format($registro->valor, 2, '.', '') : '' }}</div></td>
                        <td class="Tabelinha-TD"><a href="{{ $urlDetalhe }}"><div>{{ $destinatarios[$registro->destinatarioType.'#'.$registro->destinatarioId] ?? '' }}</div></a></td>
                        <td class="Tabelinha-TD" style="font-family:'Fira mono','Open Sans','Arial';"><a href="{{ $urlDetalhe }}"><div>{{ $registro->os }}</div></a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
