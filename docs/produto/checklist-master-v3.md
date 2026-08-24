# Checklist mestre — CellSystem RMA V3

Data: 2026-08-24. Documento único de acompanhamento: tudo que já foi investigado
(indexado, com onde encontrar o detalhe) e todo o trabalho que falta, quebrado em
tarefas pequenas e organizado por fase de implementação. `PLAN.md` continua sendo o
estado macro; `PLANO-ATAQUE.md` continua sendo o operacional (AGORA/DEPOIS/
DEPENDÊNCIAS); este documento é o **checklist granular** dos dois, para não perder
nenhum item pequeno entre uma sessão e outra.

**Sumário:** Parte 1 — tudo já investigado (índice) · Parte 2 — arquitetura decidida e
inventário de tecnologia · Parte 3 — as 10 fases de implementação, Fase 1 já em
especificação · Parte 4 — estratégia de migração em detalhe · Parte 5 — pendências
operacionais menores.

---

## Parte 1 — Índice de tudo já investigado (concluído)

### Repositório `08.24.1-gerenciador-de-rma` (este)

- [x] `docs/pareceres/2026-08-24-parecer-arqueologia-rma.md` — parecer consolidado,
      responde aos 17 pontos pedidos (o que era o produto, arquitetura, módulos,
      entidades, regras, fluxos, banco, tecnologias, preservar/substituir/descartar,
      segurança, arquitetura nova proposta, estratégia de fidelidade)
- [x] `docs/investigacoes-pendente/concluido/INV-RMA-00-arqueologia-cellsystem-15.9.7-concluido-2026-08-24.md`
      — investigação viva original, arquivada
- [x] `docs/legado/inventario-tecnico-15.9.7.md` — backup (SHA-256, estrutura, 1147
      arquivos), tecnologias, bibliotecas (AdminLTE/Bootstrap/jQuery/iCheck/Lightbox),
      banco/dumps, código morto/duplicado, arquivos sensíveis
- [x] `docs/legado/matriz-comparacao-apps-rma.md` — árvore RMA V2 → TEMA V1/TEMA V2,
      matriz funcional por item, ordem histórica confirmada pelo autor, achado do
      codinome "FIR" (TEMA V1) e "Build 2.5" (TEMA V2)
- [x] `docs/legado/cronologia-rma.md` — pistas de datação (marcadas como hipótese, não
      mais tratadas como linhagem sequencial)
- [x] `docs/legado/modelo-dominio-rma-legado.md` — entidades, campos, relacionamentos
      (RMA/`bd`, cliente, fabricante, fornecedor, assistência técnica, usuário, empresa,
      status×solução, estoque, auditoria, relatório)
- [x] `docs/legado/regras-negocio-rma-legado.md` — 21 regras catalogadas (RN-01 a
      RN-21) com origem, condição, consequência, evidência, situação por tema; inclui a
      regra MARKVISION, threshold R$75, e a regressão real de troca de senha
      (funciona em TEMA V1, quebrada em TEMA V2)
- [x] `docs/legado/inventario-banco-rma-v2.md` — schema completo das 9 tabelas,
      índices, domínios de valor por coluna, mapa de relação implícita por nome
- [x] `docs/legado/inventario-funcional-rma-v2.md` — 48 funcionalidades catalogadas
      (`LEG-RMA-001` a `LEG-RMA-048`) com situação por tema
- [x] `docs/legado/inventario-visual-tema-v1.md` / `-v2.md` — identidade visual (paleta,
      tipografia, navegação, classes CSS de estado), com evidência real do runtime
- [x] `docs/legado/legacy-runtime-ambiente.md` — histórico de design/bring-up do
      ambiente Docker (memória da sessão; operação real agora vive no repo Legacy)
- [x] `docs/produto/backlog-evolutivo.md` — Trilha B (EVO-SAAS, EVO-DOM, EVO-AUT,
      EVO-REL, EVO-SEG, EVO-PERF, EVO-IA), separada da reconstrução fiel
- [x] `docs/produto/paridade-v2-v3.md` — matriz de rastreamento V2→V3 por
      `LEG-RMA-NNN` (todos `PENDENTE` ainda — nenhuma OpenSpec/implementação começou)
