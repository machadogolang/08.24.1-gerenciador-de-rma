# Relatório Final de Paridade e Fechamento da Trilha A — CellSystem RMA V3

**Data-base:** 2026-09-04  
**Autor:** Engenharia & QA — Antigravity  
**Status:** **GATE DA TRILHA A APROVADO**  
**Fontes de Verdade:** `docs/produto/checklist-master-v3.md`, `PLANO-ATAQUE.md`, `docs/legado/inventario-funcional-rma-v2.md`, `docs/produto/paridade-v2-v3.md`, `docs/pareceres/2026-09-04-decisoes-arquitetura-e-seguranca-trilha-a.md`.

---

## 1. Sumário Executivo

A Trilha A do projeto **CellSystem RMA** teve como missão a restauração fiel, segura e moderna do produto legado (versão 15.9.7 — abrangendo os subsistemas coexistentes 14.6.1 e 15.8.1 e camada compartilhada), preservando 100% das regras de negócio, fluxos operacionais e identidade visual, com engenharia e arquitetura contemporâneas (Laravel 11, PHP 8.3/8.4, MySQL 8.4, Docker Sail e Playwright).

Este relatório consolida o encerramento formal de todas as etapas de homologação:
1. **Eixo Funcional (`F10-FUN`):** 48/48 IDs de requisitos reconciliados (44 implementados com paridade estrita, 2 exclusões de código morto justificadas, 1 deferimento de modelo polimórfico para Trilha B, 1 decisão de segurança homologada). Suíte de 6 smokes cruzados M-01 a M-06 aprovada.
2. **Eixo Visual e Navegacional (`F10-VIS` / `NAV-01..NAV-05`):** Paridade visual e estrutural homologada para Tema V1 (14.6.1) e Tema V2 (15.8.1) em 10 superfícies e 3 breakpoints normativos (390px, 768px, 1440px), com fontes locais (Open Sans e Fira Mono sem dependência de CDN), layout de 984px fixo no V1 e responsivo no V2.
3. **Eixo de Dados (`F10-DAD`):** Migração histórica real executada de ponta a ponta contra a base real do MariaDB do backup histórico (15.9.7), reconciliação das 9 tabelas do legado com integridade referencial estrita, auditoria completa de anomalias (incluindo datas `0000-00-00` e UFs não padronizadas) e prova inequívoca de idempotência.
4. **Decisões de Domínio e Segurança:** Parecer executivo emitido e homologado rejeitando hard-delete destrutivo em favor de auditoria imutável (arquivamento/bloqueio) e substituindo autocadastro público por provisionamento administrativo.

---

## 2. Eixo Funcional (`F10-FUN`)

### 2.1 Reconciliação dos 48 Requisitos (`LEG-RMA-*`)

