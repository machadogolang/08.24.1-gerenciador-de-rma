# Plano de ataque - CellSystem RMA

Ultima atualizacao: 2026-09-10 (America/Sao_Paulo). Status no padrao canonico
`[ ]`/`[R]`/`[x]` (ver `docs/operacao/padrao-status-plano.md`). Regra de texto:
nunca usar hifen longo, sempre hifen simples (ver `docs/operacao/regra-hifen.md`).

Este plano **consolida as duas instrucoes do dono de 2026-09-10**: a frente
forense de paridade Legacy -> V1/V2 e o adendo prioritario de runtime/QA/V3.
Fonte canonica de estado: `docs/produto/2026-09-10-matriz-forense-paridade-legacy-v1-v2.md`.

## Como ler este plano

- O bloco `AGORA - ADENDO P0` tem precedencia sobre qualquer onda visual (regra
  do dono: tratar o adendo antes da proxima onda visual).
- Encerrado o adendo, a fila forense volta na ordem original.
- Os blocos historicos aparecem depois, sob `ESTADO HISTORICO`, e nao devem ser
  lidos como fila executavel atual.
- Governanca: Legacy e SOMENTE LEITURA; nunca remover CSRF, Policy, tenant, hash
  seguro ou validacao moderna; PUSH NAO AUTORIZADO; handoff apenas no
  encerramento real da sessao e como ULTIMO commit.

## AGORA - ADENDO P0 (2026-09-10): runtime, QA de URLs e direcao V3

### Bloco 1 - RPEC/RCD/RMPE quebrados no runtime (P0)

- [x] AD-01 - Investigar o 500 do RPEC e classificar a causa real.
  Causa REAL confirmada: a migration
  `2026_09_10_000002_create_relatorio_informacoes_adicionais_table` estava
  **PENDENTE** no banco persistente do Sail, junto com
  `2026_09_10_000001_add_campos_historicos_de_log_...`. Nao era schema drift e
  nao faltava migration no codigo.
- [x] AD-02 - Aplicar `./vendor/bin/sail artisan migrate` no ambiente LOCAL
  (sem `migrate:fresh`, sem apagar dado) e provar `migrate:status` limpo.
- [x] AD-03 - Smoke HTTP real no runtime persistente (login + GET) provando 200
  em RCD e RPEC.
- [x] AD-04 - RMPE deterministico: `GET` sem query devolvia 302 porque
  `data_inicio`/`data_fim` eram obrigatorios, mas o Legacy RMPE nao filtra por
  data. Intervalo agora e opcional e, quando ausente, a selecao roda sem filtro
  de periodo (Legacy-fiel), provado por teste e smoke HTTP 200.
- [x] AD-05 - Nao mascarar o bug: proibido `Schema::hasTable(...)` como fachada.
  A tabela faz parte do schema atual; o que foi corrigido e o PROCESSO
  (`artisan migrate` no banco persistente).
- [ ] AD-06 - Registrar o incidente `RMA-BUG-REL-SCHEMA-001` e atualizar o
  runbook `docs/desenvolvimento/ambiente-v2-v3.md` (sem duplicar documentacao).
- [ ] AD-07 - Aprendizado operacional duravel: Feature test verde com
  `RefreshDatabase` NAO prova o banco persistente; em "table not found",
  confirmar `artisan migrate:status` antes de mexer em controller/model.

### Bloco 2 - URLs de QA deterministicas para os relatorios

- [ ] AD-10 - Rotas V1 explicitas `/v1/relatorios/{rcd,rpec,rmpe}`, nomes
  `v1.rmas.relatorios.*`, MESMO `RelatorioController` (sem duplicar regra/query).
- [ ] AD-11 - Feature test das tres rotas V1 (200 + tema V1 forcado).
- [ ] AD-12 - Provar `/v2/relatorios` (painel estatistico 15.8.1) e `/v2/creditos`.
- [ ] AD-13 - Tabela "matriz de URLs de QA" no runbook.
- [ ] AD-14 - Playwright minimo de navegacao dos relatorios V1.

### Bloco 3 - Entrada segura para o Tema V3 (previa)

- [ ] AD-20 - Flag de config `tema_v3_preview_enabled` (sem `env()` na Blade).
- [ ] AD-21 - Entrada discreta "Previa V3" no V1 e no V2 quando ligada; OFF nao
  mostra nada.
- [ ] AD-22 - Acao "Voltar ao sistema" no shell V3, saindo de `/v3`.
- [ ] AD-23 - Testes: `tema_preferido` persistido NAO muda ao entrar/sair; flag
  OFF esconde a entrada; V3 continua sem ser opcao persistente publica.