- [x] `docs/desenvolvimento/ambiente-v2-v3.md` — como subir os dois ambientes,
      isolamento Docker, smoke test completo executado (login, banco compartilhado
      entre temas, troca de tema, Mailpit)

### Repositório `08.24.4-legacy-gerenciador-de-rma`

- [x] `docs/origem-backup.md` — origem do backup, SHA-256, estrutura, versão
      identificada
- [x] `README.md` — regras do repositório, como subir, runtime identificado
- [x] Ambiente executável validado: TEMA V1 e TEMA V2 autenticando, banco compartilhado
      confirmado, troca de tema com persistência confirmada, Mailpit testado de verdade

### O que NÃO foi investigado ainda (lacunas conhecidas, não perder de vista)

- [ ] RN-12 (threshold R$75 de urgência) — não localizado em TEMA V1 por busca textual;
      não confirmado por leitura linha a linha completa de `14.6.1/menujs-right/` e
      `14.6.1/page/`
- [ ] Uso real de Lightbox2 no fluxo do RMA — não confirmado onde é usado de fato
- [ ] Skin ativa do AdminLTE — não confirmada (nenhuma classe `skin-*` encontrada até
      agora)
- [ ] Telas internas do runtime (formulário completo de novo RMA, tela de detalhes) —
      só navegação/menu capturados, não o HTML completo de cada tela
- [ ] Screenshots em imagem (PNG) — nenhum gerado ainda, só HTML/texto

---

## Parte 2 — Arquitetura (decidida) e tecnologia (inventário)

- [x] `docs/arquitetura/INV-RMA-05-arquitetura-proposta.md` escrita — monólito
      modular (referência CONAHOM real, inspecionada, não copiada de memória),
      proporcional ao domínio (ver detalhe no próprio documento)
- [x] Princípio fixo: **fidelidade é do resultado percebido, não da implementação** —
      view em Blade/Vite nativos, nunca `include()` de PHP puro do legado
- [x] Princípio fixo: **autenticação nativa do Laravel** (`Auth`/Breeze/`Hash`), nunca
      sessão manual + SHA1 do legado — só o comportamento (5 papéis, redirect por tema,
      persistência de preferência) é preservado 1:1
- [x] Princípio fixo: **sem número mágico** — todo conceito de domínio fechado vira
      `enum` com métodos nomeados; número do legado só existe isolado no migrador
- [x] Módulos definidos: `Identidade`, `Parceiros`, `Rma` (créditos/relatórios dentro
      dele), `Compartilhado`; "Temas" é apresentação, não módulo de domínio
- [x] Inventário de tecnologia completo — tabela em `INV-RMA-05` §4: Laravel 13/PHP
      8.3, MySQL 8.4 (Sail), Auth/Breeze/Hash nativos, Blade+Vite+Sass+Bootstrap 5.3,
      JS moderno (jQuery só se justificado caso a caso), PHPUnit+Playwright
- [x] Decidido: FK real desde a baseline para `cliente`/`fabricante`/`fornecedor`/
      `assistencia_tecnica` (não string) — detalhe em `INV-RMA-05` §7, com registro
      ANTES/PROBLEMA/DEPOIS/MIGRAÇÃO/COMPATIBILIDADE/TESTE
- [x] Decidido: `Rma` (só ele) usa fronteira completa `Dominio`/`Aplicacao`/
      `Infraestrutura` com interface de repositório — `Identidade`/`Parceiros` usam
      Eloquent direto (ver `INV-RMA-05` §8 para a justificativa)
- [ ] Investigar granularidade real de compartilhamento entre TEMA V1 e TEMA V2 (fica
      para a Fase 8/apresentação, quando for desenhada em detalhe):
  - [ ] Comparar as duas telas de "novo RMA" lado a lado (V1 vs V2) campo a campo
  - [ ] Comparar as duas telas de "detalhe do RMA" lado a lado campo a campo
  - [ ] Decidir se a diferença é só de apresentação (view/layout) ou também de
        Controller/rota
- [ ] Definir enum/máquina de estado final de `status` (fica para a Fase 4/ciclo de
      vida, hoje só `PROVISÓRIO`)