A matriz consolidada em [`docs/produto/paridade-v2-v3.md`](file:///home/legionario/github/08.24.1-gerenciador-de-rma/docs/produto/paridade-v2-v3.md) atingiu 100% de resolução comprovada:
- **44 Requisitos com Paridade Estrita:** Totalmente cobertos por testes unitários/feature no PHPUnit e verificados nos fluxos de usuário;
- **2 Requisitos Classificados como "NÃO RECONSTRUIR":**
  - `LEG-RMA-016`: Código morto comprovado no legado (rotas de teste e controllers sem chamada);
  - `LEG-RMA-034`: Alias morto no legado sem função de negócio;
- **1 Requisito Classificado como "RETOMAR IDEIA":**
  - `LEG-RMA-035`: Unificação polimórfica de parceiros (`Parceiro`), deferida formalmente para a Trilha B (`EVO-DOM-001`);
- **1 Requisito com Decisão de Segurança Homologada:**
  - `LEG-RMA-002`: Autocadastro com chave estática descontinuado; provisionamento delegado exclusivamente a administradores via `UsuarioController` e convite seguro em `EVO-SEG-001`.

### 2.2 Suíte de Smokes Cruzados (M-01 a M-06)

Implementada como suíte permanente automatizada em [`tests/Browser/SmokesParidadeFuncional.spec.ts`](file:///home/legionario/github/08.24.1-gerenciador-de-rma/tests/Browser/SmokesParidadeFuncional.spec.ts), executando contra os servidores reais:
- **M-01 (Autenticação e Alternância de Tema V1/V2):** Login, preferência de tema gravada no perfil, redirecionamento para o layout respectivo e logout;
- **M-02 (Criação e Edição de RMA):** Validações normativas RN-13 e RN-14 (sanitização de fabricante Hitachi/HGST e preenchimento defensivo de campos Grupo A);
- **M-03 (Busca, Listagem e Detalhes):** Localização por número, cliente, número de série e filtros múltiplos;
- **M-04 (Ciclo de Vida Completo):** Transições `Entrada` → `Recebido` → `Encaminhado` → `Concluído`, reversão para `Entrada` e arquivamento em `Controle`;
- **M-05 (Painel de Alertas e Regras de Urgência):** 10 grupos de alertas operacionais, incluindo o achado CP14 (`Rma::classeDeAlerta()` com `Urgente` para prioridade alta e prazo >30d estourado);
- **M-06 (Módulo de Crédito e Relatórios Gerenciais):** Relatórios RCD (Créditos Disponíveis), RPEC (Produtos em Estoque para Contagem) e RMPE (Movimentação por Período).

**Resultado:** 6/6 smokes 100% aprovados.

---

## 3. Eixo Visual e Navegacional (`F10-VIS` / `NAV-01..NAV-05`)

### 3.1 Paridade de Temas e Layouts

- **Tema V1 (14.6.1):** Layout clássico fixo em 984px (`$largura-fixa-tema-v1: 984px`) sem `@media` queries perturbadoras, preservando as caixas originais (`content-box`), fontes Open Sans self-hosted (sem Google CDN externo), menu superior azul e painéis colapsáveis inline (#JS-Novo, #JS-Localizar, #JS-Sessao).
- **Tema V2 (15.8.1):** Layout responsivo baseado em Bootstrap/AdminLTE histórico com 7 abas funcionais (Entrada, Recebido, Encaminhado, Concluído, Crédito, Arquivados, Todos), contêiner dinâmico e suporte aos breakpoints 390px, 768px e 1440px.

### 3.2 Auditoria Navegacional Automatizada (Lotes NAV-01 a NAV-05)

A suíte Playwright [`tests/Browser/AuditoriaNavegacionalTemaV1.spec.ts`](file:///home/legionario/github/08.24.1-gerenciador-de-rma/tests/Browser/AuditoriaNavegacionalTemaV1.spec.ts) auditou sistematicamente:
- **NAV-01:** Menu superior (10 testes);
- **NAV-02:** Menu de sessão e cadastros de parceiros/controle (8 testes);
- **NAV-03:** Página inicial, 16 contadores e Centro de Avisos com 10 grupos (5 testes);
- **NAV-04:** Ciclo de vida, links internos, autosave de anotações e rodapé seguro (9 testes);
- **NAV-05:** Consolidação e auditoria de ausência de regressões.

**Resultado:** 32/32 testes navegacionais aprovados sem nenhuma falha de console ou erro HTTP 4xx/5xx.

---

## 4. Eixo de Dados (`F10-DAD`)

### 4.1 Ambiente e Segurança da Origem Histórica

- **Origem dos Dados:** Container `rma-legacy-mariadb-1` (porta 3306), na rede interna Docker `rma-legacy_legacy-lab`, montado a partir do backup físico de arqueologia 15.9.7.
- **Isolamento e Modo Leitura Estrita:** Provisionado usuário de banco `rma_legacy_readonly` com permissão restrita `GRANT SELECT`. Operações de gravação foram testadas e rejeitadas pelo SGBD com erro `SQLSTATE 1142`.
- **Alvo Descartável:** Criado o banco isolado `rma_v3_descartavel` no MySQL 8.4 para execução de migração e prova de integridade, mantendo o banco principal de homologação intacto.

### 4.2 Reconciliação das 9 Tabelas Históricas

O comando `php artisan rma:migrar-legado` processou os dados reais com os seguintes resultados consolidados:

| Tabela Origem | Registros Origem | Tabela Destino V3 | Registros Gravados | Status / Anomalias Justificadas |
|---|---|---|---|---|
| `usuario` | 10 | `users` | 10 | **100% Migrado.** 6 datas `'0000-00-00'` convertidas com fallback defensivo para `now()`. |
| `cliente` | 165 | `clientes` | 292 | **100% Migrado.** 165 cadastros canônicos + 127 parceiros auto-descobertos nos RMAs para integridade de FK. |
| `fabricante` | 104 | `fabricantes` | 227 | **100% Migrado.** 104 cadastros canônicos + 123 auto-descobertos para garantir integridade referencial. |
| `fornecedor` | 43 | `fornecedores` | 69 | **100% Migrado.** 43 cadastros canônicos + 26 auto-descobertos para integridade de FK. |
| `assistencia_tecnica` | 32 | `assistencias_tecnicas` | 32 | **100% Migrado.** 32 registros com reconciliação exata. |
| `bd` | 1.379 | `rmas` | 1.379 | **100% Migrado.** 1.379 ordens de serviço importadas com status, datas, soluções e chave `numero_legado`. |
| `log` | 3.247 | `tentativas_de_acesso` | 3.240 | **99,8% Migrado.** 7 registros descartados com log de anomalia por possuírem valor de `retorno` fora do domínio. |
| `modificacao` | 3.568 | `modificacoes_de_rma` | 2.039 | **57,1% Migrado.** 1.529 registros órfãos descartados com log (e-mails inexistentes em `usuario` ou RMAs não encontrados). |
| `relatorio` | 0 | — | — | Tabela vazia confirmada por arqueologia técnica (código legado nunca gravava nela). |

### 4.3 Sanitização e Conversões Assistidas

- **Datas Inválidas (`QA F10-DAD-05`):** 6 usuários legados continham `data_de_cadastro = '0000-00-00'` (rejeitado pelo MySQL 8.4 em strict mode). O parser tratou com fallback para `now()`, gerando log auditável. Ocorrências pontuais de datas de NF no formato `11/08/18` ou `09102017` foram gravadas como `null` com o valor bruto preservado no relatório de anomalias.
- **UFs Inconsistentes:** Ocorrências de `uf = 'N/A'` em clientes e fornecedores foram sanitizadas via `TabelaDeTraducao::uf()` para `null`, respeitando o enum `App\Compartilhado\Uf`.
- **Status `'retornou'` (`QA F10-DAD-06`):** Confirmado arqueologicamente no banco real de 1.379 RMAs que **zero** registros utilizam `status = 'retornou'` (o campo `retornou` é exclusivamente uma data/flag de devolução; o status operacional segue estritamente os 4 estados do ciclo de vida).
- **Conversões Assistidas:** Registro `numero=1434148947` com prioridade `'urgente'` (resíduo histórico RN-08) convertido para `Prioridade::Alta`.

### 4.4 Prova Real de Idempotência (`QA F10-DAD-09`)

Executada uma segunda rodada completa da migração na base alvo (`rma:migrar-legado`):
- Registros gravados em `rmas`: **0 novos** (total manteve-se em 1.379);
- Registros gravados em `users`: **0 novos** (total manteve-se em 10);
- Registros gravados em `tentativas_de_acesso`: **0 novos** (total manteve-se em 3.240);
- Registros gravados em `modificacoes_de_rma`: **0 novos** (total manteve-se em 2.039).

A idempotência foi provada sem nenhuma duplicação ou corrupção de chaves.

---

## 5. Homologação das Decisões de Arquitetura e Domínio

Conforme parecer formal emitido em [`docs/pareceres/2026-09-04-decisoes-arquitetura-e-seguranca-trilha-a.md`](file:///home/legionario/github/08.24.1-gerenciador-de-rma/docs/pareceres/2026-09-04-decisoes-arquitetura-e-seguranca-trilha-a.md):

1. **`LEG-RMA-002` (Autocadastro com Chave Estática):** Descontinuado formalmente por violar princípios básicos de controle de acesso. Substituído por criação restrita a administradores autenticados via `UsuarioController` e `UserPolicy`. O mecanismo de auto-onboarding com convite criptográfico temporário foi registrado para a Trilha B (`EVO-SEG-001`).
2. **`VIS-V1-011` / `VIS-V1-012` (Ações Deletar RMA e Deletar Usuário):** Rejeição peremptória de hard-delete destrutivo. Em um sistema de RMA com implicações tributárias, fiscais e de garantia, a perda de histórico é inaceitável. A integridade foi atendida de forma superior através de:
   - Arquivamento de RMA (`Status::Arquivado`), que retira o item do fluxo operacional diário mantendo-o auditável no painel de Controle;
   - Desativação de conta (`Papel::Bloqueado`), revogando imediatamente o acesso sem deletar a autoria histórica das movimentações.
3. **Achado CP14 (`Rma::classeDeAlerta()`):** Resolução implementada e testada. O método agora retorna `ClasseDeAlerta::Urgente` quando a prioridade é alta e o cliente está fora do prazo de 30 dias, e `ClasseDeAlerta::SemGarantia` para casos aplicáveis.

---

## 6. Cobertura Técnica Global

- **Suíte PHPUnit:** **388 testes / 941 asserções**, cobrindo 100% das entidades de domínio, agregados, políticas de autorização, migrações de dados, regras de negócio e renderização de temas.
- **Suíte Playwright Browser:** **58 testes ponta a ponta**, cobrindo fluxos reais de login, perfil, CRUDs de parceiros, ciclo de vida de RMAs, relatórios, snapshots de regressão visual em 3 viewports e auditoria de conformidade navegacional.
- **Taxa de Sucesso:** **100% verde**, zero falhas, zero warnings impeditivos.

---

## 7. Parecer Final e Declaração de Gate

Com base nas evidências objetivas e reprodutíveis apresentadas nos três eixos (funcional, visual e de dados), a equipe de engenharia e garantia da qualidade:

> **DECLARA FORMALMENTE APROVADO O GATE DA TRILHA A (FASE 10 / `QA F10-GATE-07`).**

O produto **CellSystem RMA V3** é declarado como o substituto canônico, seguro, moderno e com paridade estrita em relação ao legado histórico 15.9.7. O repositório está apto e autorizado para o início do planejamento das evoluções da **Trilha B** (`EVO-*`), mantendo esta baseline protegida contra qualquer regressão.
