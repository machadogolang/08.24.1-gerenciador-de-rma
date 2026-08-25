# Checklist mestre — CellSystem RMA V3

Data: 2026-08-24, última reconciliação 2026-08-25 (comparação viva V3×Legado completa,
`docs/produto/comparacao-v3-legado-final.md`). Documento único de acompanhamento:
tudo que já foi investigado (indexado, com onde encontrar o detalhe) e todo o trabalho
que falta, quebrado em tarefas pequenas e organizado por fase de implementação.
`PLAN.md` continua sendo o estado macro; `PLANO-ATAQUE.md` continua sendo o operacional
(AGORA/DEPOIS/DEPENDÊNCIAS, com o detalhe do que falta em cada fase espelhado deste
documento); este documento é o **checklist granular** dos dois, para não perder nenhum
item pequeno entre uma sessão e outra.

**Sumário:** Parte 1 — tudo já investigado (índice) · Parte 2 — arquitetura decidida e
inventário de tecnologia · Parte 3 — as 10 fases de implementação, todas com OpenSpec
completo (`proposal`/`design`/`tasks`); Fases 1-5 implementadas e testadas, Fases 6-10
especificadas arquivo-por-arquivo (`INV-RMA-05` §11-§15,
`INV-RMA-06` para a Fase 9) · Parte 4 — estratégia de migração em detalhe (`INV-RMA-06`
escrito) · Parte 5 — pendências operacionais menores.

**Trilha B:** três investigações de arquitetura abertas e concluídas nesta sessão,
implementação propositalmente **não iniciada** em nenhuma delas (só depois da baseline
de paridade da Trilha A ser fechada pela Fase 10) — registrado aqui, não implementado:
- `docs/arquitetura/INV-RMA-07-evolucao-saas-multiempresa.md` — SaaS multiempresa
  (`EVO-SAAS-001`/`002`), fronteira de tenant/banco/isolamento decidida.
- `docs/arquitetura/INV-RMA-08-tema-v3-mobile-first.md` — tema V3 mobile-first (novo
  design, sem fidelidade ao Bootstrap 3 de V1/V2), decisões em `backlog-evolutivo.md`
  §`EVO-*` (linhas 116-171).
- `docs/arquitetura/INV-RMA-09-arquivos-e-configuracao-admin.md` — anexos de RMA
  (`AnexoDoRma` dentro do módulo `Rma` existente) e hub de configuração administrativa,
  decisões em `backlog-evolutivo.md` §A/§B (linhas 182-337).

Ver `docs/produto/backlog-evolutivo.md` para o backlog completo por `EVO-*`.

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
      `LEG-RMA-NNN` (atualizada a cada fase concluída — Fases 1-4 já em `PARIDADE`;
      demais itens `PENDENTE` até a fase correspondente ser implementada)
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
- [x] Granularidade de compartilhamento entre TEMA V1 e TEMA V2 — **decidida por
      evidência já reunida nas Fases 1-7** (`INV-RMA-05` §13): Controllers/casos de uso
      únicos, views/rotas por tema. 2 sub-itens continuam pendentes, mas bloqueiam só a
      *implementação* da Fase 8, não mais o *planejamento*:
  - [ ] Comparar as duas telas de "novo RMA"/"detalhe do RMA" lado a lado campo a campo
        (ainda depende de renderizar telas internas reais de TEMA V1, não capturadas)
  - [ ] Confirmar mecanismo real das âncoras de TEMA V2 (AJAX vs. scroll) inspecionando
        o LEGACY-RUNTIME
- [x] Enum/máquina de estado final de `status` definida — `INV-RMA-05` §9 (Fase 4):
      `Entrada`/`Recebido`/`Encaminhado`/`Concluido`/`Arquivado`, sem `Retornou`
- [x] Enum final de `solucao` definido — `INV-RMA-05` §9 (Fase 4): 16 valores
      confirmados por leitura direta de `15.8.1/page/rma.php`, sem agrupamento por
      categoria (só o método `implicaMesmoAparelhoDeRetorno()` para RN-15)
- [x] Identificadores: `id` incremental do Eloquent (decidido na Fase 3, `INV-RMA-05`
      §8) — mantido para as fases seguintes, sem novo caso de uso de exposição pública

## Parte 3 — As 10 fases de implementação (ordem de dependência, `INV-RMA-05` §5)

