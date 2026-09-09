# Plano de ataque — CellSystem RMA

Última atualização: 2026-09-09 (America/Sao_Paulo).
Handoff: `docs/produto/handoff-sessao-2026-09-09.md`.

## AGORA

Frente ativa **FRONT-003** (shell dos temas em telas secundárias + contrato de
ações/botões):
- UI-01/UI-02 concluído: `/rmas-credito` com shell V1/V2 e teste de regressão.
- UI-02B/C/D concluído: auditoria e contrato visual de ações documentados em
  `docs/produto/2026-09-09-investigacao-contrato-visual-acoes-botoes.md`; Parceiros,
  RMA listagens/detalhe, ciclo de vida e formulários/crédito/identidade aplicam o
  contrato; Playwright dirigido cobre cursor/hover/TAB/semântica.
- Próximo item: **UI-03** — RCD/RPEC/RMPE em shell com impressão limpa; filtros já
  nascem com `.acao`.
- Depois: UI-04 (alertas/históricos/logística), UI-05 (Controle V1), UI-06
  (identificador RMA), UI-07 (FRONT-006), UI-08 (Playwright).

Investigação/OpenSpec: `docs/produto/2026-09-09-investigacao-front-003-shell-telas-secundarias.md`,
`docs/produto/2026-09-09-investigacao-contrato-visual-acoes-botoes.md` e
`openspec/changes/front-003-shell-telas-secundarias/`.

Suíte: **477 testes / 1242 assertions PHPUnit verdes** (após UI-02B/C/D).

## DEPOIS

- Retomar EVO-SAAS-001: S10.4, S11.4, S13.2 (Playwright), S14.
- EVO-SAAS-001 permanece aberto (não fechar por conta da correção visual).

## DEPENDÊNCIAS

- S13.2/Playwright final depende da frente FRONT-003 fechada.
- S14 depende de S13 e das pendências SaaS.
- UI-08 incorpora a prova de ações (`computedStyle.cursor`, hover, TAB, semântica)
  já iniciada em UI-02D.

## DECISÕES ADIADAS

- Identificador a exibir em crédito/controle (id × numero_legado × numero_da_empresa) —
  onda UI-06, sem bloquear shell.
- UX-002 (confirmação de remoção de parceiro) — fora da padronização visual.
- Demais decisões conhecidas de EVO-SAAS-001 permanecem.

## NÃO FAZER AINDA

- Reabrir Fase 8; responsividade global do Tema V1; remover views órfãs antes da
  migração completa; push/merge/reescrita de histórico; design system/biblioteca
  nova para o contrato de ações.
