{{-- CP23 (paridade visual V2) - fonte real `legacy-source/15.8.1/page/entrada.php`.
Larguras: DATA 8%, ORIGEM 7%, T 4%, NF C 6%, NF V 6%, FABRICANTE 14%, DESCRICAO 13%,
MODELO 20%, S/N 18%, OS 4%, A 2%.

PAR-RES-001 - corrigido: as classes agora sao decididas por tela
(`classe_css_linha_v2('entrada', ...)`) como no PHP fonte: sem garantia e prioridade
alta viram `TrInconformidade`; nao existe `TrUrgente` nem checagem de prazo nesta
tela; zebra alterna `TrZebrada1/2`. --}}
@if (count($registros) === 0)
    <p style="text-align:left;padding:5px;">Nenhum produto</p>
@else
    <table class="Tabelinha-Table">
        <colgroup>
            @foreach ([8, 7, 4, 6, 6, 14, 13, 20, 18, 4, 2] as $largura)
                <col style="width:{{ $largura }}%">
            @endforeach
        </colgroup>
        <tr class="SuperTr">
            <th>DATA</th>
            <th>ORIGEM</th>
            <th>T</th>
            <th>NF C</th>
            <th>NF V</th>
            <th>FABRICANTE</th>
            <th>DESCRICAO</th>
            <th>MODELO</th>
            <th>S/N</th>
            <th>OS</th>
            <th>A</th>
        </tr>
                @php $zebraV2 = false; @endphp
@foreach ($registros as $indice => $registro)
            @php
                $tempo = $registro->createdAt ? $registro->createdAt->diffInDays(now(), true) : 0;
            @endphp
            <tr class="{{ classe_css_linha_v2('entrada', $registro, $zebraV2) }}">
                <td>{{ $registro->createdAt?->format('d/m/Y') }}</td>
                <td style="text-align:center;">{{ origem_abreviada_v1($registro->origem) }}</td>
                <td>{{ $tempo > 0 ? (int) $tempo : '' }}</td>
                <td>{{ (float) $registro->nfcompra > 0 ? $registro->nfcompra : '' }}</td>
                <td>{{ (float) $registro->nfvenda > 0 ? $registro->nfvenda : '' }}</td>
                <td>{{ $fabricantes[$registro->fabricanteId] ?? '' }}</td>
                <td>{{ $registro->descricao }}</td>
                <td>{{ $registro->modelo }}</td>
                <td>{{ $registro->sn }}</td>
                <td>{{ $registro->os }}</td>
                <td class="Tabelinha-TD" style="text-align:center;">
                    <a href="{{ route('rmas.show', ['rma' => $registro->id]) }}">
                        <img src="{{ asset('images/tema-v2/ver.png') }}" alt="Ver" title="Ver" height="25">
                    </a>
                </td>
            </tr>
        @endforeach
    </table>
@endif