### Fase 1 — Identidade — **CONCLUÍDA (2026-08-24)**

OpenSpec escrita: `openspec/changes/autenticacao-usuarios/{proposal,design,tasks}.md`.
Arquivo por arquivo já detalhado em `INV-RMA-05` §6. Implementação completa, `sail test`
100% verde (36/36 testes, 91 assertions) e login real confirmado por `curl` de ponta a
ponta (Operador → `/perfil`, Supervisor → `/usuarios`, senha errada/usuário bloqueado →
`/login`). Tasks (de `tasks.md`, resumido):

- [x] 2 migrations (`users` + `tentativas_de_acesso`)
- [x] `Papel`, `TemaPreferido`, `ResultadoDeAcesso` (enums de domínio)
- [x] `AutenticarUsuario`, `AlternarTemaPreferido` (casos de uso)
- [x] `User`, `TentativaDeAcesso` (Eloquent) + `UserPolicy`
- [x] `SessaoController`, `TemaPreferidoController` + rotas
- [x] View de login mínima (sem fidelidade visual ainda)
- [x] Factory/Seeder de usuário (1 por papel, para QA manual)
- [x] 4 arquivos de teste (autenticação, permissão, alternância de tema, enum `Papel`)
- [x] **Ajuste da revisão (`docs/arquitetura/revisao-fases-1-2-3.md`):** gestão de
      usuários incorporada a esta fase — `TrocarPropriaSenha` (`LEG-RMA-004`, TEMA V1
      como especificação, RN-21), `ResetarSenhaDeUsuario` (`LEG-RMA-003`),
      `UsuarioController` (`LEG-RMA-005`), `AtualizarAnotacaoPessoal` (`LEG-RMA-042`) +
      4 testes correspondentes
- [ ] Pendência registrada, **não decidida deliberadamente**: `LEG-RMA-002`
      (autocadastro com convite) — aguardando o usuário escolher opção A ou B (ver
      `proposal.md`); nem A nem B foi implementada nesta fase
- [x] `sail test` verde (36/36)
- [x] Atualizar `paridade-v2-v3.md` (`LEG-RMA-001`, `003`, `004`, `005`, `006`, `042`, `043` → PARIDADE)
- [x] Commit `#F1 - Identidade`

### Fase 2 — Parceiros — **CONCLUÍDA (2026-08-24)**

OpenSpec escrita: `openspec/changes/parceiros/{proposal,design,tasks}.md`. Arquivo por
arquivo detalhado em `INV-RMA-05` §7. **Decisão já tomada:** FK real desde a baseline
(não string), sem unificar os 4 tipos num `Parceiro` só (ideia fica em `EVO-DOM-001`).
Implementação completa, `sail test` 100% verde (61/61 testes, 143 assertions — 36 da
Fase 1 + 25 novos) e dedup de `EncontrarOuCriarCliente` confirmada por `tinker` de ponta
a ponta. Tasks (resumido):

- [x] 4 migrations (`clientes`/`fabricantes`/`fornecedores`/`assistencias_tecnicas`)
- [x] **Ajuste da revisão (`docs/arquitetura/revisao-fases-1-2-3.md`):**
      `app/Compartilhado/Uf.php` (enum das 27 UFs, prometido em `INV-RMA-05` §3 mas
      ausente do desenho original — campo `uf` era string solta)
- [x] `trait TemEnderecoEContato` + 4 Eloquent models
- [x] `EncontrarOuCriarCliente` (único caso de uso real — corrige dedup do legado)
- [x] 4 Policies (delegam a `Papel::podeGravar()` da Fase 1)
- [x] 4 Controllers (resource padrão) + rotas
- [x] Views genéricas (`_form.blade.php` compartilhada + `index.blade.php`)
- [x] 4 Factories + 5 arquivos de teste (CRUD ×4 + dedup)
- [x] `sail test` verde, `paridade-v2-v3.md` atualizado, commit `#F2`

### Fase 3 — Rma núcleo — **CONCLUÍDA**

OpenSpec: `openspec/changes/rma-cadastro-e-localizacao/{proposal,design,tasks}.md`
(tudo `[x]`). Único módulo com a fronteira completa `Dominio`(puro)/`Aplicacao`/
`Infraestrutura` com interface de repositório — implementado exatamente como
justificado (a Fase 9/migração vai usar essa fronteira). Tasks:

