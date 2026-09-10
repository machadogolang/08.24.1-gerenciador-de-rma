# Plano de ataque - CellSystem RMA

Ultima atualizacao: 2026-09-09 (America/Sao_Paulo). Status no padrao canonico
`[ ]`/`[R]`/`[x]` (ver `docs/operacao/padrao-status-plano.md`). Regra de texto:
nunca usar hifen longo, sempre hifen simples (ver `docs/operacao/regra-hifen.md`).
Handoff: `docs/produto/handoff-sessao-2026-09-09.md`.

## CONTINUIDADE - Auditoria residual Legacy x V1/V2 (2026-09-09)

O handoff PAR-V2 (`93fd4e4`) e um checkpoint, nao o fim do trabalho. Nova frente
aberta pelo dono: [R] PAR-LEGACY-RESIDUAL-01 - auditoria visual residual V1/V2.
Fonte viva: `docs/produto/2026-09-09-auditoria-residual-paridade-legacy-v1-v2.md`.
Baseline real: origin/main `93fd4e4`; local `b49bda0` (contem T3-12 local, sem
push). Suíte completa atual: 536 testes / 1619 assertions.

Ondas da auditoria (cada uma atomica):
- [x] ONDA A - Shell/navbar/menu/dropdown/footer (reconciliada 2026-09-10; PAR-RES-A-01/A-02 corrigidos; ver auditoria residual).
- [x] ONDA B - Listagens/pesquisa/tabelas/zebra/sidebar (77ec2ce; PAR-RES-001..003
  corrigidos e testados; PAR-RES-004/005 continuam como prova residual).
- [x] ONDA C - Create/show/edit RMA e ciclo (PAR-RES-C-01 corrigido em 1007ad0;
  PHPUnit dirigido 35/35 + Playwright 4/4 verdes em 2026-09-10).
- [x] ONDA D - Parceiros/admin/Controle/usuarios (PAR-RES-D-01..04 corrigidos em
  24c7c3a; PHPUnit dirigido 35/35 + Playwright 4/4 verdes em 2026-09-10).
- [R] ONDA E - Relatorios/Avisos/Anotacoes/secundarias (PAR-RES-E-01..03
  corrigidos em bbea068; PAR-RES-E-04 anotacoes/senha V2 dedicadas concluido em
  2026-09-10; resta PAR-RES-006 Centro de Avisos).
- [ ] ONDA F - Viewport/print/regressao residual.

Apos fechar a paridade residual e as regressoes bloqueantes, o plano segue para
P7/P8/P9/P10/P11/P12/P13/P14 e depois T3-13 em diante (T3-12 ja implementado
localmente em 57f1c13).

## AGORA - Correcao de regressoes/paridade V2 (rodada do dono, 2026-09-09)

- [x] PAR-V2-THEME-01 - Troca V1 <-> V2 em rota prefixada corrigida (9fbeac1);
  testes PHPUnit 4/4 e Playwright 1/1 verdes.
- [x] PAR-V2-NAV-02 - Navbar V2 corrigida (62cbb27): texto centralizado, Logout
  com geometria de `<a>`, breakpoints 12,5%/11,1% iguais ao media.php.
- [x] PAR-V2-DROPDOWN-02 - Dropdown Menu V2 sem moldura/faixa branca (62cbb27);
  li dono dos 25px e item POST com mesma geometria dos links.
- [x] PAR-V2-CURSOR-02 - Fechado em 2026-09-09: item ativo corrigido (62cbb27),
  varredura A inclui detalhe V1/V2 e Novo V2 (4e2294e); 0 selects com cursor
  errado nas rotas cobertas.
- [x] PAR-V2-DETAIL-02 - Detalhe RMA V2 restaurado como formulario operacional
  editavel (127b94d); leitura via Policy com controles desabilitados.
- [x] PAR-V2-NOVO-01 - Novo RMA V2 inline na aba Novo (e46fd3f): grade 3
  colunas, condicional Origem/NF, estoque, CRIAR BD e store moderno.