- [ ] Definir enum final de `solucao` (fica para a Fase 4/6 — 17 valores confirmados,
      decidir se agrupa por categoria)
- [ ] Registrar decisão de identificadores (ID incremental vs UUID/ULID) para `Rma` e
      demais entidades (fica para a Fase 3/núcleo do Rma)

## Parte 3 — As 10 fases de implementação (ordem de dependência, `INV-RMA-05` §5)

### Fase 1 — Identidade — **EM ESPECIFICAÇÃO**

OpenSpec escrita: `openspec/changes/autenticacao-usuarios/{proposal,design,tasks}.md`.
Arquivo por arquivo já detalhado em `INV-RMA-05` §6. Tasks (de `tasks.md`, resumido):

- [ ] 2 migrations (`users` + `tentativas_de_acesso`)
- [ ] `Papel`, `TemaPreferido`, `ResultadoDeAcesso` (enums de domínio)
- [ ] `AutenticarUsuario`, `AlternarTemaPreferido` (casos de uso)
- [ ] `User`, `TentativaDeAcesso` (Eloquent) + `UserPolicy`
- [ ] `SessaoController`, `TemaPreferidoController` + rotas
- [ ] View de login mínima (sem fidelidade visual ainda)
- [ ] Factory/Seeder de usuário (1 por papel, para QA manual)
- [ ] 4 arquivos de teste (autenticação, permissão, alternância de tema, enum `Papel`)
- [ ] **Ajuste da revisão (`docs/arquitetura/revisao-fases-1-2-3.md`):** gestão de
      usuários incorporada a esta fase — `TrocarPropriaSenha` (`LEG-RMA-004`, TEMA V1
      como especificação, RN-21), `ResetarSenhaDeUsuario` (`LEG-RMA-003`),
      `UsuarioController` (`LEG-RMA-005`), `AtualizarAnotacaoPessoal` (`LEG-RMA-042`) +
      4 testes correspondentes
- [ ] Pendência registrada, não decidida: `LEG-RMA-002` (autocadastro com convite) —
      perguntar ao usuário antes de implementar
- [ ] `sail test` verde
- [ ] Atualizar `paridade-v2-v3.md` (`LEG-RMA-001`, `003`, `004`, `005`, `006`, `042`, `043`)
- [ ] Commit `#F1 - Identidade`

### Fase 2 — Parceiros — **EM ESPECIFICAÇÃO**

OpenSpec escrita: `openspec/changes/parceiros/{proposal,design,tasks}.md`. Arquivo por
arquivo detalhado em `INV-RMA-05` §7. **Decisão já tomada:** FK real desde a baseline
(não string), sem unificar os 4 tipos num `Parceiro` só (ideia fica em `EVO-DOM-001`).
Tasks (resumido):

- [ ] 4 migrations (`clientes`/`fabricantes`/`fornecedores`/`assistencias_tecnicas`)
- [ ] **Ajuste da revisão (`docs/arquitetura/revisao-fases-1-2-3.md`):**
      `app/Compartilhado/Uf.php` (enum das 27 UFs, prometido em `INV-RMA-05` §3 mas
      ausente do desenho original — campo `uf` era string solta)
- [ ] `trait TemEnderecoEContato` + 4 Eloquent models
- [ ] `EncontrarOuCriarCliente` (único caso de uso real — corrige dedup do legado)
- [ ] 4 Policies (delegam a `Papel::podeGravar()` da Fase 1)
- [ ] 4 Controllers (resource padrão) + rotas
- [ ] Views genéricas (`_form.blade.php` compartilhada + `index.blade.php`)
- [ ] 4 Factories + 5 arquivos de teste (CRUD ×4 + dedup)
- [ ] `sail test` verde, `paridade-v2-v3.md` atualizado, commit `#F2`

### Fase 3 — Rma núcleo — **EM ESPECIFICAÇÃO**

