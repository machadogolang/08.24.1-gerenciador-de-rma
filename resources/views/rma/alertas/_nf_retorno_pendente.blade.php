{{--
    CP12-05H (fase 2 V1) — tabela histórica de listar_nfpendentelancar.php.
    Colunas: CONCLUIDO | T | ORIGEM | NF C | NF V | FORNECEDOR | FABRICANTE | DESCRICAO | MODELO | OS | A
    Larguras: 8% | 4% | 7% | 6% | 6% | 12% | 15% | 15% | 20% | 5% | 2%
    T = dias desde concluído (concluido_em). [CONFIRMADO-15.8.1]
--}}
<table class="Tabelinha-Table tabela-alerta-nf-retorno-pendente">
    <thead>
        <tr class="SuperTr">
            <th style="width:8%">CONCLUIDO</th>
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
                $dataConcluido = $registro->concluido_em === null
                    ? null
                    : \Carbon\CarbonImmutable::instance($registro->concluido_em)->startOfDay();
                $tempo = $dataConcluido?->diffInDays(today()) ?? 0;
                $urlDetalhe = rota_tema('rmas.show', ['rma' => $registro->id]);
                $origemExibida = mb_strtoupper((string) $registro->origem) === 'MERCADO LIVRE'
                    ? 'M LIVRE'
                    : $registro->origem;
            @endphp
            <tr class="{{ $indice % 2 === 0 ? 'TrZebrada2' : 'TrZebrada1' }}">
                <td class="Tabelinha-TDD">{{ $dataConcluido?->format('d/m/Y') }}</td>
                <td>{{ $tempo > 0 ? $tempo : '' }}</td>
                <td>{{ $origemExibida }}</td>
                <td class="Tabelinha-TDD">{{ $registro->nfcompra }}</td>
                <td class="Tabelinha-TDD">{{ $registro->nfvenda }}</td>
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
