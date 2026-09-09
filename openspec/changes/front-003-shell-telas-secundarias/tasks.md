# Tasks - FRONT-003

- [x] UI-01/UI-02 - Crédito com shell V1/V2 e teste de shell (commit `df0d02e`).
- [x] UI-02B - inventário e contrato visual de ações/botões. Investigação:
  `docs/produto/2026-09-09-investigacao-contrato-visual-acoes-botoes.md`; contrato
  `.acao`/`.acao--{primaria,secundaria,perigo,operacional,compacta}` nos SCSS V1/V2
  com cursor/hover/focus-visible (commit `694732d`).
- [x] UI-02C - contrato aplicado em Parceiros V1/V2 (`624e548`), RMA listagens/detalhe
  (`1c1a1e6`), ciclo de vida (`5ce8654`) e formulários/crédito/identidade (`42a4235`).
- [x] UI-02D - varredura residual e prova no browser (`7a17120`); calibração de perigo
  do Tema V1 (`80891b2`); PHPUnit completo 493 testes / 1318 assertions.
- [x] UI-03 - RCD/RPEC/RMPE em shell V1/V2 com `relatorio-print` e filtros `.acao`
  (commit `209309c`).
- [x] UI-04 - alertas (`838c7bf`), históricos de RMA/acesso (`8868a9e`) e logística
  frete/boletins (`75c110d`) em shell V1/V2.
- [ ] UI-05 - Controle V1: alinhamento do bloco representante, overflow local
  (reaberta na auditoria de 2026-09-09, UI-AUD-011; correção commitada em
  22c48a7; aceite final depende da regressão viewport/UI-08).
- [ ] UI-06 - auditoria/decidir `id` × `numero_legado` × `numero_da_empresa`.
- [ ] UI-07 - FRONT-006: remover views genéricas órfãs com zero consumidor
  (candidatas inventariadas em UI-AUD-016).
- [ ] UI-08 - regressão browser V1/V2 e print media.
- [ ] UI-09 - consistência de formulários, selects e controles (V1/V2), transversal.
  Investigação: `docs/produto/2026-09-09-investigacao-consistencia-ui-formularios-controles.md`
  (UI-AUD-001 a 018). Ondas C1–C6 + QA commitados (22c48a7/61222c8/1938247/d1c85dc);
  falta C7 (varredura residual) e regressão multi-viewport.
  Pontuação operacional, parceiros V1, usuários V1/V2, Controle, relatórios,
  dropdown/menu V2 implementados e testados no browser (9/9).
  UI-09.3 FECHADO [x] em 2026-09-09 (PAR-V2-CURSOR-02): item ativo da navbar
  corrigido e varredura A ampliada para detalhe V1/V2 e Novo V2 (4e2294e).