OpenSpec escrita: `openspec/changes/rma-cadastro-e-localizacao/{proposal,design,tasks}.md`.
Arquivo por arquivo detalhado em `INV-RMA-05` §8. **Decisão já tomada:** este é o único
módulo que usa a fronteira completa `Dominio`(puro)/`Aplicacao`/`Infraestrutura` com
interface de repositório (justificativa: a Fase 9/migração precisa dessa fronteira para
não vazar a leitura do schema `rma_legacy` pro resto da aplicação); identificador
incremental (sem UUID/ULID — sem caso de uso ainda). Tasks (resumido):

- [ ] Migration incremental de `rmas` (só os campos desta fase, não a tabela inteira) —
      **ajuste da revisão:** inclui `fornecedor_id` (ausente do desenho original)
- [ ] `Rma` (objeto de domínio puro), `RepositorioDeRmas` (interface),
      `CriterioDeBusca` (value object — substitui os `campo=TUDO/NF/SNPNSNID` do
      legado por named constructors)
- [ ] `RmasEmBanco` (Eloquent, implementação interna da infra) + binding no
      `AppServiceProvider`
- [ ] `CriarRma`, `EditarRma` (**ajuste da revisão** — `LEG-RMA-010` não tinha fase
      dona), `BuscarRmas`, `VerDetalheDoRma` (casos de uso)
- [ ] **Ajuste da revisão:** normalizações RN-13 (HGST→Hitachi) e RN-14 (cascata de
      `origem`) em `CriarRma`/`EditarRma` — não adiar para Fase 4/5; RN-17
      (`marcarestoque`) reproduzida só como valor do formulário, sem o cálculo morto
      do legado
- [ ] Controller + views mínimas + rotas
- [ ] Factory + 6 arquivos de teste (4 feature + 2 unit de `CriterioDeBusca`/`Rma`)
- [ ] `sail test` verde, `paridade-v2-v3.md` atualizado
      (`LEG-RMA-007/008/009/010/046`), commit `#F3`

### Fase 4 — Ciclo de vida — **NÃO INICIADA**

- [ ] Escrever `openspec/changes/rma-ciclo-de-vida/{proposal,design,tasks}.md`
- [ ] Definir enum de `status` e de `solucao`
- [ ] Casos de uso: receber/encaminhar/concluir/arquivar/reverter
      (`LEG-RMA-011` a `017`)
- [ ] Testes de todas as transições válidas/inválidas

### Fase 5 — Alertas e regras — **NÃO INICIADA**

- [ ] Escrever `openspec/changes/rma-alertas-e-prioridade/{proposal,design,tasks}.md`
- [ ] As 10 regras de alerta (`LEG-RMA-018` a `027`)
- [ ] Classificação visual de inconformidade (`LEG-RMA-028`)
- [ ] Regra MARKVISION e threshold R$75 (`LEG-RMA-019`/`029`, confirmar RN-12 antes)
- [ ] Teste unitário por regra, com fixture

### Fase 6 — Créditos e relatórios — **NÃO INICIADA**

- [ ] Escrever `openspec/changes/rma-creditos-e-relatorios/{proposal,design,tasks}.md`
- [ ] Fluxo de crédito (`LEG-RMA-036`)
- [ ] Relatórios RCD/RPEC/RMPE (`LEG-RMA-037` a `039`)
- [ ] Testes

### Fase 7 — Auditoria — **NÃO INICIADA**

- [ ] Escrever `openspec/changes/rma-logistica-e-historico/{proposal,design,tasks}.md`
      (cobre também `LEG-RMA-040`/`041` — consolidação de frete, boletins relacionados)
- [ ] Log de modificação de RMA (`LEG-RMA-044`) — decidir snapshot vs. diff estruturado
- [ ] Notificação por e-mail (`LEG-RMA-045`) — via Mailable do Laravel, destinatário
      configurável (não hardcoded como o legado)
- [ ] Testes

### Fase 8 — Apresentação (Temas V1/V2) — **NÃO INICIADA**

- [ ] Escrever `openspec/changes/temas-v1-v2/{proposal,design,tasks}.md`
- [ ] Resolver a investigação de granularidade pendente (Parte 2)
- [ ] Árvore de Blade por tema + Sass com paleta capturada
      (`inventario-visual-tema-{v1,v2}.md`)
