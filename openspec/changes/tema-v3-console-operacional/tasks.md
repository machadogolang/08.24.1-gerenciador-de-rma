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
- [x] T3-12 - RMA formularios (secoes, 2 colunas por relacao).
  Evidencia: 57f1c13, tests/Feature/Temas/FormulariosRmaV3Test.php,
  tests/Browser/FormulariosRmaV3.spec.ts.
- [x] T3-13 - Parceiros (lista/busca/detalhe/form em secoes).
  Evidencia: rotas v3.parceiros.*, views temas/v3/parceiros/{index,show,_form},
  tests/Feature/Temas/ParceirosV3Test.php (7/7 verdes),
  tests/Browser/ParceirosV3.spec.ts (1/1 verde).
- [x] T3-14 - Usuarios/admin (listagem e acoes contextuais).
  Evidencia: rotas v3.identidade.usuarios.*, views temas/v3/identidade/usuarios{,-novo},
  tests/Feature/Temas/UsuariosV3Test.php (6/6 verdes),
  tests/Browser/UsuariosV3.spec.ts (1/1 verde).
- [x] T3-15 - Relatorios (hub + RCD/RPEC/RMPE).
  Evidencia: rotas v3.rmas.relatorios.*, views temas/v3/rma/relatorios/{index,_menu,rcd,rpec,rmpe},
  tests/Feature/Temas/RelatoriosV3Test.php (5/5 verdes),
  tests/Browser/RelatoriosV3.spec.ts (1/1 verde).
- [x] T3-16 - Secundarias (alertas, historicos, logistica, ajuda, perfil).
  Evidencia: rotas v3.* (alertas, historico, historico-de-acesso, logistica, ajuda, perfil, creditos),
  views temas/v3/rma/{alertas,historico,logistica,ajuda,credito} e temas/v3/identidade/{historico-de-acesso,perfil},
  tests/Feature/Temas/SecundariasV3Test.php (7/7 verdes),
- [x] T3-17 - Selecao explicita de tema (enum/controller/UI).
  Evidencia: `DefinirTemaPreferido`, atualizacao de `TemaPreferidoController` com suporte a `tema` explicito (v1/v2/v3),
  seletor de temas no perfil V3 (`temas/v3/identidade/perfil.blade.php`),
  `tests/Feature/Temas/SelecaoExplicitaTemaTest.php` (7/7 verdes),
  `tests/Browser/SelecaoExplicitaTema.spec.ts` (1/1 verde).
- [x] T3-18 - Mobile/browser (viewports e cartoes).
  Evidencia: `tests/Browser/MobileAdaptativoV3.spec.ts` (2/2 verdes cobrindo 375x667, 768x1024 e 1440x900),
  validacao de cartoes em RMAs, usuarios, parceiros e formularios sem overflow horizontal,
  drawer mobile com abertura/fechamento por tecla ESC.
- [x] T3-19 - Acessibilidade (teclado, ARIA, alvo 44px, estados).
  Evidencia: `tests/Browser/AcessibilidadeV3.spec.ts` (1/1 verde),
  alvos de toque >= 44px em links, botoes e inputs de formulario,
  foco visivel via `:focus-visible`, navegacao por Tab,
  `aria-current="page"` no link ativo e gestao de foco com tecla ESC no drawer.
- [ ] T3-20 - Performance (bundle, consultas, build).
- [ ] T3-GATE - Liberar Tema V3 no seletor com criterios verdes.
