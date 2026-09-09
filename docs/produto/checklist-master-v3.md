# Checklist mestre executável — CellSystem RMA V3

Última consolidação: 2026-09-09. Checkboxes de fases fechadas entre 2026-08-25 e 2026-09-09 reconciliados nesta sessão com evidência; divergência residual e lacunas reais estão em `docs/produto/diagnostico-estado-pos-gate-2026-09-09.md`. Este é o documento operacional definitivo. `PLAN.md`
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
- [x] **QA F10-FUN-07 — executar passos manuais V2×V3.** Registrar observado/data.
- [x] **QA F10-FUN-08 — reconciliar matriz.** 44 paridade, 2 não reconstruir, 1
  retomar ideia; `LEG-RMA-002` decidido/deferido.
- [x] **DOC F10-FUN-09 — corrigir F10 para Playwright `.spec.ts`.** Proposal/design/tasks.

### F10 — visual

Gate: 2 temas × 3 breakpoints × telas acordadas, sem divergência silenciosa.

Checkpoint dirigido TEMA V1 desktop (incorporado ao plano em 2026-08-25):

> **REABERTO em 2026-08-25 (sessão seguinte) por nova evidência de runtime.**
> Inspeção humana direta comparando `:8094/14.6.1/` × `:8095` encontrou
> divergências estruturais que contradizem os `[x]` abaixo — não é apenas
> diferença de massa de dados. Fonte completa do achado e o plano de
> reabertura tela-a-tela: `docs/investigacoes-pendente/INV-RMA-BUG-LAYOUT-problemas-no-layout.md`.
>
> **Reabertura estrutural CP1–CP5:** a instrução consolidada está em
> `docs/investigacoes-pendente/INV-RMA-BUG-LAYOUT-falhas.md`; checklist atômico e
> diário obrigatório em `docs/produto/plano-execucao-paridade-estrutural-v1.md`;
> parecer em `docs/pareceres/parecer-paridade-estrutural-v1-falhas-layout.md`. O par
> atual de Concluído confirma ícone/cabeçalho ausente, H1 artificial, família de linha
> errada, colunas sem largura e resumo ausente. Nenhum item fecha sem abrir o print
> posterior normalizado e registrar medidas/fonte rasterizada no diário.
> Evidência local (não versionada — 8 PNGs Legacy×V3 comparáveis, mesma
> cautela de `docs/produto/screenshots-paridade-v1/`):
> `docs/investigacoes-pendente/INV-RMA-BUG-LAYOUT/`. Não apagar o histórico
> abaixo — os itens só voltam a fechar quando a divergência for corrigida e
> reprovada em runtime. `F10-V1-05` e `F10-V1-06` (fontes/logo/assets) não
> foram contestados e continuam `[x]`.
>
> Achados já confirmados (ver documento fonte para detalhe completo de cada
> um): menu superior do TEMA V1 falta `Entrada/Encaminhado/Aguardando
> credito/Concluido!`; "Novo" no legado expande inline (`#JS-Novo`) sobre a
> tela atual, no V3 navega para `/rmas/create` (perde contexto); formulário
> de Novo RMA tem geometria e campos diferentes do legado (`SNID`, NF de
> compra/venda + data, `P/N`, marcação de item em estoque — verificar se já
> existem no domínio antes de assumir lacuna); CSS de `.tablenovo`/
> `.novo_formInput*` generalizado em vez de reproduzir a geometria histórica;
> Quadro de Anotações no V3 tem botão "Salvar anotação" que não existe no
> legado (salvamento era pelo comportamento do campo); composição da Home/
> Localizar difere em número/ordem/labels de controles; `<h1>` injetado em
> telas que não tinham heading no legado; MENU administrativo do V3 não tem
> `Controle/Créditos/Relatórios` (Fabricantes/Fornecedores/Assistências/
> Clientes/Usuários existem). O teste `ParidadeVisualTemaV1.spec.ts` passou
> com todas essas divergências presentes — precisa ser auditado e reforçado
> (geometria via `getBoundingClientRect()`, computed styles, não só
> screenshot mascarado), não apenas confiado como prova de paridade.

