# Matriz de Uniao Funcional dos Temas (V1 / V2 / V3)

Data: 2026-09-10 (America/Sao_Paulo).
Regra canonica do dono:
- **VISUAL:** Tema V1 preserva identidade e linguagem visual do Legacy 14.6.1; Tema V2 preserva identidade e linguagem visual do Legacy 15.8.1; Tema V3 e evolucao propria.
- **FUNCIONAL:** Tema V1 e Tema V2 devem oferecer O MESMO CONJUNTO FUNCIONAL (Uniao das capacidades vivas de 14.6.1 + 15.8.1 + modernas aprovadas).
- **DESACOPLAMENTO:** Ao transportar uma funcionalidade de um legado para outro tema, NAO transportar o layout de origem; desenhar e adaptar nativamente na linguagem visual do tema de destino.
- **DESEMPATE:** Em aparente conflito: funcionalidade ganha a uniao cruzada (Eixo B); apresentacao ganha a fidelidade historica do tema (Eixo A).

Classificacoes padrao adotadas:
- `PRESENTE-FUNCIONAL`
- `PRESENTE-QUEBRADO`
- `CODIGO-MORTO`
- `AUSENTE`
- `DUVIDA`
- `MODERNA-EQUIVALENTE`
- `PROMOVER-AOS-DOIS-TEMAS`
- `NAO-PROMOVER`
- `DECISAO-PENDENTE`

---

## 1. Metricas Consolidadas da Auditoria Cruzada

| Metrica | Quantidade |
|---|---|
| **TOTAL DE CAPACIDADES CANONICAS AUDITADAS** | **65** |
| Capacidades comuns vivas em ambos os Legacies (V1 e V2) | 35 |
| Capacidades exclusivas do Legacy 14.6.1 (V1) | 8 |
| Capacidades exclusivas do Legacy 15.8.1 (V2) | 16 |
| Quebradas em V1 mas funcionais em V2 | 1 |
| Quebradas em V2 mas funcionais em V1 | 1 |
| Codigo morto comprovado (nao promover) | 6 |
| Decisao pendente de produto/seguranca | 3 |
| **GAPS FUNCIONAIS / DESCOBRIBILIDADE NO TEMA V1 NOVO** | **0** (11 resolvidos) |
| **GAPS FUNCIONAIS / DESCOBRIBILIDADE NO TEMA V2 NOVO** | **0** (5 resolvidos) |

---

## 2. Resumo Executivo de Gaps por Tema