- [x] Migration incremental de `rmas` (inclui `fornecedor_id`, ajuste da revisão)
- [x] `Rma` (objeto de domínio puro), `RepositorioDeRmas` (interface — ganhou um
      método `atualizar()` não previsto no snippet original do `design.md`, necessário
      para `EditarRma` não furar a fronteira), `CriterioDeBusca` (value object)
- [x] `RmasEmBanco` (Eloquent interno) + binding no `AppServiceProvider`
- [x] `CriarRma`, `EditarRma`, `BuscarRmas`, `VerDetalheDoRma` (casos de uso)
- [x] Normalizações RN-13/RN-14 em `CriarRma`/`EditarRma`, confirmadas de ponta a ponta
      na criação e na edição; RN-17 sem o cálculo morto do legado
- [x] Controller + views mínimas + rotas
- [x] Factory + 6 arquivos de teste (4 feature + 2 unit de `CriterioDeBusca`/`Rma`)
- [x] `sail test` verde (85/85, mantendo os 61 das Fases 1-2), `paridade-v2-v3.md`
      atualizado (`LEG-RMA-007/008/009/010/046` → PARIDADE), commit `#F3`

### Fase 4 — Ciclo de vida — **CONCLUÍDA**

OpenSpec: `openspec/changes/rma-ciclo-de-vida/{proposal,design,tasks}.md` (tudo `[x]`).
Estendeu `Dominio\Rma`/`RepositorioDeRmas`/`RmasEmBanco` da Fase 3, não recriou.
**Decisões confirmadas:** `ArquivarRma` usa TEMA V2 como especificação (TEMA V1
confirmado quebrado — `Fatal Error` incondicional, classe `controle` inexistente),
provado por `ArquivarRmaTest` (arquiva `Recebido` com sucesso, o cenário que quebraria
em TEMA V1); `Solucao` enum com os 16 valores lidos diretamente de
`15.8.1/page/rma.php:578-595`; datas por transição
(`recebido_em`/`encaminhado_em`/`concluido_em`/`arquivado_em`), não `updated_at`
genérico; `destinatario` como par `destinatario_type`/`destinatario_id` (relação
polimórfica Eloquent) — no objeto de domínio puro, representado como
`destinatarioType`/`destinatarioId` (string/int), mesmo padrão de `fabricanteId`/
`fornecedorId` (sem acoplar o domínio a models Eloquent). Tasks:

- [x] Migration incremental de `rmas` (`status`, datas por transição, `protocolo`,
      `solucao`, `snretorno`, `destinatario_type`/`destinatario_id` polimórficos)
- [x] `Status`, `Solucao` (enums de domínio, sem backing numérico/case `Retornou`)
- [x] `Dominio\Rma` estendido com os novos campos + `comSnretornoAutoPreenchido()` (RN-15)
- [x] `Papel::podeReverterAlemDoMesmoDia()` (novo método, Fase 1 estendida)
- [x] `ReceberRma`, `EncaminharRma`, `ConcluirRma` (+ evento `RmaConcluido`),
      `ArquivarRma`, `ReverterRmaParaEntrada`, `RegistrarSolucao`
      (`LEG-RMA-011` a `017`, `047`) — todos usando `RepositorioDeRmas::atualizar()`
      já existente, sem método novo por transição
- [x] `RmasEmBanco`/`Models\Rma` atualizados (casts de `Status`/`Solucao`,
      `morphTo('destinatario')`)
- [x] `CicloDeVidaController` + `_acoes_de_transicao.blade.php` + rotas
- [x] 6 arquivos de teste feature + 2 unit (+ 1 unit em `PapelTest` já existente)
- [x] `sail test` verde (131/131, mantendo os 85 das Fases 1-3),
      `paridade-v2-v3.md` atualizado (`LEG-RMA-011/012/013/014/015/016/017/047`),
      teste manual via `tinker` (receber→encaminhar→concluir, `snretorno`
      auto-preenchido confirmado), commit `#F4`

### Fase 5 — Alertas e regras — **CONCLUÍDA**

