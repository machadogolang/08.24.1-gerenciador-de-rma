# Checklist mestre executável — CellSystem RMA V3

Última consolidação: 2026-08-25. Este é o documento operacional definitivo. `PLAN.md`
resume fases; `PLANO-ATAQUE.md` seleciona o lote corrente. Em divergência, prevalecem
código/testes/runtime, Git, OpenSpec, investigações e, por fim, planejamento.

Convenção: apenas `[ ]` e `[x]`. Classes: `ARQ`, `DEV`, `QA`, `DOC`, `DECISAO`, `EVO`,
`OPS`. Cada pendência informa dependência ou gate.

## A. Estado confirmado

- [x] **ARQ A-01 — identificar o 15.9.7.** Container com apps 14.6.1/15.8.1 e camada
  compartilhada; evidência: matriz de comparação.
- [x] **ARQ A-02 — preservar fonte histórica.** Backup, SHA-256 e inventário técnico.
- [x] **ARQ A-03 — catalogar funcionalidade/regras.** 48 `LEG-RMA-*` e RN-01…RN-21.
- [x] **ARQ A-04 — mapear banco/migração.** Inventário de banco e `INV-RMA-06`.
- [x] **OPS A-05 — Legacy em `:8094`.** Modos sanitizado/histórico; histórico com
  1.379 RMAs e 165 clientes.
- [x] **OPS A-06 — V3 em `:8095`.** Seed QA determinístico e execução simultânea.
- [x] **QA A-07 — baseline automatizada.** 310 testes/608 assertions antes desta
  consolidação; renovar em F10-GATE-01.
- [x] **DOC A-08 — comparação viva.** `docs/produto/comparacao-v3-legado-final.md`.

## B. Trilha A

### Fases concluídas

- [x] **DEV F1 — Identidade.** `LEG-RMA-001/003/004/005/006/042/043`; OpenSpec/testes.
- [x] **DEV F2 — Parceiros.** `LEG-RMA-030…033`; enum UF, policies e CRUDs.
- [x] **DEV F3 — RMA núcleo.** `LEG-RMA-007…010/046`; RN-13/RN-14.
- [x] **DEV F4 — ciclo de vida.** `LEG-RMA-011…017/047`; `016` não reconstruído.
- [x] **DEV F5 — alertas.** `LEG-RMA-018…029`; RN-12 testada na V3.
- [x] **DEV F6 — créditos/relatórios.** `LEG-RMA-036…039/048`.
- [x] **DEV F7 — logística/histórico.** `LEG-RMA-040/041/043/044/045`.
- [x] **DEV F8 — temas V1/V2.** Escopo aprovado, Playwright e 9 screenshots.
- [x] **DEV F8.1 — avisos/relatórios estilizados.** Commit `adcd27c`.
- [x] **DEV F9 — migrador.** 8 importadores, parser, idempotência, relatório e 43 testes.
- [x] **DECISAO F9.1 — omitir `relatorio.informacaoadicional`.** Recuperável no backup.

### F10 — funcional

Gate: os 48 IDs têm prova explícita ou justificativa de exclusão.

- [x] **DOC F10-FUN-01 — criar roteiro funcional.** Arquivo
  `docs/qa/roteiro-paridade-funcional.md`, com ambiente, dados, esperado/observado.
- [x] **QA F10-FUN-02 — mapear `LEG-RMA-001…010`.** Teste, passo manual ou decisão.
- [x] **QA F10-FUN-03 — mapear `LEG-RMA-011…020`.** Mesmo critério.
- [x] **QA F10-FUN-04 — mapear `LEG-RMA-021…030`.** Mesmo critério.
- [x] **QA F10-FUN-05 — mapear `LEG-RMA-031…040`.** Mesmo critério.
- [x] **QA F10-FUN-06 — mapear `LEG-RMA-041…048`.** Mesmo critério.
- [ ] **QA F10-FUN-07 — executar passos manuais V2×V3.** Registrar observado/data.
- [ ] **QA F10-FUN-08 — reconciliar matriz.** 44 paridade, 2 não reconstruir, 1
  retomar ideia; `LEG-RMA-002` decidido/deferido.
