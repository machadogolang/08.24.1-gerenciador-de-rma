{{-- CP20/CP23 (paridade visual V2) — fonte real
`legacy-source/15.8.1/subp/pesquisar_rma.php`: busca geral usada tanto pela aba
"Início" quanto pela aba "Pesquisar" (`page/inicio.php` inclui `page/pesquisar.php`
por inteiro — são a MESMA composição, não duas telas diferentes). Larguras de coluna
conferidas no PHP fonte: DT ENTRADA 9%, ORIGEM 8%, NF C 6%, NF V 6%, FABRICANTE 12%,
DESCRICAO 13%, MODELO 20%, S/N 16%, OS 5%, S(status) 2%, A(ação) 2%. O legado computa
`$soma`/`$quantidadetotal`/`$quantidadesemvalor` nesta página mas NUNCA os imprime
(dead code confirmado por leitura completa do arquivo) — sem resumo inferior aqui,
diferente de Concluídos no Tema V1.

[INVESTIGAR] — zebra desta tabela específica difere do padrão RN-11 já implementado em
`classe_css_de_alerta()`: o PHP fonte só usa `TrSemGarantia1/2` quando
`status=concluido AND solucao=SEM GARANTIA` (fora dessa combinação, solução
"SEM GARANTIA" cai no mesmo `TrInconformidade`/`TrZebrada` que os demais critérios).
`Rma::classeDeAlerta()` (Fase 5) mapeia solução SemGarantia para `Inconformidade`
incondicionalmente, sem olhar o status — não é a mesma regra. Reaproveitado aqui sem
alteração (mesma disciplina de "não reescrever regra de negócio sem necessidade" já
seguida no restante desta frente) — divergência registrada para investigação futura,
não corrigida às cegas. --}}
@if ($valor !== '' && count($rmas) === 0)
    {{-- Fonte não emite nenhuma mensagem quando a busca não retorna nada
    (`else { }` vazio) — mantido em branco por fidelidade; nenhum HTML aqui. --}}
@elseif (count($rmas) > 0)
    <hr>
    <table class="Tabelinha-Table">
        <colgroup>
            @foreach ([9, 8, 6, 6, 12, 13, 20, 16, 5, 2, 2] as $largura)
                <col style="width:{{ $largura }}%">
            @endforeach
        </colgroup>
        <tr class="SuperTr">
            <th>DT ENTRADA</th>
            <th>ORIGEM</th>
            <th>NF C</th>
            <th>NF V</th>
            <th>FABRICANTE</th>
            <th>DESCRICAO</th>
            <th>MODELO</th>
            <th>S/N</th>
            <th>OS</th>
            <th>S</th>
            <th>A</th>
        </tr>
        @foreach ($rmas as $indice => $registro)
            @php
                $iconeStatus = match ($registro->status) {
                    \App\Rma\Dominio\Status::Entrada => 'entrada',
                    \App\Rma\Dominio\Status::Recebido => 'recebido',
                    \App\Rma\Dominio\Status::Encaminhado => 'encaminhado',
                    \App\Rma\Dominio\Status::Concluido, \App\Rma\Dominio\Status::Arquivado => 'concluido',
                };
            @endphp
            <tr class="{{ classe_css_de_alerta($registro->classeDeAlerta(), \App\Identidade\Dominio\TemaPreferido::V2, $indice) }}">
                <td class="Tabelinha-TD"><div>{{ $registro->createdAt?->format('d/m/Y') }}</div></td>
                <td style="text-align:center;"><div>{{ $registro->origem }}</div></td>
                <td class="Tabelinha-TD"><div>{{ (float) $registro->nfcompra > 0 ? $registro->nfcompra : '' }}</div></td>
                <td class="Tabelinha-TD"><div>{{ (float) $registro->nfvenda > 0 ? $registro->nfvenda : '' }}</div></td>
                <td class="Tabelinha-TD"><div>{{ $fabricantes[$registro->fabricanteId] ?? '' }}</div></td>
                <td class="Tabelinha-TD"><div>{{ $registro->descricao }}</div></td>
                <td class="Tabelinha-TD"><div>{{ $registro->modelo }}</div></td>
                <td class="Tabelinha-TD"><div>{{ $registro->sn }}</div></td>
                <td class="Tabelinha-TD"><div>{{ $registro->os }}</div></td>
                <td class="Tabelinha-TD" style="text-align:center;">
                    <img src="{{ asset('images/tema-v2/'.$iconeStatus.'.png') }}" alt="{{ $iconeStatus }}" title="{{ $iconeStatus }}" height="25">
                </td>
                <td class="Tabelinha-TD" style="text-align:center;">
                    <a href="{{ route('rmas.show', ['rma' => $registro->id]) }}">
                        <img src="{{ asset('images/tema-v2/ver.png') }}" alt="Ver" title="Ver" height="25">
                    </a>
                </td>
            </tr>
        @endforeach
    </table>
    <hr>
@endif