OpenSpec: `openspec/changes/rma-alertas-e-prioridade/{proposal,design,tasks}.md` (tudo
`[x]`). Arquivo por arquivo detalhado em `INV-RMA-05` §10. **Decisão confirmada:**
filtro de data inteiramente no SQL (query builder), nunca em PHP pós-`SELECT` — elimina
por construção a classe de bug "num_rows mentiroso" do legado; todos os limites de data
usam operador estrito (`>`/`<`). RN-12 (threshold R$75) implementada para os dois temas
(inferência registrada, não evidência direta — ver `design.md`). Coluna `valor`
(ausente do schema original do `design.md`) adicionada nesta revisão — origem
confirmada em `15.8.1/banco.php:777`. Tasks:

- [x] Migration incremental (`prioridade`, `marcarestoque`, NF compra/venda,
      `lancadoretorno`, `valor`)
- [x] `Origem`, `Prioridade` (sem case `Urgente` morto), `StatusDeLancamento`,
      `ClasseDeAlerta` (enums de domínio)
- [x] `Dominio\Rma` estendido (`classeDeAlerta()`, `prazoLegal()`, novas propriedades
      readonly incl. `createdAt`)
- [x] 10 classes de regra + `UrgenciaPorThreshold` em `app/Rma/Aplicacao/Alertas/`
      (cada uma lendo `App\Models\Rma` diretamente, filtro inteiro no SQL)
- [x] `RmasEmBanco`/`Models\Rma` atualizados (casts de `Prioridade`/`StatusDeLancamento`,
      relações `fabricante()`/`fornecedor()` para o join de `NaoVaiDarGarantia`) —
      `origem` deliberadamente SEM cast Eloquent (ver decisão no
      `log-implementacao-v3.md`)
- [x] `PainelDeAlertasController` + `_painel_de_alertas.blade.php` + rota
      `rmas-alertas` (view mínima, sem fidelidade visual — Fase 8)
- [x] 12 arquivos de teste unitário (10 regras + `ClasseDeAlertaTest` +
      `UrgenciaPorThresholdTest`), cada regra de data com caso que dispara/não
      dispara/limite exato (prova do operador estrito)
- [x] `sail test` verde (190/190, mantendo os 131 das Fases 1-4),
      `paridade-v2-v3.md` atualizado (`LEG-RMA-018` a `029` → PARIDADE), teste manual
      via `tinker` (RMA `valor=100.00` disparado por `UrgenciaPorThreshold`,
      `valor=75.00` exato não disparado — operador estrito confirmado), commit `#F5`

### Fase 6 — Créditos e relatórios — **CONCLUÍDA**

OpenSpec: `openspec/changes/rma-creditos-e-relatorios/{proposal,design,tasks}.md` (tudo
`[x]`). Arquivo por arquivo detalhado em `INV-RMA-05` §11. Cobre `LEG-RMA-036` a `039` e
`048` (reconstrói só a intenção do módulo de créditos quebrado em TEMA V2 — um fluxo
único, não 3 sub-rotas). **Decisão confirmada:** sem transição automática
`PendenteCredito`→`GeradoCredito`→`credito_disponivel=true` — o legado também não
automatiza (`EVO-AUT-002` fica como melhoria futura). RMPE usa intervalo de datas real
(`data_inicio`/`data_fim` obrigatórios via validação de request), corrigindo o
intervalo hardcoded para "2014" do legado (bug de manutenção, não RN documentada).
Tasks:

- [x] Migration incremental (`credito_disponivel`)
- [x] `MarcarCreditoDisponivel`, `AguardandoCredito` em `app/Rma/Aplicacao/`
- [x] 3 relatórios (RCD/RPEC/RMPE) em `app/Rma/Aplicacao/Relatorios/` — RMPE corrige
      intervalo hardcoded para 2014
- [x] `RelatorioController`, `CreditoController` + views mínimas + rotas (sem
      fidelidade visual — Fase 8)
- [x] 6 arquivos de teste (feature: `MarcarCreditoDisponivelTest`,
      `RelatorioControllerTest`; unit: `AguardandoCreditoTest` + 3 relatórios)
- [x] `sail test` verde (218/218, mantendo os 196 das Fases 1-5),
      `paridade-v2-v3.md` atualizado (`LEG-RMA-036/037/038/039/048` → PARIDADE), teste
      manual via `tinker` (RMA `solucao=GeradoCredito` → `MarcarCreditoDisponivel`
      grava `credito_disponivel=true`; RMA `solucao=Reparo` → negado com 422), commit
      `#F6`

### Fase 7 — Auditoria — **CONCLUÍDA**

