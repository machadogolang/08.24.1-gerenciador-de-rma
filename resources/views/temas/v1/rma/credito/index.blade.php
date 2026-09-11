@extends('temas.v1.layout')

{{-- PAR14-CREDIT-001 / UF-11 (GAP-V1-07):
Historicamente o 14.6.1/menujs-right/creditos.php era apenas um painel que apontava para o RCD.
Pela nova regra canônica do produto (DECISÃO 2026-09-10 - união funcional entre temas):
o Tema V1 oferece a listagem tabular rica completa de créditos disponíveis e a capacidade de marcar
crédito, preservando a identidade visual do 14.6.1 (Tabelinha-Table e detalhes nativos). --}}
@section('conteudo')
    @if (session('status'))
        <p class="centrodeavisos">{{ session('status') }}</p>
    @endif

    <div>
        <p class="title-icone fl" style="margin-left:0px;margin-top:8px;">
            <img src="{{ asset('images/rma/relatorios.png') }}" alt="" width="50" height="50">
        </p>
        <h1 class="title-comicone fl" style="font-size:18px;">Relatórios</h1>
        <hr class="both">
        <div>
            <a href="{{ route('rmas.relatorios.rcd') }}">
                <p class="TitleRel"><strong>RCD</strong> - RELATORIO DE CREDITOS DISPONIVEIS</p>
            </a>
            <p><span style="color:gold;margin-left:10px;">Credito disponivel</span></p>
        </div>
        <hr style="height:1px;background-color:rgba(255,255,255,0.2);margin-top:5px;margin-bottom:15px;">
    </div>

    <h2 class="title-comicone" style="font-size:15px;margin-bottom:10px;">Créditos disponíveis</h2>

    @if (count($creditos) === 0)
        <p class="nenhumencontrado">Nenhum produto com crédito disponível.</p>
    @else
        <table class="Tabelinha-Table" data-tabela-skinless="true">
            <thead>
                <tr class="TableListarFPEF-TR">
                    <th>DATA</th>
                    <th>NF C</th>
                    <th>FABRICANTE</th>
                    <th>DESCRIÇÃO</th>
                    <th>MODELO</th>
                    <th>NF R</th>
                    <th>PROTOCOLO</th>
                    <th>DESTINATÁRIO</th>
                    <th>OS</th>
                    <th>VALOR</th>
                    <th>VER</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($creditos as $indice => $registro)
                    @php
                        $urlDetalhe = rota_tema('rmas.show', ['rma' => $registro->id]);
                    @endphp
                    <tr class="{{ $indice % 2 === 0 ? 'Tabelinha-TR1' : 'Tabelinha-TR2' }}">
                        <td class="Tabelinha-TD"><a href="{{ $urlDetalhe }}"><div>{{ $registro->encaminhadoEm?->format('d/m/Y') ?? '' }}</div></a></td>
                        <td class="Tabelinha-TD"><a href="{{ $urlDetalhe }}"><div>{{ (float) $registro->nfcompra > 0 ? $registro->nfcompra : '' }}</div></a></td>
                        <td class="Tabelinha-TD"><a href="{{ $urlDetalhe }}"><div>{{ $fabricantes[$registro->fabricanteId] ?? '' }}</div></a></td>
                        <td class="Tabelinha-TD"><a href="{{ $urlDetalhe }}"><div>{{ $registro->descricao }}</div></a></td>
                        <td class="Tabelinha-TD"><div>{{ $registro->modelo }}</div></td>
                        <td class="Tabelinha-TD"><div>{{ $registro->nfRemessa }}</div></td>
                        <td class="Tabelinha-TD"><div>{{ $registro->protocolo }}</div></td>
                        <td class="Tabelinha-TD"><div>{{ $destinatarios[($registro->destinatarioType ?? '').':'.($registro->destinatarioId ?? '')] ?? '' }}</div></td>
                        <td class="Tabelinha-TD"><a href="{{ $urlDetalhe }}"><div>{{ $registro->os }}</div></a></td>
                        <td class="Tabelinha-TD"><a href="{{ $urlDetalhe }}"><div>{{ (float) $registro->valor > 0 ? number_format((float) $registro->valor, 2, '.', '') : '' }}</div></a></td>
                        <td class="Tabelinha-TD" style="text-align:center;">
                            <a href="{{ $urlDetalhe }}" title="Ver">
                                <img src="{{ asset('images/rma/ver.png') }}" alt="Ver" height="20">
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div style="margin-top:20px;">
        <details>
            <summary class="formTitlePanel">MARCAR CRÉDITO DISPONÍVEL</summary>
            <form method="POST" action="{{ route('rmas.credito.marcar') }}" style="margin-top:10px;">
                @csrf
                <p class="fl formLabelPanel">NÚMERO DO RMA:</p>
                <p class="fl"><input class="formInputPanel" type="text" name="rma_id" inputmode="numeric" required></p>
                <p class="fl"><button class="formButtonEnviarPanel" type="submit">MARCAR CRÉDITO</button></p>
            </form>
            <div style="height:10px;clear:both;"></div>
        </details>
    </div>
@endsection
