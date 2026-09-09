{{--
    CP12-05I (fase 2 V1) - tabela histórica de listar_pgarantiafornecedorexpirado.php.
    Colunas: ENTRADA | ORIGEM | NF C | T C | NF V | FORNECEDOR | FABRICANTE | DESCRICAO | MODELO | OS | A
    Larguras: 8% | 7% | 6% | 6% | 6% | 12% | 15% | 15% | 18% | 5% | 2%
    T C = dias desde emissão da NF de compra (sempre > 365 neste grupo). [CONFIRMADO-15.8.1]
--}}
<table class="Tabelinha-Table tabela-alerta-garantia-fornecedor-expirada">
    <thead>
        <tr class="SuperTr">
            <th style="width:8%">ENTRADA</th>
            <th style="width:7%">ORIGEM</th>
            <th style="width:6%">NF C</th>
            <th style="width:6%">T C</th>
            <th style="width:6%">NF V</th>
            <th style="width:12%">FORNECEDOR</th>
            <th style="width:15%">FABRICANTE</th>
            <th style="width:15%">DESCRICAO</th>
            <th style="width:18%">MODELO</th>
            <th style="width:5%">OS</th>
            <th style="width:2%">A</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rmas as $indice => $registro)
            @php
                $dataEntrada = $registro->created_at === null
                    ? null
                    : \Carbon\CarbonImmutable::instance($registro->created_at)->startOfDay();
                $dataNfCompra = $registro->nfcompra_emissao === null
                    ? null
                    : \Carbon\CarbonImmutable::parse($registro->nfcompra_emissao);
                $tempoNfCompra = $dataNfCompra?->diffInDays(today()) ?? 0;
                $urlDetalhe = rota_tema('rmas.show', ['rma' => $registro->id]);
                $origemExibida = mb_strtoupper((string) $registro->origem) === 'MERCADO LIVRE'
                    ? 'M LIVRE'
                    : $registro->origem;
            @endphp
            <tr class="{{ $indice % 2 === 0 ? 'TrZebrada2' : 'TrZebrada1' }}">
                <td class="Tabelinha-TDD">{{ $dataEntrada?->format('d/m/Y') }}</td>
                <td>{{ $origemExibida }}</td>
                <td class="Tabelinha-TDD">{{ $registro->nfcompra }}</td>
                <td>{{ $tempoNfCompra > 0 ? $tempoNfCompra : '' }}</td>
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
