# Tasks — Apresentação (Temas V1/V2)

- [x] **Pré-requisito de implementação:** rodar LEGACY-RUNTIME (`docker ps` em
      `08.24.4-legacy-gerenciador-de-rma`) e resolver as 2 pendências originais
      (mecanismo de âncoras TEMA V2, RN-11 em TEMA V1) — **feito em 2026-08-24 por
      inspeção direta** (HTML/CSS/JS reais com sessão autenticada, não só os
      inventários estáticos). Resultado em `design.md`/`proposal.md`. **2 pendências
      novas surgiram dessa inspeção** (fonte Open Sans nunca carrega; comportamento
      pós-login assimétrico) — ver task de decisão de produto abaixo, antes de
      implementar view final.
- [x] **Decisões de produto RESOLVIDAS pelo usuário em 2026-08-25 (ver `design.md`/
      `proposal.md`):** (a) fonte Open Sans do TEMA V2 — **reproduzir o fallback**
      (`Arial`/`Fira Sans`, o que renderiza hoje), NUNCA self-hostar Open Sans de
      verdade (mudaria a tipografia percebida); (b) comportamento pós-login —
      **unificado, sempre respeita `tema_preferido`**, sem exceção. Não existe login
      próprio de TEMA V1 separado do gateway na V3.
- [x] `resources/sass/temas/_compartilhado.scss` — portar de verdade `pattern/15.9.7.css`
      (classes `TrInconformidade`/`TrUrgente`/`TrZebrada1/2`/`TrSemGarantia1/2`,
      `.breadcrumb`, `.centrodeavisos`, `.formSelect`, `.designedby`, `.pmo`), não só a
      variável `$cor-alerta`
- [x] `resources/sass/temas/v1.scss` (sem dependência de framework CSS — TEMA V1 não
      usa Bootstrap; usar `$largura-fixa-tema-v1` nomeada, nunca `984px` solto) e
      `v2.scss` (Bootstrap 3.3.5 real — ver desvio registrado no `log-implementacao-v3.md`:
      `bootstrap@3.3.5` só publica LESS/CSS pré-compilado, não SCSS, então é `@import`
      do CSS de distribuição real, escopado só a este entry point; usar o mapa
      `$breakpoints-tema-v2` nomeado, nunca os 6 valores de `css/media.php` soltos)
- [x] `resources/js/temas/v1.js`, `v2.js` (v2 importa o plugin de abas do Bootstrap 3,
      `data-toggle="tab"`, para reproduzir a troca client-side sem reload do dashboard)
- [x] `vite.config.js` com 2 `input` distintos (`temas/v1.js`, `temas/v2.js`) gerando 2
      bundles CSS/JS separados
- [x] Self-hostar Fira Mono/Fira Sans via Vite (hoje vêm de CDN externo — Google Fonts +
      `code.cdn.mozilla.net` — trocar por asset local, arquivo `.ttf` já existe em
      `framework/fonts/Fira_Mono/`). **Open Sans NÃO é self-hostada** — decisão
      resolvida é reproduzir o fallback (`Arial`/`Fira Sans`), nenhum arquivo/CSS de
      Open Sans é portado para a V3. (Nota: só Fira Mono foi self-hostada via
      `@font-face`; "Fira Sans" já é coberta pelo fallback funcional real observado, ver
      "Fontes" no `design.md` — nenhuma regra CSS visível do legado depende de um
      arquivo `.ttf` de Fira Sans próprio.)
- [x] `app/Http/Middleware/ResolverTemaAtivo.php` — reproduz o tema ativo por
      `tema_preferido` (equivalente a `usuario.app` no legado) ou pelo prefixo de rota
      (`v1.`/`v2.`, usado por QA/testes de smoke)
- [x] Helper `view_do_tema()` (+ `rota_tema()`, `classe_css_de_alerta()`) em
      `app/Support/view_do_tema.php` para resolução de view/rota/classe CSS por tema
- [x] `resources/views/identidade/login.blade.php` — **único ponto de login** (não é
      nem V1 nem V2 — visual próprio), usado por qualquer usuário; redirect pós-login
      sempre respeita `tema_preferido` (decisão resolvida)
