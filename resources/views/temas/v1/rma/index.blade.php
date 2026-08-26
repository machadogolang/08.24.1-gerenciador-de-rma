@extends('temas.v1.layout')

@section('conteudo')
    {{-- CP6 (fase 2, `plano-execucao-paridade-visual-v1-fase2.md`) — `startpage.php`
    não tem link "Novo RMA" próprio; o atalho já existe no menu superior ("Novo",
    `#menu-novo` em `temas/v1/layout.blade.php`). Este link duplicava o mesmo
    destino sem fonte real no Legacy — removido.
    CP7 — o painel Localizar (antes um `<form>` fixo só desta view) virou global em
    `temas/v1/layout.blade.php` (`#JS-Localizar`, sempre no DOM, igual ao `#JS-Novo`,
    aberto por padrão só na Página Inicial) — ver `_form_localizar.blade.php`. --}}
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

    {{-- "QUADRO DE ANOTACOES" + sidebar de contadores por solução — correção de
    fidelidade Fase 8 (2026-08-25), fonte real: `14.6.1/index.php`. Anotação reusa a
    MESMA rota/caso de uso já existente desde a Fase 1 (`AtualizarAnotacaoPessoal`, ver
    `temas/v1/identidade/perfil.blade.php`) — nenhuma lógica nova, só um segundo lugar
    na UI editando o mesmo dado. Contadores vêm de `RmaController::contadoresDoPainel()`
    (consulta de composição, não regra de negócio nova). --}}
    <div class="painel-inicial-v1">
        <form method="POST" action="{{ route('identidade.perfil.anotacao.update') }}" class="quadro-de-anotacoes">
            @csrf
            @method('PUT')

            <p class="quadro-de-anotacoes-titulo">
                <img src="{{ asset('images/rma/notas.png') }}" width="20" alt="">
                QUADRO DE ANOTACOES
            </p>
            <textarea name="anotacao" rows="14" class="textareaanotacao">{{ old('anotacao', auth()->user()?->anotacao) }}</textarea>
            <button type="submit" class="buttonSave">Salvar anotação</button>
        </form>

        <div class="contadores-do-painel">
            @foreach ($contadores as $rotulo => $quantidade)
                <p class="formLabelStats fl">{{ $rotulo }}</p>
                <p class="formValorStats fl">{{ $quantidade }}</p>
                <div class="both"></div>
            @endforeach
        </div>
    </div>
    <div class="both"></div>

    @include('rma._centro_de_avisos', ['grupos' => $grupos])
@endsection
