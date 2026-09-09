# Handoff de sessão — CellSystem RMA V3

Data do checkpoint: 2026-09-09. Substitui o conteúdo anterior como ponto de partida.
Fonte de status: `PLANO-ATAQUE.md`.

## Estado geral

- Frente ativa: **Paridade total dirigida por fluxos** (Legacy executável × V3).
- P0 (baseline + mapa + matriz + OpenSpec) e P1 (troca de tema no menu V1) concluídos.
- FRONT-003 (UI-01..UI-04) e EVO-SAAS-001 permanecem rastreáveis e abertos.
- Suíte PHPUnit corrente: **493 testes / 1318 assertions** (baseline) + 6 testes
  Feature P1 verdes. Playwright: fluxo de tema verde + contrato de ações verde.
- PUSH NÃO REALIZADO; origin/main: `75c110d`.

## Repositórios

- V3 inicial da rodada: `a254d58` (local) / `75c110d` (origin; estado mais novo usado).
- Legacy usado como referência: `f83542c` (`08.24.4-legacy-gerenciador-de-rma`,
  somente leitura; working tree tem `dockerfile.map.md` não rastreado e não foi tocado).

## Entregas desta rodada

1. `ed30fbc` — `#FRONT-RMA - Restaura troca de tema no menu do Tema V1`
   - Item "Trocar p/ 15.8.1" no painel/session V1, mesma posição do Legacy
     (`menuright.php`), POST/CSRF, visual de item de menu.
   - Feature `TrocarTemaMenuV1Test` (3 papéis × item e persistência).
   - Playwright `tests/Browser/Fluxos/Tema.spec.ts`: V1→V2, relogin, V2→V1, relogin.
2. `7472557` — `#DOC-RMA - Mapeia fluxos Legacy V1 V2 contra V3`
   - `docs/produto/2026-09-09-mapa-fluxos-legado-v3.md` (FLOW-RMA-001…100 +
     FLOW-EXT-001…006).
   - `docs/produto/2026-09-09-matriz-cobertura-legacy-v3.md` (38 superfícies).
   - OpenSpec `openspec/changes/paridade-fluxos-legado-v3/` e `PLANO-ATAQUE.md`.

## Mapa e classificação (resumo)

- Fluxos mapeados: 42 `FLOW-RMA-*` + 6 `FLOW-EXT-*`.
- Já conformes: identidade, cadastro parceiros/RMA, ciclo, crédito, relatórios,
  alertas, filas, históricos, logística.
- Gaps reais: detalhe de parceiro/RMAs (C), busca contrapartes (C parcial),
  anotações V2 dedicada (C), UX de destinatário (B), troca de tema V1 (D — corrigido).
- Rotas extras: `anotacoes` real (C), `avisar`/`enviar_email` (I), `representantes`
  (I), `marcarcomo` (I/J), `pomodoro` (J — rota morta, sem página).
- Bug Legacy descartados: arquivar V1, senha V2, hard deletes, busca SQL insegura,
  rota `retornou`.

## Próximo item exato

**P5 — detalhe de parceiro e RMAs associados** (V1/V2, tenant atual, policy e links)
ou, se a varredura P4 indicar dependência de navegação, P4 primeiro. Na dúvida, o
próximo passo é P4 (menu item a item Legacy × V3) por ser pré-requisito documental de
descobribilidade.
