# Design - Unificacao Funcional dos Temas V1 e V2

Conceito central: **Capability Union** (Uniao de Capacidades com Adaptadores Visuais Especializados).

## 1. Principio Arquitetural: Um Dominio, Multiplas Apresentacoes

A unificacao funcional desacopla formalmente a regra de negocio da sua apresentacao visual:

```
                      [ Caso de Uso / Dominio / Query ]
                                     |
                                     v
                           [ Controller Compartilhado ]
                                     |
           +-------------------------+-------------------------+
           |                                                   |
           v                                                   v
   [ Adaptador Tema V1 ]                               [ Adaptador Tema V2 ]
   - Linguagem 14.6.1                                  - Linguagem 15.8.1
   - Tabelas compactas                                 - Tabs Bootstrap 3
   - Menus topo e lateral                              - Dropdown e Sidebar
   - CSS 14.6.1 / pattern                              - CSS 15.8.1 / pattern
```

### Regras estruturais:
1. **Zero duplicacao de regra de negocio:** Proibido criar servicos paralelos como `RelatorioEstatisticoV1Service` e `RelatorioEstatisticoV2Service`. A query, regras de filtro, calculos de totais e politicas de autorizacao sao unicos.
2. **Compartilhamento quando cabivel:** Controllers, rotas base, DTOs, read models e Policies sao compartilhados sempre que o contrato de entrada/saida for compativel.
3. **Especializacao visual:** Arquivos Blade, composicao de layout, classes CSS e navegacao sao estritamente separados sob `resources/views/temas/v1/` e `resources/views/temas/v2/`.
4. **Nenhum vazamento de HTML:** Nunca importar classes de layout especificas do V2 (ex.: grid Bootstrap, `.nav-tabs`, `col-md-*`) para dentro de telas do V1, e vice-versa.

---

## 2. Criterios Canonicos para Existencia de uma Capacidade

Para que uma funcionalidade seja considerada presente e convergida em um tema, ela deve satisfazer obrigatoriamente quatro condicoes:

1. **CAPACIDADE:** O caso de uso, backend e persistencia operam de forma correta e estavel.
2. **AUTORIZACAO:** Policy e guardas de tenant/papel aplicados adequadamente.
3. **DESCOBRIBILIDADE:** O operador consegue encontrar e alcancar a tela atraves dos menus, paineis ou links nativos daquele tema.
4. **APRESENTAÇÃO:** A interface respeita a identidade visual, paleta, tipografia e densidade daquele tema.

Se um endpoint responde HTTP 200 mas nao ha link na interface do tema para acessa-lo, o status documental e `GAP DE NAVEGACAO / DESCOBRIBILIDADE` e a capacidade nao esta concluida naquele tema.

---

## 3. Desenho de Adaptacao dos Gaps Principais

### 3.1 FUN-UNION-001 - Fila de Recebidos no Tema V1 (`GAP-V1-01`)
- **Regra de Dominio:** Status `recebido`, query filtrando `status = 'recebido'`.
- **Tema V2:** Permanece como a tab historica `#recebido` no painel inicial de 7 abas.
- **Tema V1:**
  - Adicao do item `Recebidos` no menu superior do 14.6.1 (`#TOPO`), entre `Entrada` e `Encaminhados` (ou posicao operacional harmonica).
  - Tela/listagem dedicada no padrao `page/entrada.php` do 14.6.1 (tabela com cabecalhos caracteristicos, zebra e links para detalhes).
  - Rota nomeada: `v1.rmas.recebidos` (`/rmas-recebidos` sob Tema V1).

### 3.2 FUN-UNION-005 - Relatorios Cruzados (`GAP-V1-06` e `GAP-V2-01..03`)
- **Relatorios Fiscais (RCD, RPEC, RMPE):**
  - **No V1:** Ja existem com visual 14.6.1 fiel (tabelas completas com colunas historicas, totais, informacoes adicionais persistidas e impressao limpa).
  - **No V2:** Devem ser descobertos a partir da experiencia de Relatorios do V2. A pagina `/v2/relatorios` contera uma secao/botoes operacionais integrados para navegar para RCD, RPEC e RMPE, renderizados com adaptador visual compativel com o Tema V2.
