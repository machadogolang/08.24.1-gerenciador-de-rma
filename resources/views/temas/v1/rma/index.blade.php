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
    (consulta de composição, não regra de negócio nova).

    CP9 (fase 2 V1) — `startpage.php` salva a cada `onkeyup` (AJAX antigo, não
    portado), sem botão "Salvar" e sem `<form>` (o campo oculto `id="em"`/`onkeyup`
    fazem tudo via JS). Aqui vira `data-anotacao-autosave` + `fetch` debounced
    (`v1.js`) pro mesmo endpoint que o formulário tradicional do perfil usa — sem
    reimplementar o polling antigo, sem botão. `rows="20"`/classes `panotacao`/
    `imganotacao`/`textareaanotacao` com os valores medidos no Legacy
    (CMP-V1-2-004). --}}
    <div class="painel-inicial-v1">
        <div class="quadro-de-anotacoes">
            <p class="panotacao"><img class="imganotacao" src="{{ asset('images/rma/notas.png') }}" width="20" alt="">QUADRO DE ANOTACOES</p>
            <textarea
                id="anotacao"
                class="textareaanotacao"
                rows="20"
                data-anotacao-autosave
                data-anotacao-url="{{ route('identidade.perfil.anotacao.update') }}"
            >{{ auth()->user()?->anotacao }}</textarea>
        </div>

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