- [x] **DOC F10-FUN-09 — corrigir F10 para Playwright `.spec.ts`.** Proposal/design/tasks.

### F10 — visual

Gate: 2 temas × 3 breakpoints × telas acordadas, sem divergência silenciosa.

Checkpoint dirigido TEMA V1 desktop (incorporado ao plano em 2026-08-25):

- [x] **QA F10-V1-01 — comparar runtimes reais em 1440 px.** Dez superfícies Legacy
  14.6.1 × V3, com 20 capturas locais reproduzíveis.
- [x] **ARQ F10-V1-02 — auditar CSS integral e HTML autenticado.** Matriz consciente em
  `docs/produto/paridade-visual-tema-v1.md`; sem cópia integral das folhas históricas.
- [x] **DEV F10-V1-03 — restaurar cabeçalho/menu/painel históricos.** Blade Laravel e
  seletores usados, mantendo base fixa de 984 px.
- [x] **DEV F10-V1-04 — corrigir usuários, busca e novo RMA.** Tabelas, formulários,
  controles e wrappers compatíveis com a composição 14.6.1.
- [x] **DEV F10-V1-05 — tornar fontes e logo locais.** Fira Mono válida via Vite e logo
  próprio do V3; nenhuma consulta ao Legacy como CDN.
- [x] **QA F10-V1-06 — provar assets essenciais.** Sem 4xx/5xx/falha em build, fonte e
  imagem nas superfícies monitoradas.
- [x] **QA F10-V1-07 — criar regressão Playwright.** Geometria, fonte, assets e matriz
  desktop em `ParidadeVisualTemaV1.spec.ts`.
- [x] **DOC F10-V1-08 — registrar diferenças conscientes.** Gateway compartilhado,
  ações Laravel e dados distintos não são redesenho.

- [ ] **QA F10-VIS-01 — fixar matriz de telas.** Login, home/alertas, novo RMA,
  detalhe/edição, busca/listagem, parceiros, crédito, relatórios e histórico.
- [ ] **QA F10-VIS-02 — inventariar evidências reutilizáveis da F8.** Por tela/tema/ponto.
- [ ] **QA F10-VIS-03 — comparar TEMA V1 em 390/768/1440.** Playwright V2×V3.
- [ ] **QA F10-VIS-04 — comparar TEMA V2 em 390/768/1440.** Breakpoints reais.
- [ ] **QA F10-VIS-05 — capturar login/home.** Dois temas × três breakpoints.
- [ ] **QA F10-VIS-06 — capturar novo/detalhe/edição.** Dois temas × três breakpoints.
- [ ] **QA F10-VIS-07 — capturar busca/listagem.** Dois temas × três breakpoints.
- [ ] **QA F10-VIS-08 — cobrir alertas/crédito/relatórios/histórico.** Lacuna da F8.
- [ ] **QA F10-VIS-09 — classificar divergências.** Correção, rasterização ou `EVO-*`.

### F10 — dados

Gate: migração real em alvo descartável e reconciliação sem diferença inexplicada.

- [ ] **OPS F10-DAD-01 — definir origem histórica somente leitura.** Host/schema/contagens.
- [ ] **OPS F10-DAD-02 — preparar alvo V3 descartável e rollback.** Nunca a base corrente.
- [ ] **OPS F10-DAD-03 — viabilizar rede V3→Legacy.** Só ambiente, não fonte histórica.
- [ ] **QA F10-DAD-04 — executar `rma:migrar-legado --dry-run`.** Arquivar relatório.
- [ ] **QA F10-DAD-05 — revisar datas inválidas.** Valor bruto rastreável como anomalia.
- [ ] **QA F10-DAD-06 — verificar `status='retornou'`.** Decidir somente se existir.
- [ ] **QA F10-DAD-07 — importar no alvo descartável.** Após dry-run explicado.
- [ ] **QA F10-DAD-08 — reconciliar 9 tabelas.** Contagens, anomalias e descartes.
- [ ] **QA F10-DAD-09 — provar idempotência real.** Segunda execução sem duplicação.

