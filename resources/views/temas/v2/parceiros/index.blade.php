@extends('temas.v2.layout')

@section('conteudo')
    {{-- Contrato de ações FRONT-003/UI-02B: Novo (primary), Editar (secondary+compact),
    Remover (danger+compact). Mesma semântica HTML das rotas - sem JS de navegação.

    UX-001 (P8) - apresentação consciente de Policy: quem não pode gravar não vê
    Novo/Editar/Remover (a autorização real continua no controller, nada é movido
    para o browser). UX-002 - a remoção pede confirmação (`data-confirmar-remocao`,
    tratado no JS do tema). --}}
    @can('create', $modelo)
        <p><a href="{{ rota_tema('parceiros.' . $tipo . '.create') }}" class="acao acao--primaria">Novo</a></p>
    @endcan

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
                            @can('update', $registro)
                                <a href="{{ rota_tema('parceiros.' . $tipo . '.edit', $registro) }}" class="acao acao--secundaria acao--compacta">Editar</a>
                            @endcan
                            @can('delete', $registro)
                                <form method="POST" action="{{ rota_tema('parceiros.' . $tipo . '.destroy', $registro) }}" style="display:inline" data-confirmar-remocao="Remover este registro?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="acao acao--perigo acao--compacta">Remover</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