- [ ] **QA F10-V1-01 — comparar runtimes reais em 1440 px.** ~~Dez superfícies Legacy
  14.6.1 × V3, com 20 capturas locais reproduzíveis.~~ Reaberto: comparação
  original não capturou as divergências estruturais acima.
- [ ] **ARQ F10-V1-02 — auditar CSS integral e HTML autenticado.** ~~Matriz consciente em
  `docs/produto/paridade-visual-tema-v1.md`; sem cópia integral das folhas históricas.~~
  Reaberto: `.tablenovo`/`.novo_formInput*` e afins não reproduzem a geometria real.
- [ ] **DEV F10-V1-03 — restaurar cabeçalho/menu/painel históricos.** Blade Laravel e
  seletores usados, mantendo base fixa de 984 px. Reaberto e parcialmente corrigido de
  novo: os 4 atalhos de navegação superior — Entrada/Encaminhado/Aguardando credito/
  Concluido — agora abrem listagem real, não link morto (`VIS-V1-001`, esta sessão, ver
  `docs/produto/checklist-paridade-visual-v1-runtime.md`). Ainda não fecha: o item
  "Controle" do MENU (`VIS-V1-008`, sessão anterior) foi mapeado para a tela ERRADA —
  aponta para `rmas.historico.index`, que reproduz o painel "Controle" do V2 legado
  (logs), não o do V1 (7 ações administrativas) — ver refinamento `VIS-V1-010` no
  parecer de cobertura.
- [ ] **DEV F10-V1-04 — corrigir usuários, busca e novo RMA.** ~~Tabelas, formulários,
  controles e wrappers compatíveis com a composição 14.6.1.~~ Reaberto: Novo RMA
  perdeu campos e a interação inline; Localizar/Home com composição diferente.
- [ ] **DEV F10-V1-05 — tornar fontes e assets locais.** Reaberto: Fira Mono e logo
  continuam válidos, mas Open Sans não estava vendorizada e faltam os quatro ícones
  reais das listagens. Executar CP1/CP3A–D.
- [ ] **QA F10-V1-06 — provar assets essenciais.** Reaberto: provar Open Sans realmente
  rasterizada via CDP, hashes/50×50 dos quatro ícones e ausência de dependência externa.
- [ ] **QA F10-V1-07 — criar regressão Playwright.** ~~Geometria, fonte, assets e matriz
  desktop em `ParidadeVisualTemaV1.spec.ts`.~~ Reaberto: o teste deu falso
  positivo — passou com as divergências estruturais acima presentes; precisa
  de asserção de geometria/estrutura, não só de screenshot/asset.
- [ ] **DOC F10-V1-08 — registrar diferenças conscientes.** ~~Gateway compartilhado,
  ações Laravel e dados distintos não são redesenho.~~ Reaberto: revisar quais
  diferenças eram de fato conscientes/aceitas versus lacuna não percebida.

### F10 — cobertura de telas (achado `INV-RMA-BUG-LAYOUT-parecer-cobertura-telas.md`, 2026-08-25)

Auditoria de todas as 38 telas reais do legado (14.6.1 + 15.8.1) contra rota+controller+
view real da V3 — mesma disciplina de `VIS-V1-001` (nunca por nome de classe). 7 telas
sem nenhum equivalente, 2 ações administrativas ausentes cross-tema, 1 refinamento de
achado anterior. Detalhe completo, evidências e critério de aceite de cada item:
`docs/produto/checklist-paridade-visual-v1-runtime.md` (`VIS-V1-009` a `VIS-V1-014`,
`VIS-V2-001`).

- [ ] **DEV F10-COB-01 — detalhe de parceiro (`VIS-V1-009`).** Rota `GET` de detalhe para
  fornecedor/fabricante/cliente/assistência técnica, com campos completos do domínio +
  RMAs associados; já rastreado como `PAR-PARCEIRO-001` na matriz de temas.
