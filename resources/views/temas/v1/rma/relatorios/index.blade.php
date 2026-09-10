@extends('temas.v1.layout')

@section('omitirTituloPadrao')
@endsection

@section('conteudo')
    {{-- UF-09 (GAP-V1-06 / CAP-REL-004) - Hub Estatistico de Relatorios adaptado ao TEMA V1 (14.6.1).
    Apresenta as mesmas contagens e metricas tenant-aware de PainelEstatisticoV2
    utilizando tabelas compactas, cabecalhos e tipografia nativas do Tema V1. --}}
    <p class="title-icone title-icone-status-v1 fl">
        <img src="{{ asset('images/tema-v1/bd.png') }}" alt="Relatorios" width="50" height="50">
    </p>
    <p class="title-comicone fl">Resumo estatistico e metricas operacionais do sistema</p>
    <hr class="both">

    <div style="display:flex;flex-wrap:wrap;gap:20px;">
        {{-- Bloco Situacao --}}
        <div style="flex:1;min-width:280px;">
            <table class="Tabelinha-Table" style="margin-bottom:15px;">
                <thead>
                    <tr class="TableListarFPEF-TR">
                        <th colspan="2">SITUACAO</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($painel['situacao'] as $item)
                        <tr>
                            <td class="Tabelinha-TD">{{ $item['rotulo'] }}</td>
                            <td class="Tabelinha-TD" style="text-align:right;font-weight:bold;">{{ $item['valor'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Bloco Dados do Sistema --}}
        <div style="flex:1;min-width:280px;">
            <table class="Tabelinha-Table" style="margin-bottom:15px;">
                <thead>
                    <tr class="TableListarFPEF-TR">
                        <th colspan="2">DADOS DO SISTEMA</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($painel['sistema'] as $item)
                        <tr>
                            <td class="Tabelinha-TD">{{ $item['rotulo'] }}</td>
                            <td class="Tabelinha-TD" style="text-align:right;font-weight:bold;">{{ $item['valor'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div style="display:flex;flex-wrap:wrap;gap:20px;">
        {{-- Bloco Resolucao --}}
        <div style="flex:1;min-width:280px;">
            <table class="Tabelinha-Table" style="margin-bottom:15px;">
                <thead>
                    <tr class="TableListarFPEF-TR">
                        <th colspan="2">RESOLUCAO</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($painel['resolucao'] as $item)
                        <tr>
                            <td class="Tabelinha-TD">{{ $item['rotulo'] }}</td>
                            <td class="Tabelinha-TD" style="text-align:right;font-weight:bold;">{{ $item['valor'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Bloco Origem --}}
        <div style="flex:1;min-width:280px;">
            <table class="Tabelinha-Table" style="margin-bottom:15px;">
                <thead>
                    <tr class="TableListarFPEF-TR">
                        <th colspan="2">ORIGEM</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($painel['origem'] as $item)
                        <tr>
                            <td class="Tabelinha-TD">{{ $item['rotulo'] }}</td>
                            <td class="Tabelinha-TD" style="text-align:right;font-weight:bold;">{{ $item['valor'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div style="display:flex;flex-wrap:wrap;gap:20px;">
        {{-- Bloco Notas Fiscais --}}
        <div style="flex:1;min-width:280px;">
            <table class="Tabelinha-Table" style="margin-bottom:15px;">
                <thead>
                    <tr class="TableListarFPEF-TR">
                        <th colspan="2">DADOS RELACIONADOS A NF</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($painel['notas'] as $item)
                        <tr>
                            <td class="Tabelinha-TD">{{ $item['rotulo'] }}</td>
                            <td class="Tabelinha-TD" style="text-align:right;font-weight:bold;">{{ $item['valor'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Bloco Fornecedores --}}
        <div style="flex:1;min-width:280px;">
            <table class="Tabelinha-Table" style="margin-bottom:15px;">
                <thead>
                    <tr class="TableListarFPEF-TR">
                        <th colspan="2">TOP FORNECEDORES</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($painel['fornecedores'] as $item)
                        <tr>
                            <td class="Tabelinha-TD">{{ $item['rotulo'] }}</td>
                            <td class="Tabelinha-TD" style="text-align:right;font-weight:bold;">{{ $item['valor'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Series Historicas anuais/mensais --}}
    @if (!empty($painel['anos']))
        <table class="Tabelinha-Table" style="margin-top:10px;margin-bottom:25px;">
            <thead>
                <tr class="TableListarFPEF-TR">
                    <th colspan="13">SERIES HISTORICAS POR PERIODO</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($painel['anos'] as $ano => $meses)
                    <tr>
                        <td class="Tabelinha-TD" colspan="13" style="font-weight:bold;background:#F1F1F1;">ANO {{ $ano }}</td>
                    </tr>
                    <tr>
                        <td class="Tabelinha-TD" style="font-weight:bold;">Metrica</td>
                        @for ($m = 1; $m <= 12; $m++)
                            <td class="Tabelinha-TD" style="text-align:center;font-size:10px;">{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</td>
                        @endfor
                    </tr>
                    <tr>
                        <td class="Tabelinha-TD">Entradas</td>
                        @for ($m = 1; $m <= 12; $m++)
                            <td class="Tabelinha-TD" style="text-align:center;">{{ $meses[$m]['entrada'] ?? 0 }}</td>
                        @endfor
                    </tr>
                    <tr>
                        <td class="Tabelinha-TD">Concluidos</td>
                        @for ($m = 1; $m <= 12; $m++)
                            <td class="Tabelinha-TD" style="text-align:center;">{{ $meses[$m]['concluido'] ?? 0 }}</td>
                        @endfor
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
