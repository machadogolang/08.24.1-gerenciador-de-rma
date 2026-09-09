# Plano de ataque — CellSystem RMA

Última atualização: 2026-09-09 (America/Sao_Paulo). Status no padrão canônico
`[ ]`/`[R]`/`[x]` (ver `docs/operacao/padrao-status-plano.md`). Referência da
frente de paridade: proposal em `openspec/changes/paridade-fluxos-legado-v3/`.
Handoff: `docs/produto/handoff-sessao-2026-09-09.md`.

## AGORA

- [R] UI-09 — Consistência de formulários, selects e controles (V1/V2).
  Investigação canônica: `docs/produto/2026-09-09-investigacao-consistencia-ui-formularios-controles.md`
  (UI-AUD-001..018), commit `16c1913`.
  - [x] UI-09.1 — Auditar RCD/RPEC/RMPE, parceiros V1/V2, usuários V1/V2,
    Controle, dropdown V2, ciclo de vida e hífen operacional (UI-AUD-001..018).
  - [x] UI-09.2 — Commit documental da Fase A (`16c1913`).
  - [x] UI-09.3 — Cursor de selects + pontuação operacional (onda C1) —
    `22c48a7`; contrato A no browser.
  - [x] UI-09.4 — Geometria dos formulários V1 (onda C2) — `22c48a7`;
    contrato B no browser (4 tipos de parceiro + classe aplicada à edição RMA).
  - [x] UI-09.5 — Gestão de usuários V1/V2 (onda C3) — `22c48a7`;
    contratos D/D2 no browser.
  - [x] UI-09.6 — Controle V1 (onda C4/UI-05) — `22c48a7`; contrato E no
    browser; falta apenas regressão viewport ampla (UI-05.4/UI-08).
  - [x] UI-09.7 — Título único RCD/RPEC/RMPE no V1 (onda C5) — `61222c8` +
    `1938247`; contrato C no browser.
  - [x] UI-09.8 — Dropdown V2 + controles de ciclo de vida (onda C6) —
    `22c48a7`; contrato F no browser.
  - [R] UI-09.9 — Regressão: spec dirigido 9/9 verde e PHPUnit completo
    515 testes / 1421 assertions verdes; regressão ampla/quadrante ainda
    depende de Legacy `:8094` de pé e execução serial.
  - [ ] UI-09.10 — C7: varredura residual em mais superfícies e viewports
    (1366/1440/1600; 390/768 no V2) + regressão UI-08/print media.

- [R] UI-05 — Controle V1 (mantém identidade própria dentro de FRONT-003).
  - [x] UI-05.1 — Causa do desalinhamento confirmada e documentada
    (UI-AUD-011, commit `16c1913`).
  - [x] UI-05.2 — Alinhamento do bloco representante corrigido (`22c48a7`).
  - [x] UI-05.3 — Overflow local da tabela de arquivados validado no browser
    (contrato E; sem scroll horizontal da página).
  - [ ] UI-05.4 — Regressão viewport/Playwright ampla.

## DEPOIS

- [ ] UI-09.10/C7 + UI-08 — Varredura residual e regressão browser/print final.
- [R] UI-07 — Remover views genéricas órfãs com zero consumidor.
  - [x] UI-07.1 — Inventário de candidatas (UI-AUD-016; views sem consumidor).
  - [ ] UI-07.2 — Remoção na onda própria, com prova de zero consumidor e teste.
- [ ] P7 — UX-003: Encaminhar por seleção validada (sem ID técnico cru),
  seguido de PAR-RMA-008 (Concluir/legado).
- [ ] P8 — Anotações V2 dedicada, confirmação de remoção, UX-001/UX-004.
- [ ] P9 — Inventário de rotas/plugins residuais.
- [ ] P10 — Reconciliação documental (checklist, paridade, matriz temas, roteiro).
- [ ] P11 — Regressão funcional por fluxo.
- [ ] P12 — Playwright quatro quadrantes em `tests/Browser/Fluxos/`.
- [ ] P13 — PHPUnit completo + build final (baseline real 515/1421 nesta sessão).
- [ ] P14 — Fechamento/handoff da paridade.
- [ ] EVO-SAAS-001 — S10.4/S11.4/S13.2/S14 (Trilha B, ondas pequenas).

## DEPENDÊNCIAS

C7/UI-08 dependem apenas das ondas C1–C6 (concluídas) e de uma sessão com
viewports e Legacy de pé. P7 depende de ler a regra de encaminhamento Legacy e de
manter polymorphic tenant. P10 depende de P5/P6/P7/P8 fechados. P13 depende de
P7–P12.

## DECISÕES ADIADAS

- [R] DEC-01 — Identificador operacional de RMA (UI-06): investigação concluída;
  decisão final ainda pendente do dono.
- [R] DEC-02 — FLOW-EXT-003/004/006 (avisar/enviar e-mail/representantes):
  decisão de produto registrada, sem implementação.
- [ ] DEC-03 — PAR-RMA-008 (Concluir/legado): diagnóstico insuficiente para
  direção; precisa de investigação dedicada antes de qualquer marcação `[R]`.
- [R] DEC-04 — UX-002/remoção definitiva e views órfãs (UI-07): caminho
  identificado; execução depende de onda própria.

## CRITÉRIO DE SAÍDA

UI-09: auditoria commitada; bugs confirmados (UI-AUD-001..011 e 013) corrigidos e
testados; C7/UI-08 concluídos; docs reconciliadas; handoff commitado por último.
Paridade: P0–P14 fechados sem bloqueio, full suite e Playwright quatro quadrantes
verdes. Trilha B: somente após os gates formais.

## NÃO FAZER AINDA

Editar Legacy; reproduzir bugs/rotas mortas; relaxar Policy/tenant; iniciar Tema
V3; push/PR/merge; remover views órfãs antes da onda UI-07; aplicar replace global
de hífen longo; marcar `[x]` sem evidência real.