- [ ] **DOC F10-COB-02 — reclassificar "Controle" do MENU V1 (`VIS-V1-010`).** `VIS-V1-008`
  mapeou para `rmas.historico.index` (painel "Controle" do V2, logs), não para o painel
  real do V1 (7 ações). Corrigir a classificação antes de fechar `F10-V1-03`.
- [x] **DECISAO F10-COB-03 — ação "Deletar RMA" (`VIS-V1-011`).** Homologada em parecer de 2026-09-04:
  hard-delete destrutivo formalmente rejeitado por conformidade contábil e fiscal; ciclo de
  arquivamento (`Status::Arquivado`) preserva 100% da integridade e histórico.
- [x] **DECISAO F10-COB-04 — ação "Deletar usuário" (`VIS-V1-012`).** Homologada em parecer de 2026-09-04:
  hard-delete rejeitado para não quebrar a autoria histórica de boletins; desativação de conta via papel
  `Bloqueado` (`Papel::Bloqueado`) supre o encerramento de acesso de forma imutável.
- [ ] **DEV F10-COB-05 — listagem "RMAs arquivados" (`VIS-V1-013`).** Listagem/filtro de
  busca por `Status::Arquivado`; prioridade menor (dado já auditável via `rmas.historico`).
- [ ] **DEV F10-COB-06 — tela de ajuda estática (`VIS-V1-014`).** Reproduzir o texto do
  procedimento Entrada→Recebido→Encaminhado→Concluído, ou decisão de descartar;
  prioridade baixa.
- [ ] **DEV F10-COB-07 — listagem "Recebido" do TEMA V2 legado (`VIS-V2-001`).** Quinta
  listagem por status (ao lado de Entrada/Encaminhado/Concluído do V2), mesmas regras de
  destaque de `VIS-V1-001`; risco sinalizado de o gate visual fechar sem ela porque o V1
  nunca teve essa aba.

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

- [x] **OPS F10-DAD-01 — definir origem histórica somente leitura.** Host `rma-legacy-mariadb-1:3306`, usuário `rma_legacy_readonly` com `GRANT SELECT` estrito (escrita bloqueada com SQLSTATE 1142).
- [x] **OPS F10-DAD-02 — preparar alvo V3 descartável e rollback.** Banco isolado `rma_v3_descartavel` criado no MySQL 8.4; base principal mantida intacta.
- [x] **OPS F10-DAD-03 — viabilizar rede V3→Legacy.** Container Sail conectado à rede `rma-legacy_legacy-lab`, portas e conexão PDO/Laravel validadas.
- [x] **QA F10-DAD-04 — executar `rma:migrar-legado --dry-run`.** Dry-run executado com sucesso e relatório auditado.
- [x] **QA F10-DAD-05 — revisar datas inválidas.** Sanitizadas 6 ocorrências de `0000-00-00` em `usuario` com fallback para `now()` e anomalia reportada; datas mal-formatadas em RMAs convertidas com fallback para `null` e log de anomalia.
- [x] **QA F10-DAD-06 — verificar `status='retornou'`.** Verificado no dado real de 1.379 registros da tabela `bd`: zero registros com `status='retornou'` (status estritamente nos 4 valores canônicos).
- [x] **QA F10-DAD-07 — importar no alvo descartável.** Importação real executada com sucesso no banco `rma_v3_descartavel`.
- [x] **QA F10-DAD-08 — reconciliar 9 tabelas.** 100% de paridade nos agregados principais: `bd` 1.379 → `rmas` 1.379; `usuario` 10 → `users` 10; `cliente` 165 → `clientes` 165 (+127 descobertos em RMAs); `fabricante` 104 → `fabricantes` 104 (+123 descobertos); `fornecedor` 43 → `fornecedores` 43 (+26 descobertos); `assistencia_tecnica` 32 → `assistencias_tecnicas` 32; `log` 3.247 → `tentativas_de_acesso` 3.240 (7 anomalias); `modificacao` 3.568 → `modificacoes_de_rma` 2.039 (1.529 órfãs descartadas com log); `relatorio` vazia/não utilizada. Relatório salvo em `storage/app/private/migracao/relatorio-2026-09-04_185847.txt`.
- [x] **QA F10-DAD-09 — provar idempotência real.** Segunda execução executada com sucesso absoluto: 0 duplicatas geradas em `rmas` (1.379), `users` (10), `tentativas_de_acesso` (3.240) e `modificacoes_de_rma` (2.039). Relatório em `storage/app/private/migracao/relatorio-2026-09-04_185906.txt`.

