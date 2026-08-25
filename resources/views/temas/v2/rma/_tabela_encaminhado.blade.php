{{-- CP23 (paridade visual V2) — fonte real
`legacy-source/15.8.1/page/encaminhado.php`. Larguras: DATA 8%, ORIGEM 7%, T 4%,
NF C 5%, FABRICANTE 14%, DESCRICAO 13%, MODELO 16%, NF R 5%, PROTOCOLO 10%,
DESTINATARIO 14%, OS 4%, A 2%. NF R (`nfremessa`) sem campo equivalente no domínio
atual — mesma decisão já registrada para o TEMA V1 (`encaminhados.blade.php`), célula
vazia, geometria preservada. --}}
@if (count($registros) === 0)
    <p style="text-align:left;padding:5px;">Nenhum produto</p>
@else
    <table class="Tabelinha-Table">
        <colgroup>
            @foreach ([8, 7, 4, 5, 14, 13, 16, 5, 10, 14, 4, 2] as $largura)
                <col style="width:{{ $largura }}%">
            @endforeach
        </colgroup>
        <tr class="SuperTr">
            <th>DATA</th>
            <th>ORIGEM</th>
            <th>T</th>
            <th>NF C</th>
            <th>FABRICANTE</th>
            <th>DESCRICAO</th>
            <th>MODELO</th>
            <th>NF R</th>
            <th>PROTOCOLO</th>
            <th>DESTINATARIO</th>
            <th>OS</th>
            <th>A</th>
        </tr>
        @foreach ($registros as $indice => $registro)
            @php
                $tempo = $registro->encaminhadoEm ? $registro->encaminhadoEm->diffInDays(now(), true) : 0;
            @endphp
            <tr class="{{ classe_css_de_alerta($registro->classeDeAlerta(), \App\Identidade\Dominio\TemaPreferido::V2, $indice) }}">
                <td>{{ $registro->encaminhadoEm?->format('d/m/Y') }}</td>
                <td style="text-align:center;">{{ origem_abreviada_v1($registro->origem) }}</td>
                <td>{{ $tempo > 0 ? (int) $tempo : '' }}</td>
                <td>{{ (float) $registro->nfcompra > 0 ? $registro->nfcompra : '' }}</td>
                <td>{{ $fabricantes[$registro->fabricanteId] ?? '' }}</td>
                <td>{{ $registro->descricao }}</td>
                <td>{{ $registro->modelo }}</td>
                <td></td>
                <td>{{ $registro->protocolo }}</td>
                <td>{{ $destinatarios[$registro->destinatarioType.'#'.$registro->destinatarioId] ?? '' }}</td>
                <td>{{ $registro->os }}</td>
                <td style="text-align:center;">
                    <a href="{{ route('rmas.show', ['rma' => $registro->id]) }}">
                        <img src="{{ asset('images/tema-v2/ver.png') }}" alt="Ver" title="Ver" height="25">
                    </a>
                </td>
            </tr>
        @endforeach
    </table>
@endif
