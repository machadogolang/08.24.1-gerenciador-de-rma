@extends('temas.v2.layout')

{{-- PAR15-CREDIT-001/003 - tela de Creditos do TEMA V2 na fonte REAL
`15.8.1/page/credito.php`: a rota `/creditos` do `.htaccess` cai em
`index.php?p=credito`, e `index.php` inclui `page/credito.php` (a tabela). O
`page/creditos.php` + `inc/menu_creditos.php` (Disponiveis/Pendentes/Usados) sao
CODIGO MORTO: apontam para `subp/{disponiveis,pendentes,usados}.php`, que nao existem
no container - por isso nao sao reproduzidos (PAR15-CREDIT-002/004 = [CODIGO-MORTO]).

Colunas do Legacy: DATA, NF C, FABRICANTE, DESCRICAO, MODELO, NF R, PROTOCOLO,
DESTINATARIO, OS, VALOR, A (Ver), com a zebra/classificacao do 15.8.1. A capacidade
moderna de marcar credito continua disponivel em bloco recolhido, sem trocar o
contrato principal. --}}
@section('conteudo')
    @if (session('status'))
        <p class="centrodeavisos">{{ session('status') }}</p>
    @endif


    @php $zebraCredito = false; @endphp

    {{-- O Legacy imprime o cabecalho da tabela ANTES do teste de vazio (e so entao o
    "Nenhum produto"); reproduzimos o cabecalho sempre visivel. --}}
    <table class="Tabelinha-Table">
        <thead>
            <tr class="SuperTr">
                    <th style="width:8%">DATA</th>
                    <th style="width:5%">NF C</th>
                    <th style="width:14%">FABRICANTE</th>
                    <th style="width:13%">DESCRICAO</th>
                    <th style="width:17%">MODELO</th>
                    <th style="width:5%">NF R</th>
                    <th style="width:10%">PROTOCOLO</th>
                    <th style="width:14%">DESTINATARIO</th>
                    <th style="width:4%">OS</th>
                    <th style="width:10%">VALOR</th>
                <th style="width:2%">A</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($creditos as $registro)
                    <tr class="{{ classe_css_linha_v2('credito', $registro, $zebraCredito) }}">
                        <td class="Tabelinha-TD"><div>{{ $registro->encaminhadoEm?->format('d/m/Y') ?? '' }}</div></td>
                        <td class="Tabelinha-TD"><div>{{ (float) $registro->nfcompra > 0 ? $registro->nfcompra : '' }}</div></td>
                        <td class="Tabelinha-TD"><div>{{ $fabricantes[$registro->fabricanteId] ?? '' }}</div></td>
                        <td class="Tabelinha-TD"><div>{{ $registro->descricao }}</div></td>
                        <td class="Tabelinha-TD"><div>{{ $registro->modelo }}</div></td>
                        <td class="Tabelinha-TD"><div>{{ $registro->nfRemessa }}</div></td>
                        <td class="Tabelinha-TD"><div>{{ $registro->protocolo }}</div></td>
                        <td class="Tabelinha-TD"><div>{{ $destinatarios[($registro->destinatarioType ?? '').':'.($registro->destinatarioId ?? '')] ?? '' }}</div></td>
                        <td class="Tabelinha-TD"><div>{{ $registro->os }}</div></td>
                        <td class="Tabelinha-TD"><div>{{ (float) $registro->valor > 0 ? number_format((float) $registro->valor, 2, '.', '') : '' }}</div></td>
                        <td class="Tabelinha-TD" style="text-align:center;">
                            <a href="{{ rota_tema('rmas.show', ['rma' => $registro->id]) }}" title="Ver">
                                <img src="{{ asset('images/rma/ver.png') }}" alt="Ver" height="25">
                            </a>
                        </td>
                    </tr>
            @endforeach
        </tbody>
    </table>

    @if (count($creditos) === 0)
        <p style="text-align:left;padding:5px;min-height:400px;">Nenhum produto</p>
    @endif

    <details class="detalhe-bd-acoes-avancadas">
        <summary>Acao moderna: marcar credito disponivel</summary>
        <p>Exige solucao = "GERADO CREDITO" no RMA informado (fluxo moderno mantido).</p>
        <form method="POST" action="{{ route('rmas.credito.marcar') }}">
            @csrf
            <label>RMA <input type="number" name="rma_id" required></label>
            <button type="submit" class="acao acao--primaria">Marcar credito disponivel</button>
        </form>

        @if ($aguardandoCredito->isNotEmpty())
            <p>Aguardando credito (solucao = PENDENTE CREDITO):</p>
            <ul>
                @foreach ($aguardandoCredito as $registro)
                    <li><a href="{{ rota_tema('rmas.show', ['rma' => $registro->id]) }}">#{{ $registro->id }} - {{ $registro->descricao }}</a></li>
                @endforeach
            </ul>
        @endif
    </details>
@endsection
