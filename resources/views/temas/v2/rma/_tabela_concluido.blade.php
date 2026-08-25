{{-- CP23 (paridade visual V2) — fonte real `legacy-source/15.8.1/page/concluido.php`.
Larguras: DATA 8%, ORIGEM 10%, T 4%, NF C 6%, NF V 5%, FABRICANTE 14%, DESCRICAO 15%,
MODELO 18%, S/N 15%, OS 4%, A 2%. T aqui é o tempo de giro (dias entre entrada e
conclusão), não dias até hoje.

Zebra própria desta tela (achado já confirmado para o TEMA V1/Concluídos, mesmo
padrão aqui): só `solucao=SEM GARANTIA` importa (`TrSemGarantia1/2`), sem checar
prioridade/origem/marcarestoque — `Rma::classeDeAlerta()` faria isso errado (nunca
devolve o caso SemGarantia puro, sempre cai em Inconformidade, e considera critérios
que esta tela não usa) — não reaproveitado de propósito, mesma decisão já tomada
para `TableV1/concluidos.blade.php`. --}}
@if (count($registros) === 0)
    <p style="text-align:left;padding:5px;">Nenhum produto</p>
@else
    <table class="Tabelinha-Table">
        <colgroup>
            @foreach ([8, 10, 4, 6, 5, 14, 15, 18, 15, 4, 2] as $largura)
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
        @php
            $indiceZebra = 0;
        @endphp
        @foreach ($registros as $registro)
            @php
                $semGarantia = $registro->solucao === \App\Rma\Dominio\Solucao::SemGarantia;
                $classeLinha = $semGarantia
                    ? ($indiceZebra++ % 2 === 0 ? 'TrSemGarantia1' : 'TrSemGarantia2')
                    : ($indiceZebra++ % 2 === 0 ? 'TrZebrada1' : 'TrZebrada2');
                $tempo = ($registro->createdAt && $registro->concluidoEm)
                    ? $registro->createdAt->diffInDays($registro->concluidoEm, true)
                    : 0;
            @endphp
            <tr class="{{ $classeLinha }}">
                <td>{{ $registro->concluidoEm?->format('d/m/Y') }}</td>
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