### F10 — fechamento

- [ ] **QA F10-GATE-01 — rodar suíte completa.** Testes/assertions/skips/commit.
- [ ] **QA F10-GATE-02 — confirmar eixo funcional.** Gate funcional fechado.
- [ ] **QA F10-GATE-03 — confirmar eixo visual.** Gate visual fechado.
- [ ] **QA F10-GATE-04 — confirmar eixo de dados.** Gate dados fechado.
- [ ] **DECISAO F10-GATE-05 — endereçar decisões materiais.** Fazer, adiar ou recusar.
- [ ] **DOC F10-GATE-06 — criar relatório final.** `docs/qa/relatorio-paridade-final.md`.
- [ ] **QA F10-GATE-07 — declarar ou negar gate da Trilha A.** Sem liberação parcial.

## C. Investigações e decisões

- [ ] **DECISAO C-01 — `LEG-RMA-002`.** Convite seguro ou criação só por admin;
  dependência: usuário; bloqueia F10-GATE-05.
- [ ] **ARQ C-02 — RN-12 no TEMA V1.** Busca dirigida; confirmar ausência/presença.
- [ ] **ARQ C-03 — Lightbox2.** Uso funcional ou resíduo de template.
- [ ] **ARQ C-04 — skin AdminLTE.** Identificar skin efetiva ou ausência comprovável.
- [x] **DECISAO C-05 — Open Sans.** Reproduzir fallback, não self-hostar.
- [x] **DECISAO C-06 — pós-login.** Gateway respeita `tema_preferido`.
- [x] **DECISAO C-07 — `informacaoadicional`.** Não migrar na baseline.
- [ ] **DECISAO C-08 — visibilidade do V3.** Operacional; requer autorização.
- [ ] **DECISAO C-09 — `EVO-AUD-001`.** Snapshot atende baseline; priorizar diff pós-gate.

## D. Ambiente, QA e documentação

- [x] **OPS D-01 — documentar V2/V3 locais.** Portas, modos e comandos seguros.
- [x] **OPS D-02 — corrigir reset Legacy.** Modos sanitizado/histórico validados.
- [x] **QA D-03 — seed QA V3.** Dados representativos e teste de idempotência.
- [x] **QA D-04 — screenshots F8.** Nove PNGs versionados.
- [ ] **DOC D-05 — atualizar contagens em resumos correntes.** Preservar logs históricos.
- [ ] **QA D-06 — auditar `ExampleTest`.** Remover/substituir placeholders sem valor.
- [ ] **DOC D-07 — fechar OpenSpec F10 por evidência.** Nunca por intenção.

## E. Trilha B — backlog ordenado

Gate comum: F10-GATE-07. Investigação/especificação pode avançar; código não.

- [ ] **EVO E-01 — `EVO-SAAS-001`, prioridade alta pós-F10.** Investigação concluída;
  faltam OpenSpec, companies/company_user, tenant context/scope, isolamento, contador,
  backfill/migração e testes cross-tenant. Decidir superadmin/agregação de segurança.
- [ ] **EVO E-02 — `EVO-SAAS-002`.** Depende E-01; catálogo global, cópia independente,
  dedup/autorização; decidir quem faz curadoria.
- [ ] **EVO E-03 — `EVO-SAAS-003`.** Depende E-01; primeiro investigar identidade
  comunitária, fronteira público×operacional e moderação.
