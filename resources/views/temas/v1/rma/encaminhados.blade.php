@extends('temas.v1.layout')

@section('conteudo')
    {{-- VIS-V1-001 — fonte real `legacy-source/14.6.1/page/encaminhados.php`:
    `status='Encaminhado'`, ordenado por `encaminhado_em`. Coluna "NF R" (nfremessa) do
    legado omitida: o campo só existe como coluna histórica preenchida pelo migrador
    (`Rma::$fillable`, Fase 9), sem regra de negócio dona nem exposição no domínio de
    aplicação — não simular dado que a camada atual não escreve de verdade. --}}
    <p class="title-comicone">{{ $descricao }}</p>

    @if (count($registros) === 0)
        <p class="nenhumencontrado">Nenhum RMA encontrado.</p>
    @else
        <table class="Tabelinha-Table">
            <thead>
                <tr class="TableListarFPEF-TR">
                    <th>DATA</th>
                    <th>ORIGEM</th>
                    <th>NF C</th>
                    <th>FABRICANTE</th>
                    <th>DESCRICAO</th>
                    <th>MODELO</th>
                    <th>PROTOCOLO</th>
                    <th>DESTINATARIO</th>
                    <th>OS</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($registros as $indice => $registro)
                    <tr class="{{ classe_css_de_alerta($registro->classeDeAlerta(), \App\Identidade\Dominio\TemaPreferido::V1, $indice) }}">
                        <td class="Tabelinha-TD">
                            <a href="{{ route('rmas.show', ['rma' => $registro->id]) }}">{{ $registro->encaminhadoEm?->format('d/m/Y') }}</a>
                        </td>
                        <td class="Tabelinha-TD">
                            <a href="{{ route('rmas.show', ['rma' => $registro->id]) }}">{{ $registro->origem }}</a>
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
                            <a href="{{ route('rmas.show', ['rma' => $registro->id]) }}">{{ $registro->modelo }}</a>
                        </td>
                        <td class="Tabelinha-TD">{{ $registro->protocolo }}</td>
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