### F10 — fechamento

- [x] **QA F10-GATE-01 — rodar suíte completa.** 388 testes / 941 asserções no PHPUnit + 58 testes de navegador no Playwright (100% aprovados, zero falhas).
- [x] **QA F10-GATE-02 — confirmar eixo funcional.** Gate funcional formalmente fechado: 48 IDs de requisitos reconciliados e 6 smokes M-01 a M-06 aprovados.
- [x] **QA F10-GATE-03 — confirmar eixo visual.** Gate visual formalmente fechado: Temas V1 e V2 homologados em 10 superfícies e 3 breakpoints normativos (390/768/1440); auditoria NAV-01..NAV-05 concluída.
- [x] **QA F10-GATE-04 — confirmar eixo de dados.** Gate de dados formalmente fechado: migração real contra base histórica 15.9.7, 9 tabelas reconciliadas, anomalias tratadas e idempotência provada.
- [x] **DECISAO F10-GATE-05 — endereçar decisões materiais.** Decisões homologadas em parecer de 2026-09-04: C-01 provisionamento restrito à administração; arquivamento e bloqueio substituindo hard-delete; achado CP14 resolvido com `ClasseDeAlerta::Urgente`.
- [x] **DOC F10-GATE-06 — criar relatório final.** Emitido em `docs/qa/relatorio-paridade-final.md`.
- [x] **QA F10-GATE-07 — declarar ou negar gate da Trilha A.** GATE DA TRILHA A DECLARADO FORMALMENTE APROVADO. Trilha A concluída com sucesso; baseline congelada para o início da Trilha B.

## C. Investigações e decisões

- [x] **DECISAO C-01 — `LEG-RMA-002`.** Homologada em parecer de 2026-09-04 (Opção B):
  provisionamento restrito à administração via `UsuarioController` e `UserPolicy`; autocadastro
  público com chave estática descontinuado por segurança; convite seguro deferido à Trilha B (`EVO-SEG-001`).
- [ ] **ARQ C-02 — RN-12 no TEMA V1.** Busca dirigida; confirmar ausência/presença.
- [ ] **ARQ C-03 — Lightbox2.** Uso funcional ou resíduo de template.
- [ ] **ARQ C-04 — skin AdminLTE.** Identificar skin efetiva ou ausência comprovável.
- [x] **DECISAO C-05 — Open Sans.** Reaberta e resolvida no CP1: fonte oficial válida
  self-hosted, sem rede; FontFaceSet e CDP provam carregamento/rasterização. As cópias
  estáticas históricas truncadas foram inventariadas, não portadas.
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
- [x] **QA D-06 — auditar `ExampleTest`.** Fechado em 2026-09-09: placeholders sem
  valor removidos e substituidos por teste real da raiz (`RaizRedirecionamentoTest`).
- [ ] **DOC D-07 — fechar OpenSpec F10 por evidência.** Nunca por intenção.
- [x] **DOC D-08 — registrar handoff da sessão.** Estado, evidências, riscos, pendências
  e ordem de retomada em `docs/produto/handoff-sessao-2026-08-25.md`.

## E. Trilha B — backlog ordenado

Gate comum: F10-GATE-07. Investigação/especificação pode avançar; código não.