### Bloco 4 - Nova direcao visual do Tema V3 (documental)

- [ ] AD-30 - Documento de decisao "Console Operacional Dark" (referencia
  conceitual Laravel/Ignition, sem copiar HTML/assets).
- [ ] AD-31 - Registrar precedencia: direcao clara anterior SUPERADA; nova
  direcao e NORTE VISUAL, implementacao ampla nas tasks T3-13+.
- [ ] AD-32 - Nao redesenhar o V3 agora: apenas o necessario para a previa segura.

### Bloco 5 - Reconciliacao documental da matriz (instrucao 1)

- [ ] AD-40 - Passagem de consistencia na matriz: tabelas 5.1/5.2, secao 6
  (fila), derivabilidade SO/APP, senha/anotacoes, secoes "proximo passo".
- [ ] AD-41 - Investigar `snretorno` (PAR15-RMA-DET-004) sem trocar `[ ]` por `[x]`.
- [ ] AD-42 - Protecao simples contra inconsistencia (mesmo ID simultaneamente
  `[x]` e `[R]`/`[ ]` em estado corrente).
- [ ] AD-43 - Commit documental isolado.

## ORDEM DE EXECUCAO (consolidada)

1. [x] Consolidar as duas instrucoes neste plano + commit documental.
2. [ ] ADENDO P0 (blocos 1 a 4): runtime, URLs de QA, previa V3, doc visual V3.
3. [ ] Reconciliacao da matriz (bloco 5) + commit documental isolado.
4. [ ] Reforco QA do detalhe V2 (`ParidadeDetalheRmaV2Geometria`: conjunto de
   opcoes igual + tolerancia de gap coerente, 2-4px).
5. [ ] Novo Usuario V2 (PAR15-USR-007/009).
6. [ ] Alterar senha V2 (PAR15-SEC-001).
7. [ ] Anotacoes V2 (PAR15-NOTE-001).
8. [ ] RG/IE (PAR15-PART-DATA-001) + importacao.
9. [ ] Edit Cliente/Fornecedor/Fabricante/Assistencia (PART-002..005; create != edit).
10. [ ] RMAs associados dos 4 parceiros (PART-001/PART-006) + testes separados.
11. [ ] Sweep de `[R]`/`[ ]`: PAR14-NAV-002, PAR15-RMA-LIST-001,
    PAR15-SEARCH-001, PAR15-RMA-DET-004, PAR15-AUD-005, PAR15-EMAIL-001,
    PAR15-RMA-MARCAR-001.
12. [ ] PF-14 completo (auditoria visual ampla) e PF-15.
13. [ ] P11 revalidacao, P12, P13 final, P14.
14. [ ] Depois, V3 conforme dependencias reais (T3-13+).

## REGRA DE STATUS PAI/FILHO (2026-09-10)

Pai `[x]` so quando significa PARIDADE COMPLETA. Se o pai significa apenas
CAPACIDADE FUNCIONAL, o criterio fica explicito no nome/decisao
(`funcional = [x]`, `visual amplo = [R]`). Proibido pai `[x]` com filho visual
obrigatorio `[R]` sem explicacao.

## CRITERIO DE SAIDA

Auditoria forense fechada: todos os IDs `PAR14-*`/`PAR15-*` com `[x]` provado por
codigo/teste/runtime/documento, `[R]`/`[ ]` com proximo passo claro, PF-14
completo, PF-15 reconciliado, P11/P12/P13/P14 verdes. V3 permanece oculto e nao
persistivel fora do preview de QA.

## NAO FAZER AINDA

Editar o Legacy; reproduzir bug/rota morta; reproduzir SQL inseguro/GET
destrutivo/SHA1/sem CSRF; relaxar Policy/tenant/validacao moderna; expor o Tema
V3 no seletor antes do T3-GATE; push/PR/merge; marcar `[x]` sem evidencia real;
usar hifen longo.

---

## ESTADO HISTORICO (blocos anteriores, preservados)


## AGORA - Auditoria forense de paridade Legacy -> Novo (2026-09-10)

Frente aberta pelo dono, com prioridade sobre P12/P14:
`[R] PAR-FORENSE-LEGACY-01 - inventario capability-first V1/V2`. Fonte canonica:
`docs/produto/2026-09-10-matriz-forense-paridade-legacy-v1-v2.md` (IDs
`PAR14-XXX-NNN` para o Legacy 14.6.1 e `PAR15-XXX-NNN` para o Legacy 15.8.1).

