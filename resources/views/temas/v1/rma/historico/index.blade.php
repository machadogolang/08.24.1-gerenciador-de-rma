@extends('temas.v1.layout')

@section('omitirTituloPadrao')
@endsection

@section('conteudo')
    {{-- UF-10 (GAP-V1-04/05) - Historico de modificacoes de RMA sob o TEMA V1 (14.6.1).
    Mesma capacidade do V2 (subp/logs_de_modificacao.php), expressa com a linguagem visual nativa
    do 14.6.1: cabecalho com icone, Tabelinha-Table, linhas zebradas e acao Ver direcionada ao detalhe. --}}
    <p class="title-icone fl" style="margin-left:0px;margin-top:8px;">
        <img src="{{ asset('images/rma/notas.png') }}" alt="" width="50" height="50">
    </p>
    <p class="title-comicone fl">{{ $titulo }}</p>
    <a href="{{ rota_tema('rmas.controle.index') }}" style="float:right;font-size:12px;margin-top:20px;color:#333;text-decoration:none;">&larr; Voltar ao Controle</a>
    <hr class="both">

    <div class="historico-tabela">
        @if ($modificacoes->isEmpty())
            <p class="nenhumencontrado">Nenhuma modificação registrada.</p>
        @else
            <table class="Tabelinha-Table" data-tabela-skinless="true">
                <thead>
                    <tr class="TableListarFPEF-TR">
                        <th>DATA</th>
                        <th>RMA</th>
                        <th>FABRICANTE</th>
                        <th>DESCRIÇÃO</th>
                        <th>MODELO</th>
                        <th>USUÁRIO</th>
                        <th>AÇÃO</th>
                        <th>IP</th>
                        <th>VER</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($modificacoes as $indice => $modificacao)
                        @php
                            $estado = $modificacao->estado_apos ?? [];
                            $fabricanteLog = $estado['fabricante'] ?? $modificacao->rma?->fabricante?->nome ?? '-';
                            $descricaoLog = $estado['descricao'] ?? $modificacao->rma?->descricao ?? '-';
                            $modeloLog = $estado['modelo'] ?? $modificacao->rma?->modelo ?? '-';
                            $numeroLog = $modificacao->rma?->numero_legado ?? $modificacao->rma_id;
                            $urlRma = rota_tema('rmas.show', ['rma' => $modificacao->rma_id]);
                        @endphp
                        <tr class="{{ $indice % 2 === 0 ? 'Tabelinha-TR1' : 'Tabelinha-TR2' }}">
                            <td class="Tabelinha-TD"><div>{{ $modificacao->created_at?->format('d/m/Y H:i:s') }}</div></td>
                            <td class="Tabelinha-TD"><a href="{{ $urlRma }}"><div>#{{ $numeroLog }}</div></a></td>
                            <td class="Tabelinha-TD"><div>{{ $fabricanteLog }}</div></td>
                            <td class="Tabelinha-TD"><div>{{ $descricaoLog }}</div></td>
                            <td class="Tabelinha-TD"><div>{{ $modeloLog }}</div></td>
                            <td class="Tabelinha-TD"><div>{{ $modificacao->user?->name ?? '-' }}</div></td>
                            <td class="Tabelinha-TD"><div>{{ $modificacao->acao->name }}</div></td>
                            <td class="Tabelinha-TD"><div>{{ $modificacao->ip ?? '-' }}</div></td>
                            <td class="Tabelinha-TD" style="text-align:center;">
                                <a href="{{ $urlRma }}" title="Ver">
                                    <img src="{{ asset('images/rma/ver.png') }}" alt="Ver" height="20">
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{ $modificacoes->links() }}
        @endif
    </div>
@endsection