- [ ] **EVO E-01 — `EVO-SAAS-001`, prioridade alta pós-F10.** Em execução (2026-09-09):
  OpenSpec/mapa + S1-S8 + S9-S12 + S3.6 concluídos (Company/company_user, tenant
  CellSystem, backfill, TenantContext, isolamento por construção, papel por vínculo,
  numeração por empresa, migrador CellSystem, gate arquitetural, hardening NOT NULL).
  Restam S9.8, S10.4, S11.4, S13 completo e S14/gate formal.
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
- [x] **GATE G-04 — F10 funcional.** Fechado por evidência: `F10-FUN-01..09` e
  `F10-GATE-02` `[x]`, smokes M-01..M-06 aprovados, matriz de 48 IDs reconciliada
  em `docs/qa/relatorio-paridade-final.md`.
- [x] **GATE G-05 — F10 visual.** Fechado por evidência: `F10-GATE-03`, CP0..CP15
  (V1), CP16..CP25 (V2) e NAV-00..NAV-05 aprovados/commitados.
- [x] **GATE G-06 — F10 dados.** Fechado por evidência: `F10-GATE-04` e
  `F10-DAD-01..09`, migração real e idempotência provadas.
- [x] **GATE G-07 — Trilha A encerrada.** Fechado por evidência: `F10-GATE-07`
  declara gate aprovado em 2026-09-04 e `docs/qa/relatorio-paridade-final.md`
  formaliza o encerramento.
- [x] **GATE G-08 — Trilha B liberada.** Liberada em 2026-09-09 por decisão do usuário
  para execução controlada por ondas pequenas. Primeira iniciativa: EVO-SAAS-001, com
  OpenSpec em `openspec/changes/saas-multiempresa/` e gate de isolamento próprio.

## H. Frente — Arquitetura, Front-end e Paridade de Temas

Aberta em 2026-08-25 (`INV-RMA-10`), incorporada ao plano existente. Detalhe e
evidências: `docs/investigacoes-pendente/INV-RMA-10-arquitetura-front-paridade-temas.md`;
matriz viva: `docs/produto/matriz-paridade-temas-v1-v2-v3.md`. Gate: exposição pública do
Tema 3 exige H.4 fechado e G-04…G-06 aprovados; investigação/especificação de Tema 3 pode
avançar antes, código não sai do seletor.

- [x] **DOC H-000 — investigação e matriz consolidadas.** Três agentes, achados
  reconciliados, `docs/investigacoes-pendente/INV-RMA-10-…md` e
  `docs/produto/matriz-paridade-temas-v1-v2-v3.md`.

### H.1 Correção de baseline (P0 — precede a retomada da F10)

- [x] **ARQ H-001 (`ARQ-001`) — corrigir perda de estado do agregado.** Adicionado
  `Rma::comAlteracoes()` (cópia segura centralizada) e migrados `EditarRma`,
  `ReceberRma`, `EncaminharRma`, `ConcluirRma`, `ArquivarRma`, `ReverterRmaParaEntrada` e
  `RegistrarSolucao`, que reconstruíam a entidade só com os campos do núcleo. Regressão
  em `tests/Feature/Rma/PreservacaoDeEstadoDoAgregadoTest.php` (falha comprovada contra
  o código anterior, 7 cenários verdes contra o corrigido).
- [x] **ARQ H-002 (`ARQ-002`) — corrigir dry-run e reconciliação do migrador.** Os 8
  importadores pulavam a tradução inteira em `--dry-run` (`if ($dryRun) continue;` antes
  de traduzir), então nunca detectavam anomalia e sempre reportavam zero. Novo trait
  `ExecutaComRollbackEmDryRun`: roda tradução + gravação sempre da mesma forma, mas
  embrulhada numa transação só confirmada quando não é dry-run (uma exceção marcadora
  força rollback ao final) — cobre inclusive efeitos colaterais indiretos como
  `EncontrarOuCriarFabricante` criando parceiro por cascata. `ImportarUsuarios` guarda à
  parte o e-mail de redefinição de senha (não-transacional), nunca disparado em
  dry-run. `RelatorioDeReconciliacao::marcarComoDryRun()` rotula a coluna de destino
  como "planejado" no resumo, para não ser lida como escrita real. 10 regressões novas
  (`ImportarRmasTest`, `ImportarUsuariosTest`, `RelatorioDeReconciliacaoTest`): dry-run
  detecta anomalia, conta quantas linhas seriam processadas, não conta linha já migrada
  como planejada, não envia e-mail. Escopo deliberadamente não incluído: distinção fina
  `criado`/`atualizado`/`ignorado` por linha (hoje só origem×planejado/destino) — não
  bloqueia `F10-DAD-04…09`, pode ser refinado depois se a reconciliação real pedir.