OpenSpec: `openspec/changes/rma-logistica-e-historico/{proposal,design,tasks}.md` (tudo
`[x]`). Arquivo por arquivo detalhado em `INV-RMA-05` §12. Cobre `LEG-RMA-040`/`041`
(consolidação de frete Porto Alegre e boletins relacionados) e `LEG-RMA-043`/`044`/`045`
(auditoria de acesso, modificação de RMA e notificações).

**Pré-requisito descoberto na revisão desta fase:** só o evento `RmaConcluido` existia
(Fase 4). Os outros 7 eventos de domínio (`RmaCriado`, `RmaEditado`, `RmaRecebido`,
`RmaEncaminhado`, `RmaArquivado`, `RmaRevertido`, `SolucaoRegistrada`) foram criados
nesta fase em `app/Rma/Dominio/Eventos/` e `::dispatch()` foi adicionado ao final dos 7
casos de uso já existentes das Fases 3/4 — extensão aditiva, `sail test` confirmado
verde nas Fases 3/4 após cada arquivo tocado, nenhuma regressão. `RmaConcluido` também
ganhou a propriedade `ator` (não existia na Fase 4), necessária para
`RegistrarModificacaoDeRma` gravar `user_id`.

`ConsolidarFretePorCidade` usa TEMA V2 como especificação (TEMA V1 tem o mesmo código,
mas desativado/comentado), cidade "PORTO ALEGRE" hardcoded. Log de modificação de RMA
usa snapshot estruturado com ação nomeada (`AcaoDeModificacao`), não diff campo-a-campo
— `EVO-AUD-001` fica como pendência registrada, **aguardando decisão do usuário** (ver
`proposal.md`). Notificação de conclusão via Mailable, destinatário configurável via
`.env` (`RMA_NOTIFICACAO_CONCLUSAO`), não hardcoded como o legado. Notificação de
tentativa negada (`naopermitido()`) registrada via log de aplicação, não e-mail — desvio
de implementação porque `design.md`/`tasks.md` não listam um Mailable dedicado para
esse caso (só o listener).

**Desvio de implementação:** `BoletinsRelacionados` teve a query ajustada em relação ao
pseudocódigo do `design.md` — condições por `destinatario_id`/`fabricante_id`/
`fornecedor_id` só entram quando o campo correspondente do RMA de referência não é
nulo, evitando que o `IS NULL` genérico do Query Builder do Laravel casasse RMAs sem
nenhuma contraparte em comum (achado confirmado em teste automatizado durante esta
fase). Tasks:

- [x] `app/Rma/Dominio/Eventos/{RmaCriado,RmaEditado,RmaRecebido,RmaEncaminhado,RmaArquivado,RmaRevertido,SolucaoRegistrada,TentativaDeGravacaoNaoPermitida}.php`
- [x] `::dispatch()` adicionado às Fases 3/4 (`CriarRma`, `EditarRma`, `ReceberRma`,
      `EncaminharRma`, `ArquivarRma`, `ReverterRmaParaEntrada`, `RegistrarSolucao`) +
      `RmaPolicy::update()` dispara `TentativaDeGravacaoNaoPermitida`
- [x] Migration `modificacoes_de_rma` (FK real para `rmas`/`users`)
- [x] `AcaoDeModificacao` (enum), `ModificacaoDeRma` (model)
- [x] `RegistrarModificacaoDeRma`, `EnviarNotificacaoDeConclusao`,
      `EnviarNotificacaoDeTentativaNaoPermitida` (listeners), `RmaConcluidoMailable`,
      registrados em `AppServiceProvider::boot()` (projeto sem `EventServiceProvider`
      explícito)
- [x] `config/rma.php` (`notificacoes.conclusao` via `.env`)
- [x] `ConsolidarFretePorCidade`, `BoletinsRelacionados`
- [x] Controllers de histórico (modificação de RMA + acesso) + logística + views
      mínimas + rotas (sem fidelidade visual — Fase 8)
- [x] 8 arquivos de teste novos (27 testes: `RegistrarModificacaoDeRmaTest`,
      `EnviarNotificacaoDeConclusaoTest`, `EnviarNotificacaoDeTentativaNaoPermitidaTest`,
      `HistoricoDeModificacaoTest`, `HistoricoDeAcessoTest`,
      `ConsolidarFretePorCidadeTest`, `BoletinsRelacionadosTest`)
