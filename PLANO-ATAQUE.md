# Plano de ataque - CellSystem RMA

Ultima atualizacao: 2026-09-09 (America/Sao_Paulo). Status no padrao canonico
`[ ]`/`[R]`/`[x]` (ver `docs/operacao/padrao-status-plano.md`). Regra de texto:
nunca usar hifen longo, sempre hifen simples (ver `docs/operacao/regra-hifen.md`).
Handoff: `docs/produto/handoff-sessao-2026-09-09.md`.

## AGORA - UI/paridade corrente

- [R] UI-09 - Consistencia de formularios, selects e controles (V1/V2).
  Investigacao: `docs/produto/2026-09-09-investigacao-consistencia-ui-formularios-controles.md`
  (UI-AUD-001..018), commit `16c1913`.
  - [x] UI-09.1 - Auditar superfícies (UI-AUD-001..018).
  - [x] UI-09.2 - Commit documental da Fase A (`16c1913`).
  - [x] UI-09.3 - Cursor de selects + pontuacao operacional (onda C1).
  - [x] UI-09.4 - Geometria dos formularios V1 (onda C2).
  - [x] UI-09.5 - Gestao de usuarios V1/V2 (onda C3).
  - [x] UI-09.6 - Controle V1 (onda C4/UI-05).
  - [x] UI-09.7 - Titulo unico RCD/RPEC/RMPE no V1 (onda C5).
  - [x] UI-09.8 - Dropdown V2 + ciclo de vida (onda C6).
  - [R] UI-09.9 - Regressao dirigida 9/9 e PHPUnit 515/1421; regressao ampla
    depende de Legacy `:8094` de pe e execucao serial.
  - [ ] UI-09.10 - C7: varredura residual e viewports (1366/1440/1600; 390/768 V2)
    + regressao UI-08/print.

- [R] UI-05 - Controle V1.
  - [x] UI-05.1 - Causa confirmada e documentada.
  - [x] UI-05.2 - Alinhamento corrigido.
  - [x] UI-05.3 - Overflow local validado.
  - [ ] UI-05.4 - Regressao viewport/Playwright ampla.

## AGORA - Frente de investigacao do Tema V3 (sem implementacao)

- [R] EVO-UX-001 - Tema V3 / Console Operacional Adaptativa.
  Arquitetura investigada e especificada; implementacao ainda nao liberada.
  Referencia: `docs/arquitetura/2026-09-09-refinamento-evo-ux-001-tema-v3-console-operacional.md`.
  - [x] T3-00 - Ler e reconciliar INV-RMA-08/10 e EVO-UX-001.
  - [x] T3-01 - Matriz V1 x V2 -> V3.
  - [x] T3-02 - Arquitetura de informacao.
  - [x] T3-03 - Mapa de telas.
  - [x] T3-04 - Wireframes textuais.
  - [x] T3-05 - Contrato de selecao explicita V1/V2/V3 (documental).
  - [x] T3-06 - Spike documental Tailwind x CSS semantico.
  - [x] T3-07 - OpenSpec `tema-v3-console-operacional`.
  - [ ] T3-08 - Implementar shell oculto.
  - [ ] T3-09 - Dashboard.
  - [ ] T3-10 - RMAs listagem.
  - [ ] T3-11 - RMA detalhe.
  - [ ] T3-12 - RMA formularios.
  - [ ] T3-13 - Parceiros.
  - [ ] T3-14 - Usuarios/admin.
  - [ ] T3-15 - Relatorios.
  - [ ] T3-16 - Secundarias.
  - [ ] T3-17 - Selecao explicita de tema (implementacao).
  - [ ] T3-18 - Mobile/browser.
  - [ ] T3-19 - Acessibilidade.
  - [ ] T3-20 - Performance.
  - [ ] T3-GATE - Liberar Tema V3 no seletor.

## DEPOIS

- [ ] UI-09.10/C7 + UI-08 - Varredura residual e regressao browser/print final.
- [R] UI-07 - Remover views orfas com zero consumidor.
  - [x] UI-07.1 - Inventario de candidatas (UI-AUD-016).
  - [ ] UI-07.2 - Remocao com prova de zero consumidor e teste.
- [ ] P7 - UX-003: Encaminhar por selecao validada, seguido de PAR-RMA-008.
- [ ] P8 - Anotacoes V2 dedicada, confirmacao de remocao, UX-001/UX-004.
- [ ] P9 - Inventario de rotas/plugins residuais.
- [ ] P10 - Reconciliacao documental (checklist, paridade, matriz temas, roteiro).
- [ ] P11 - Regressao funcional por fluxo.
- [ ] P12 - Playwright quatro quadrantes em `tests/Browser/Fluxos/`.
- [ ] P13 - PHPUnit completo + build final (baseline real 515/1421).
- [ ] P14 - Fechamento/handoff da paridade.
- [ ] EVO-SAAS-001 - S10.4/S11.4/S13.2/S14.

## DEPENDENCIAS

C7/UI-08 dependem das ondas C1-C6 (concluidas) e de sessao com viewports e Legacy
de pe. T3 depende de: frente UI/paridade segura, matriz funcional sem lacuna,
OpenSpec aprovado e gate de qualidade. P7 depende de ler regra de encaminhamento
Legacy e manter polymorphic tenant. P10 depende de P5/P6/P7/P8 fechados. P13
depende de P7-P12.

## DECISOES ADIADAS

- [R] DEC-01 - Identificador operacional de RMA (UI-06): investigado; decisao
  pendente do dono.
- [R] DEC-02 - FLOW-EXT-003/004/006 (avisar/enviar e-mail/representantes):
  decisao de produto registrada.
- [ ] DEC-03 - PAR-RMA-008 (Concluir/legado): diagnostico insuficiente.
- [R] DEC-04 - UX-002/remocao definitiva e views orfas (UI-07).
- [R] DEC-05 - Implementacao do Tema V3: liberada somente em gate futuro
  ([GATE-PENDENTE] nesta rodada).

## CRITERIO DE SAIDA

UI-09: auditoria commitada; bugs confirmados corrigidos e testados; C7/UI-08
concluidos; docs reconciliadas. EVO-UX-001: especificacao completa e commitada;
V3 nao implementado nem selecionavel. Paridade: P0-P14 fechados sem bloqueio, full
suite e Playwright quatro quadrantes verdes.

## NAO FAZER AINDA

Editar Legacy; reproduzir bugs/rotas mortas; relaxar Policy/tenant; implementar
Tema V3; push/PR/merge; remover views orfas antes da onda UI-07; aplicar replace
global de hifen longo; marcar `[x]` sem evidencia real.