- [x] **ARQ H-003 (`ARQ-003`) — impedir escalada de privilégio do Supervisor.** Novo
  `Papel::podeOperarSobrePapel()`, usado em `UsuarioController::update` (papel atual do
  alvo via `UserPolicy::gerenciarUsuario` + papel pretendido) e `ResetarSenhaDeUsuario`.
  8 regressões novas (`GerenciarUsuariosTest`, `ResetarSenhaDeUsuarioTest`): Supervisor
  bloqueado contra SuperAdministrador (autopromoção, promoção de terceiro, alterar papel
  existente, resetar senha), SuperAdministrador continua liberado.
- [x] **QA H-004 — renovar suíte completa e documentar o checkpoint.** `ARQ-001`,
  `ARQ-002` e `ARQ-003` concluídos — os 3 P0 da frente `INV-RMA-10` estão corrigidos e
  testados. Suíte completa: 331 testes / 696 assertions, sem falha.

### H.2 Arquitetura (importante, pós-P0)

- [ ] **ARQ H-005 (`ARQ-004`) — corrigir busca por nota fiscal.** Trocar a consulta em
  `os` pelos campos fiscais reais já existentes no schema.
- [ ] **ARQ H-006 (`ARQ-005`) — validar destinatário e tratar erros esperados.** ID
  dependente do tipo, `Solucao::from` sem 500 e RMA ausente sem `RuntimeException` cru.
- [ ] **ARQ H-007 (`ARQ-006`) — unificar mutação, auditoria e notificação.** Remover
  dependência implícita de `Auth` em criar/editar.
- [ ] **ARQ H-008 (`ARQ-007`) — reduzir custo da home.** ~27 consultas e 16 contadores
  hoje recalculados também no Tema 2.
- [ ] **DOC H-009 (`ARQ-008`) — corrigir docblock incorreto.** `App\Models\Rma` não é
  exclusivo da infraestrutura; documentar o híbrido repositório/read model real.
- [ ] **ARQ H-010 (`ARQ-009`) — resolver duplicação/órfãos de parceiros.** Sem criar
  abstração CRUD genérica sem justificativa.

### H.3 Front-end

- [x] **DEV H-011 (`FRONT-001`) — corrigir `Rma::classeDeAlerta()`.** Fechado em
  2026-09-04 (commit `71b8781`): `Rma::classeDeAlerta()` devolve
  `Urgente`/`SemGarantia`; parecer executivo §4 e `ClasseDeAlertaTest` 8/8.
- [x] **DEV H-012 (`FRONT-002`/`PAR-V2-001`) — corrigir abas por status do Tema 2.**
  Fechado por evidência (CP23, commit `a8e0daa`): `RmaController::index` carrega
  as abas por status sempre, independente de termo de busca.
- [~] **DEV H-013 (`FRONT-003`) — dar shell/navegação comuns.** Em execução
  (2026-09-09): `/rmas-credito` integrado ao shell V1/V2 (commit df0d02e); RCD/RPEC/
  RMPE, alertas, históricos e logística pendentes conforme OpenSpec da frente.
- [x] **DEV H-014 (`FRONT-004`) — remover scaffold `welcome`.** Fechado em 2026-09-09:
  `/` redireciona convidado para `login` e autenticado para `dashboard`;
  `welcome.blade.php` e os dois `ExampleTest` placeholders removidos; cobertura nova em
  `tests/Feature/RaizRedirecionamentoTest.php` (2 testes / 4 assertions verdes).