### Gaps do Tema V1 Novo (precisam ser introduzidos na linguagem visual V1)
1. `GAP-V1-01` (CAP-RMA-004): [x] CONVERGIDO - Fila dedicada de Recebidos (rota `/rmas-recebidos`, view V1 `temas.v1.rma.recebidos` e link no `#TOPO`).
2. `GAP-V1-02` (CAP-ID-005): [x] CONVERGIDO - Criacao de usuario por operador/admin no padrao V1 (view `usuarios-novo` e link no Controle/Usuarios).
3. `GAP-V1-03` (CAP-AUD-002): [x] CONVERGIDO - Historico e logs de autenticacao com visualizacao na linguagem V1 e painel no Controle.
4. `GAP-V1-04` (CAP-AUD-003): [x] CONVERGIDO - Historico e logs de modificacao de RMA na linguagem V1 e painel no Controle.
5. `GAP-V1-05` (CAP-AUD-004): [x] CONVERGIDO - Detalhe do log / acao Ver (#id e ver.png) acessivel nos logs V1.
6. `GAP-V1-06` (CAP-REL-004): [x] CONVERGIDO - Hub estatistico de relatorios adaptado ao Tema V1 (`temas.v1.rma.relatorios.index` e link no menu de sessao).
7. `GAP-V1-07` (CAP-CRD-001): [x] CONVERGIDO - Visualizacao tabular rica de Creditos Disponiveis e acao de marcar na linguagem V1.
8. `GAP-V1-08` (CAP-ALT-004): [x] CONVERGIDO - Sinalizacao de Urgencia e Alerta com threshold de R$ 75 no padrao V1 (`Rma::ehUrgentePorThreshold` e `TrUrgente`).
9. `GAP-V1-09` (CAP-ALT-003): [x] CONVERGIDO - Campo e indicador de Prioridade (Baixa, Normal, Alta) no form e detalhe V1.
10. `GAP-V1-10` (CAP-PAR-006): [x] CONVERGIDO - Listagem de RMAs associados ao visualizar parceiros (renderizada via `parceiros._detalhe` em `temas.v1.parceiros.show`).
11. `GAP-V1-11` (CAP-LOG-001): [x] CONVERGIDO - Painel/consulta de Transporte Porto Alegre no padrao V1 (`temas.v1.rma.logistica.frete-porto-alegre`) e link no `#JS-Sessao`.

### Gaps do Tema V2 Novo (precisam ser introduzidos na linguagem visual V2)
1. `GAP-V2-01` (CAP-REL-001): [x] CONVERGIDO - Relatorio RCD (Creditos) descobrivel no Tema V2 via `_menu_relatorios`.
2. `GAP-V2-02` (CAP-REL-002): [x] CONVERGIDO - Relatorio RPEC (Estoque) descobrivel no Tema V2 via `_menu_relatorios`.
3. `GAP-V2-03` (CAP-REL-003): [x] CONVERGIDO - Relatorio RMPE (Movimentacao) descobrivel no Tema V2 via `_menu_relatorios`.
4. `GAP-V2-04` (CAP-LOG-002): [x] CONVERGIDO - Tabela e dados de Destinatarios com frete e CFOP descobriveis em `parceiros._detalhe` e acessiveis no Tema V2.
5. `GAP-V2-05` (CAP-AUX-001): [x] CONVERGIDO - Procedimento operacional / Ajuda de RMA acessivel na interface V2 (`temas.v2.rma.ajuda` e link no dropdown Menu).

---

## 3. Matriz Canônica Cruzada Detalhada (65 Capacidades)

### 3.1 Dominio: Sessao e Identidade

| ID | Capacidade | Dominio | Legacy 14.6.1 | Legacy 15.8.1 | Fonte Escolhida | Promover? | V1 Novo (Back/UI/Desc/Test/Status) | V2 Novo (Back/UI/Desc/Test/Status) | V3 (Status/T3) | OpenSpec | Pendencia | Status Geral |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| CAP-ID-001 | Autenticacao / Login | Identidade | PRESENTE-FUNCIONAL | PRESENTE-FUNCIONAL | Moderna | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-01 | autenticacao-usuarios | Nenhuma | [x] Convergido |
| CAP-ID-002 | Logout | Identidade | PRESENTE-FUNCIONAL | PRESENTE-FUNCIONAL | Moderna | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-01 | autenticacao-usuarios | Nenhuma | [x] Convergido |
| CAP-ID-003 | Alterar propria senha | Identidade | PRESENTE-FUNCIONAL | PRESENTE-QUEBRADO | V1 / Moderna | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-01 | autenticacao-usuarios | V2 visual [R] PAR15-SEC-001 | [x] Funcional Convergido |
| CAP-ID-004 | Listar usuarios | Identidade | PRESENTE-FUNCIONAL | PRESENTE-FUNCIONAL | Compartilhada | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-01 | autenticacao-usuarios | Nenhuma | [x] Convergido |
| CAP-ID-005 | Criar novo usuario (admin) | Identidade | AUSENTE | PRESENTE-FUNCIONAL | V2 / Moderna | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | Planejado / T3-13 | unificacao-funcional | Nenhuma | [x] Convergido |
| CAP-ID-006 | Resetar senha por operador | Identidade | PRESENTE-FUNCIONAL | PRESENTE-FUNCIONAL | Moderna | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-01 | autenticacao-usuarios | Nenhuma | [x] Convergido |
| CAP-ID-007 | Mudar permissao de usuario | Identidade | PRESENTE-FUNCIONAL | PRESENTE-FUNCIONAL | Moderna | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-01 | autenticacao-usuarios | Nenhuma | [x] Convergido |
| CAP-ID-008 | Exclusao/desativacao de usuario | Identidade | PRESENTE-FUNCIONAL | PRESENTE-FUNCIONAL | DECISAO-PENDENTE | DECISAO-PENDENTE | N / N / N / N / [ ] | N / N / N / N / [ ] | Planejado / T3-13 | autenticacao-usuarios | Avaliar desativacao tenant-safe | [R] Decisao Pendente |
| CAP-ID-009 | Alternar tema / preferencia | Identidade | PRESENTE-FUNCIONAL | PRESENTE-FUNCIONAL | Moderna | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-01 | temas-v1-v2 | Nenhuma | [x] Convergido |
| CAP-ID-010 | Salvar anotacoes do operador | Identidade | PRESENTE-FUNCIONAL | PRESENTE-FUNCIONAL | Compartilhada | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-01 | front-003 | V2 visual autosave PAR15-NOTE-001 | [x] Funcional Convergido |
| CAP-ID-011 | Autocadastro com convite/segredo | Identidade | PRESENTE-FUNCIONAL | AUSENTE | NAO-PROMOVER | DECISAO-PENDENTE | N / N / N / N / [ ] | N / N / N / N / [ ] | N/A | autenticacao-usuarios | Deferido por seguranca | [R] Deferido |

### 3.2 Dominio: RMA - Ciclo de Vida e Listagens

| ID | Capacidade | Dominio | Legacy 14.6.1 | Legacy 15.8.1 | Fonte Escolhida | Promover? | V1 Novo (Back/UI/Desc/Test/Status) | V2 Novo (Back/UI/Desc/Test/Status) | V3 (Status/T3) | OpenSpec | Pendencia | Status Geral |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| CAP-RMA-001 | Criar novo RMA | RMA | PRESENTE-FUNCIONAL | PRESENTE-FUNCIONAL | Moderna | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-03 | rma-cadastro | Nenhuma | [x] Convergido |
| CAP-RMA-002 | Buscar / Localizar RMAs | RMA | PRESENTE-FUNCIONAL | PRESENTE-FUNCIONAL | Compartilhada | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-03 | rma-cadastro | Nenhuma | [x] Convergido |
| CAP-RMA-003 | Fila de Entrada | RMA | PRESENTE-FUNCIONAL | PRESENTE-FUNCIONAL | Compartilhada | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-04 | rma-ciclo-de-vida | Nenhuma | [x] Convergido |
| CAP-RMA-004 | Fila dedicada de Recebidos | RMA | AUSENTE | PRESENTE-FUNCIONAL | V2 / Moderna | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-04 | unificacao-funcional | Nenhuma | [x] Convergido |
| CAP-RMA-005 | Fila de Encaminhados | RMA | PRESENTE-FUNCIONAL | PRESENTE-FUNCIONAL | Compartilhada | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-04 | rma-ciclo-de-vida | Nenhuma | [x] Convergido |
| CAP-RMA-006 | Fila de Concluidos | RMA | PRESENTE-FUNCIONAL | PRESENTE-FUNCIONAL | Compartilhada | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-04 | rma-ciclo-de-vida | Nenhuma | [x] Convergido |
| CAP-RMA-007 | Fila de Aguardando Credito | RMA | PRESENTE-FUNCIONAL | PRESENTE-FUNCIONAL | Compartilhada | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-04 | rma-ciclo-de-vida | Nenhuma | [x] Convergido |
| CAP-RMA-008 | Fila de Arquivados | RMA | PRESENTE-FUNCIONAL | PRESENTE-FUNCIONAL | Compartilhada | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-04 | rma-ciclo-de-vida | Nenhuma | [x] Convergido |
| CAP-RMA-009 | Rota Retornou (0 bytes) | RMA | AUSENTE | CODIGO-MORTO | N/A | NAO-PROMOVER | N / N / N / N / [x] | N / N / N / N / [x] | N/A | rma-ciclo-de-vida | Nao reproduzir | [x] Codigo Morto |
| CAP-RMA-010 | Detalhe e Edicao de RMA | RMA | PRESENTE-FUNCIONAL | PRESENTE-FUNCIONAL | Compartilhada | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-04 | rma-ciclo-de-vida | Nenhuma | [x] Convergido |
| CAP-RMA-011 | Transicao: Receber RMA | RMA | PRESENTE-FUNCIONAL | PRESENTE-FUNCIONAL | Moderna | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-04 | rma-ciclo-de-vida | Nenhuma | [x] Convergido |
| CAP-RMA-012 | Transicao: Encaminhar RMA | RMA | PRESENTE-FUNCIONAL | PRESENTE-FUNCIONAL | Moderna | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-04 | rma-ciclo-de-vida | Nenhuma | [x] Convergido |
| CAP-RMA-013 | Transicao: Concluir RMA | RMA | PRESENTE-FUNCIONAL | PRESENTE-FUNCIONAL | Moderna | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-04 | rma-ciclo-de-vida | Nenhuma | [x] Convergido |
| CAP-RMA-014 | Transicao: Reverter p/ Entrada | RMA | PRESENTE-FUNCIONAL | PRESENTE-FUNCIONAL | Moderna | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-04 | rma-ciclo-de-vida | Nenhuma | [x] Convergido |
| CAP-RMA-015 | Arquivar RMA | RMA | PRESENTE-QUEBRADO | PRESENTE-FUNCIONAL | V2 / Moderna | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-04 | rma-ciclo-de-vida | Nenhuma | [x] Convergido |
| CAP-RMA-016 | Deletar solicitacao RMA | RMA | PRESENTE-FUNCIONAL | AUSENTE | DECISAO-PENDENTE | DECISAO-PENDENTE | N / N / N / N / [ ] | N / N / N / N / [ ] | N/A | rma-ciclo-de-vida | Nao reproduzir exclusao insegura | [R] Deferido |
| CAP-RMA-017 | Solucoes de encerramento (15) | RMA | PRESENTE-FUNCIONAL | PRESENTE-FUNCIONAL | Compartilhada | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-04 | rma-ciclo-de-vida | Nenhuma | [x] Convergido |
| CAP-RMA-018 | Controle de Estoque (marcarestoque) | RMA | PRESENTE-FUNCIONAL | PRESENTE-FUNCIONAL | Compartilhada | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-04 | rma-ciclo-de-vida | Nenhuma | [x] Convergido |
| CAP-RMA-019 | Credito disponivel no RMA | RMA | PRESENTE-FUNCIONAL | PRESENTE-FUNCIONAL | Compartilhada | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-04 | rma-creditos | Nenhuma | [x] Convergido |
| CAP-RMA-020 | Gestao fiscal (NFs/chaves/emissao) | RMA | PRESENTE-FUNCIONAL | PRESENTE-FUNCIONAL | Compartilhada | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-04 | rma-ciclo-de-vida | Nenhuma | [x] Convergido |
| CAP-RMA-021 | Codigos de rastreio ida/retorno | RMA | PRESENTE-FUNCIONAL | PRESENTE-FUNCIONAL | Compartilhada | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-04 | rma-ciclo-de-vida | Nenhuma | [x] Convergido |
| CAP-RMA-022 | Destinatario / fone / email | RMA | PRESENTE-FUNCIONAL | PRESENTE-FUNCIONAL | Compartilhada | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-04 | rma-ciclo-de-vida | Nenhuma | [x] Convergido |
| CAP-RMA-023 | SN retorno (RN-15) | RMA | PRESENTE-FUNCIONAL | PRESENTE-FUNCIONAL | Moderna (Dominio) | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-04 | rma-ciclo-de-vida | Nenhuma | [x] Convergido |
| CAP-RMA-024 | Marcar como (alias orfao) | RMA | AUSENTE | CODIGO-MORTO | N/A | NAO-PROMOVER | N / N / N / N / [x] | N / N / N / N / [x] | N/A | rma-ciclo-de-vida | Nao reproduzir | [x] Codigo Morto |

### 3.3 Dominio: Alertas e Prioridade

| ID | Capacidade | Dominio | Legacy 14.6.1 | Legacy 15.8.1 | Fonte Escolhida | Promover? | V1 Novo (Back/UI/Desc/Test/Status) | V2 Novo (Back/UI/Desc/Test/Status) | V3 (Status/T3) | OpenSpec | Pendencia | Status Geral |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| CAP-ALT-001 | Alertas de prazo e tempo | Alertas | PRESENTE-FUNCIONAL | PRESENTE-FUNCIONAL | Compartilhada | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-05 | rma-alertas | Nenhuma | [x] Convergido |
| CAP-ALT-002 | Indicadores Sem NF/SN/Garantia | Alertas | PRESENTE-FUNCIONAL | PRESENTE-FUNCIONAL | Compartilhada | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-05 | rma-alertas | Nenhuma | [x] Convergido |
| CAP-ALT-003 | Nivel de Prioridade (Baixa/Normal/Alta) | Alertas | AUSENTE | PRESENTE-FUNCIONAL | V2 / Moderna | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-05 | unificacao-funcional | Nenhuma | [x] Convergido |
| CAP-ALT-004 | Urgencia / Threshold R$ 75 | Alertas | AUSENTE | PRESENTE-FUNCIONAL | V2 / Moderna | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-05 | unificacao-funcional | Nenhuma | [x] Convergido |
| CAP-ALT-005 | Classificacoes visuais operacionais | Alertas | PRESENTE-FUNCIONAL | PRESENTE-FUNCIONAL | V2 / Compartilhada | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | Planejado / T3-14 | rma-alertas | Adaptar CSS sem copiar HTML | [x] Funcional Convergido |

### 3.4 Dominio: Credito

| ID | Capacidade | Dominio | Legacy 14.6.1 | Legacy 15.8.1 | Fonte Escolhida | Promover? | V1 Novo (Back/UI/Desc/Test/Status) | V2 Novo (Back/UI/Desc/Test/Status) | V3 (Status/T3) | OpenSpec | Pendencia | Status Geral |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| CAP-CRD-001 | Listagem tabular rica de Creditos | Credito | AUSENTE | PRESENTE-FUNCIONAL | V2 / Moderna | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-06 | unificacao-funcional | Nenhuma | [x] Convergido |
| CAP-CRD-002 | Submenus de Creditos (orfao) | Credito | AUSENTE | CODIGO-MORTO | N/A | NAO-PROMOVER | N / N / N / N / [x] | N / N / N / N / [x] | N/A | rma-creditos | Nao reproduzir sub-rotas mortas | [x] Codigo Morto |

### 3.5 Dominio: Relatorios

| ID | Capacidade | Dominio | Legacy 14.6.1 | Legacy 15.8.1 | Fonte Escolhida | Promover? | V1 Novo (Back/UI/Desc/Test/Status) | V2 Novo (Back/UI/Desc/Test/Status) | V3 (Status/T3) | OpenSpec | Pendencia | Status Geral |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| CAP-REL-001 | Relatorio RCD (Creditos) | Relatorios | PRESENTE-FUNCIONAL | AUSENTE | V1 / Moderna | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-06 | unificacao-funcional | Nenhuma | [x] Convergido |
| CAP-REL-002 | Relatorio RPEC (Estoque) | Relatorios | PRESENTE-FUNCIONAL | AUSENTE | V1 / Moderna | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-06 | unificacao-funcional | Nenhuma | [x] Convergido |
| CAP-REL-003 | Relatorio RMPE (Movimentacao) | Relatorios | PRESENTE-FUNCIONAL | AUSENTE | V1 / Moderna | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-06 | unificacao-funcional | Nenhuma | [x] Convergido |
| CAP-REL-004 | Hub Estatistico de Relatorios | Relatorios | AUSENTE | PRESENTE-FUNCIONAL | V2 / Moderna | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | Planejado / T3-15 | unificacao-funcional | Nenhuma | [x] Convergido |
| CAP-REL-005 | Impressao limpa de relatorios | Relatorios | PRESENTE-FUNCIONAL | PRESENTE-FUNCIONAL | Compartilhada | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-06 | rma-creditos | Nenhuma | [x] Convergido |
| CAP-REL-006 | Informacao adicional persistida | Relatorios | PRESENTE-FUNCIONAL | AUSENTE | V1 / Moderna | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | Planejado / T3-15 | rma-creditos | Nenhuma | [x] Convergido |

### 3.6 Dominio: Parceiros

| ID | Capacidade | Dominio | Legacy 14.6.1 | Legacy 15.8.1 | Fonte Escolhida | Promover? | V1 Novo (Back/UI/Desc/Test/Status) | V2 Novo (Back/UI/Desc/Test/Status) | V3 (Status/T3) | OpenSpec | Pendencia | Status Geral |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| CAP-PAR-001 | Gestao de Clientes (CRUD) | Parceiros | PRESENTE-FUNCIONAL | PRESENTE-FUNCIONAL | Compartilhada | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-02 | parceiros | V2 visual edit PAR15-PART-002 | [x] Funcional Convergido |
| CAP-PAR-002 | Gestao de Fornecedores (CRUD) | Parceiros | PRESENTE-FUNCIONAL | PRESENTE-FUNCIONAL | Compartilhada | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-02 | parceiros | V2 visual edit PAR15-PART-003 | [x] Funcional Convergido |
| CAP-PAR-003 | Gestao de Fabricantes (CRUD) | Parceiros | PRESENTE-FUNCIONAL | PRESENTE-FUNCIONAL | Compartilhada | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-02 | parceiros | V2 visual edit PAR15-PART-004 | [x] Funcional Convergido |
| CAP-PAR-004 | Gestao de Assistencias (CRUD) | Parceiros | PRESENTE-FUNCIONAL | PRESENTE-FUNCIONAL | Compartilhada | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-02 | parceiros | V2 visual edit PAR15-PART-005 | [x] Funcional Convergido |
| CAP-PAR-005 | RG / IE nos parceiros | Parceiros | PRESENTE-FUNCIONAL | PRESENTE-FUNCIONAL | Compartilhada | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-02 | parceiros | Persistencia PAR15-PART-DATA-001 | [x] Funcional Convergido |
| CAP-PAR-006 | RMAs associados ao parceiro | Parceiros | AUSENTE | PRESENTE-FUNCIONAL | V2 / Moderna | PROMOVER-AOS-DOIS-TEMAS | S / N / N / N / [ ] | S / S / S / S / [R] | Planejado / T3-14 | unificacao-funcional | Exibir no V1 (GAP-V1-10) | [ ] Aberto V1 |
| CAP-PAR-007 | Modelo `assistencias(tipo)` | Parceiros | PRESENTE-FUNCIONAL | AUSENTE | N/A | NAO-PROMOVER | N / N / N / N / [x] | N / N / N / N / [x] | N/A | parceiros | Substituido pelas 4 entidades | [x] Codigo Morto |

### 3.7 Dominio: Controle e Auditoria

| ID | Capacidade | Dominio | Legacy 14.6.1 | Legacy 15.8.1 | Fonte Escolhida | Promover? | V1 Novo (Back/UI/Desc/Test/Status) | V2 Novo (Back/UI/Desc/Test/Status) | V3 (Status/T3) | OpenSpec | Pendencia | Status Geral |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| CAP-AUD-001 | Hub de Controle Operacional | Controle | PRESENTE-FUNCIONAL | PRESENTE-FUNCIONAL | Compartilhada | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | Planejado / T3-14 | front-003 | Nenhuma | [x] Convergido |
| CAP-AUD-002 | Logs de Autenticacao (acesso) | Controle | AUSENTE | PRESENTE-FUNCIONAL | V2 / Moderna | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-01 | unificacao-funcional | Nenhuma | [x] Convergido |
| CAP-AUD-003 | Logs de Modificacao de RMA | Controle | AUSENTE | PRESENTE-FUNCIONAL | V2 / Moderna | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-04 | unificacao-funcional | Nenhuma | [x] Convergido |
| CAP-AUD-004 | Acao Ver / Detalhe do log | Controle | AUSENTE | PRESENTE-FUNCIONAL | V2 / Moderna | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-04 | unificacao-funcional | Nenhuma | [x] Convergido |

### 3.8 Dominio: Logistica

| ID | Capacidade | Dominio | Legacy 14.6.1 | Legacy 15.8.1 | Fonte Escolhida | Promover? | V1 Novo (Back/UI/Desc/Test/Status) | V2 Novo (Back/UI/Desc/Test/Status) | V3 (Status/T3) | OpenSpec | Pendencia | Status Geral |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| CAP-LOG-001 | Transporte para Porto Alegre | Logistica | AUSENTE | PRESENTE-FUNCIONAL | V2 / Moderna | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | Planejado / T3-14 | unificacao-funcional | Nenhuma (UF-16) | [x] Convergido |
| CAP-LOG-002 | Destinatarios com frete e CFOP | Logistica | PRESENTE-FUNCIONAL | AUSENTE | V1 / Moderna | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | Planejado / T3-14 | unificacao-funcional | Nenhuma (UF-17) | [x] Convergido |

### 3.9 Dominio: Outros e Auxiliares

| ID | Capacidade | Dominio | Legacy 14.6.1 | Legacy 15.8.1 | Fonte Escolhida | Promover? | V1 Novo (Back/UI/Desc/Test/Status) | V2 Novo (Back/UI/Desc/Test/Status) | V3 (Status/T3) | OpenSpec | Pendencia | Status Geral |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| CAP-AUX-001 | Ajuda / Procedimento de RMA | Auxiliar | PRESENTE-FUNCIONAL | AUSENTE | V1 / Moderna | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | Planejado / T3-14 | unificacao-funcional | Nenhuma (UF-18) | [x] Convergido |
| CAP-AUX-002 | Avisar alguem (stub) | Auxiliar | AUSENTE | CODIGO-MORTO | N/A | NAO-PROMOVER | N / N / N / N / [x] | N / N / N / N / [x] | N/A | front-003 | Stub 15 linhas (DEC-02) | [x] Codigo Morto |
| CAP-AUX-003 | Enviar e-mail (stub) | Auxiliar | AUSENTE | CODIGO-MORTO | N/A | NAO-PROMOVER | N / N / N / N / [x] | N / N / N / N / [x] | N/A | front-003 | Stub 11 linhas (DEC-02) | [x] Codigo Morto |
| CAP-AUX-004 | Paginas de Erro 403 / 404 | Auxiliar | PRESENTE-FUNCIONAL | PRESENTE-FUNCIONAL | Compartilhada | PROMOVER-AOS-DOIS-TEMAS | S / S / S / S / [x] | S / S / S / S / [x] | [x] / T3-01 | front-003 | Nenhuma | [x] Convergido |

---

## 4. Tabela Resumida de Gaps e Acoes Necessarias

| ID | Capacidade | Origem Legacy | V1 Novo | V2 Novo | Acao Necessaria |
|---|---|---|---|---|---|
| CAP-RMA-004 | Fila dedicada de Recebidos | So V2 Legacy | PRESENTE | PRESENTE | [x] CONVERGIDO: rota /rmas-recebidos, listagem V1 e link #TOPO |
| CAP-REL-001 | Relatorio RCD (Creditos) | So V1 Legacy | PRESENTE | PRESENTE | [x] CONVERGIDO: descobrivel via _menu_relatorios no V2 |
| CAP-REL-002 | Relatorio RPEC (Estoque) | So V1 Legacy | PRESENTE | PRESENTE | [x] CONVERGIDO: descobrivel via _menu_relatorios no V2 |
| CAP-REL-003 | Relatorio RMPE (Movimentacao) | So V1 Legacy | PRESENTE | PRESENTE | [x] CONVERGIDO: descobrivel via _menu_relatorios no V2 |
| CAP-REL-004 | Hub Estatistico de Relatorios | So V2 Legacy | PRESENTE | PRESENTE | [x] CONVERGIDO: adaptado em temas.v1.rma.relatorios.index |
| CAP-AUD-002 | Logs de Autenticacao | So V2 Legacy | PRESENTE | PRESENTE | [x] CONVERGIDO: superficie V1 e painel no Controle |
| CAP-AUD-003 | Logs de Modificacao de RMA | So V2 Legacy | PRESENTE | PRESENTE | [x] CONVERGIDO: superficie V1 e painel no Controle |
| CAP-AUD-004 | Acao Ver / Detalhe do Log | So V2 Legacy | PRESENTE | PRESENTE | [x] CONVERGIDO: detalhe e icone ver.png em ambos |
| CAP-ID-005 | Criar Novo Usuario pelo Admin | So V2 Legacy | PRESENTE | PRESENTE | [x] CONVERGIDO: view usuarios-novo e link no Controle V1 |
| CAP-CRD-001 | Listagem Tabular de Creditos | So V2 Legacy | PRESENTE | PRESENTE | [x] CONVERGIDO: tabela rica e acao marcar no Tema V1 |
| CAP-ALT-004 | Urgencia / Threshold R$ 75 | So V2 Legacy | PRESENTE | PRESENTE | [x] CONVERGIDO: regra e classe TrUrgente nos dois temas |
| CAP-ALT-003 | Nivel de Prioridade (Baixa/Alta)| So V2 Legacy | PRESENTE | PRESENTE | [x] CONVERGIDO: campo prioridade exposto e editavel no V1 |
| CAP-PAR-006 | RMAs Associados ao Parceiro | So V2 Legacy | PRESENTE | PRESENTE | [x] CONVERGIDO: secao renderizada em parceiros._detalhe |
| CAP-LOG-001 | Logistica / Transporte Porto A | So V2 Legacy | PRESENTE | PRESENTE | [x] CONVERGIDO: consulta V1 e link no #JS-Sessao e V2 |
| CAP-LOG-002 | Destinatarios com frete e CFOP | So V1 Legacy | PRESENTE | PRESENTE | [x] CONVERGIDO: exibido em parceiros._detalhe em ambos |
| CAP-AUX-001 | Ajuda / Procedimento de RMA | So V1 Legacy | PRESENTE | PRESENTE | [x] CONVERGIDO: central V1 e painel/link no menu V2 |
