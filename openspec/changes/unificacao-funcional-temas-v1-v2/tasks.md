# Tasks - Unificacao Funcional dos Temas V1 e V2

Frente canonica: `F-UNION-01` (Uniao de Capacidades Vivas V1 + V2).
Marcadores canonicos: `[ ]` (Pendente), `[R]` (Revisado), `[x]` (Concluido).

## Fase 1 - Inventario e Governanca Documental

- [x] UF-01 - Conduzir a auditoria cruzada de 65 capacidades canonicas e publicar a matriz `docs/produto/2026-09-10-matriz-uniao-funcional-temas.md` com contagens, status de maturidade e mapeamento de gaps.
- [x] UF-02 - Publicar a OpenSpec `openspec/changes/unificacao-funcional-temas-v1-v2/` (`proposal.md`, `design.md`, `tasks.md`).
- [x] UF-03 - Atualizar o `PLANO-ATAQUE.md` reconciliando pendencias anteriores do Adendo P0 e posicionando a frente funcional com precedencia.
- [x] UF-04 - Adicionar nota de precedencia da decisao de uniao funcional de 2026-09-10 nos documentos de paridade historicos.

## Fase 2 - Provas de Conceito Convergidas (Verificacao)

- [x] UF-05 - Verificar convergencia de Arquivamento de RMA (CAP-RMA-015 / FUN-UNION-002): prova de que a implementacao correta e segura do 15.8.1 esta disponivel e funcional nos dois temas sem reproduzir o Fatal Error do 14.6.1.
- [x] UF-06 - Verificar convergencia de Alteracao de Propria Senha (CAP-ID-003 / FUN-UNION-003): prova de que o fluxo correto do 14.6.1 foi adotado como especificacao e funciona nos dois temas sem o bug SQL do 15.8.1.

## Fase 3 - Gaps Prioritarios de Ciclo e Relatorios (P0)

- [x] UF-07 - Fila de Recebidos no Tema V1 (`GAP-V1-01` / CAP-RMA-004):
  - [x] Implementar rota/listagem de Recebidos sob o Tema V1 com o padrao de tabela do 14.6.1 (`page/entrada.php`).
  - [x] Inserir o link "Recebidos" no menu de navegacao superior V1 (`#TOPO`).
  - [x] Escrever teste Feature garantindo HTTP 200 e presenca dos registros recebidos na view V1.
- [x] UF-08 - Descoberta e Acesso aos Relatorios RCD, RPEC e RMPE no Tema V2 (`GAP-V2-01..03` / CAP-REL-001..003):
  - [x] Criar pontos de entrada e navegacao descobrivel dentro de `/v2/relatorios` para RCD, RPEC e RMPE via `_menu_relatorios`.
  - [x] Garantir que os relatorios respondam sob o Tema V2 mantendo seu contrato de dados.
  - [x] Teste Feature de navegacao e resposta 200 sob Tema V2.
- [x] UF-09 - Hub Estatistico de Relatorios no Tema V1 (`GAP-V1-06` / CAP-REL-004):
  - [x] Criar adaptacao visual das estatisticas (Situacao, Resolucao, Origem, NFs, Dados do Sistema, Series) no painel de Relatorios V1 (`temas.v1.rma.relatorios.index`).
  - [x] Adicionar opcao no menu de relatorios/sessao do Tema V1.
  - [x] Teste Feature provando resposta 200 e dados consistentes no V1.

## Fase 4 - Auditoria e Creditos Cruzados (P1)

- [x] UF-10 - Logs de Autenticacao e Modificacao no Tema V1 (`GAP-V1-03..05` / CAP-AUD-002..004):
  - [x] Criar visualizacoes dos logs de acesso e modificacao no padrao de tabela e densidade do Tema V1.
  - [x] Adicionar links de acesso no painel Controle V1 (`/rmas-controle`).
  - [x] Assegurar link `Ver` funcional para inspecionar o RMA.
  - [x] Testes Feature de acesso aos logs sob o Tema V1.
- [x] UF-11 - Visualizacao Tabular Rica de Creditos no Tema V1 (`GAP-V1-07` / CAP-CRD-001):
  - [x] Implementar visualizacao da tabela completa de creditos (11 colunas) formatada no CSS do 14.6.1.
  - [x] Teste Feature de exibicao de creditos sob Tema V1.

