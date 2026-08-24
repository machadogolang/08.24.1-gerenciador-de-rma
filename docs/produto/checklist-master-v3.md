# Checklist mestre — CellSystem RMA V3

Data: 2026-08-24. Documento único de acompanhamento: tudo que já foi investigado
(indexado, com onde encontrar o detalhe) e todo o trabalho que falta, quebrado em
tarefas pequenas. `PLAN.md` continua sendo o estado macro; `PLANO-ATAQUE.md` continua
sendo o operacional (AGORA/DEPOIS/DEPENDÊNCIAS); este documento é o **checklist
granular** dos dois, para não perder nenhum item pequeno entre uma sessão e outra.

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

## Parte 2 — Checklist granular do que falta (tarefas pequenas)

### A. Arquitetura da V3 (`INV-RMA-05`, documento ainda não criado)

- [ ] Criar `docs/arquitetura/INV-RMA-05-arquitetura-proposta.md`
- [ ] **Fixar como princípio explícito (ainda não escrito em lugar nenhum até agora):**
      fidelidade visual/comportamental é do **resultado percebido**, não da
      implementação. A camada de apresentação da V3 usa recursos nativos do Laravel —
      Blade (`@extends`/`@include`/`@component`/layouts), Vite para
      CSS/JS/Sass, asset pipeline normal — nunca os `include()` de PHP puro,
      `<?php echo $local; ?>` espalhado, nem os arquivos `.php` do legado copiados
      como template. O HTML final deve reproduzir a aparência de TEMA V1/TEMA V2
      (mesma paleta, mesma estrutura, mesmas classes de estado visual como
      `TrInconformidade`/`TrUrgente`), mas o código-fonte da view é Blade idiomático.
  - [ ] Mapear os `inc/`/`page/`/`subp/` do legado (por tema) para uma árvore de Blade
        (`resources/views/temas/v1/...`, `resources/views/temas/v2/...` ou estrutura
        equivalente — decidir ao escrever `INV-RMA-05`)
  - [ ] Decidir se os componentes repetidos entre os dois temas (ex.: badge de status,
        linha de alerta) viram Blade Components/View Components compartilhados, com só
        o CSS variando por tema
  - [ ] Portar a paleta/tipografia de cada tema para variáveis Sass (não copiar o CSS
        legado arquivo por arquivo — reconstruir com Sass moderno, valores idênticos)
  - [ ] Decidir se jQuery é mantido (onde a interação é simples e já funciona) ou
        substituído por JS moderno, avaliando caso a caso conforme a diretriz do
        usuário ("usar a tecnologia correta para este produto", nem purismo nem apego)
- [ ] Decidir: FK reais desde a baseline para `cliente`/`fabricante`/`fornecedor`/
      `assistencia_tecnica`, ou preservar relação por string na primeira versão?
      (ver regra de evolução do banco — baseline antes de melhoria estrutural)
- [ ] Decidir estrutura de camadas (Controller → Application/UseCase → Domain →
      Persistence) e até onde ela se justifica para este domínio (não copiar CONAHOM
      cegamente)
- [ ] Investigar granularidade real de compartilhamento entre TEMA V1 e TEMA V2:
  - [ ] Comparar as duas telas de "novo RMA" lado a lado (V1 vs V2) campo a campo
  - [ ] Comparar as duas telas de "detalhe do RMA" lado a lado campo a campo
  - [ ] Decidir se a diferença é só de apresentação (view/layout) ou também de
        Controller/rota
