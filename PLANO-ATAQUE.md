# Plano de ataque — CellSystem RMA

Última atualização: 2026-09-09 (America/Sao_Paulo). Referência da frente: anexo
“Paridade total dirigida por fluxos” (proposal em
`openspec/changes/paridade-fluxos-legado-v3/`). Handoff:
`docs/produto/handoff-sessao-2026-09-09.md`.

## AGORA

Frente ativa: **Paridade total dirigida por fluxos (Legacy executável × V3)**.
- Concluído: P0 (mapa/matriz/OpenSpec), P1 (troca de tema V1), P4
  (descobribilidade de RPEC/RMPE + auditoria de menus), P5 (detalhe de parceiro +
  RMAs, A×B) e P6 (busca por contrapartes, A×B).
- Suíte: **515 testes / 1421 assertions PHPUnit verdes**; Playwright tema + ações
  verdes; Vite build verde.
- Próximo item: **P7 — UX-003: Encaminhar por seleção validada** (sem ID técnico cru),
  seguido da investigação PAR-RMA-008 (Concluir/legado).

## DEPOIS

- P8 anotações/remoção/policy/flash; P9 rotas residuais; P10 reconciliação; P11–P14.
- FRONT-003 rastreável: UI-05 (Controle V1), UI-06, UI-07, UI-08.
- EVO-SAAS-001: S10.4/S11.4/S13.2/S14.

## DEPENDÊNCIAS

- P7 depende de ler regra de encaminhamento Legacy e de manter polymorphic tenant.
- P10 depende de P5/P6/P7/P8 fechados.
- P13 depende de P7–P12.

## DECISÕES ADIADAS

- FLOW-EXT-003/004/006 (avisar/enviar e-mail/representantes) — decisão de produto.
- UI-06 identificador RMA; UX-002 confirmação de remoção (parte P8); PAR-RMA-008.

## CRITÉRIO DE SAÍDA

P0–P14 fechados sem bloqueio, docs reconciliadas, full suite e Playwright quatro
quadrantes verdes, handoff atualizado.

## NÃO FAZER AINDA

Editar Legacy; reproduzir bugs/rotas mortas; relaxar Policy/tenant; iniciar Tema V3;
push/PR/merge.
