{{--
    CP12-05D (fase 2 V1) — tabela histórica de listar_semnota.php.
    Colunas: RECEBIDO | T | ORIGEM | FORNECEDOR | FABRICANTE | DESCRICAO | MODELO | S/N | OS | A
    Larguras: 8% | 4% | 7% | 12% | 14% | 13% | 18% | 17% | 5% | 2%
--}}
<table class="Tabelinha-Table tabela-alerta-sem-nota">
    <thead>
        <tr class="SuperTr">
            <th style="width:8%">RECEBIDO</th>
            <th style="width:4%">T</th>
            <th style="width:7%">ORIGEM</th>
            <th style="width:12%">FORNECEDOR</th>
            <th style="width:14%">FABRICANTE</th>
            <th style="width:13%">DESCRICAO</th>
            <th style="width:18%">MODELO</th>
            <th style="width:17%">S/N</th>
            <th style="width:5%">OS</th>
            <th style="width:2%">A</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rmas as $indice => $registro)
            @php
                $dataDaLinha = $registro->recebido_em === null
                    ? null
                    : \Carbon\CarbonImmutable::instance($registro->recebido_em)->startOfDay();
                $tempo = $dataDaLinha?->diffInDays(today()) ?? 0;
                $urlDetalhe = rota_tema('rmas.show', ['rma' => $registro->id]);
                $origemExibida = ($abreviarMercadoLivre ?? true) && mb_strtoupper((string) $registro->origem) === 'MERCADO LIVRE'
                    ? 'M LIVRE'
                    : $registro->origem;
            @endphp
            {{-- Os SELECTs históricos deste grupo não retornam solução/prioridade/
            marcarestoque; portanto os branches de alerta do arquivo são
            inalcançáveis e a execução cai em TrZebrada1/2. --}}
            <tr class="{{ $indice % 2 === 0 ? 'TrZebrada2' : 'TrZebrada1' }}">
                <td class="Tabelinha-TDD">{{ $dataDaLinha?->format('d/m/Y') }}</td>
                <td>{{ $tempo > 0 ? $tempo : '' }}</td>
                <td>{{ $origemExibida }}</td>
                <td class="Tabelinha-TDD">{{ $fornecedores[$registro->fornecedor_id] ?? '' }}</td>
                <td class="Tabelinha-TDD">{{ $fabricantes[$registro->fabricante_id] ?? '' }}</td>
                <td class="Tabelinha-TDD">{{ $registro->descricao }}</td>
                <td class="Tabelinha-TDD">{{ $registro->modelo }}</td>
                <td class="Tabelinha-TDD">{{ $registro->sn }}</td>
                <td class="Tabelinha-TDD">{{ $registro->os }}</td>
                <td>
                    <a href="{{ $urlDetalhe }}">
                        <img src="{{ asset('images/rma/ver.png') }}" alt="Ver" title="Ver" height="25">
                    </a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