- [x] `sail test` verde (248/248, mantendo os 221 das Fases 1-6),
      `paridade-v2-v3.md` atualizado (`LEG-RMA-040/041/043/044/045` → PARIDADE), teste
      manual via `tinker` (RMA criado + recebido via `CriarRma`/`ReceberRma`, linha em
      `modificacoes_de_rma` confirmada com `acao=Receber` e `estado_apos.status=
      "Recebido"`), commit `#F7`

### Fase 8 — Apresentação (Temas V1/V2) — **CONCLUÍDA** (2026-08-25)

OpenSpec escrita: `openspec/changes/temas-v1-v2/{proposal,design,tasks}.md`. Arquivo por
arquivo detalhado em `INV-RMA-05` §13. **Investigação de granularidade (Parte 2)
resolvida por evidência já reunida nas Fases 1-7:** Controllers/casos de uso únicos
(nenhuma regra de negócio diverge por tema, exceto RN-15/RN-21 já tratadas por
presença/ausência), views e rotas por tema.

**As 2 pendências reais originais foram RESOLVIDAS em 2026-08-24** por inspeção direta
do LEGACY-RUNTIME (`:8094`, sessão autenticada, HTML/CSS/PHP reais — não só os
inventários estáticos): (1) as âncoras de TEMA V2 são o plugin de abas nativo do
Bootstrap 3.3.5 (`data-toggle="tab"`, sem AJAX, 7 painéis pré-renderizados no mesmo
HTML); (2) RN-11 em TEMA V1 está presente de verdade (`TrInconformidade`/`TrUrgente`/
`TrZebrada1/2`, via CSS compartilhado `pattern/15.9.7.css`, confirmado em 3 páginas
reais do código-fonte). Detalhe completo em `design.md`/`proposal.md`.

**As 2 pendências de produto NOVAS foram RESOLVIDAS pelo usuário em 2026-08-25:** (3)
fonte Open Sans do TEMA V2 — reproduzir o fallback real (`Arial`/`Fira Sans`), nunca
self-hostar Open Sans de verdade; (4) comportamento pós-login — unificado, sempre
respeita `tema_preferido`, sem login embutido de TEMA V1 separado do gateway. Ver
`design.md`/`proposal.md`.

Tasks (resumido, ver `tasks.md` completo — marcado item a item):

- [x] Resolver as 2 pendências reais originais (pré-requisito de implementação)
- [x] Decidir as 2 pendências novas de produto (fonte Open Sans; assimetria pós-login)
- [x] Sass por tema (`v1.scss`/`v2.scss`/`_compartilhado.scss` — porta de verdade
      `pattern/15.9.7.css`: `TrInconformidade`/`TrUrgente`/`TrZebrada1/2`/
      `TrSemGarantia1/2`, `.breadcrumb`, `.centrodeavisos`, `.formSelect`,
      `.designedby`, `.pmo`); Fira Mono self-hostado via Vite; Bootstrap 3.3.5 real
      (`node_modules/bootstrap` dist CSS/JS, `@import` escopado só a `v2.scss`)
- [x] `ResolverTemaAtivo` (middleware, `app/Http/Middleware/`) + `view_do_tema()`/
      `rota_tema()`/`classe_css_de_alerta()` (`app/Support/view_do_tema.php`) + rotas
      por tema (`routes/tema-{v1,v2}.php`, prefixo `/v1`/`/v2`, mesmos Controllers) +
      `identidade/login.blade.php` do gateway compartilhado (achado: não é nem V1 nem
      V2)
- [x] Árvore de Blade por tema (`resources/views/temas/{v1,v2}/`) — layout, `rma/
      {index,create,edit,show}` (índice V2 com os 7 tab-panes reais via Bootstrap
      tabs), `parceiros/{index,_form}`, `identidade/{usuarios,perfil}`
- [x] Testes de smoke por tema (`tests/Feature/Temas/RenderizaTemaV{1,2}Test.php`, 13
      testes) + Playwright REAL (`tests/Browser/ComparacaoVisualTemaV{1,2}Test.spec.ts`,
      instalado com Chromium dentro do container `laravel.test` via
      `npx playwright install --with-deps chromium`, roda de verdade contra
      `http://localhost` interno ao container — 390/768/1440px: TEMA V1 confirma
      largura computada fixa de 984px nos 3 breakpoints via `getComputedStyle`, TEMA V2
      confirma a largura de `.container` esperada em 768/1440px por
      `tests/Browser/Support/breakpoints-tema-v2.json` — 390px corretamente pulado por
      estar abaixo do menor breakpoint do tema)