Sentido obrigatorio: LEGACY -> CAPACIDADE -> NOVO -> EQUIVALENTE? -> COMPLETO?
-> VISUALMENTE EQUIVALENTE? -> FUNCIONALMENTE EQUIVALENTE? Capacidade que sumiu
do novo so aparece porque a auditoria comeca no Legacy, nunca nas rotas Laravel.
Rota com nome parecido nao fecha `[x]`.

Subtarefas:

- [x] PF-01 - inventario de capacidades 14.6.1 (30 capacidades, secao 1.1).
- [x] PF-02 - inventario de capacidades 15.8.1 (36 capacidades, secao 1.2).
- [x] PF-03 - detalhe RMA V1 campo a campo (secao 5.1; estoque/credito fechados).
- [R] PF-04 - detalhe RMA V2 REABERTO 2026-09-10 por validacao runtime do dono
  (geometria/opcoes; DET-011..014 corrigidos, browser 2/2 verde).
- [x] PF-05 - usuarios V1 (PAR14-USR-001 fechado; acoes no Controle).
- [R] PF-06 - usuarios V2 REABERTO 2026-09-10: Novo Usuario implementado, paridade
  visual reaberta (USR-007/USR-009).
- [x] PF-07 - auditoria/logs V1 (14.6.1 nao tinha tela propria de log).
- [x] PF-08 - auditoria/logs V2 (AUD-001..004 fechados; AUD-005 geometria em PF-14).
- [x] PF-09 - relatorios V1 (RPEC/RCD/RMPE fechados com totais e info adicional).
- [x] PF-10 - relatorios V2 (hub estatistico + menu historico corrigido).
- [R] PF-11 - parceiros (PAR15-PART-001: detalhe/RMAs do parceiro segue aberto).
- [R] PF-12 - navegacao/shell reconciliada, mas reaberta pelo sweep de credito/parceiros.
- [R] PF-13 - sweep de capacidades nao mapeadas (secoes 4 e 7) segue aberto.
- [R] PF-14 - browser comparison Legacy x novo (1a fatia: /v2/usuarios x 15.8.1/usuarios,
  `tests/Browser/ParidadeUsuariosV2.spec.ts` verde em 1440px - mesmas colunas e linha
  ~30px; demais superficies ainda pendentes).
- [ ] PF-15 - reconciliacao final e fechamento da frente.

Fila de correcao apos o checkpoint documental (secao 6 da matriz):

1. [x] PAR15-USR-001 - organizacao historica de /v2/usuarios (c556d15).
2. [x] PAR15-RMA-DET-001 - select operacional + OK no topo (e rodape) do detalhe V2.
3. [x] PAR14/PAR15-RMA-STOCK/CREDIT - fechados: V2 select 30px, V1 check custom
   475x40 identico ao Legacy, persistencia (salvar/reload) e Policy provadas.
4. [x] PAR15-AUD-001..005 - hub Controle V2, colunas do Legacy projetadas de
   estado_apos/user_agent, acao Ver e SO/APP preservados (AUD-005 geometria em PF-14).
5. [x] PAR14-REL-RPEC-001..005 - RPEC V1 com colunas, totais e informacao adicional.
6. [x] PAR14-REL-RCD-001..003 - RCD V1 com colunas/totais e regra do Legacy.
7. [x] PAR15-REL-001..010 - hub estatistico V2 + menu historico com item unico
   Relatorios (REL-010, markup compartilhado de RPEC/RCD/RMPE, fica com as ondas V1).
8. [ ] demais gaps da matriz.

Sequencia executavel refinada (segunda passagem, decisao do dono 2026-09-10):
estoque/credito runtime (3) -> auditoria/Controle V2 (4) -> RPEC V1 (5) -> RCD V1 (6)
-> RMPE V1 (7) -> hub+menu Relatorios V2 (8/9/10) -> Usuarios V1 (11, feito) -> Novo Usuario
V2 (Novo Usuario) -> sweep de [R]/[ ] -> PF-01..PF-13 -> PF-14 -> PF-15; P12/P14 so depois de PF-15.
Reconciliacao ja feita na matriz: PAR15-USR-002/003/005/006/008 = [x];
PAR15-USR-004 = [DECISAO-PENDENTE]; PAR15-USR-007 = [x] (Novo Usuario V2 implementado);
PAR15-REL-008/009 = decisao resolvida (menu V2 com item unico Relatorios).

