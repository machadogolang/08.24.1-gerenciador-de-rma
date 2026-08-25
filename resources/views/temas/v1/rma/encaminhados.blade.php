@extends('temas.v1.layout')

@section('omitirTituloPadrao')
@endsection

@section('conteudo')
    {{-- VIS-V1-001/CP3C — fonte real `legacy-source/14.6.1/page/encaminhados.php`:
    `status='Encaminhado'`, ordenado por `encaminhado_em`. Coluna "NF R" (`nfremessa`)
    é geometria histórica confirmada (achado 6) mas não tem campo equivalente no
    domínio de aplicação atual (só existe como coluna histórica do migrador,
    `Rma::$fillable` Fase 9, sem dono) — mantida vazia para preservar a largura/posição
    da coluna, não simulada com dado falso. Regra de destaque reaproveitada de
    `Rma::classeDeAlerta()`/`classe_css_de_alerta()` (Fase 5), fora do escopo desta
    correção estrutural. --}}
    <p class="title-icone title-icone-status-v1 fl">
        <img src="{{ asset('images/tema-v1/encaminhado.png') }}" alt="" width="50" height="50">
    </p>
    <p class="title-comicone fl">{{ $descricao }}</p>
    <hr class="both">

    @if (count($registros) === 0)
        <p class="nenhumencontrado">Nenhum RMA encontrado.</p>
    @else
        <table class="Tabelinha-Table" id="zebrada">
            <colgroup>
                @foreach ([8, 10, 6, 13, 13, 18, 6, 8, 14, 4] as $largura)
                    <col style="width:{{ $largura }}%">
                @endforeach
            </colgroup>
            <thead>
                <tr class="TableListarFPEF-TR">
                    <th>DATA</th>
                    <th>ORIGEM</th>
                    <th>NF C</th>
                    <th>FABRICANTE</th>
                    <th>DESCRICAO</th>
                    <th>MODELO</th>
                    <th>NF R</th>
                    <th>PROTOCOLO</th>
                    <th>DESTINATARIO</th>
                    <th>OS</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($registros as $indice => $registro)
                    @php
                        $origemExibida = origem_abreviada_v1($registro->origem);
                        $urlDetalhe = route('rmas.show', ['rma' => $registro->id]);
                    @endphp
                    <tr class="{{ classe_css_de_alerta($registro->classeDeAlerta(), \App\Identidade\Dominio\TemaPreferido::V1, $indice) }}">
                        <td class="Tabelinha-TD"><a href="{{ $urlDetalhe }}"><div>{{ $registro->encaminhadoEm?->format('d/m/Y') }}</div></a></td>
                        <td class="Tabelinha-TD"><a href="{{ $urlDetalhe }}"><div>{{ $origemExibida }}</div></a></td>
                        <td class="Tabelinha-TD"><a href="{{ $urlDetalhe }}"><div>{{ (float) $registro->nfcompra > 0 ? $registro->nfcompra : '' }}</div></a></td>
                        <td class="Tabelinha-TD"><a href="{{ $urlDetalhe }}"><div>{{ $fabricantes[$registro->fabricanteId] ?? '' }}</div></a></td>
                        <td class="Tabelinha-TD"><a href="{{ $urlDetalhe }}"><div>{{ $registro->descricao }}</div></a></td>
                        <td class="Tabelinha-TD"><a href="{{ $urlDetalhe }}"><div>{{ $registro->modelo }}</div></a></td>
                        <td class="Tabelinha-TD"><a href="{{ $urlDetalhe }}"><div></div></a></td>
                        <td class="Tabelinha-TD"><div>{{ $registro->protocolo }}</div></td>
                        <td class="Tabelinha-TD"><a href="{{ $urlDetalhe }}"><div>{{ $destinatarios[$registro->destinatarioType.'#'.$registro->destinatarioId] ?? '' }}</div></a></td>
                        <td class="Tabelinha-TD" style="font-family:'Fira mono','Open Sans','Arial';"><a href="{{ $urlDetalhe }}"><div>{{ $registro->os }}</div></a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
