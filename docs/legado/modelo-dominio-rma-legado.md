# Modelo de domínio do CellSystem RMA (legado)

Data: 2026-08-24. Documenta o domínio **como ele realmente existia** no backup
15.9.7 (container + apps 14.6.1/15.8.1 + camada compartilhada `metodo.php`). Não define
arquitetura Laravel nem schema novo — isso é `INV-RMA-05`/`INV-RMA-06`, ainda não
escritos. Ver `docs/legado/matriz-comparacao-apps-rma.md` para a arquitetura multi-app.

Tags: **[CONFIRMADO]** lido diretamente no código · **[CONFIRMADO-BANCO]** confirmado
pelo schema do dump SQL · **[INFERIDO]** dedução razoável sem leitura direta ·
**[DÚVIDA]** pergunta em aberto.

## Entidade central: RMA (tabela `bd`)

**[CONFIRMADO] [CONFIRMADO-BANCO]** Tabela única, ~60 colunas, sem herança. Chave de
negócio `numero` (INT, gerado em PHP por tentativa aleatória de 6 dígitos com retry —
não usa o `AUTO_INCREMENT` do MySQL apesar de a coluna tê-lo configurado). Compartilhada
por igual entre os dois apps (mesma tabela, mesmo `conexao.php`).

Grupos de campo confirmados no schema (`dump-cellsyst_rma-201912161213.sql`):
- **Identidade do produto:** `descricao`, `fabricante`, `modelo`, `sn`, `snid`, `pn`,
  `snretorno`.
- **Operação:** `os`, `origem`, `empresa`, `prioridade`, `protocolo`, `defeito`,
  `observacao`, `usuario` (e-mail de quem operou).
- **Estado:** `status`, `entrada`, `recebido`, `encaminhado`, `concluido`, `prazo`,
  `solucao`, `ano`.
- **Partes:** `fornecedor`, `cliente`, `cliente_email`, `destinatario`,
  `destinatario_fone`, `destinatario_email` — todos texto livre, **sem FK**.
- **Nota fiscal (4 blocos completos):** compra (`nfcompra`/`_emissao`/`_chave`), venda
  (`nfvenda`/…), remessa (`nfremessa`/…), retorno (`nfretorno`/…); mais avulsos
  `nfdevolucaodevenda`, `nfentrada_cli`, `nfretorno_cli`. Cada `_chave` é a chave DANFE
  de 44 dígitos (nota fiscal eletrônica brasileira).
- **Logística:** `rastreio_ida`, `rastreio_retorno`.
- **Financeiro:** `valor` (DOUBLE), `creditodisponivel` (0/1), `marcarestoque` (0/1),
  `lancadoretorno`.
- **Auditoria:** `dtains` (criação), `dtaalt` (última alteração).

**[CONFIRMADO]** Não existe entidade `Equipamento` separada de `RMA` — os atributos do
produto são colunas diretas de `bd`. Um mesmo equipamento físico que retorne por um
segundo defeito vira uma linha independente, sem vínculo com a primeira, exceto por
busca textual manual (seção "boletins relacionados", ver `regras-negocio-rma-legado.md`).

## As quatro entidades de "contraparte"

**[CONFIRMADO]** `cliente`, `fabricante`, `fornecedor`, `assistencia_tecnica` — schema
quase idêntico entre si (endereço completo, contato, `politicadegarantia` texto livre
exceto em `cliente`). A diferença real não é o schema, é o **papel** que o nome ocupa
numa linha de `bd`:

| Papel | Coluna em `bd` | Semântica confirmada |
|---|---|---|
| Fabricante | `bd.fabricante` | quem produziu — origem da garantia de fábrica |
| Fornecedor | `bd.fornecedor` | de quem a empresa comprou — origem da NF de compra e do prazo de 365 dias de garantia |
| Cliente | `bd.cliente` | a quem a empresa vendeu — destino da NF de venda; **único** auto-criado ao salvar RMA com nome inédito |
| Destinatário | `bd.destinatario` | para onde o produto foi despachado — **polimórfico**, resolvido por nome em cascata: `assistencia_tecnica` → `fornecedor` → `fabricante` |

**[CONFIRMADO] Entidade "autorizada" é alias morto de `assistencia_tecnica`** no app
15.8.1: arquivos `subp/*_autorizada*` são cópias quase idênticas dos arquivos
`*_assistencia_tecnica*`, sem nenhuma rota alcançável no `.htaccess`. **[CONFIRMADO,
só no app 14.6.1]** existe uma tentativa de tabela unificada `assistencias(tipo)` sendo
referenciada por `menujs-right/fornecedores.php`, mas as funções de política de garantia
em cascata **não a incluem** — inconsistência real dentro do próprio app 14.6.1, não
resolvida em nenhum dos dois apps.