- [ ] Seletor de tema com fidelidade visual
- [ ] QA visual lado a lado com o Legacy (`:8094`)

### Fase 9 — Migração V2→V3 — **NÃO INICIADA**

- [ ] Escrever `docs/arquitetura/INV-RMA-06-estrategia-reconstrucao.md`
- [ ] Escrever `openspec/changes/migracao-v2-v3/{proposal,design,tasks}.md`
- [ ] Mapa completo legado → V3 por tabela/campo (ver detalhe abaixo, Parte 4)
- [ ] Migrador oficial + relatório de reconciliação + idempotência
- [ ] Teste de migração determinístico

### Fase 10 — QA de paridade — **contínua, fecha por último**

- [ ] Paridade funcional por `LEG-RMA-NNN` (atualizar `paridade-v2-v3.md` a cada fase)
- [ ] Paridade visual (screenshot V2×V3, 390/768/1440)
- [ ] Paridade de dados (contagens pós-migração)

---

## Parte 4 — Estratégia de migração em detalhe (`INV-RMA-06` + `MIG-V3`, ainda não escritos)

- [ ] Criar `docs/arquitetura/INV-RMA-06-estrategia-reconstrucao.md`
- [ ] Mapa completo legado → V3, tabela por tabela:
  - [ ] `bd` → entidade `Rma` (mapear as ~56 colunas uma a uma)
  - [ ] `cliente` → `Cliente`/`Parceiro`
  - [ ] `fabricante` → `Fabricante`/`Parceiro`
  - [ ] `fornecedor` → `Fornecedor`/`Parceiro`
  - [ ] `assistencia_tecnica` → `AssistenciaTecnica`/`Parceiro`
  - [ ] `usuario` → `User` + papel/permissão
  - [ ] `log` → auditoria de autenticação
  - [ ] `modificacao` → auditoria de RMA (decidir: manter snapshot ou virar diff
        estruturado — ver regra de evolução do banco)
  - [ ] `relatorio` → decidir se vira config genérica ou é descartado
  - [ ] `assistencias` (tabela órfã) → **não migrar dados**, só usar a ideia (parceiro
        com papel) na modelagem nova
- [ ] Desenhar o migrador oficial (`php artisan rma:migrate-legacy` ou nome melhor)
  - [ ] Definir contrato de entrada (aponta para `rma_legacy` via conexão secundária?)
  - [ ] Implementar deduplicação de parceiro por nome (com relatório de ambiguidade)
  - [ ] Implementar relatório de reconciliação (contagem por entidade, avisos,
        não-reconhecidos)
  - [ ] Implementar idempotência (rodar duas vezes não duplica dados)
  - [ ] Escrever teste de migração determinístico (banco V2 conhecido → V3 → validação)
- [ ] Definir estratégia para valores legados fora do domínio moderno (`origem=Rolo`,
      `prioridade=urgente`, `status=retornou`, `empresa=R A`) — normalizar, enum legado,
      campo original preservado, ou warning? (decidir caso a caso, não em bloco)

## Parte 5 — Pendências operacionais menores

- [ ] `scripts/legacy-reset.sh` (Legacy) — escrito, ainda não testado de fato
      (`./scripts/legacy-reset.sh` rodar do zero e confirmar que reimporta o schema)
- [ ] Confirmar se `machadogolang/08.24.4-legacy-gerenciador-de-rma` foi mesmo
      publicado no GitHub (checado nesta sessão: **HTTP 404**, ainda não existe)
- [ ] Confirmar/trocar `machadogolang/08.24.1-gerenciador-de-rma` para privado (checado
      nesta sessão: **ainda público**)
- [ ] Capturar screenshots reais (PNG) dos dois temas autenticados, para
      `inventario-visual-tema-v1.md`/`-v2.md`

---

## Como usar este documento

Risque `[ ]` → `[x]` conforme cada tarefa pequena for concluída. Quando uma seção
inteira fechar, atualizar também o checkpoint correspondente em `PLANO-ATAQUE.md`
(que continua sendo a fonte de "qual é a fase atual"). Este arquivo não substitui os
documentos de detalhe listados na Parte 1 — é só o mapa de tudo, para não perder tarefa
pequena entre sessões.
