@extends('temas.v1.layout')

@section('conteudo')
    {{-- VIS-V1-001 — fonte real `legacy-source/14.6.1/page/entrada.php`:
    `status='entrada' OR status='recebido'`, ordenado por data de criação (`entrada`
    no legado, `created_at` aqui — ver decisão em `RmasEmBanco::listarPorPainel()`). --}}
    <p class="title-comicone">{{ $descricao }}</p>

    @if (count($registros) === 0)
        <p class="nenhumencontrado">Nenhum RMA encontrado.</p>
    @else
        <table class="Tabelinha-Table">
            <thead>
                <tr class="TableListarFPEF-TR">
                    <th>RECEBIDO</th>
                    <th>ORIGEM</th>
                    <th>NF C</th>
                    <th>NF V</th>
                    <th>FABRICANTE</th>
                    <th>DESCRICAO</th>
                    <th>MODELO</th>
                    <th>S/N</th>
                    <th>VALOR</th>
                    <th>OS</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($registros as $indice => $registro)
                    <tr class="{{ classe_css_de_alerta($registro->classeDeAlerta(), \App\Identidade\Dominio\TemaPreferido::V1, $indice) }}">
                        <td class="Tabelinha-TD">
                            <a href="{{ route('rmas.show', ['rma' => $registro->id]) }}">{{ $registro->recebidoEm?->format('d/m/Y') }}</a>
                        </td>
                        <td class="Tabelinha-TD">
                            <a href="{{ route('rmas.show', ['rma' => $registro->id]) }}">{{ $registro->origem }}</a>
                        </td>
                        <td class="Tabelinha-TD">
                            <a href="{{ route('rmas.show', ['rma' => $registro->id]) }}">{{ $registro->nfcompra }}</a>
                        </td>
                        <td class="Tabelinha-TD">
                            <a href="{{ route('rmas.show', ['rma' => $registro->id]) }}">{{ $registro->nfvenda }}</a>
                        </td>
                        <td class="Tabelinha-TD">
                            <a href="{{ route('rmas.show', ['rma' => $registro->id]) }}">{{ $fabricantes[$registro->fabricanteId] ?? '' }}</a>
                        </td>
                        <td class="Tabelinha-TD">
                            <a href="{{ route('rmas.show', ['rma' => $registro->id]) }}">{{ $registro->descricao }}</a>
                        </td>
                        <td class="Tabelinha-TD">{{ $registro->modelo }}</td>
                        <td class="Tabelinha-TD">{{ $registro->sn }}</td>
                        <td class="Tabelinha-TD">
                            <a href="{{ route('rmas.show', ['rma' => $registro->id]) }}">{{ $registro->valor > 0 ? number_format($registro->valor, 2, ',', '.') : '' }}</a>
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