- **Hub Estatistico:**
  - **No V2:** Ja existe em `/v2/relatorios` com blocos de Situacao, Resolucao, Origem, NF, Dados do Sistema e Series Anuais/Mensais.
  - **No V1:** Sera disponibilizado no painel de Relatorios do V1 como opcao "Resumo e Estatisticas do Sistema", apresentando os mesmos dados e contagens tenant-aware atraves de tabelas e blocos com o CSS e tipografia do Tema V1.

### 3.3 FUN-UNION-004 - Auditoria e Logs no Tema V1 (`GAP-V1-03..05`)
- **Regra de Dominio:**
  - Logs de autenticacao: registros de `tentativas_de_acesso` com usuario, IP, data, resultado, SO e app.
  - Logs de modificacao: registros de `modificacoes_de_rma` com dados do RMA, usuario, acao, alteracoes e agente.
- **No V2:** Hub `/v2/controle` com abas dedicadas e colunas historicas.
- **No V1:**
  - Insercao das opcoes "Logs de Acesso" e "Logs de Modificacao" dentro do painel `Controle` do Tema V1 (`/rmas-controle`), ou no menu lateral de sessao.
  - Exibicao dos registros em tabelas no padrao visual do 14.6.1, mantendo a acao `Ver` para inspecionar o detalhe do RMA associado.

### 3.4 FUN-UNION-006 e 007 - Urgencia, Prioridade e Classificacoes Operacionais (`GAP-V1-08/09`)
- **Regra de Dominio:**
  - Urgencia (threshold R$ 75): `(status in ('entrada','recebido','encaminhado')) AND (((origem in ('Cliente','Licitacao') AND marcarestoque=0) AND valor > 75 AND prazo < NOW()) OR prioridade = 'alta')`.
  - Prioridade: `baixa`, `normal`, `alta`.
- **No V2:** Accordion `URGENTE` na sidebar direita e select no detalhe do RMA.
- **No V1:**
  - Inclusao do campo de prioridade no formulario de edicao de RMA V1.
  - Sinalizacao de itens urgentes na listagem de Entrada/Recebidos do V1 e inclusao de filtro/bloco de atencao operacional com a linguagem visual do 14.6.1.

### 3.5 Creditos Tabulares no Tema V1 (`GAP-V1-07`)
- **Regra de Dominio:** Itens com `creditodisponivel = 1` ordenados por data de conclusao/encaminhamento.
- **No V2:** Tabela de 11 colunas em `/v2/creditos` e `/rmas-credito`.
- **No V1:** O painel de Creditos do V1, alem do link para o relatorio RCD, passara a disponibilizar a listagem tabular equivalente formatada no CSS do 14.6.1.

### 3.6 Novo Usuario Administrativo no Tema V1 (`GAP-V1-02`)
- **Regra de Dominio:** Criacao de usuario pelo operador administrador com nome, e-mail, senha e permissao/papel seguro.
- **No V2:** Tela `/v2/usuarios/novo` (PAR15-USR-007).
- **No V1:** Opcao "Novo Usuario" acessivel dentro do modulo de Controle ou Usuarios do V1, com formulario estilizado na estetica do 14.6.1.

---

## 4. Estrategia de Testes de Contrato

Para blindar o sistema contra regressoes e assimetrias de produto, sao instituidos dois tipos de testes automatizados:

1. **`CapabilityContractTest`:**
   Testa a presenca da capacidade e resposta HTTP valida (200) sob ambos os temas forcados:
   - Recebidos: V1 = 200, V2 = 200.
   - Hub Estatistico: V1 = 200, V2 = 200.
   - RCD / RPEC / RMPE: V1 = 200, V2 = 200.
   - Logs de Autenticacao: V1 = 200, V2 = 200.
   - Logs de Modificacao: V1 = 200, V2 = 200.
   - Creditos: V1 = 200, V2 = 200.
   - Novo Usuario: V1 = 200, V2 = 200.

2. **`DescobribilidadeTemasTest`:**
   Testa se o HTML do shell/menus do Tema V1 e do Tema V2 contem links e rotas validas para alcancar todas as capacidades do catalogo canonico. Proibe endpoints orfaos de navegacao.