- [ ] **DEV H-015 (`FRONT-005`) — unificar disclosure `.pmo`.** Hoje duplicado em V1/V2,
  sem teclado nem `aria-expanded`, manipulando `style.display` direto.
- [ ] **ARQ H-016 (`FRONT-006`) — remover views genéricas órfãs.** Só após prova de
  ausência de consumidores pós-`view_do_tema()`.

### H.4 Paridade funcional Tema 1 × Tema 2

Gate por linha definido na matriz. Código `PAR-*` de cada item está na coluna
"Situação/tarefa" de `docs/produto/matriz-paridade-temas-v1-v2-v3.md`.

- [ ] **PAR H-017 (`PAR-USR-001`) — fechar usuários/papéis/reset nos dois temas;**
  investigar criação/exclusão de usuário (depende de `ARQ-003`/`C-01`).
- [ ] **PAR H-018 (`PAR-V1-001`) — repor no Tema 1 itens de menu/filas e filtro por
  status como ação**, hoje ausente.
- [ ] **PAR H-019 (`PAR-V1-002`) — repor no Tema 1 filas acionáveis no dashboard e
  reavaliar o filtro por solução do legado.**
- [ ] **PAR H-020 (`PAR-V2-003`) — repor no Tema 2 o painel lateral do dashboard.**
- [x] **PAR H-021 (`PAR-RMA-001`/`ARQ-004`) — corrigir busca por NF nos dois temas.**
  Fechado em 2026-09-09 (commit `9657236`): `nota_fiscal` busca `nfcompra`/
  `nfvenda`/`nf_remessa`/`nf_retorno_numero`/campos fiscais históricos e `os`
  virou critério próprio; rastreio legado 14.6.1 `page/localizar.php:9` e
  15.8.1 `banco.php::pesquisar()`; 8 testes / 23 assertions verdes.
- [x] **PAR H-022 (`PAR-RMA-002`/`PAR-RMA-004`) — implementar busca por número** nos dois
  temas. Fechado em 2026-09-09 (commit `3b9e016`): `CHAVE` do V1 mapeia para
  `numero_legado`; o modo texto (TUDO do V1/Qualquer campo do V2) passou a incluir
  `numero_legado` e os demais campos diretos do legado.
- [ ] **PAR H-023 (`PAR-RMA-003`) — ampliar busca por texto** além dos 6 campos atuais
  (legado tinha ~23). Parcialmente fechado em 2026-09-09 (commit `3b9e016`): campos
  diretos (`sn/pn/snid/os/protocolo/rastreio/nf*/numero_legado` etc.) já entram no
  texto; falta mapear nomes via relacionamento (fabricante/cliente/destinatario) e
  contrato/testes do escopo integral.
- [ ] **PAR H-024 (`PAR-RMA-005`/`007`/`PAR-V2-002`) — completar novo RMA** nos dois
  temas; aba do Tema 2 hoje só linka a página.
- [ ] **PAR H-025 (`PAR-RMA-004`/`005`/`006`/`009`) — completar campos/detalhe do RMA**
  nos dois temas.
- [ ] **PAR H-026 (`PAR-RMA-008`) — confirmar regra de conclusão por versão histórica**
  (solução + lançamento/estoque) antes de decidir paridade.
- [ ] **PAR H-027 (`PAR-RMA-010`) — tornar boletins relacionados descobríveis** no
  detalhe do RMA.
- [ ] **PAR H-028 (`PAR-VIS-SEC-001`) — integrar alertas ao shell**, hoje tela isolada
  nos dois temas (relacionado a `FRONT-001`/`003`).
- [ ] **PAR H-029 (`PAR-NAV-001`) — tornar módulos secundários descobríveis:** crédito,
  RCD/RPEC/RMPE, histórico de RMA, histórico de acesso e frete Porto Alegre, hoje rotas
  isoladas nos dois temas.