## Usuário / Papel

**[CONFIRMADO]** Tabela `usuario`: `id_usuario`, `nome`, `email` (único), `Key1461`,
`Key1581` (dois hashes SHA1 de senha, sem salt — login aceita qualquer um dos dois),
`permissao` (INT), `app` (preferência de app: "14.6.1" ou "15.8.1"), `anotacao`
(bloco de notas pessoal), `quantidade_login`, `ultimo_login`, `data_de_cadastro`.

Domínio de `permissao`, **confirmado idêntico nos dois apps**:

| Valor | Papel efetivo |
|---|---|
| `-1` | Bloqueado — login negado mesmo com senha certa |
| `1` | Leitura — navega, não grava; tentativa de gravação dispara e-mail de alerta |
| `2` | Operador — CRUD completo de RMA |
| `3` | Supervisor — administra usuários; vê auditoria de todos exceto do desenvolvedor (e-mail hardcoded excluído da query) |
| `4` | Super-admin oculto — não aparece na listagem de usuários; único nível que reverte status fora da janela de "mesmo dia" |

Não existe conceito de multiempresa por usuário (`usuario` não tem `empresa_id`) — ver
seção "Empresa" abaixo.

## Empresa (embrião de multiempresa)

**[CONFIRMADO] [CONFIRMADO-BANCO]** `bd.empresa`: texto livre, 6 valores observados
(`Cellsystem`, `Expert`, `Registros Ativos`/`R A`, `Informatica`/`T A`). Já existe um
grupo de empresas operando dentro do mesmo banco, **sem qualquer isolamento** — nunca
filtra nenhuma query, nenhum relatório, nenhum dos 10 alertas. Candidato direto a
`tenant_id` na evolução SaaS futura (registrado em `docs/produto/backlog-evolutivo.md`),
não uma decisão da reconstrução fiel.

## Status × Solução — duas máquinas de estado independentes

**[CONFIRMADO]**, idêntico nos dois apps:
- **`status`** (logístico): `entrada → recebido → encaminhado → concluido`, mais
  `arquivado` (paralelo, reabrível) e `retornou` (órfão — rota existe, tela vazia, nunca
  gravado por nenhuma transição em nenhum app).
- **`solucao`** (comercial/técnico): 17 valores possíveis, completamente ortogonal ao
  `status` — ver `regras-negocio-rma-legado.md` para o domínio completo e as regras que
  dependem dela.

## Estoque

**[CONFIRMADO]** Não existe módulo de estoque separado. `bd.marcarestoque` (0/1) é um
flag manual no próprio RMA indicando se o item pertence ao estoque da empresa (não é de
terceiro). É o discriminador central da regra de "inconformidade" visual — ver
`regras-negocio-rma-legado.md` para o bug confirmado onde esse valor é calculado e depois
sobrescrito.

## Auditoria

**[CONFIRMADO] [CONFIRMADO-BANCO]** Duas tabelas de auditoria, ambas compartilhadas
pelos dois apps: `log` (tentativas de autenticação: e-mail, IP, navegador, SO, app usado,
resultado) e `modificacao` (edições de RMA: número, quem, quando, IP, navegador,
snapshot desnormalizado dos campos-chave do produto no momento da edição — não registra
a ação nem os valores antigos, só o estado após a mudança).

## Relatório

**[CONFIRMADO]** Tabela `relatorio` (`id` varchar, `informacaoadicional` text) — usada
para persistir uma nota de texto livre por relatório gerado (ex.: financeiro anotando
como um crédito foi usado). Três relatórios reais confirmados no app 15.8.1: RCD
(créditos disponíveis), RPEC (produtos em estoque para contagem), RMPE (produtos
encaminhados). Presentes desde o app 14.6.1 com a mesma estrutura.

## Relacionamentos — como tudo se conecta (achado estrutural central)

**[CONFIRMADO] Não existem foreign keys em lugar nenhum do schema.** Todo vínculo de
`bd` com `cliente`/`fabricante`/`fornecedor`/`assistencia_tecnica` é feito por
**comparação de string de nome** (`bd.fornecedor = fornecedor.nome`), não por ID. Único
JOIN real de todo o sistema: `right_portoalegre()` (consolidação de frete por cidade, ver
regras de negócio). Isso é a maior decisão estrutural a corrigir na reconstrução — não
porque o legado esteja "errado" no sentido de negócio, mas porque nomes duplicados/
digitados diferente geram RMAs "órfãos" que não aparecem em nenhuma listagem de
contraparte.