- [x] Screenshots reais (PNG) — 9 capturas em `docs/produto/screenshots-fase8/` via
      `tests/Browser/CapturarScreenshotsTemas.spec.ts` (login/perfil/RMAs/clientes dos
      dois temas, incluindo a aba "Entrada" do painel V2) — fecha a pendência da
      Parte 1/Parte 5
- [x] `sail test` verde (263/263: 250 das Fases 1-7 + 13 novos smoke de tema),
      `checklist-master-v3.md`/`paridade-v2-v3.md` atualizados (paridade visual),
      commit `#F8`

**Escopo real coberto** (árvore explícita do `design.md`): login-gateway, RMA
(index/create/edit/show), parceiros (index/_form, 4 tipos), identidade
(usuarios/perfil). **Fora do escopo desta fase** (não listado na árvore do
`design.md`): alertas, crédito, relatórios, histórico/auditoria e logística continuam
com view mínima das Fases 5-7, sem estilização por tema — não é regressão, é escopo já
fechado no planejamento; registrado como próximo passo natural, não bloqueia a Fase 8.
Ver `log-implementacao-v3.md` (Fase 8) para os desvios de implementação registrados
(bootstrap@3.3.5 só publica LESS/CSS pré-compilado, não SCSS — `@import` do CSS de
distribuição real em vez de `@import` de um SCSS que não existe).

### Fase 9 — Migração V2→V3 — **CONCLUÍDA** (2026-08-25)

OpenSpec escrita: `openspec/changes/migracao-v2-v3/{proposal,design,tasks}.md`. Mapa
campo-a-campo completo: `docs/arquitetura/INV-RMA-06-estrategia-reconstrucao.md`.
Arquivo por arquivo detalhado em `INV-RMA-05` §14.

- [x] Escrever `docs/arquitetura/INV-RMA-06-estrategia-reconstrucao.md`
- [x] Escrever `openspec/changes/migracao-v2-v3/{proposal,design,tasks}.md`
- [x] Mapa completo legado → V3 por tabela/campo
- [x] Migrador oficial + relatório de reconciliação + idempotência —
      `app/Rma/Infraestrutura/Migracao/` (`TabelaDeTraducao`, `ConexaoLegado`,
      `RelatorioDeReconciliacao`, `ResolverDestinatario`, `ParserDeDataLegado` + 8
      importadores), comando `php artisan rma:migrar-legado`
      (`--somente`/`--dry-run`/`--forcar`)
- [x] Teste de migração determinístico — 8 testes de importador +
      `MigrarLegadoComandoTest`, fixture pequena via schema reproduzido
      (`tests/Feature/Migracao/Suporte/ComBancoLegadoDeTeste.php`), `sail test`
      308/308 (265 das Fases 1-8 + 43 novos)
- [x] Dry-run real contra o Legacy tentado, bloqueado por rede (porta `3309` do Legacy
      só em `127.0.0.1` do host — ver `log-implementacao-v3.md`, Fase 9); dry-run
      automatizado contra fixture (`MigrarLegadoComandoTest`) é a evidência que conta

**Nuance de roadmap (reconfirmada nesta sessão, 2026-08-25):** "CONCLUÍDA" aqui
significa *o migrador existe, está codificado, testado e determinístico* — não que a
migração já rodou operacionalmente contra o banco real do Legacy. Isso nunca aconteceu,
por bloqueio de rede ainda não contornado (não é um problema do código do migrador). Rodar
o migrador de verdade contra `rma-legacy-mariadb-1` continua sendo trabalho real
pendente, hoje enquadrado dentro do escopo de "Paridade de dados" da Fase 10 (ver
`qa-paridade/tasks.md`), não uma tarefa à parte.

### Fase 10 — QA de paridade — **EM ESPECIFICAÇÃO, contínua, fecha por último**

OpenSpec escrita: `openspec/changes/qa-paridade/{proposal,design,tasks}.md`. Critério
objetivo por eixo + gate de conclusão do projeto detalhados em `INV-RMA-05` §15.

