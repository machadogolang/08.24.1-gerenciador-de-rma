# Inventário de banco — CellSystem RMA V2

Data: 2026-08-24. Fonte: `dump-cellsyst_rma-201912161213.sql` (schema, dez/2019) +
`app/1maiode2019.sql`/`2maiode2019.sql` (mesmo schema, com dados reais — usados só para
contagem, não conteúdo). Compartilhado por TEMA V1 e TEMA V2 (mesmo `conexao.php`,
confirmado também pelo LEGACY-RUNTIME rodando os dois temas contra o mesmo
`rma_legacy`). Complementa `modelo-dominio-rma-legado.md` (que já cobre esse schema em
prosa) com uma referência tabela-a-tabela mais direta, pedida como documento próprio.

## Tabelas (9)

### `bd` — RMA (entidade central, ~56 colunas)

- PK: `numero` (int, gerado em PHP, não AUTO_INCREMENT real de fato usado).
- Índices: `idx_1` (`status`), `idx_2` (`destinatario`), `idx_3` (composto largo:
  `protocolo,numero,defeito,destinatario,fabricante,modelo,nfcompra,nfremessa,nfvenda,
  observacao(100),origem,os,pn,sn,snid,descricao`) — índice composto muito largo,
  provavelmente criado para acelerar a busca genérica de `pesquisar()` (23 colunas via
  `LIKE`), não uma decisão de modelagem relacional normal.
- **Sem FK** para nenhuma outra tabela — todo relacionamento é por string de nome.
- **Anomalia:** `AUTO_INCREMENT=2147483648` (2^31) já no dump de maio/2019 — contador
  MySQL corrompido/saturado; irrelevante porque `numero` não usa esse mecanismo.
- Engine: MyISAM (não InnoDB) — sem suporte a FK mesmo se fosse tentado, sem
  transação real.

### `cliente`, `fabricante`, `fornecedor`, `assistencia_tecnica`

- Schema quase idêntico entre as 4 (endereço completo, contato, `data_de_cadastro`).
- `fabricante`/`fornecedor`/`assistencia_tecnica` têm `politicadegarantia` (text, nunca
  parseado por código — só exibido); `cliente` não tem.
- `fornecedor`/`cliente` usam coluna `observacaoFR` (nome vazado de módulo copiado);
  `fabricante`/`assistencia_tecnica` usam `observacao`.
- Todas Engine=InnoDB (diferente de `bd`, que é MyISAM).
- **Sem FK** entre si nem para `bd`.

### `assistencias` — tabela órfã de tentativa de unificação

- `id, nome, ddd, fone, email, localidade, observacao, un, uf, cep, site, tipo
  (default 'REPRESENTANTE'), frete, cfop`.
- Engine=MyISAM. Campo `tipo` sugere que era para ser um Parceiro polimórfico
  (representante/fornecedor/fabricante).
- **Só referenciada por TEMA V1** (`menujs-right/fornecedores.php`) — TEMA V2 não a usa
  em lugar nenhum (confirmado pelos dois agentes de arqueologia). Tabela viva no banco,
  mas funcionalmente abandonada na versão de referência.

### `usuario`

- PK `id_usuario`, UNIQUE `email`.
- `Key1461`/`Key1581` — dois campos de hash SHA1 de senha, sem salt, um por tema
  histórico (login aceita OR entre os dois).
- `permissao` int(2) — domínio `-1/1/2/3/4` (ver `regras-negocio-rma-legado.md`).
- `app` varchar(11) NOT NULL, **sem DEFAULT** — guarda preferência de tema
  ("14.6.1"/"15.8.1"); confirmado sem valor default no schema, então todo registro
  precisa gravar isso explicitamente na criação (não verificado se o código de
  cadastro de usuário sempre faz isso — risco de string vazia).
- `anotacao` text NOT NULL — bloco de notas pessoal.

### `log` — auditoria de autenticação

