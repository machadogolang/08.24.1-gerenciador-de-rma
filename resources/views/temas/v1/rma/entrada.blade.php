@extends('temas.v1.layout')

@section('omitirTituloPadrao')
@endsection

@section('conteudo')
    {{-- VIS-V1-001/CP3B — fonte real `legacy-source/14.6.1/page/entrada.php`:
    `status='entrada' OR status='recebido'`, ordenado por data de criação (`entrada`
    no legado, `created_at` aqui — ver decisão em `RmasEmBanco::listarPorPainel()`).
    Regra de destaque (`TrInconformidade`/`TrUrgente`/`TrZebrada1`/`TrZebrada2`) já
    provada pela Fase 5 via `Rma::classeDeAlerta()`/`classe_css_de_alerta()`, reaproveitada
    sem alteração — não é escopo desta correção estrutural (achados 1-8 tratavam
    composição/geometria, não a regra RN-11). --}}
    <p class="title-icone title-icone-status-v1 fl">
        <img src="{{ asset('images/tema-v1/entrada.png') }}" alt="" width="50" height="50">
    </p>
    <p class="title-comicone fl">{{ $descricao }}</p>
    <hr class="both">

    @if (count($registros) === 0)
        <p class="nenhumencontrado">Nenhum RMA encontrado.</p>
    @else
        <table class="Tabelinha-Table">
            <colgroup>
                @foreach ([8, 10, 6, 6, 13, 12, 18, 17, 6, 4] as $largura)
                    <col style="width:{{ $largura }}%">
                @endforeach
            </colgroup>
            <thead>
                <tr class="TableListarFPEF-TR">
                    <th>RECEBIDO</th>
                    <th>ORIGEM</th>
                    <th>NF C</th>
                    <th>NF V</th>
                    <th>FABRICANTE</th>
                    <th>DESCRICAO</th>
                    <th>MODELO</th>
                    <th>S/N</th>
                    <th>VALOR</th>
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
                        <td class="Tabelinha-TD"><a href="{{ $urlDetalhe }}"><div>{{ $registro->recebidoEm?->format('d/m/Y') }}</div></a></td>
                        <td class="Tabelinha-TD"><a href="{{ $urlDetalhe }}"><div>{{ $origemExibida }}</div></a></td>
                        <td class="Tabelinha-TD"><a href="{{ $urlDetalhe }}"><div>{{ (float) $registro->nfcompra > 0 ? $registro->nfcompra : '' }}</div></a></td>
                        <td class="Tabelinha-TD"><a href="{{ $urlDetalhe }}"><div>{{ (float) $registro->nfvenda > 0 ? $registro->nfvenda : '' }}</div></a></td>
                        <td class="Tabelinha-TD"><a href="{{ $urlDetalhe }}"><div>{{ $fabricantes[$registro->fabricanteId] ?? '' }}</div></a></td>
                        <td class="Tabelinha-TD"><a href="{{ $urlDetalhe }}"><div>{{ $registro->descricao }}</div></a></td>
                        <td class="Tabelinha-TD"><div>{{ $registro->modelo }}</div></td>
                        <td class="Tabelinha-TD"><div>{{ $registro->sn }}</div></td>
                        <td class="Tabelinha-TD"><a href="{{ $urlDetalhe }}"><div>{{ $registro->valor > 0 ? number_format($registro->valor, 2, '.', '') : '' }}</div></a></td>
                        <td class="Tabelinha-TD" style="font-family:'Fira mono','Open Sans','Arial';"><a href="{{ $urlDetalhe }}"><div>{{ $registro->os }}</div></a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
