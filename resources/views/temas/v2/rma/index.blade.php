@extends('temas.v2.layout')

@php
    // Achado confirmado no LEGACY-RUNTIME (design.md "Mecanismo de navegação por
    // tema"): os 7 painéis (#inicio, #pesquisar, #novo_rma, #entrada, #recebido,
    // #encaminhado, #concluido) vêm TODOS renderizados no mesmo HTML; a troca é o
    // plugin de abas nativo do Bootstrap 3 (`data-toggle="tab"`), sem AJAX/reload.
    //
    // CP23 — achado que corrige um bug real: as 4 abas por status eram um recorte do
    // resultado de BUSCA (`$rmas`), que só tem conteúdo quando há termo digitado —
    // ficavam vazias por padrão, diferente do Legacy (`page/{entrada,recebido,
    // encaminhado,concluido}.php` são listagens próprias, sempre cheias). Corrigido:
    // `RmaController@index` agora entrega `$porStatusV2` com as 4 listagens reais
    // (`PainelDeStatus::EntradaSomente/RecebidoSomente/Encaminhados/Concluidos`).

    // CP22 (paridade visual V2) — `15.8.1/page/inicio.php` inclui só 10 dos 11
    // `subp/listar_*.php` que `ListarGruposDeAlertas` compõe (lido por inteiro nesta
    // sessão, sem mais includes depois do último) — "Urgência por valor" não aparece
    // na Home do TEMA V2 (pode ser exclusivo de outra tela/do TEMA V1, não
    // verificado aqui, fora de escopo). Ordem e texto do título conferidos linha a
    // linha contra cada `subp/listar_*.php` (`<li ...>TITULO:</li>`) — os títulos de
    // `ListarGruposDeAlertas` são descritivos, não o texto literal do legado.
    // Reordenação/relabel só nesta view (não em `ListarGruposDeAlertas`, que também
    // serve `PainelDeAlertasController` e o TEMA V1 — mudar a ordem/chave lá sem
    // verificar os dois primeiro seria risco desnecessário).
    $ordemHistoricaCentroDeAvisosV2 = [
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
    $gruposCentroDeAvisosV2 = [];
    foreach ($ordemHistoricaCentroDeAvisosV2 as $chaveInterna => $tituloLiteral) {
        $gruposCentroDeAvisosV2[$tituloLiteral] = $grupos[$chaveInterna] ?? collect();
    }
@endphp

@section('conteudo')
    {{-- CP17 (`docs/produto/plano-execucao-paridade-v2.md`) — a `<ul class="nav
    nav-tabs">` histórica virou parte do header único (`temas/v2/layout.blade.php`),
    fonte real `legacy-source/15.8.1/inc/menu.php`: os 9 itens (Inicio…Logout) são um
    componente do layout inteiro, não desta tela — mesmo tratamento já dado ao TEMA
    V1. --}}
    <div class="tab-content">
        <div id="inicio" class="tab-pane fade in active">
            {{-- `page/inicio.php` = `include("page/pesquisar.php")` por inteiro, depois
            separador + Centro de Avisos — não é uma versão simplificada, é a MESMA
            composição da aba #pesquisar (achado confirmado lendo o PHP fonte
            completo nesta sessão; a suposição anterior de "busca simplificada" nesta
            aba estava errada, corrigida no CP20). --}}
            @include('temas.v2.rma._pesquisar_conteudo')

            <div style="clear:both;"></div>
            <img src="{{ asset('images/tema-v2/separador2.png') }}" alt="Separador" title="Separador" height="40" style="margin-top:50px;float:right;">
            <div style="clear:both;"></div>

            {{-- `rma._centro_de_avisos` já renderiza o ícone/título/hr do Centro de
            Avisos (`lembrete.png`/"CENTRO DE AVISOS E RELATORIOS"/`hrup`) — não
            duplicar aqui. --}}
            @include('rma._centro_de_avisos', [
                'grupos' => $gruposCentroDeAvisosV2,
            ])
        </div>

        <div id="pesquisar" class="tab-pane fade">
            @include('temas.v2.rma._pesquisar_conteudo')
        </div>

        <div id="novo_rma" class="tab-pane fade">
            <p><a href="{{ rota_tema('rmas.create') }}" class="btn formSubmit">Abrir novo RMA</a></p>
        </div>

        <div id="entrada" class="tab-pane fade">
            @include('temas.v2.rma._tabela_entrada', ['registros' => $porStatusV2['entrada']])
        </div>

        <div id="recebido" class="tab-pane fade">
            @include('temas.v2.rma._tabela_recebido', ['registros' => $porStatusV2['recebido']])
        </div>

        <div id="encaminhado" class="tab-pane fade">
            @include('temas.v2.rma._tabela_encaminhado', ['registros' => $porStatusV2['encaminhado']])
        </div>

        <div id="concluido" class="tab-pane fade">
            @include('temas.v2.rma._tabela_concluido', ['registros' => $porStatusV2['concluido']])
        </div>
    </div>
@endsection
