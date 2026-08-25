@extends('temas.v1.layout')

@section('conteudo')
    <p><a href="{{ rota_tema('rmas.create') }}">Novo RMA</a></p>

    <form method="GET" action="{{ rota_tema('rmas.index') }}" id="LINHA">
        <label>Buscar por
            <select name="tipo" class="formSelect">
                <option value="texto" @selected($tipo === 'texto')>Texto</option>
                <option value="serial" @selected($tipo === 'serial')>Serial</option>
                <option value="nota_fiscal" @selected($tipo === 'nota_fiscal')>Nota fiscal</option>
            </select>
        </label>
        <input type="text" name="valor" value="{{ $valor }}">
        <button type="submit">Buscar</button>
    </form>

    @if (count($rmas) === 0)
        <p class="nenhumencontrado">Nenhum RMA encontrado.</p>
    @else
        <table class="Tabelinha-Table">
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
                @foreach ($rmas as $indice => $registro)
                    {{-- RN-11 (Fase 5): TEMA V1 usa TrInconformidade/TrUrgente/TrZebrada1/2 via
                    o CSS compartilhado (pattern/15.9.7.css) — "SEM GARANTIA" cai em
                    TrInconformidade (não tem classe própria em TEMA V1, ver design.md). --}}
                    <tr class="{{ classe_css_de_alerta($registro->classeDeAlerta(), \App\Identidade\Dominio\TemaPreferido::V1, $indice) }}">
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
@endsection
