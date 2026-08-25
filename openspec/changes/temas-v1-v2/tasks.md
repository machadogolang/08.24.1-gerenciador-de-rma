# Tasks — Apresentação (Temas V1/V2)

- [x] **Pré-requisito de implementação:** rodar LEGACY-RUNTIME (`docker ps` em
      `08.24.4-legacy-gerenciador-de-rma`) e resolver as 2 pendências originais
      (mecanismo de âncoras TEMA V2, RN-11 em TEMA V1) — **feito em 2026-08-24 por
      inspeção direta** (HTML/CSS/JS reais com sessão autenticada, não só os
      inventários estáticos). Resultado em `design.md`/`proposal.md`. **2 pendências
      novas surgiram dessa inspeção** (fonte Open Sans nunca carrega; comportamento
      pós-login assimétrico) — ver task de decisão de produto abaixo, antes de
      implementar view final.
- [ ] **Decisão de produto pendente (perguntar ao usuário antes de implementar):**
      (a) fonte Open Sans do TEMA V2 — reproduzir o fallback quebrado (Arial/Fira Sans,
      o que renderiza hoje) ou self-hostar corretamente (arquivos já existem em
      `framework/fonts/OpenSans/` no legado); (b) comportamento pós-login — reproduzir a
      assimetria real (login-gateway respeita `tema_preferido`, login próprio de V1
      ignora) ou unificar.
- [ ] `resources/sass/temas/_compartilhado.scss` — portar de verdade `pattern/15.9.7.css`
      (classes `TrInconformidade`/`TrUrgente`/`TrZebrada1/2`/`TrSemGarantia1/2`,
      `.breadcrumb`, `.centrodeavisos`, `.formSelect`, `.designedby`, `.pmo`), não só a
      variável `$cor-alerta`
- [ ] `resources/sass/temas/v1.scss` (sem dependência de framework CSS — TEMA V1 não
      usa Bootstrap; usar `$largura-fixa-tema-v1` nomeada, nunca `984px` solto) e
      `v2.scss` (`@import` do Bootstrap 3 SCSS escopado só a este entry point, para não
      vazar pro bundle de v1; usar o mapa `$breakpoints-tema-v2` nomeado, nunca os 6
      valores de `css/media.php` soltos — ver `design.md`)
- [ ] `resources/js/temas/v1.js`, `v2.js` (v2 importa o plugin de abas do Bootstrap 3,
      `data-toggle="tab"`, para reproduzir a troca client-side sem reload do dashboard)
- [ ] `vite.config.js` com 2 `input` distintos (`temas/v1.js`, `temas/v2.js`) gerando 2
      bundles CSS/JS separados
- [ ] Self-hostar fontes via Vite (Fira Mono/Fira Sans hoje vêm de CDN externo —
      Google Fonts + `code.cdn.mozilla.net` — trocar por asset local, arquivo `.ttf` já
      existe em `framework/fonts/Fira_Mono/`; Open Sans depende da decisão de produto
      acima)
- [ ] `app/Http/Middleware/ResolverTemaAtivo.php` — reproduzir o redirecionamento
      pós-login por `tema_preferido` (equivalente a `usuario.app` no legado)
- [ ] Helper `view_do_tema()` (ou equivalente) para resolução de view por tema
- [ ] `resources/views/identidade/login.blade.php` — login-gateway COMPARTILHADO (não é
      nem V1 nem V2 — visual próprio, achado desta revisão)
- [ ] `resources/views/temas/v1/layout.blade.php` + árvore completa (rma/parceiros/
      identidade, incluindo `identidade/login.blade.php` PRÓPRIO de V1 — achado: V1 tem
      login embutido redundante ao gateway)
- [ ] `resources/views/temas/v2/layout.blade.php` + árvore completa (rma/parceiros/
      identidade — SEM `identidade/login.blade.php` próprio, usa o gateway)
- [ ] `resources/views/temas/v2/rma/index.blade.php` — um único painel com os 7
      "tab-panes" (início/pesquisar/novo_rma/entrada/recebido/encaminhado/concluído),
      dados de todas as abas já resolvidos pelos Controllers/casos de uso existentes
- [ ] `routes/tema-v1.php`, `routes/tema-v2.php`
- [ ] Registrar middleware no `bootstrap/app.php`/`Kernel`
- [ ] `tests/Feature/Temas/RenderizaTemaV1Test.php`
- [ ] `tests/Feature/Temas/RenderizaTemaV2Test.php`
- [ ] `tests/Browser/ComparacaoVisualTemaV1Test.php` (Playwright, 390/768/1440 —
      TEMA V1 não tem NENHUM `@media` query no legado; o teste correto é confirmar que
      o layout V3 continua fixo/não-responsivo nesses 3 breakpoints, não "consertar" a
      responsividade)
- [ ] `tests/Browser/ComparacaoVisualTemaV2Test.php` (Playwright, 390/768/1440 — TEMA V2
      tem breakpoints PRÓPRIOS em `15.8.1/css/media.php`: 568/800/992/1080/1280/1366px;
      a asserção em cada um dos 3 breakpoints de QA deve usar a largura de `.container`
      esperada pela regra `min-width` mais próxima abaixo, não um valor arbitrário —
      ler esses 6 valores de uma fonte nomeada única compartilhada com
      `$breakpoints-tema-v2` do Sass, ex. `tests/Browser/Support/breakpoints-tema-v2.json`
      gerado do mesmo mapa, nunca redigitados como literais no teste)
- [ ] `sail test` verde
- [ ] Capturar screenshots reais (PNG) — pendência já registrada em
      `checklist-master-v3.md` Parte 5
- [x] Atualizar `docs/legado/inventario-visual-tema-v1.md`/`-v2.md` e
      `openspec/changes/temas-v1-v2/{proposal,design}.md` com o resultado da
      investigação de RN-11 e do mecanismo de âncoras (ambos resolvidos, 2026-08-24)
- [ ] Atualizar `docs/produto/checklist-master-v3.md` (Fase 8 concluída, Parte 2
      pendência de granularidade resolvida)
- [ ] Atualizar `docs/produto/paridade-v2-v3.md` — paridade visual, todos os
      `LEG-RMA-NNN` já `PARIDADE` funcional ganham a coluna visual preenchida
- [ ] Commit `#F8 - Apresentacao (Tema V1 + Tema V2 fieis)`
