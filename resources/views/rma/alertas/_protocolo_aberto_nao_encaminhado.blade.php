{{--
    CP12-05 (fase 2 V1) — port literal da tabela de
    `15.8.1/subp/listar_pabertonaoencaminhado.php`. O mesmo partial atende os dois
    temas porque ambos incluem esse arquivo do container histórico. A consulta
    permanece no caso de uso `ProtocoloAbertoNaoEncaminhado`; aqui há somente
    composição de apresentação.
--}}
<table class="Tabelinha-Table tabela-alerta-protocolo">
    <thead>
        <tr class="SuperTr">
            <th style="width:8%">RECEBIDO</th>
            <th style="width:4%">T</th>
            <th style="width:7%">ORIGEM</th>
            <th style="width:6%">NF C</th>
            <th style="width:6%">NF V</th>
            <th style="width:12%">FORNECEDOR</th>
            <th style="width:15%">FABRICANTE</th>
            <th style="width:15%">DESCRICAO</th>
            <th style="width:20%">MODELO</th>
            <th style="width:5%">OS</th>
            <th style="width:2%">A</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rmas as $indice => $registro)
            @php
                $recebidoEm = $registro->recebido_em === null
                    ? null
                    : \Carbon\CarbonImmutable::instance($registro->recebido_em)->startOfDay();
                $tempo = $recebidoEm?->diffInDays(today()) ?? 0;
                $urlDetalhe = rota_tema('rmas.show', ['rma' => $registro->id]);
            @endphp
            {{-- O SELECT histórico deste grupo não retorna solução/prioridade/
            marcarestoque/entrada; portanto os branches de alerta do arquivo são
            inalcançáveis e a execução cai em TrZebrada1/2. Não acoplar esta tabela
            ao achado CP14, que pertence às listagens que realmente têm tais dados. --}}
            <tr class="{{ $indice % 2 === 0 ? 'TrZebrada2' : 'TrZebrada1' }}">
                <td class="Tabelinha-TDD">{{ $recebidoEm?->format('d/m/Y') }}</td>
                <td>{{ $tempo > 0 ? $tempo : '' }}</td>
                <td>{{ $registro->origem }}</td>
                <td>{{ (float) $registro->nfcompra > 0 ? $registro->nfcompra : '' }}</td>
                <td>{{ (float) $registro->nfvenda > 0 ? $registro->nfvenda : '' }}</td>
                <td class="Tabelinha-TDD">{{ $fornecedores[$registro->fornecedor_id] ?? '' }}</td>
                <td class="Tabelinha-TDD">{{ $fabricantes[$registro->fabricante_id] ?? '' }}</td>
                <td class="Tabelinha-TDD">{{ $registro->descricao }}</td>
                <td class="Tabelinha-TDD">{{ $registro->modelo }}</td>
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
