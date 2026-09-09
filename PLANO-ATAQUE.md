# Plano de ataque — CellSystem RMA

Última atualização: 2026-09-09 (America/Sao_Paulo). Referência da frente: anexo
“Paridade total dirigida por fluxos” (proposal em
`openspec/changes/paridade-fluxos-legado-v3/`). Handoff:
`docs/produto/handoff-sessao-2026-09-09.md`.

## AGORA

Frente transversal ativa: **FRONT-003/UI-09 — consistência visual e de interação
de formulários, selects e controles (V1 e V2)**. Investigação canônica:
`docs/produto/2026-09-09-investigacao-consistencia-ui-formularios-controles.md`
(UI-AUD-001 a UI-AUD-018). Fase A concluída com commit exclusivamente documental;
correções em ondas pequenas C1–C7 (cursor/pontuação, parceiros V1, usuários
V1/V2, Controle/UI-05, relatórios, dropdown V2, varredura residual).

Frente anterior de paridade permanece registrada: P0/P1/P4/P5/P6 concluídos;
próximo item dela continua **P7 — UX-003: Encaminhar por seleção validada**,
seguido de P8–P14 — a executar depois ou em paralelo conforme decisão do dono.

- Suíte referenciada antes desta rodada: **515 testes / 1421 assertions PHPUnit
  verdes**; Playwright tema + ações verdes; Vite build verde (contagem real será
  reconferida na Fase QA desta frente).

## DEPOIS

- Ondas C1–C7 da frente UI-09 (ver documento de investigação).
- Paridade: P7–P14 (UX-003/PAR-RMA-008 primeiro).
- FRONT-003 rastreável: UI-05 (Controle V1), UI-06, UI-07 (órfãs, inclui
  candidatas UI-AUD-016), UI-08, UI-09.
- EVO-SAAS-001: S10.4/S11.4/S13.2/S14.

## DEPENDÊNCIAS

- Ondas C1–C7 dependem apenas do commit documental da Fase A e de testes
  dirigidos por bug.
- P7 depende de ler regra de encaminhamento Legacy e de manter polymorphic tenant.
- P10 depende de P5/P6/P7/P8 fechados.
- P13 depende de P7–P12.

## DECISÕES ADIADAS

- FLOW-EXT-003/004/006 (avisar/enviar e-mail/representantes) — decisão de produto.
- UI-06 identificador RMA; UX-002 confirmação de remoção (parte P8); PAR-RMA-008.
- UI-07: remoção de views órfãs (candidatas em UI-AUD-016) só na onda própria.

## CRITÉRIO DE SAÍDA

UI-09: auditoria commitada; bugs confirmados (UI-AUD-001..011 e 013) corrigidos e
testados; docs reconciliadas. Paridade: P0–P14 fechados sem bloqueio, full suite e
Playwright quatro quadrantes verdes, handoff atualizado.

## NÃO FAZER AINDA

Editar Legacy; reproduzir bugs/rotas mortas; relaxar Policy/tenant; iniciar Tema V3;
push/PR/merge; remover views órfãs antes da onda UI-07; aplicar replace global de
hífen longo.