- `email, nome, data, sistema_operacional, ip, navegador, retorno, app` — sem PK
  significativa além de `id_log` auto-increment. `retorno` ∈ {permitido, negado,
  bloqueado} (inferido do código, não é enum de banco).

### `modificacao` — auditoria de edição de RMA

- `numero` (FK lógica para `bd.numero`, sem constraint real), snapshot desnormalizado
  (`nome, email, dta, descricao, app, ip, navegador, so, fabricante, modelo, sn`) — grava
  o estado dos campos-chave no momento da edição, não um diff nem a ação específica.

### `relatorio`

- `id` varchar(50) PK, `informacaoadicional` text — bloco de nota livre por relatório
  (ex.: id `'RCRD'`), usado por financeiro para anotar acompanhamento manual.

## Valores de domínio confirmados por coluna (para enum na V3)

| Coluna | Domínio confirmado | Fonte |
|---|---|---|
| `bd.status` | `entrada`, `recebido`, `encaminhado`, `concluido`, `arquivado`, `retornou` (órfão) | código + RN-01..10 |
| `bd.solucao` | 17 valores (ver `regras-negocio-rma-legado.md` RN-17/domínio) | código, confirmado também via menu real do TEMA V1 nesta sessão |
| `bd.prioridade` | `baixa`/`media`/`alta` no formulário; `urgente` aparece em código de destaque mas não é selecionável (resíduo) | RN-08, RN-11 |
| `bd.origem` | `Unknown`, `Loja`, `Casa`, `Cliente`, `Licitação`, `Leilão`, `Mercado Livre`, `Credito`, `AC`, `Rolo` (nem todos selecionáveis no form) | RN-14 |
| `bd.empresa` | `Cellsystem`, `Expert`, `Registros Ativos`/`R A`, `Informatica`/`T A` | modelo-dominio §Empresa |
| `bd.lancadoretorno` | `''`, `pendente`, `nf_devolucao`, `sem_movimentacao`, `nao`, `sim` | agente 15.8.1 |
| `usuario.permissao` | `-1, 1, 2, 3, 4` | regras-negocio §permissões |
| `usuario.app` | `14.6.1`, `15.8.1` | confirmado por `trocarapp.php` e pelo LEGACY-RUNTIME |
| `assistencias.tipo` | `REPRESENTANTE` (default), `fornecedor`, `fabricante` (inferido do uso em `menujs-right/fornecedores.php`) | modelo-dominio §Autorizada |

## Relações implícitas (por nome, não FK) — mapa

```
bd.cliente        → cliente.nome
bd.fabricante     → fabricante.nome
bd.fornecedor     → fornecedor.nome
bd.destinatario   → assistencia_tecnica.nome  (1ª tentativa)
                  → fornecedor.nome            (2ª tentativa, cascata)
                  → fabricante.nome            (3ª tentativa, cascata)
modificacao.numero → bd.numero  (sem constraint)
log/modificacao    → usuario.email (sem constraint)
```

## Tabelas/campos mortos ou de baixo valor para a V3

- `assistencias` — abandonada (ver acima), a **ideia** (parceiro com papel) é candidata a
  virar a modelagem real da V3, não a tabela em si.
- `bd.status='retornou'` — nunca gravado, remover do enum final ou manter só como
  registro histórico de que existiu.
- `relatorio.informacaoadicional` — uso muito específico (nota manual por relatório
  fixo); avaliar se ainda faz sentido como conceito na V3 ou se vira campo de anotação
  genérico em outro lugar.

## Pendente

- Confirmar se `usuario.app` sempre recebe valor não-vazio na criação (schema não tem
  DEFAULT) — checar código de cadastro de usuário em TEMA V1 e TEMA V2 (ainda não
  comparado linha a linha, ver `INV-RMA-00` §9).
- Índices de FK "lógica" (`modificacao.numero`) — decidir se a V3 usa FK real desde a
  baseline ou só na fase de evolução (ver regra de evolução do banco, `INV-RMA-00` §10).