- [x] `resources/views/temas/v1/layout.blade.php` + árvore completa (rma/parceiros/
      identidade) — **SEM** `identidade/login.blade.php` próprio (decisão resolvida:
      não existe login embutido separado do gateway na V3)
- [x] `resources/views/temas/v2/layout.blade.php` + árvore completa (rma/parceiros/
      identidade) — SEM `identidade/login.blade.php` próprio, usa o gateway
- [x] `resources/views/temas/v2/rma/index.blade.php` — um único painel com os 7
      "tab-panes" (início/pesquisar/novo_rma/entrada/recebido/encaminhado/concluído),
      dados de todas as abas já resolvidos pelos Controllers/casos de uso existentes
      (partição por `$registro->status` do MESMO resultado já buscado por
      `RmaController@index` — nenhuma regra de negócio nova; ver desvio de escopo
      registrado no `log-implementacao-v3.md` sobre a aba "início"/"novo_rma")
- [x] `routes/tema-v1.php`, `routes/tema-v2.php`
- [x] Registrar middleware no `bootstrap/app.php` (Laravel 13, `appendToGroup('web', ...)`,
      não `Kernel.php`)
- [x] `tests/Feature/Temas/RenderizaTemaV1Test.php`
- [x] `tests/Feature/Temas/RenderizaTemaV2Test.php`
- [x] `tests/Browser/ComparacaoVisualTemaV1Test.spec.ts` (Playwright REAL — Chromium
      instalado com `--with-deps` dentro do container `laravel.test`, roda de verdade;
      390/768/1440 — TEMA V1 não tem NENHUM `@media` query no legado; o teste confirma
      que a largura COMPUTADA (`getComputedStyle`, não `getBoundingClientRect`, que
      inclui padding) continua fixa em 984px nos 3 breakpoints). Nome do arquivo é
      `.spec.ts` (runner Playwright), não `.php` como o texto original do task sugeria
      — um arquivo `.php` não seria descoberto pelo `npx playwright test`; ver desvio
      registrado no `log-implementacao-v3.md`.
- [x] `tests/Browser/ComparacaoVisualTemaV2Test.spec.ts` (Playwright REAL — TEMA V2 tem
      breakpoints PRÓPRIOS em `15.8.1/css/media.php`: 568/800/992/1080/1280/1366px; a
      asserção em cada um dos 3 breakpoints de QA usa a largura de `.container`
      esperada pela regra `min-width` mais próxima abaixo, lida de
      `tests/Browser/Support/breakpoints-tema-v2.json` (mantido manualmente em sincronia
      com `$breakpoints-tema-v2`/`$larguras-container-tema-v2` do Sass — sem gerador
      automático nesta fase, desvio pragmático registrado no `log-implementacao-v3.md`),
      nunca redigitados como literais no teste. 390px corretamente pulado
      (`test.skip`) por estar abaixo do menor breakpoint do tema (568px).
- [x] `sail test` verde (263/263: 250 das Fases 1-7 + 13 novos smoke de tema)
- [x] Capturar screenshots reais (PNG) — `docs/produto/screenshots-fase8/` (9 capturas,
      via `tests/Browser/CapturarScreenshotsTemas.spec.ts`) — fecha a pendência já
      registrada em `checklist-master-v3.md` Parte 5
- [x] Atualizar `docs/legado/inventario-visual-tema-v1.md`/`-v2.md` e
      `openspec/changes/temas-v1-v2/{proposal,design}.md` com o resultado da
      investigação de RN-11 e do mecanismo de âncoras (ambos resolvidos, 2026-08-24)
- [x] Atualizar `docs/produto/checklist-master-v3.md` (Fase 8 concluída, Parte 2
      pendência de granularidade resolvida)
- [x] Atualizar `docs/produto/paridade-v2-v3.md` — paridade visual, todos os
      `LEG-RMA-NNN` já `PARIDADE` funcional cobertos pelo escopo explícito da Fase 8
      (login/RMA/parceiros/identidade) ganham a nota de paridade visual; itens fora
      desse escopo (alertas/crédito/relatórios/histórico/logística) permanecem sem
      estilização por tema, registrado como não-bloqueante
- [x] Commit `#F8 - Apresentacao (Tema V1 + Tema V2 fieis)`
