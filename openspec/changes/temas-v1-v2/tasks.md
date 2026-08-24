# Tasks — Apresentação (Temas V1/V2)

- [ ] **Pré-requisito de implementação (não de planejamento):** rodar LEGACY-RUNTIME
      (`docker ps` em `08.24.4-legacy-gerenciador-de-rma`) e resolver as 2 pendências
      do `proposal.md` (mecanismo de âncoras TEMA V2, RN-11 em TEMA V1) antes de
      escrever qualquer view final
- [ ] `resources/sass/temas/_v1.scss`, `_v2.scss`, `_compartilhado.scss`
- [ ] `app/Http/Middleware/ResolverTemaAtivo.php`
- [ ] Helper `view_do_tema()` (ou equivalente) para resolução de view por tema
- [ ] `resources/views/temas/v1/layout.blade.php` + árvore completa (rma/parceiros/identidade)
- [ ] `resources/views/temas/v2/layout.blade.php` + árvore completa
- [ ] `routes/tema-v1.php`, `routes/tema-v2.php`
- [ ] Registrar middleware no `bootstrap/app.php`/`Kernel`
- [ ] `tests/Feature/Temas/RenderizaTemaV1Test.php`
- [ ] `tests/Feature/Temas/RenderizaTemaV2Test.php`
- [ ] `tests/Browser/ComparacaoVisualTemaV1Test.php` (Playwright, 390/768/1440)
- [ ] `tests/Browser/ComparacaoVisualTemaV2Test.php` (Playwright, 390/768/1440)
- [ ] `sail test` verde
- [ ] Capturar screenshots reais (PNG) — pendência já registrada em
      `checklist-master-v3.md` Parte 5
- [ ] Atualizar `docs/legado/inventario-visual-tema-v1.md` com o resultado da
      investigação de RN-11 (resolvida ou ainda `[DÚVIDA]`)
- [ ] Atualizar `docs/produto/checklist-master-v3.md` (Fase 8 concluída, Parte 2
      pendência de granularidade resolvida)
- [ ] Atualizar `docs/produto/paridade-v2-v3.md` — paridade visual, todos os
      `LEG-RMA-NNN` já `PARIDADE` funcional ganham a coluna visual preenchida
- [ ] Commit `#F8 - Apresentacao (Tema V1 + Tema V2 fieis)`
