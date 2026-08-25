@extends('temas.v1.layout')

@section('omitirTituloPadrao')
@endsection

@section('conteudo')
    {{-- VIS-V1-001 — fonte real `legacy-source/14.6.1/page/concluidos.php`:
    `status='CONCLUIDO'`, ordenado por `concluido_em`. --}}
    <p class="title-icone title-icone-status-v1 fl">
        <img src="{{ asset('images/tema-v1/concluido.png') }}" alt="" width="50" height="50">
    </p>
    <p class="title-comicone fl">{{ $descricao }}</p>
    <hr class="both">

    <table class="Tabelinha-Table" id="zebrada">
        <colgroup>
            @foreach ([8, 5, 5, 12, 14, 10, 18, 17, 6, 4] as $largura)
                <col style="width:{{ $largura }}%">
            @endforeach
        </colgroup>
        <thead>
            <tr class="TableListarFPEF-TR">
                <th>DATA</th>
                <th>NF C</th>
                <th>NF V</th>
                <th>FABRICANTE</th>
                <th>DESCRICAO</th>
                <th>ORIGEM</th>
                <th>MODELO</th>
                <th>S/N</th>
                <th>VALOR</th>
                <th>OS</th>
            </tr>
        </thead>
        <tbody>
            @php
                $indiceZebra = 0;
            @endphp
            @foreach ($registros as $registro)
                @php
                    // [DÚVIDA] O PHP isolado começa em TR2, mas o runtime/print de
                    // referência começa em TR1 por estado compartilhado de `$TR1`.
                    // A V3 elimina o vazamento e reproduz deterministicamente o runtime.
                    $semGarantia = $registro->solucao === \App\Rma\Dominio\Solucao::SemGarantia;
                    $classeLinha = $semGarantia
                        ? 'Tabelinha-TR3'
                        : ($indiceZebra++ % 2 === 0 ? 'Tabelinha-TR1' : 'Tabelinha-TR2');

                    $origemExibida = origem_abreviada_v1($registro->origem);
                    $urlDetalhe = route('rmas.show', ['rma' => $registro->id]);
                @endphp
                <tr class="{{ $classeLinha }}">
                    <td class="Tabelinha-TD"><a href="{{ $urlDetalhe }}"><div>{{ $registro->concluidoEm?->format('d/m/Y') }}</div></a></td>
                    <td class="Tabelinha-TD"><a href="{{ $urlDetalhe }}"><div>{{ (float) $registro->nfcompra > 0 ? $registro->nfcompra : '' }}</div></a></td>
                    <td class="Tabelinha-TD"><a href="{{ $urlDetalhe }}"><div>{{ (float) $registro->nfvenda > 0 ? $registro->nfvenda : '' }}</div></a></td>
                    <td class="Tabelinha-TD"><a href="{{ $urlDetalhe }}"><div>{{ $fabricantes[$registro->fabricanteId] ?? '' }}</div></a></td>
                    <td class="Tabelinha-TD"><a href="{{ $urlDetalhe }}"><div>{{ $registro->descricao }}</div></a></td>
                    <td class="Tabelinha-TD"><a href="{{ $urlDetalhe }}"><div>{{ $origemExibida }}</div></a></td>
                    <td class="Tabelinha-TD"><a href="{{ $urlDetalhe }}"><div>{{ $registro->modelo }}</div></a></td>
                    <td class="Tabelinha-TD"><div>{{ $registro->sn }}</div></td>
                    <td class="Tabelinha-TD"><div>{{ $registro->valor > 0 ? number_format($registro->valor, 2, '.', '') : '' }}</div></td>
                    <td class="Tabelinha-TD" style="font-family:'Fira mono','Open Sans','Arial';"><a href="{{ $urlDetalhe }}"><div>{{ $registro->os }}</div></a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
