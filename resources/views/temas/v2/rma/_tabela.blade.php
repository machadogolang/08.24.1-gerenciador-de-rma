@if (count($registros) === 0)
    <p class="nenhumencontrado">Nenhum RMA encontrado.</p>
@else
    <table class="Tabelinha-Table table">
        <thead>
            <tr>
                <th>#</th>
                <th>Descrição</th>
                <th>Defeito</th>
                <th>Origem</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($registros as $indice => $registro)
                {{-- RN-11 (Fase 5): TEMA V2 usa o conjunto completo de classes de
                alerta, incluindo TrSemGarantia1/2 (design.md "RN-11 em TEMA V1"). --}}
                <tr class="{{ classe_css_de_alerta($registro->classeDeAlerta(), \App\Identidade\Dominio\TemaPreferido::V2, $indice) }}">
                    <td>{{ $registro->id }}</td>
                    <td>{{ $registro->descricao }}</td>
                    <td>{{ $registro->defeito }}</td>
                    <td>{{ $registro->origem }}</td>
                    <td>
                        <a href="{{ rota_tema('rmas.show', ['rma' => $registro->id]) }}">Ver</a>
                        <a href="{{ rota_tema('rmas.edit', ['rma' => $registro->id]) }}">Editar</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