- [ ] **Fixar como princípio explícito:** autenticação da V3 usa os recursos nativos do
      Laravel (`Auth` facade, guards de sessão, scaffolding Breeze, `Hash::make` —
      bcrypt/argon2, não o SHA1 sem salt do legado) — nunca reimplementar login/sessão
      na mão como o legado fazia. O que precisa ser **preservado 1:1** é só o
      comportamento percebido:
  - [ ] 5 níveis de permissão (`-1` bloqueado / `1` leitura / `2` operador / `3`
        supervisor / `4` super-admin oculto da listagem) — via Policy/Gate do Laravel,
        não `if ($permissao == X)` espalhado
  - [ ] Login redireciona para o tema (V1/V2) conforme a preferência salva do usuário —
        mesma regra de `usuario.app`/`trocarapp.php`, implementada como coluna +
        Controller Laravel, não a função PHP procedural
  - [ ] Troca de tema com persistência entre sessões (já validada no Legacy, ver
        `docs/desenvolvimento/ambiente-v2-v3.md`)
  - [ ] Decidir stack exata (Breeze/Fortify/Sanctum — qual se aplica a um sistema sem
        API pública ainda) ao escrever `INV-RMA-05`
- [ ] Decidir onde entra a fronteira arquitetural entre "modelo legado" e "aplicação
      moderna" (repositories/adapters/DTOs) — ver seção 6 da diretriz mestre do usuário
- [ ] Definir enum/máquina de estado final de `status` (hoje só `PROVISÓRIO`)
- [ ] Definir enum final de `solucao` (17 valores confirmados — usar como está ou
      agrupar por categoria?)
- [ ] Registrar decisão de identificadores (ID incremental vs UUID/ULID) para `Rma` e
      demais entidades

### B. Estratégia de migração (`INV-RMA-06` + `MIG-V3`, documentos ainda não criados)

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

### C. OpenSpecs (catálogo já proposto em `PLANO-ATAQUE.md`, nenhuma escrita ainda)

- [ ] `openspec/changes/autenticacao-usuarios/` (proposal.md + design.md + tasks.md) —
      candidato à primeira fatia (schema estável, regra já validada nos dois temas)
- [ ] `openspec/changes/rma-cadastro-e-localizacao/`
- [ ] `openspec/changes/rma-ciclo-de-vida/`
- [ ] `openspec/changes/rma-alertas-e-prioridade/`
- [ ] `openspec/changes/parceiros/`
- [ ] `openspec/changes/rma-creditos-e-relatorios/`
- [ ] `openspec/changes/rma-logistica-e-historico/`
- [ ] `openspec/changes/temas-v1-v2/`
- [ ] `openspec/changes/migracao-v2-v3/`

### D. Implementação (só depois da OpenSpec correspondente madura — nenhuma iniciada)

- [ ] Autenticação (login, logout, papéis/permissão de 5 níveis)
- [ ] CRUD de `Parceiro` (cliente/fabricante/fornecedor/assistência técnica)
- [ ] Entidade `Rma` + criação + busca/localização
- [ ] Transições de estado (receber/encaminhar/concluir/arquivar/rollback)
- [ ] As 10 regras de alerta (prazo/garantia/NF/S/N/prioridade)
- [ ] Fluxo de crédito
- [ ] Relatórios (RCD/RPEC/RMPE)
- [ ] Auditoria (log de autenticação + modificação de RMA)
- [ ] TEMA V1 (apresentação fiel ao legado)
- [ ] TEMA V2 (apresentação fiel ao legado)
- [ ] Seletor de tema + persistência de preferência por usuário

### E. Testes / QA (nenhum específico de RMA ainda — só suíte padrão do Laravel)

- [ ] Testes unitários das 10 regras de alerta (uma por regra, com fixture)
- [ ] Testes de transição de estado (todas as combinações válidas/inválidas)
- [ ] Testes de autorização por nível de permissão (5 níveis)
- [ ] QA funcional: comparar cada `LEG-RMA-NNN` implementado contra o Legacy rodando
      em `:8094` (atualizar `docs/produto/paridade-v2-v3.md` a cada um)
- [ ] QA visual: screenshot lado a lado V2×V3 por tema, larguras 390/768/1440 (padrão
      já usado no CONAHOM)
- [ ] QA de migração: rodar o migrador contra o banco de laboratório do Legacy,
      validar contagens (RMAs, clientes, fornecedores, fabricantes, usuários, logs)

### F. Pendências operacionais menores

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
