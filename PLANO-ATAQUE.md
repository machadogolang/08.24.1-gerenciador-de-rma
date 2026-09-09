# Plano de ataque — CellSystem RMA

Última atualização: 2026-09-09 (America/Sao_Paulo).
Handoff: `docs/produto/handoff-sessao-2026-09-09.md`.

## AGORA

Frente ativa: **Paridade total dirigida por fluxos** (Legacy executável × V3).
- P0 concluído: baseline V3/Legacy confirmadas; mapa de fluxos
  (`2026-09-09-mapa-fluxos-legado-v3.md`) e matriz de cobertura
  (`2026-09-09-matriz-cobertura-legacy-v3.md`); OpenSpec
  `openspec/changes/paridade-fluxos-legado-v3/`.
- P1 concluído: troca V1→V2 pelo menu do Tema V1 (POST/CSRF) + Playwright.
- Próximo item: **P5 — detalhe de parceiro/RMAs** (valor funcional alto, sem decisão
  pendente) ou **P4 — varredura de menus/descobribilidade** se a investigação indicar.
- P2/P3 são reconciliação de evidências já implementadas em FRONT-003 (UI-03/UI-04).

Suíte: **493 testes / 1318 assertions PHPUnit** (baseline) + 6 Feature P1 + 1
Playwright de fluxo de tema verdes.

## DEPOIS

- FRONT-003 permanece rastreável: restam UI-05 (Controle V1), UI-06, UI-07, UI-08.
- EVO-SAAS-001: S10.4/S11.4/S13.2/S14.

## DEPENDÊNCIAS

- P5 depende de Policy/tenant atuais (sem relaxamento) e de testes A×B.
- P6/P7 dependem de investigação dirigida Legacy antes de código.
- P13 depende de P5–P12 fechados.
- UI-08/S13.2 dependem do fechamento das frentes de paridade visual.

## DECISÕES ADIADAS

- FLOW-EXT-003/004 (avisar alguém/enviar e-mail manuais), FLOW-EXT-006
  (representantes como módulo) — exigem decisão/uso real do produto.
- Identificador RMA em crédito/controle (UI-06).
- UX-002 confirmação de remoção — parte de P8, sem nova decisão de segurança.

## CRITÉRIO DE SAÍDA

P0–P14 fechados, classificações A–J documentadas sem bloqueio, docs antigas
reconciliadas, full suite verde, Playwright quatro quadrantes verde e handoff
atualizado.

## NÃO FAZER AINDA

Editar Legacy; reproduzir bugs/rota morta; relaxar Policy/tenant; copiar SQL
inseguro; iniciar Tema V3; push/PR/merge.