- [x] PAR-V2-SWEEP-01 - Varredura residual V2 executada nas superfícies
  implementadas + regressao; residuos conhecidos classificados no addendum
  (secao 17), sem BUG-CONFIRMADO residual novo nos fluxos cobertos.

Ondas (cada uma com teste, commit atomico e atualizacao de plano/docs):

- [x] ONDA 1 - PAR-V2-THEME-01 + testes de troca (9fbeac1).
- [x] ONDA 2 - PAR-V2-NAV-02 + PAR-V2-DROPDOWN-02 + PAR-V2-CURSOR-02 (parcial;
  varredura ampla segue em PAR-V2-CURSOR-02).
- [x] ONDA 3 - PAR-V2-DETAIL-02 (A6 funcional, 127b94d).
- [x] ONDA 4 - PAR-V2-NOVO-01 (Novo RMA inline, e46fd3f).
- [x] ONDA 5 - PAR-V2-SWEEP-01 (varredura + regressao; 532/532 PHPUnit e
  Playwright dirigido verde).

## AGORA - UI/paridade corrente

- [R] UI-09 - Consistencia de formularios, selects e controles (V1/V2).
  Investigacao: `docs/produto/2026-09-09-investigacao-consistencia-ui-formularios-controles.md`
  (UI-AUD-001..018), commit `16c1913`.
  - [x] UI-09.1 - Auditar superfícies (UI-AUD-001..018).
  - [x] UI-09.2 - Commit documental da Fase A (`16c1913`).
  - [x] UI-09.3 - Cursor de selects + pontuacao operacional (onda C1; reaberta
    2026-09-09 e fechada com varredura A ampliada - ver PAR-V2-CURSOR-02).
  - [x] UI-09.4 - Geometria dos formularios V1 (onda C2).
  - [x] UI-09.5 - Gestao de usuarios V1/V2 (onda C3).
  - [x] UI-09.6 - Controle V1 (onda C4/UI-05).
  - [x] UI-09.7 - Titulo unico RCD/RPEC/RMPE no V1 (onda C5).
  - [x] UI-09.8 - Dropdown V2 + ciclo de vida (onda C6).
  - [R] UI-09.9 - Regressao dirigida 9/9 e PHPUnit 536/1619 (baseline real
    2026-09-09); regressao ampla depende de Legacy de pe e execucao serial.
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
  - [x] T3-08 - Implementar shell oculto.
  - [x] T3-09 - Dashboard.
  - [x] T3-10 - RMAs listagem.
  - [x] T3-11 - RMA detalhe (ver A7/T3-11 acima; linha da frente V3 reconciliada em 2026-09-09).
  - [x] T3-12 - RMA formularios (57f1c13; PHPUnit 536/1619 e Playwright
    2/2 verdes).
  - [ ] T3-13 - Parceiros.
  - [ ] T3-14 - Usuarios/admin.
  - [ ] T3-15 - Relatorios.
  - [ ] T3-16 - Secundarias.
  - [ ] T3-17 - Selecao explicita de tema (implementacao).
  - [ ] T3-18 - Mobile/browser.
  - [ ] T3-19 - Acessibilidade.
  - [ ] T3-20 - Performance.
  - [ ] T3-GATE - Liberar Tema V3 no seletor.


## AUDITORIA E CORRECOES CONFIRMADAS (2026-09-09)

- [x] A1 - Reconciliar T3-08/09/10 em plano e OpenSpec (commit 3ba4d12).
- [x] A2 - Corrigir AUD-V3-01 (overflow 768) e AUD-V3-02 (logout V3) - commit 04a5634.
- [R] A3 - T3-17 selecao explicita de tema. DECISAO-PENDENTE: persistir V3 antes do
  T3-GATE quebra rotas canonicas sem view V3; caminho recomendado e V1/V2 explicito
  + V3 por sessao de QA local ate a matriz fechar.