- [x] Escrever `openspec/changes/qa-paridade/{proposal,design,tasks}.md`
- [ ] Paridade funcional por `LEG-RMA-NNN` (atualizar `paridade-v2-v3.md` a cada fase)
- [ ] Paridade visual (screenshot V2×V3, 390/768/1440)
- [ ] Paridade de dados (contagens pós-migração, Fase 9)

---

## Parte 4 — Estratégia de migração em detalhe (`INV-RMA-06` — escrito 2026-08-24)

- [x] `docs/arquitetura/INV-RMA-06-estrategia-reconstrucao.md` escrito — mapa completo
      campo-a-campo das 9 tabelas legadas, com 4 pendências reais registradas (formato
      de data ambíguo; ocorrência real de `status='retornou'`; destino de
      `relatorio.informacaoadicional`; coordenação de `rmas.valor` — resolvida em
      2026-08-25, coluna adicionada ao schema da Fase 5)
- [x] OpenSpec do migrador escrita: `openspec/changes/migracao-v2-v3/
      {proposal,design,tasks}.md` — arquitetura completa (8 importadores, ordem de
      dependência, `TabelaDeTraducao`, `RelatorioDeReconciliacao`, idempotência via
      `numero_legado`/dedup por nome)
- [x] Fases 4/5 existiam em código antes desta fase começar a ser codificada
- [x] Migrador codificado + 8 importadores + relatório de reconciliação + idempotência
      + testes de migração determinísticos (ver `openspec/changes/migracao-v2-v3/
      tasks.md` para a lista arquivo-por-arquivo)
- [x] Resolver ou registrar decisão do usuário para as 4 pendências de `INV-RMA-06` (3
      resolvidas na implementação — parser de 3 tentativas, anomalia sem case novo,
      opção B por omissão — e `rmas.valor` já resolvida na Fase 5; ver `proposal.md`)

## Parte 5 — Pendências operacionais menores

- [x] `scripts/legacy-reset.sh` (Legacy) — **testado de verdade nesta sessão**
      (2026-08-25): rodado do zero (`docker compose down -v` + `up -d`), reimportou
      `db/schema-only.sql`, `:8094` voltou a responder `200` e o banco ficou saudável
      (`mysqladmin ping` → `mysqld is alive`) — o reset em si **funciona**. **Bug real
      encontrado, não corrigido** (regra desta sessão: não alterar o repositório
      Legacy): o loop de espera do script chama `mariadb-admin ping`, binário que **não
      existe** na imagem `mariadb:10.3` usada pelo `compose.yaml` (só `mysqladmin`
      existe) — `exec: "mariadb-admin": executable file not found in $PATH`. O script
      trava indefinidamente nesse loop mesmo com o banco já pronto (precisou ser
      interrompido por timeout de 180s nesta verificação). Correção trivial de uma
      linha (`mariadb-admin` → `mysqladmin`), mas fica registrada como achado
      pendente de decisão/aplicação pelo usuário no repositório Legacy, não aplicada
      aqui.
- [x] `machadogolang/08.24.4-legacy-gerenciador-de-rma` publicado no GitHub — `git push`
      confirmado com sucesso nesta sessão (commits `#L0`/`#L1` no remoto). **Visibilidade
      reconfirmada nesta sessão** (2026-08-25): `curl` não-autenticado à API do GitHub
      devolve `404 Not Found` para este repositório — consistente com **privado**
      (repositório inexistente devolveria a mesma resposta para um não-autenticado, mas
      o push já documentado confirma que ele existe) — fechado.
- [ ] `machadogolang/08.24.1-gerenciador-de-rma` (este repo) segue **público** —
      reconfirmado nesta sessão via API do GitHub (`"private": false`,
      `"visibility": "public"`). Decisão de trocar para privado continua **pendente do
      usuário**, não decidida por este agente.
- [x] Capturar screenshots reais (PNG) dos dois temas autenticados — feito na Fase 8,
      `docs/produto/screenshots-fase8/` (9 capturas via Playwright real) — confirmado
      presente nesta sessão.

---

## Como usar este documento

Risque `[ ]` → `[x]` conforme cada tarefa pequena for concluída. Quando uma seção
inteira fechar, atualizar também o checkpoint correspondente em `PLANO-ATAQUE.md`
(que continua sendo a fonte de "qual é a fase atual"). Este arquivo não substitui os
documentos de detalhe listados na Parte 1 — é só o mapa de tudo, para não perder tarefa
pequena entre sessões.