P12 permanece pendente enquanto esta auditoria esta aberta; P14 nao fecha antes
dela. Os quatro quadrantes de browser tests nascem da matriz forense (PF-14),
nunca de um contrato de paridade incompleto.


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
- [x] ONDA E - Relatorios/Avisos/Anotacoes/secundarias. PAR-RES-E-01..03 (bbea068),
  PAR-RES-E-04 (anotacoes/senha V2 dedicadas) e PAR-RES-006 (Centro de Avisos)
  fechados em 2026-09-10.
- [x] ONDA F - Viewport/print/regressao residual (2026-09-10; OndaFRegressaoViewport
  3/3; PAR-RES-F-01 de impressao corrigido).

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
  - [x] UI-09.10 - C7: varredura residual e viewports (fechado 2026-09-10:
    ConsistenciaVisualControles integral + OndaFRegressaoViewport 3/3, inclui UI-08/print).

- [R] UI-05 - Controle V1.
  - [x] UI-05.1 - Causa confirmada e documentada.
  - [x] UI-05.2 - Alinhamento corrigido.
  - [x] UI-05.3 - Overflow local validado.
  - [x] UI-05.4 - Regressao viewport/Playwright ampla (OndaFRegressaoViewport: shell
    V1/V2 sem overflow no desktop).

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

- [x] UI-09.10/C7 + UI-08 - Varredura residual e regressao browser/print final
  (fechado 2026-09-10).
- [x] UI-07 - Remover views orfas com zero consumidor.
  - [x] UI-07.1 - Inventario de candidatas (UI-AUD-016).
  - [x] UI-07.2 - Remocao com prova de zero consumidor e teste (15 views removidas em
    2026-09-10; guarda `ViewsOrfasRemovidasTest`; PHPUnit 546/1676 verde).
- [x] P7 - UX-003: Encaminhar por selecao validada + PAR-RMA-008 (fechado 2026-09-10).
  Selecao `destinatario` (`tipo:id`) validada no servidor por `OpcoesDeDestinatario`
  (tipo, existencia e tenant); Feature 548/1691 e Playwright dirigido verdes.
- [ ] P8 - Parcial 2026-09-10: Anotacoes V2 dedicada reconciliada (PAR-RES-E-04),
  confirmacao de remocao (UX-002), UX-001 e duplo envio de UX-004 implementados com
  testes. Falta o contrato transversal completo de flash/validacao/estado vazio/403/
  404/500.

- [x] P9 - Inventario de rotas/plugins residuais (`docs/produto/2026-09-10-inventario-rotas-e-plugins-residuais.md`,
  2026-09-10; 150 rotas, sem orfas, V3 segue oculto).
- [ ] P10 - Parcial 2026-09-10: matriz de temas (Encaminhar/Concluir) e checklist-master
  (H-026/H-034/H-035/H-036) reconciliados; PLAN.md nao referenciava os itens. Faltam
  `paridade-v2-v3.md`, `checklist-paridade-temas.md` e o roteiro final.
- [x] P11 - Regressao funcional por fluxo: `SmokesParidadeFuncional` M-01/M-02/M-04/M-06
  4/4 verdes em 2026-09-10 (specs reconciliados com o detalhe V1 de edicao inline e a
  folha de relatorio).
- [x] P13 - PHPUnit completo + build final: 550 testes / 1699 assertions verdes +
  `npm run build` verde (2026-09-10).
- [ ] P12 - Playwright quatro quadrantes em `tests/Browser/Fluxos/`: hoje so
  `Fluxos/Tema.spec.ts`; os demais quadrantes nao foram escritos nesta sessao.
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
- [x] DEC-03 - PAR-RMA-008 (Concluir/legado): fechado 2026-09-10 - `concluir()` grava
  apenas status+data em 14.6.1 e 15.8.1 (evidencia no contrato P7).
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

## REABERTURA 2026-09-10 (validacao runtime do dono, terceira passagem)

O dono validou o runtime apos o push e refutou `[x]`. Nova fila, antes de N/PF-14:
detalhe RMA V2 (DET-011..014, ja corrigido com browser), Credito V2
(CREDIT-001..004) e Credito V1 (PAR14-CREDIT-001), Novo Usuario V2 (USR-007/009),
Alterar senha V2 (SEC-001), Anotacoes V2 (NOTE-001), Edit Cliente/Fornecedor/
Fabricante/Assistencia (PART-002..005), RG/IE (PART-DATA-001) e RMAs associados
(PART-001/PART-006). Regra nova de `[x]`: paridade visual exige Legacy e novo
abertos, mesmo viewport, boundingBox/computedStyle e fluxo funcional.

PUSH NAO AUTORIZADO.
