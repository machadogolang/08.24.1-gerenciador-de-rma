@extends('temas.v2.layout')

@section('conteudo')
    {{-- Contrato de ações FRONT-003/UI-02B: Novo (primary), Editar (secondary+compact),
    Remover (danger+compact). Mesma semântica HTML das rotas - sem JS de navegação. --}}
    <p><a href="{{ rota_tema('parceiros.' . $tipo . '.create') }}" class="acao acao--primaria">Novo</a></p>

    @if (count($registros) === 0)
        <p class="nenhumencontrado">Nenhum registro encontrado.</p>
    @else
        <table class="table Tabelinha-Table">
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
                        <td class="acoes-de-tabela">
                            <a href="{{ rota_tema('parceiros.' . $tipo . '.show', $registro) }}" class="acao acao--secundaria acao--compacta">Ver</a>
                            <a href="{{ rota_tema('parceiros.' . $tipo . '.edit', $registro) }}" class="acao acao--secundaria acao--compacta">Editar</a>
                            <form method="POST" action="{{ rota_tema('parceiros.' . $tipo . '.destroy', $registro) }}" style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="acao acao--perigo acao--compacta">Remover</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