- [R] A4 - Residuos de botoes: nenhum novo confirmado alem de V3 nao ter acoes de
  detalhe (aguarda T3-11).
- [x] A5 - Paridade detalhe RMA Tema V1 (Legacy 14.6.1) - fechado com edicao
  inline, controles e QA (2beecb3/70a2ed8).
  - [x] A5.1 - Estrutura visual BOLETIM DE DEFEITO restaurada.
  - [x] A5.2 - Campos historicos apresentados.
  - [x] A5.3 - Restaurar edicao inline do detalhe (inputs reais + persistencia) - 2beecb3.
  - [x] A5.4 - Restaurar controles estoque/credito (checkboxes reais) - 2beecb3.
  - [x] A5.5 - Restaurar composicao operacional das acoes (rodape esquerda/direita) - 2beecb3.
  - [x] A5.6 - QA funcional + visual contra Legacy (digitar/salvar/reload) - 70a2ed8.
- [x] A6 - Paridade detalhe RMA Tema V2 (Legacy 15.8.1). Fechado de novo em
  2026-09-09 com formulario operacional editavel (127b94d). Reabertura:
  o detalhe tinha virado leitura (`show.blade.php` com paragrafos) e o Legacy
  `page/rma.php` e formulario operacional editavel. Ver PAR-V2-DETAIL-02.
  - [x] A6.1 - Reproducao/documentacao com metricas e screenshots
    (`docs/produto/2026-09-09-addendum-paridade-detalhe-rma-v1-v2.md`, PAR-DET-V2-01).
  - [x] A6.2 - Restaurar cabecalho operacional e grupos/colunas do `show` V2 - 6b78ac3.
  - [x] A6.3 - Acoes/cabecalho integrados sem duplicar regra de negocio - 6b78ac3.
  - [x] A6.4 - Browser regression V2 (abas, show/edit/acoes) - 10f786f +
    a71a3d8 (evidencia invalidada para o contrato funcional; mantida como prova de
    estrutura).
  - [x] A6.5 - Detalhe V2 funcional: campos editaveis, salvar, reload, acoes e
    Policy de escrita/leitura (PAR-V2-DETAIL-02, onda 3, 127b94d).
- [x] PAR-V2-GEO-01 - Geometria das abas/listagens V2 (delta medido: tabela -7px;
  documento no addendum).
  - [x] V2-GEO-01.1 - Alinhar conteudo das abas V2 a y=47px e reconferir rodape - c030e87.
  - [x] V2-GEO-01.2 - Playwright de geometria por aba - a71a3d8.
- [x] PAR-V2-PESQ-01 - Composicao Pesquisar V2 (classes historicas ausentes do CSS
  compilado).
  - [x] V2-PESQ-01.1 - Estrutura `submenu-subpage` + port de `.boxtop-subpage`,
    `.box-subpage`, `.buttonSearch`, `.InputSeek` - c030e87.
  - [x] V2-PESQ-01.2 - Playwright de bounding boxes/cores da composicao - a71a3d8.
- [x] A7/T3-11 - Detalhe operacional do RMA no Tema V3
  (5885073/9f2e492; checkpoint docs/produto/2026-09-09-checkpoint-t3-11-detalhe-rma-v3.md).

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
- [ ] P13 - PHPUnit completo + build final (baseline real atual 536/1619;
  execucao completa ja verde, pendente apenas da rodada final apos P7-P12).
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
V3 implementado de forma oculta (T3-08..T3-12) e ainda NAO selecionavel. Paridade: P0-P14 fechados sem bloqueio, full
suite e Playwright quatro quadrantes verdes.

## NAO FAZER AINDA

Editar Legacy; reproduzir bugs/rotas mortas; relaxar Policy/tenant; expor
Tema V3 no seletor antes de T3-GATE; push/PR/merge; remover views orfas antes da onda UI-07; aplicar replace
global de hifen longo; marcar `[x]` sem evidencia real.
