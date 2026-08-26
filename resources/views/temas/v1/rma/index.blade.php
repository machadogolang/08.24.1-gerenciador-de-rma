@extends('temas.v1.layout')

@php
    // CP12 (fase 2 V1) — mesmo achado/mapeamento já provado no TEMA V2 (CP22/
    // CMP-V2-005): `startpage.php` inclui os mesmos 10 `subp/listar_*.php` de
    // `../15.8.1/subp/` (lido por inteiro nos CP6/CP9/CP10 — sem mais includes
    // depois do último, "Urgência por valor" também não aparece aqui). Títulos
    // literais idênticos aos já confirmados pro V2 (mesmos arquivos-fonte).
    // Reordenação/relabel só nesta view, não em `ListarGruposDeAlertas` (mesma
    // decisão já tomada pro V2 — evita risco às duas superfícies compartilhadas).
    $ordemHistoricaCentroDeAvisosV1 = [
        'Prioridade alta sem encaminhar' => 'PRODUTOS COM MAIOR PRIORIDADE SEM ENCAMINHAMENTO',
        'Protocolo aberto não encaminhado' => 'PROTOCOLO ESTA ABERTO E O PRODUTO NAO ENCAMINHADO',
        'Sem número de série' => 'NECESSARIO IDENTIFICAR O S/N',
        'Sem nota fiscal' => 'SEM NF DE COMPRA E NF DE VENDA',
        'Prazo do destinatário estourado' => 'O DESTINATARIO ESTOUROU O PRAZO DE 30 DIAS PARA RETORNAR',
        'Recebidos há mais de 30 dias sem encaminhar' => 'RECEBIDO A MAIS DE 30 DIAS E NAO ENCAMINHADO',
        'Garantia do fornecedor expirada' => 'PRAZO DE GARANTIA COM O FORNECEDOR EXPIRADO MAIS DE 1 ANO',
        'Garantia do fornecedor expirando em até 30 dias' => 'FALTA MENOS DE 30 DIAS PARA EXPIRAR GARANTIA DE 1 ANO COM O FORNECEDOR',
        'Não vai dar garantia' => 'NAO VAI DAR GARANTIA',
        'NF de retorno pendente de lançar' => 'PRODUTOS COM PENDENCIA DE LANCAR NF DO RETORNO',
    ];
    $gruposCentroDeAvisosV1 = [];
    foreach ($ordemHistoricaCentroDeAvisosV1 as $chaveInterna => $tituloLiteral) {
        $gruposCentroDeAvisosV1[$tituloLiteral] = $grupos[$chaveInterna] ?? collect();
    }
    $buscaExecutadaV1 = (string) ($valor ?? '') !== '' || (string) ($solucao ?? '%') !== '%';
@endphp

@section('conteudo')
    {{-- CP6 (fase 2, `plano-execucao-paridade-visual-v1-fase2.md`) — `startpage.php`
    não tem link "Novo RMA" próprio; o atalho já existe no menu superior ("Novo",
    `#menu-novo` em `temas/v1/layout.blade.php`). Este link duplicava o mesmo
    destino sem fonte real no Legacy — removido.
    CP7 — o painel Localizar (antes um `<form>` fixo só desta view) virou global em
    `temas/v1/layout.blade.php` (`#JS-Localizar`, sempre no DOM, igual ao `#JS-Novo`,
    aberto por padrão só na Página Inicial) — ver `_form_localizar.blade.php`. --}}
    @if ($buscaExecutadaV1)
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

        {{-- CP10 (fase 2 V1) — cada contador é um `<a>` real no Legacy
        (`inc/startpage.php:17-176`), não texto estático — ver `link_do_contador_v1()`
        em `app/Support/view_do_tema.php` pro mapeamento rótulo→destino
        (`[GAP]` documentado lá pra "QUANTIDADE TOTAL DE ITENS"). --}}
        <div class="contadores-do-painel">
            @foreach ($contadores as $rotulo => $quantidade)
                <a href="{{ link_do_contador_v1($rotulo) }}">
                    <p class="formLabelStats fl">{{ $rotulo }}</p>
                    <p class="formValorStats fl">{{ $quantidade }}</p>
                </a>
                <div class="both"></div>
            @endforeach
        </div>
    </div>
    <div class="both"></div>

    {{-- CP11 (fase 2 V1) — separador entre o painel Anotações/Contadores e o Centro
    de Avisos, fonte real `startpage.php:182` (`separador2.png`,
    `margin-top:50px;float:right;height:40px`). --}}
    <img src="{{ asset('images/tema-v1/separador2.png') }}" alt="Separador" title="Separador" height="40" class="separador2-inicial">
    <div class="both"></div>

    @include('rma._centro_de_avisos', [
        'grupos' => $gruposCentroDeAvisosV1,
    ])
@endsection