## Fase 5 - Alertas, Prioridade e Identidade (P2)

- [x] UF-12 - Urgencia e Alerta de Prazo com Threshold R$ 75 no Tema V1 (`GAP-V1-08` / CAP-ALT-004):
  - [x] Portar a condicao de dominio `right_urgente` para as listagens e paineis do Tema V1 (`Rma::ehUrgentePorThreshold`).
  - [x] Sinalizar visualmente itens com prazo vencido e threshold na tabela V1 (`TrUrgente`).
- [x] UF-13 - Nivel de Prioridade no Tema V1 (`GAP-V1-09` / CAP-ALT-003):
  - [x] Expor campo e indicador de Prioridade (Baixa, Normal, Alta) no detalhe e formulario de criacao V1.
- [x] UF-14 - Criacao de Novo Usuario pelo Operador no Tema V1 (`GAP-V1-02` / CAP-ID-005):
  - [x] Criar superficie de novo usuario na estetica do Tema V1 acessivel via Controle/Usuarios (`usuarios-novo.blade.php`).

## Fase 6 - Parceiros, Logistica e Procedimentos (P3)

- [x] UF-15 - RMAs Associados na Visualizacao de Parceiros no Tema V1 (`GAP-V1-10` / CAP-PAR-006):
  - [x] Renderizar historico de RMAs vinculados ao cliente/fornecedor/fabricante no V1.
- [x] UF-16 - Consulta de Transporte Porto Alegre no Tema V1 (`GAP-V1-11` / CAP-LOG-001):
  - [x] Disponibilizar bloco/consulta de transporte regional na linguagem visual do 14.6.1.
- [x] UF-17 - Consulta de Destinatarios com Frete e CFOP no Tema V2 (`GAP-V2-04` / CAP-LOG-002):
  - [x] Disponibilizar visualizacao de dados logisticos de destinatarios na interface V2.
- [x] UF-18 - Procedimento Operacional / Ajuda no Tema V2 (`GAP-V2-05` / CAP-AUX-001):
  - [x] Integrar link e tela de ajuda/procedimentos na interface V2.

## Fase 7 - Blindagem Inicial com Testes e Reconciliacao (P4)

- [R] UF-19 - Criar `CapabilityContractTest`:
  - Reaberto para [R]: O provider atual cobre apenas ~15 rotas e um caso especial de Recebidos, nao sendo suficiente para validar as 65 capabilities. Requer expansao completa via catalogo canonico.
- [R] UF-20 - Criar `DescobribilidadeTemasTest`:
  - Reaberto para [R]: Faz apenas `assertSee(URL)` no HTML, provando presenca de href mas nao o fluxo real (menu -> clique -> request -> autorizacao -> view -> capacidade), conforme comprovado pela falha de clique em "Usuarios" no Tema V1.
- [R] UF-21 - Reconciliacao da matriz de uniao funcional:
  - Reaberto para [R]: Pendente de incorporar a segregacao `STATUS_FUNCIONAL` vs `STATUS_APRESENTACAO` e os resultados das jornadas executaveis.

## Fase 8 - Validacao Executavel Capability-by-Capability

- [ ] UF-22 - Catalogo executavel das 65 capabilities (`tests/Support/CapabilityCatalog.php`) com metadata de QA completa (id, nome, dominio, papel minimo, tipo, rotas V1/V2, requires_navigation, requires_persistence, requires_browser, status).
- [ ] UF-23 - Teste de cobertura machine-readable (`tests/Feature/Rma/CapabilityCatalogCoverageTest.php`) garantindo paridade com a OpenSpec e impedindo drift.
- [ ] UF-24 - Jornadas de navegacao e click-through V1/V2 (Playwright por dominio, iniciando por Identidade/Usuarios reproduzindo caminho de tela).
- [ ] UF-25 - Auditoria comportamental por acao (C1..C10 alem de GET 200, testando transicoes, criacoes, updates e persistencia).
- [ ] UF-26 - Registro e correcao de bugs encontrados (P0: BUG-CAP-ID-USERS-V1-001 - clique quebrado em Usuarios via menu V1).
- [ ] UF-27 - Fechamento dos gaps visuais V2 conhecidos (PAR15-USR-007/009, SEC-001, NOTE-001, PART-001..006, PART-DATA-001).
- [ ] UF-28 - Reconciliacao final da matriz e suite completa de testes.
