{{--
    CP12-05E (fase 2 V1) - tabela histórica de listar_prazodestinatario.php.
    Colunas: ENCAMINHADO | T | ORIGEM | FABRICANTE | DESCRICAO | MODELO | PROTOCOLO | DESTINATARIO | OS | A
    Larguras: 10% | 4% | 7% | 13% | 13% | 16% | 14% | 16% | 5% | 2%
--}}
<table class="Tabelinha-Table tabela-alerta-prazo-destinatario">
    <thead>
        <tr class="SuperTr">
            <th style="width:10%">ENCAMINHADO</th>
            <th style="width:4%">T</th>
            <th style="width:7%">ORIGEM</th>
            <th style="width:13%">FABRICANTE</th>
            <th style="width:13%">DESCRICAO</th>
            <th style="width:16%">MODELO</th>
            <th style="width:14%">PROTOCOLO</th>
            <th style="width:16%">DESTINATARIO</th>
            <th style="width:5%">OS</th>
            <th style="width:2%">A</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rmas as $indice => $registro)
            @php
                $dataDaLinha = $registro->encaminhado_em === null
                    ? null
                    : \Carbon\CarbonImmutable::instance($registro->encaminhado_em)->startOfDay();
                $tempo = $dataDaLinha?->diffInDays(today()) ?? 0;
                $urlDetalhe = rota_tema('rmas.show', ['rma' => $registro->id]);
                $origemExibida = mb_strtoupper((string) $registro->origem) === 'MERCADO LIVRE'
                    ? 'M LIVRE'
                    : $registro->origem;
                $destinatarioChave = ($registro->destinatario_type ?? $registro->destinatarioType) . '#' . ($registro->destinatario_id ?? $registro->destinatarioId);
                $nomeDestinatario = $destinatarios[$destinatarioChave] ?? $registro->destinatario_nome_legado ?? '';
            @endphp
            {{-- Os SELECTs históricos deste grupo não retornam solução/prioridade/
            marcarestoque; portanto os branches de alerta do arquivo são
            inalcançáveis e a execução cai em TrZebrada1/2. --}}
            <tr class="{{ $indice % 2 === 0 ? 'TrZebrada2' : 'TrZebrada1' }}">
                <td class="Tabelinha-TDD">{{ $dataDaLinha?->format('d/m/Y') }}</td>
                <td>{{ $tempo > 0 ? $tempo : '' }}</td>
                <td>{{ $origemExibida }}</td>
                <td class="Tabelinha-TDD">{{ $fabricantes[$registro->fabricante_id] ?? '' }}</td>
                <td class="Tabelinha-TDD">{{ $registro->descricao }}</td>
                <td class="Tabelinha-TDD">{{ $registro->modelo }}</td>
                <td class="Tabelinha-TDD">{{ $registro->protocolo }}</td>
                <td class="Tabelinha-TDD">{{ $nomeDestinatario }}</td>
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