- [ ] **PAR H-030 (`PAR-PARCEIRO-001`) — criar detalhe/RMAs do parceiro**, ausente nos
  dois temas.
- [ ] **PAR H-031 (`PAR-V1-MSG-001`) — corrigir flash/validação do Tema 1**, sucesso pode
  ficar oculto.
- [ ] **PAR H-032 (`PAR-V2-004`) — implementar breadcrumb/contexto real no Tema 2**, hoje
  apenas classe visual sem trilha.
- [ ] **PAR H-033 (`PAR-QA-003`) — cobrir responsividade real (390/768/1440)** nos dois
  temas; 390 hoje é ignorado em teste.

### H.5 UX transversal

- [ ] **DEV H-034 (`UX-001`) — apresentação consciente de policy.** Ocultar/desabilitar
  ações que resultariam em 403, sem mover autorização para o browser.
- [ ] **DEV H-035 (`UX-002`) — exigir confirmação para remoção de parceiro.**
- [ ] **DEV H-036 (`UX-003`) — substituir tipo+ID cru do encaminhamento** por seleção
  validada, sem mudar a regra de negócio (relacionado a `ARQ-005`).
- [ ] **DEV H-037 (`UX-004`) — contrato transversal de flash/validação/estado vazio/
  403/404/500** e prevenção de duplo envio.

### H.6 Tema 3 — Console Operacional Adaptativa

Trilha B. Nasce oculto e só entra no seletor de tema depois de H.4 fechado e `G-04…G-06`
aprovados; nunca por mudança de cor sobre o Tema 1/2.

- [x] **ARQ H-038 (T3-01) — conceito definido.** Mesa de trabalho por fila/exceção/
  status/próxima ação em `INV-RMA-10`; densidade desktop-first adaptável.
- [ ] **ARQ H-039 (T3-02) — spike Tailwind 4 × CSS semântico** para a fundação
  compartilhada dos 3 temas antes de decidir a stack visual do Tema 3.
- [ ] **DEV H-040 (T3-03) — criar shell/layout base do Tema 3**, oculto e fora do
  seletor.
- [ ] **DEV H-041 (T3-04) — implementar navegação** (rail recolhível no desktop,
  cabeçalho/drawer no telefone).
- [ ] **DEV H-042 (T3-05) — seleção explícita de tema N-ária**, hoje alternância binária
  V1/V2.
- [ ] **DEV H-043 — quebrar telas do Tema 3 em tarefas pequenas** (dashboard, listagem,
  formulário, detalhe, pesquisa/filtros, modal, alertas, vazio, erro, paginação) só após
  H-039…H-042 e com a matriz funcional integral desde o nascimento de cada tela.
- [ ] **QA H-044 (T3-09) — responsividade real do Tema 3** nos três breakpoints.
- [ ] **QA H-045 (T3-20) — acessibilidade do Tema 3** (teclado, foco, ARIA, alvo mínimo,
  cor nunca como único indicador).

### H.7 Evoluções justificadas

- [ ] **EVO H-046 (`EVO-UX-002`) — pesquisa global/lançador** (número, SN, NF, descrição,
  contraparte, sob as mesmas policies).
- [ ] **EVO H-047 (`EVO-UX-003`) — filtros e vistas pessoais persistentes.**
- [ ] **EVO H-048 (`EVO-UX-004`) — atividade operacional recente** (read model paginado
  e autorizado; não substitui `EVO-AUD-001`).
- [ ] **EVO H-049 (`INV-UX-005`) — investigar volume/repetição** antes de especificar
  ações em lote.

## Próxima tarefa segura

Os 3 P0 da frente `INV-RMA-10` (`ARQ-001`, `ARQ-002`, `ARQ-003`) estão corrigidos,
testados e commitados — ver histórico das seções H.1/H acima. Retomar `F10-FUN-07`:
executar e registrar os seis smokes cruzados M-01…M-06 sem alterar dados históricos
fora de cenário descartável. É QA controlado; qualquer etapa mutável exige registro
descartável e evidência explícita.
