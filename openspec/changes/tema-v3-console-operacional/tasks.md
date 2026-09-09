# Tasks - Tema V3 / Console Operacional Adaptativa

Status do plano: [ ] pendente, [R] revisado, [x] concluido com evidencia.

## Documentacao e arquitetura

- [x] T3-00 - Ler e reconciliar INV-RMA-08, INV-RMA-10 e EVO-UX-001.
  Evidencia: `docs/arquitetura/2026-09-09-refinamento-evo-ux-001-tema-v3-console-operacional.md`.
- [x] T3-01 - Matriz V1 x V2 -> V3.
  Evidencia: `docs/produto/2026-09-09-matriz-aproveitamento-v1-v2-para-v3.md`.
- [x] T3-02 - Arquitetura de informacao e navegacao por dominios.
  Evidencia: refinamento EVO-UX-001.
- [x] T3-03 - Mapa de telas com origem/destino por rota/capacidade.
  Evidencia: `docs/produto/2026-09-09-mapa-telas-tema-v3.md`.
- [x] T3-04 - Wireframes textuais das 10 superficies principais.
  Evidencia: `docs/produto/2026-09-09-wireframes-tema-v3.md`.
- [x] T3-05 - Contrato de selecao explicita V1/V2/V3 documentado (sem alterar
  enum/controller).
- [x] T3-06 - T3-SPIKE-01: comparacao Tailwind 4 x Sass/CSS semantico.
  Evidencia: `docs/arquitetura/2026-09-09-spike-t3-tailwind-vs-css-semantico-v3.md`.
- [x] T3-07 - OpenSpec do Tema V3 criado (proposal/design/tasks).

## Implementacao futura (gate pendente)

- [x] T3-08 - Fundacao oculta e shell (entry Vite, tokens, layout, drawer/rail).
  Evidencia: commits 06ed7a5/572d20b/1bc15ea e tests/Feature/Temas/RenderizaTemaV3Test.php.
- [x] T3-09 - Dashboard operacional (busca, filas, alertas e contadores acionaveis).
  Evidencia: 572d20b e Playwright TemaV3PrimeiraTranche.
- [x] T3-10 - RMAs listagem (tabela densa + cartoes mobile + filtros).
- [x] T3-11 - RMA detalhe (cabecalho operacional + secoes + historico).
  Evidencia: 5885073/9f2e492 e checkpoint 2026-09-09.
- [ ] T3-12 - RMA formularios (secoes, 2 colunas por relacao).
- [ ] T3-13 - Parceiros (lista/busca/detalhe/form em secoes).
- [ ] T3-14 - Usuarios/admin (listagem e acoes contextuais).
- [ ] T3-15 - Relatorios (hub + RCD/RPEC/RMPE).
- [ ] T3-16 - Secundarias (alertas, historicos, logistica, ajuda, perfil).
- [ ] T3-17 - Selecao explicita de tema (enum/controller/UI). DECISAO-PENDENTE:
  persistir V3 exige matriz completa; QA local pode usar sessao ate T3-GATE.
- [ ] T3-18 - Mobile/browser (viewports e cartoes).
- [ ] T3-19 - Acessibilidade (teclado, ARIA, alvo 44px, estados).
- [ ] T3-20 - Performance (bundle, consultas, build).
- [ ] T3-GATE - Liberar Tema V3 no seletor com criterios verdes.