- [ ] **EVO E-04 — `EVO-UX-001`, prioridade média.** Arquitetura concluída; decidir
  tokens/paleta/seletor/aceite; depois enum V3, assets, views e testes mobile.
- [ ] **EVO E-05 — `EVO-CONF-001`, prioridade média.** OpenSpec completo; implementar
  tasks existentes. Persistência append-only, Supervisor/SuperAdmin e tela única já
  foram decididos pelo OpenSpec posterior.
- [ ] **EVO E-06 — `EVO-ARQ-001`, prioridade média.** OpenSpec completo; implementar
  tasks existentes depois de E-05. Acrescentar testes de binding `{rma}`/anexo, papel
  sem gravação e, futuramente, cross-tenant.
- [ ] **DOC E-07 — corrigir `EVO-DOM-001`.** FK já concluída; separar o residual de
  unificação polimórfica `Parceiro` e só então investigar migração.
- [ ] **EVO E-08 — `EVO-DOM-002`.** Investigar identidade/serial/ownership/migração do
  Equipamento antes de OpenSpec.
- [ ] **EVO E-09 — `EVO-DOM-003`.** Regra multidimensional de garantia; relaciona-se a
  E-05, mas não duplica configuração escalar.
- [ ] **EVO E-10 — `EVO-AUT-001/002`.** Investigar canais, agenda, idempotência e máquina
  de estados do crédito antes de OpenSpec.
- [ ] **EVO E-11 — `EVO-REL-001/002`.** Definir formatos, filtros, métricas, índices e
  dataset de validação.
- [ ] **EVO E-12 — `EVO-SEG-001`.** MFA/SSO; coordenar com E-01 e segurança cross-tenant.
- [ ] **EVO E-13 — `EVO-AUD-001`.** Se aprovado, especificar antes/depois, campos
  sensíveis, retenção, UI e testes.
- [ ] **QA E-14 — `EVO-PERF-001`.** Item misto: medir query count/EXPLAIN/índices com
  massa QA antes de classificar correção de baseline ou evolução.
- [ ] **EVO E-15 — `EVO-IA-001`.** Após dados/tenant/auditoria; definir ground truth,
  consentimento, human-in-the-loop e avaliação antes de integração.

## F. Segurança, performance e higiene

- [x] **QA F-01 — bloquear e-mail real no Legacy.** Mailpit validado.
- [x] **DEV F-02 — não reproduzir credenciais históricas.** Só laboratório.
- [x] **DEV F-03 — usar policies e Hash/Auth nativos.** Coberto nas fases.
- [ ] **QA F-04 — auditar segurança da baseline.** Papéis, CSRF, enumeração e segredos.
- [ ] **QA F-05 — medir performance.** Home, busca e relatórios com massa QA.
- [ ] **DOC F-06 — validar links internos.** Eliminar caminhos inexistentes.
- [ ] **DOC F-07 — manter dúvidas na investigação correta.** Não duplicar pendências.

## G. Gates

- [x] **GATE G-01 — arqueologia suficiente.** Inventários/regras/banco/visual/parecer.
- [x] **GATE G-02 — F1–F8 implementadas/testadas.** Dependências satisfeitas.
- [x] **GATE G-03 — F9 implementada/testada por fixture.** Não é reconciliação real.
- [ ] **GATE G-04 — F10 funcional.** Seção funcional concluída.
- [ ] **GATE G-05 — F10 visual.** Seção visual concluída.
- [ ] **GATE G-06 — F10 dados.** Seção de dados concluída.
- [ ] **GATE G-07 — Trilha A encerrada.** Fechamento e relatório aprovados.
- [ ] **GATE G-08 — Trilha B liberada.** Somente após G-07.

## Próxima tarefa segura

`F10-FUN-07`: executar e registrar os seis smokes cruzados M-01…M-06 sem alterar
dados históricos fora de cenário descartável.
É QA controlado; qualquer etapa mutável exige registro descartável e evidência explícita.
