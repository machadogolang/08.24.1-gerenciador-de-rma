@extends('temas.v1.layout')

@section('conteudo')
    <p><a href="{{ rota_tema('parceiros.' . $tipo . '.create') }}">Novo</a></p>

    @if (count($registros) === 0)
        <p class="nenhumencontrado">Nenhum registro encontrado.</p>
    @else
        <table class="Tabelinha-Table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Cidade/UF</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($registros as $indice => $registro)
                    <tr class="{{ $indice % 2 === 0 ? 'TrZebrada1' : 'TrZebrada2' }}">
                        <td>{{ $registro->nome }}</td>
                        <td>{{ $registro->cidade }}{{ $registro->uf ? '/' . $registro->uf->value : '' }}</td>
                        <td>
                            <a href="{{ rota_tema('parceiros.' . $tipo . '.edit', $registro) }}">Editar</a>
                            <form method="POST" action="{{ rota_tema('parceiros.' . $tipo . '.destroy', $registro) }}" style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit">Remover</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
