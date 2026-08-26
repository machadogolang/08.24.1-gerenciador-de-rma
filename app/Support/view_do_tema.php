<?php

use App\Identidade\Dominio\TemaPreferido;
use App\Rma\Dominio\ClasseDeAlerta;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Request;

if (! function_exists('view_do_tema')) {
    /**
     * Fase 8 — resolve `$view` ("rma.index") para a view estilizada do tema ativo
     * (`temas.{v1,v2}.rma.index`). O tema ativo é resolvido por `ResolverTemaAtivo`
     * (middleware) e guardado em `request()->attributes`. Controllers continuam
     * únicos (Fases 1-7) — só a view retornada muda por tema, nenhuma regra de
     * negócio nova entra aqui.
     *
     * @param  array<string, mixed>  $data
     */
    function view_do_tema(string $view, array $data = []): View
    {
        $tema = Request::instance()->attributes->get('temaAtivo') ?? TemaPreferido::V2;

        /** @var TemaPreferido $tema */
        return view("temas.{$tema->value}.{$view}", $data);
    }
}

if (! function_exists('rota_tema')) {
    /**
     * Gera a URL de uma rota respeitando o prefixo de tema da rota ATUAL (`v1.`/`v2.`,
     * ver `routes/tema-{v1,v2}.php`) quando existir, ou a rota sem prefixo (fluxo
     * normal pós-login, `routes/web.php`) caso contrário. Permite que a MESMA view de
     * tema (`temas/v1/rma/index.blade.php` etc.) funcione tanto quando acessada via
     * `/v1/rma` (QA visual/testes) quanto quando resolvida normalmente por
     * `tema_preferido` numa rota sem prefixo — sem duplicar Blade por rota.
     *
     * @param  mixed  $parametros
     */
    function rota_tema(string $nome, mixed $parametros = []): string
    {
        $atual = Request::instance()->route()?->getName() ?? '';

        $prefixo = match (true) {
            str_starts_with($atual, 'v1.') => 'v1.',
            str_starts_with($atual, 'v2.') => 'v2.',
            default => '',
        };

        return route($prefixo.$nome, $parametros);
    }
}

if (! function_exists('classe_css_de_alerta')) {
    /**
     * RN-11 (Fase 5, `Rma::classeDeAlerta()`) — mapeia o enum de domínio `ClasseDeAlerta`
     * (puro, sem CSS) para a classe CSS real por tema, achado confirmado em
     * `page/{entrada,encaminhados,localizar}.php` (TEMA V1) e `subp/pesquisar_rma.php`
     * (TEMA V2): os DOIS temas compartilham a mesma folha `pattern/15.9.7.css`
     * (`_compartilhado.scss`), mas TEMA V1 não usa `TrSemGarantia1/2` como classe
     * própria — "SEM GARANTIA" cai em `TrInconformidade`, enquanto TEMA V2 usa o
     * conjunto completo. `$indice` alterna a zebra neutra (`TrZebrada1`/`TrZebrada2`).
     */
    function classe_css_de_alerta(ClasseDeAlerta $classe, TemaPreferido $tema, int $indice): string
    {
        return match (true) {
            $classe === ClasseDeAlerta::Inconformidade => 'TrInconformidade',
            $classe === ClasseDeAlerta::Urgente => 'TrUrgente',
            $classe === ClasseDeAlerta::SemGarantia && $tema === TemaPreferido::V2
                => $indice % 2 === 0 ? 'TrSemGarantia1' : 'TrSemGarantia2',
            $classe === ClasseDeAlerta::SemGarantia => 'TrInconformidade', // TEMA V1, ver docblock
            default => $indice % 2 === 0 ? 'TrZebrada1' : 'TrZebrada2',
        };
    }
}

if (! function_exists('link_do_contador_v1')) {
    /**
     * CP10 (fase 2 V1, `plano-execucao-paridade-visual-v1-fase2.md`) — cada contador
     * da sidebar (`inc/startpage.php:17-176`) é um `<a>` real no Legacy, não texto
     * estático. Os 4 primeiros (`ENTRADA`/`PENDENTE CREDITO`/`ENCAMINHADO`/
     * `CONCLUIDO`) apontam pras 4 listagens dedicadas (rotas SEM prefixo por tema,
     * mesmo critério já usado em `temas/v2/layout.blade.php` pra Creditos/
     * Relatorios/Controle — `rota_tema()` só resolve `v1.*`/`v2.*`). Os demais
     * (soluções + total) apontam pro Localizar com `solucao` — filtro aditivo que
     * só existe a partir do CP7 (`CriterioDeBusca::solucao()`).
     *
     * `[GAP]` "QUANTIDADE TOTAL DE ITENS": o Legacy usa `solucao=%` (curinga SQL,
     * sem equivalente em `Solucao::tryFrom()`) pra listar TODO o banco sem filtro —
     * a busca V3 não tem modo "listar tudo sem filtro" (evitaria uma tabela sem
     * paginação); aponta pro Localizar vazio (mesmo destino de clicar "Pag.
     * Inicial"), não reproduz a listagem completa.
     */
    function link_do_contador_v1(string $rotulo): string
    {
        return match ($rotulo) {
            'ENTRADA' => route('rmas.entrada'),
            'PENDENTE CREDITO' => route('rmas.aguardando-credito'),
            'ENCAMINHADO' => route('rmas.encaminhados'),
            'CONCLUIDO' => route('rmas.concluidos'),
            'QUANTIDADE TOTAL DE ITENS' => rota_tema('rmas.index'),
            default => rota_tema('rmas.index', ['solucao' => $rotulo]),
        };
    }
}

if (! function_exists('origem_abreviada_v1')) {
    /**
     * VIS-V1-001 — abreviação de apresentação confirmada em
     * `legacy-source/14.6.1/page/{entrada,encaminhados,aguardandocredito,concluidos}.php`:
     * as 4 listagens abreviam "Mercado Livre"/"Leilão"/"Licitação" só nesta camada, sem
     * alterar o valor gravado. Normaliza maiúsculas/acentos antes de comparar — os 4
     * arquivos legados tratam variações de caixa (`"MERCADO LIVRE"` e `"Mercado Livre"`)
     * e de encoding (`Leil�o`/`Licita��o`) como o mesmo caso.
     */
    function origem_abreviada_v1(?string $origem): string
    {
        return match (mb_strtoupper((string) $origem)) {
            'MERCADO LIVRE' => 'M LIVRE',
            'LEILÃO', 'LEILAO' => 'LEILAO',
            'LICITAÇÃO', 'LICITACAO' => 'LICITACAO',
            default => (string) $origem,
        };
    }
}
