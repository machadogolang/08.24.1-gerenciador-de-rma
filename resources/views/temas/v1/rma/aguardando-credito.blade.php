@extends('temas.v1.layout')

@section('conteudo')
    {{-- VIS-V1-001 — fonte real `legacy-source/14.6.1/page/aguardandocredito.php`:
    `solucao='PENDENTE CREDITO'` (não é filtro por `status`). Coluna "NF R" omitida,
    mesma decisão de `encaminhados.blade.php`. Distinta de `rmas.credito.index`
    (`/rmas-credito`, item "Creditos" do MENU administrativo): aquela é o fluxo de
    marcar crédito disponível; esta é a listagem read-only do atalho do header. --}}
    <p class="title-comicone">{{ $descricao }}</p>

    @if (count($registros) === 0)
        <p class="nenhumencontrado">Nenhum RMA encontrado.</p>
    @else
        <table class="Tabelinha-Table">
            <thead>
                <tr class="TableListarFPEF-TR">
                    <th>ENTRADA</th>
                    <th>NF C</th>
                    <th>FABRICANTE</th>
                    <th>DESCRICAO</th>
                    <th>ORIGEM</th>
                    <th>MODELO</th>
                    <th>PROTOCOLO</th>
                    <th>VALOR</th>
                    <th>DESTINATARIO</th>
                    <th>OS</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($registros as $indice => $registro)
                    <tr class="{{ classe_css_de_alerta($registro->classeDeAlerta(), \App\Identidade\Dominio\TemaPreferido::V1, $indice) }}">
                        <td class="Tabelinha-TD">
                            <a href="{{ route('rmas.show', ['rma' => $registro->id]) }}">{{ $registro->createdAt?->format('d/m/Y') }}</a>
                        </td>
                        <td class="Tabelinha-TD">
                            <a href="{{ route('rmas.show', ['rma' => $registro->id]) }}">{{ $registro->nfcompra }}</a>
                        </td>
                        <td class="Tabelinha-TD">
                            <a href="{{ route('rmas.show', ['rma' => $registro->id]) }}">{{ $fabricantes[$registro->fabricanteId] ?? '' }}</a>
                        </td>
                        <td class="Tabelinha-TD">
                            <a href="{{ route('rmas.show', ['rma' => $registro->id]) }}">{{ $registro->descricao }}</a>
                        </td>
                        <td class="Tabelinha-TD">
                            <a href="{{ route('rmas.show', ['rma' => $registro->id]) }}">{{ $registro->origem }}</a>
                        </td>
                        <td class="Tabelinha-TD">
                            <a href="{{ route('rmas.show', ['rma' => $registro->id]) }}">{{ $registro->modelo }}</a>
                        </td>
                        <td class="Tabelinha-TD">{{ $registro->protocolo }}</td>
                        <td class="Tabelinha-TD">{{ $registro->valor > 0 ? number_format($registro->valor, 2, ',', '.') : '' }}</td>
                        <td class="Tabelinha-TD">
                            <a href="{{ route('rmas.show', ['rma' => $registro->id]) }}">{{ $destinatarios[$registro->destinatarioType.'#'.$registro->destinatarioId] ?? '' }}</a>
                        </td>
                        <td class="Tabelinha-TD" style="font-family:'Fira mono','Open Sans','Arial';">
                            <a href="{{ route('rmas.show', ['rma' => $registro->id]) }}">{{ $registro->os }}</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
