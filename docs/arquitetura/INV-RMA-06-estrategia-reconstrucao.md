# INV-RMA-06 — Estratégia de reconstrução: mapa de migração V2→V3

Data: 2026-08-24. Mapa completo, campo a campo, das 9 tabelas de `rma_legacy` (schema
real em `~/github/08.24.4-legacy-gerenciador-de-rma/db/schema-only.sql`, extraído do
dump `dump-cellsyst_rma-201912161213.sql`) para o schema V3 desenhado nas Fases 1-8
(`INV-RMA-05`). Este documento é a "tabela de tradução explícita" exigida pelo princípio
`INV-RMA-05` §1.1: todo número/string mágico do legado só pode aparecer aqui, nunca
vazado para regra de negócio, view ou teste do resto da aplicação.

Convenção de evidência: cada linha cita a coluna legada real (lida diretamente do
`CREATE TABLE`, não de memória) e o destino V3 real (lido das migrations já
implementadas, Fases 1-2, ou do desenho já fechado das Fases 3-8 em `INV-RMA-05`).
Onde não há evidência suficiente para decidir sozinho, a linha diz **PENDÊNCIA** e não
inventa comportamento.

## 0. Regra geral de tradução

Nenhum número/string cru do legado aparece fora desta camada. Toda tradução vive em uma
classe dedicada, `app/Rma/Infraestrutura/Migracao/TabelaDeTraducao.php` (um método
estático por enum: `status()`, `solucao()`, `origem()`, `prioridade()`,
`statusDeLancamento()`, `papel()`, `temaPreferido()`), que os "Importadores" (§5) chamam
— nunca um `match`/`if` de string solto dentro do importador.

## 1. `bd` → `rmas` (entidade central, campo a campo)

`bd` tem PK `numero` (int, gerado em PHP, não FK'd por ninguém). `rmas` usa `id`
incremental do Eloquent (decidido na Fase 3). Para rastreabilidade e **idempotência**
(requisito fixo da Fase 9), a migração acrescenta uma coluna nova:

- `rmas.numero_legado` (int, nullable, `unique`) — grava o `bd.numero` de origem. O
  importador de RMA sempre verifica primeiro `Rma::where('numero_legado', $numero)
  ->exists()` — se existir, pula a linha (idempotência por construção, não por
  "checar se já rodou" externo).

### 1.1. Campos já com destino nas Fases 3-8 (mapeamento direto ou com transformação)

| Coluna `bd` (tipo real) | Destino `rmas` | Transformação |
|---|---|---|
| `numero` (int PK) | `numero_legado` | cópia direta (nova coluna, ver acima) |
| `descricao` (varchar 50) | `descricao` | cópia direta |
| `fabricante` (varchar 50) | `fabricante_id` | `EncontrarOuCriarFabricante` (§4), aplica RN-13 (HGST→Hitachi) defensivamente antes de buscar/criar — dado histórico já deveria estar normalizado (RN-13 já era ativa em ambos os temas no momento da gravação original), a reaplicação é só proteção contra registro anterior à regra existir |
| `modelo` (varchar 50) | `modelo` | cópia direta |
| `os` (varchar 11) | `os` | cópia direta |
| `status` (varchar 50) | `status` (enum `Status`) | tabela de tradução §2 |
| `origem` (varchar 50) | `origem` (enum `Origem`) | tabela de tradução §3, aplica cascata RN-14 já resolvida na gravação original (valor já deveria estar normalizado) |
| `sn` (varchar 50) | `sn` | cópia direta |
| `prioridade` (varchar 150, default `'media'`) | `prioridade` (enum `Prioridade`) | tabela de tradução §4 |
| `defeito` (varchar 150) | `defeito` | cópia direta |
| `nfcompra` (varchar 11) | `nfcompra` | cópia direta |
| `entrada` (datetime) | `created_at` | cópia direta, **valor histórico, não `now()`** — o importador grava a data real (Eloquent permite mass-assignment de timestamps ao importar) |
| `recebido` (varchar 50, string de data) | `recebido_em` | parse de data — ver PENDÊNCIA-1 (formato) |
| `encaminhado` (varchar 50) | `encaminhado_em` | idem |
| `concluido` (varchar 50) | `concluido_em` | idem |
| `nfcompra_emissao` (varchar 50) | `nfcompra_emissao` | parse de data — mesma PENDÊNCIA-1 |
| `nfvenda` (varchar 11) | `nfvenda` | cópia direta |
| `nfvenda_emissao` (varchar 50) | `nfvenda_emissao` | parse de data |
| `nfcompra_chave` (varchar 100) | `nfcompra_chave` | cópia direta |
| `nfvenda_chave` (varchar 100) | `nfvenda_chave` | cópia direta |
| `observacao` (text) | `observacao` | cópia direta |
| `solucao` (varchar 50) | `solucao` (enum `Solucao`) | tabela de tradução §5 |
| `marcarestoque` (int(11), nullable) | `marcarestoque` (boolean) | `1`→`true`, `0`→`false`, `NULL`→`true` (mesmo default da coluna V3, Fase 5) |
| `creditodisponivel` (int(11), default `0`) | `credito_disponivel` (boolean) | `1`→`true`, `0`/`NULL`→`false` |
| `empresa` (varchar 50) | `empresa` | cópia direta, com normalização de 2 abreviações conhecidas — ver §6 (não é enum, ver justificativa) |
| `cliente` (varchar 50) | `cliente_id` | `EncontrarOuCriarCliente` (Fase 2, já implementado) |
| `destinatario` (varchar 50) | `destinatario_type`/`destinatario_id` (polimórfico) | cascata de resolução §7 (**sem auto-criação** — decisão justificada abaixo) |
| `protocolo` (varchar 50) | `protocolo` | cópia direta |
| `arquivado` (varchar 50, string de data) | `arquivado_em` | parse de data |
| `fornecedor` (varchar 50) | `fornecedor_id` | `EncontrarOuCriarFornecedor` (§4) |
| `valor` (float) | `valor` | cópia direta — **nota de coordenação**, ver §8 |
| `snretorno` (varchar 50) | `snretorno` | cópia direta |
| `lancadoretorno` (varchar 11) | `lancadoretorno` (enum `StatusDeLancamento`) | tabela de tradução §9 |
| `dtaalt` (datetime) | `updated_at` | cópia direta, valor histórico |

### 1.2. Campos sem regra de negócio dona (preservados por completude, não por uso)

Nenhuma das 21 RN / 48 `LEG-RMA-NNN` lê estes campos. Preservá-los é exigido pelo
princípio de migração sem perda de dado (a Fase 9 é a única fase autorizada a acrescentar
coluna "porque o dado existe", diferente das Fases 1-8 que só criam coluna quando a regra
que a usa já existe — aqui a "regra" é a auditoria histórica em si). Entram numa migration
nova só desta fase, `2026_09_01_000000_add_campos_historicos_de_migracao_to_rmas_table.php`,
todas `nullable`, sem cast especial (strings/datas cruas):

| Coluna `bd` | Coluna nova em `rmas` | Nota |
|---|---|---|
| `nfdevolucaodevenda` (varchar 11) | `nf_devolucao_de_venda` | sem uso conhecido |
| `nfentrada_cli` (varchar 100) | `nf_entrada_cliente_legado` | sem uso conhecido |
| `nfretorno_cli` (varchar 100) | `nf_retorno_cliente_legado` | sem uso conhecido |
| `nfremessa` (varchar 50) | `nf_remessa` | sem uso conhecido |
| `nfremessa_emissao` (varchar 50) | `nf_remessa_emissao` | parse de data |
| `nfremessa_chave` (varchar 50) | `nf_remessa_chave` | sem uso conhecido |
| `nfretorno` (varchar 50) | `nf_retorno_numero` | **renomeado** para não colidir com `snretorno` (S/N de retorno, Fase 4) — são conceitos diferentes (NF de devolução vs. número de série do item que voltou) |
| `nfretorno_emissao` (varchar 50) | `nf_retorno_emissao` | parse de data |
| `nfretorno_chave` (varchar 50) | `nf_retorno_chave` | sem uso conhecido |
| `pn` (varchar 50) | `pn` | part number, sem uso conhecido |
| `snid` (varchar 50) | `snid` | sem uso conhecido |
| `rastreio_ida` (varchar 50) | `rastreio_ida` | sem uso conhecido |
| `rastreio_retorno` (varchar 50) | `rastreio_retorno` | sem uso conhecido |
| `cliente_email` (varchar 50) | `cliente_email_legado` | **não** grava em `clientes.email` — é o e-mail digitado *naquele RMA específico*, pode divergir do cadastro atual do parceiro; snapshot histórico, não autoritativo |
| `destinatario_email` (varchar 150) | `destinatario_email_legado` | mesma lógica — snapshot por RMA, não grava no cadastro do parceiro resolvido |
| `destinatario_fone` (varchar 50) | `destinatario_fone_legado` | idem |
| `descricao_final` (varchar 50) | `descricao_final_legado` | nunca referenciado em nenhum `LEG-RMA-NNN`/RN — campo aparentemente não usado por nenhuma tela viva; preservado por precaução, candidato a remoção futura se o relatório de reconciliação confirmar 100% `NULL`/vazio em todos os registros reais |
| `usuario` (varchar 50, e-mail de quem operou) | `operador_email_legado` | ver §10 — resolução de FK opcional |

### 1.3. Campos não migrados (com justificativa)

| Coluna `bd` | Por que não migra |
|---|---|
| `prazo` (varchar 50) | **Recalculado, não persistido** — Fase 5 já decide `Rma::prazoLegal() = created_at->addDays(30)`, resultado idêntico sem denormalizar (`INV-RMA-05` §10) |
| `ano` (int 4) | Derivável de `created_at->year`; nenhuma regra o usa isoladamente |
| `dtains` (datetime) | Seria duplicata de `entrada`/`created_at`. **Usado só como verificação cruzada no relatório de reconciliação** (§ Fase 9, se `dtains != entrada` num registro, o relatório lista como divergência a investigar) — não vira coluna V3 |
| `retornou` (date) | Estado morto (`LEG-RMA-016`/RN-20, `Status` não tem case para ele). Se o relatório de reconciliação encontrar alguma linha com `retornou IS NOT NULL` (dado real contradizendo "nunca gravado"), isso é listado como **anomalia** no relatório — não silenciosamente descartado. Ver PENDÊNCIA-2 |

## 2. Tradução de `status`

| `bd.status` | `Status` (V3) |
|---|---|
| `'entrada'` | `Status::Entrada` |
| `'recebido'` | `Status::Recebido` |
| `'encaminhado'` | `Status::Encaminhado` |
| `'concluido'` | `Status::Concluido` |
| `'arquivado'` | `Status::Arquivado` |
| `'retornou'` | **sem case correspondente** — ver PENDÊNCIA-2 |
| qualquer outro valor / `NULL` | **anomalia**, linha reportada no relatório de reconciliação, RMA importado sem `status` resolvido (fica pendente de correção manual pós-migração, não bloqueia o restante do import) |

## 3. Tradução de `origem`

O domínio confirmado em `inventario-banco-rma-v2.md` já bate 1:1 com o enum `Origem`
fechado na Fase 5 (`INV-RMA-05` §10):

| `bd.origem` | `Origem` (V3) |
|---|---|
| `'Unknown'` | `Origem::Unknown` |
| `'Loja'` | `Origem::Loja` |
| `'Casa'` | `Origem::Casa` |
| `'Cliente'` | `Origem::Cliente` |
| `'Licitação'` | `Origem::Licitacao` |
| `'Leilão'` | `Origem::Leilao` |
| `'Mercado Livre'` | `Origem::MercadoLivre` |
| `'Credito'` | `Origem::Credito` |
| `'AC'` | `Origem::Ac` |
| `'Rolo'` | `Origem::Rolo` |
| qualquer outro valor (variação de digitação, valor legado anterior à normalização RN-14) | **anomalia**, reportada no relatório de reconciliação, `origem` importado como `NULL` — não inventa um case novo no enum sem confirmar volume real de ocorrência |

## 4. Tradução de `prioridade`

| `bd.prioridade` | `Prioridade` (V3) |
|---|---|
| `'baixa'` | `Prioridade::Baixa` |
| `'media'` (também o `DEFAULT` da coluna) | `Prioridade::Media` |
| `'alta'` | `Prioridade::Alta` |
| `'urgente'` (resíduo documentado em RN-08 — usado em código de destaque, nunca no `<select>`) | **Decisão condicional, não confirmada contra dado real:** se o relatório de reconciliação encontrar alguma linha real com esse valor, mapear para `Prioridade::Alta` (mais próximo semanticamente — ambos disparam destaque visual no legado) e listar a linha como **conversão assistida** no relatório (não silenciosa). Não promovido a mapeamento "normal" da tabela acima porque não há evidência de que o valor realmente ocorre em dado real — só em código morto |
| qualquer outro valor | anomalia, `prioridade` importado como `NULL` |

## 5. Tradução de `solucao`

Os 16 valores fechados na Fase 4 (`INV-RMA-05` §9, lidos diretamente de
`15.8.1/page/rma.php:578-595`) mapeiam 1:1 por igualdade de string (os valores do enum
já são literalmente os valores do `<select>` original, backed string):

```
'REPARO' → Solucao::Reparo
'TROCA DO PRODUTO' → Solucao::TrocaDoProduto
'TROCA DE PECA INTERNA' → Solucao::TrocaDePecaInterna
'PENDENTE CREDITO' → Solucao::PendenteCredito
'GERADO CREDITO' → Solucao::GeradoCredito
'DEVOLUCAO DO PRODUTO' → Solucao::DevolucaoDoProduto
'REEMBOLSO DO DINHEIRO' → Solucao::ReembolsoDoDinheiro
'ORCAMENTO PAGO' → Solucao::OrcamentoPago
'ORCAMENTO PENDENTE' → Solucao::OrcamentoPendente
'ORCAMENTO NEGADO' → Solucao::OrcamentoNegado
'REPARO PELO RMA' → Solucao::ReparoPeloRma
'CASO SOLUCIONADO' → Solucao::CasoSolucionado
'TESTADO TUDO OK' → Solucao::TestadoTudoOk
'PROCON' → Solucao::Procon
'DESCRITO NA OBSERVACAO' → Solucao::DescritoNaObservacao
'SEM GARANTIA' → Solucao::SemGarantia
```

Para qualquer valor que não bata exatamente (typo histórico, valor anterior à
consolidação do `<select>` atual, ou string vazia): grava o valor original em coluna nova
`solucao_legado_bruto` (string, nullable — mesma migration de §1.2), deixa `solucao`
`NULL`, reporta no relatório de reconciliação. Não tenta "adivinhar" aproximação (ao
contrário de `prioridade`, aqui não há par semântico óbvio de 1 valor perdido para outro).

## 6. `empresa` — por que fica string, não vira enum

`empresa` nunca é lido por nenhuma das 21 RN nem filtra nenhum relatório
(`modelo-dominio-rma-legado.md` §Empresa: "embrião de multiempresa... sem qualquer
isolamento"). O princípio `INV-RMA-05` §1.1 exige enum para "conceito de domínio com
conjunto fechado de valores **usado por regra de negócio**" — como nenhuma regra
existente compara/ramifica sobre `empresa`, não há comparação `==`/`in_array` a
substituir; é rótulo livre de exibição, não decisão de domínio. Fica como string, sem
cast. Única normalização aplicada na migração (documentada, não decisão nova): as duas
abreviações confirmadas em `inventario-banco-rma-v2.md` viram o nome completo, por
consistência de exibição:

| `bd.empresa` | `rmas.empresa` |
|---|---|
| `'R A'` | `'Registros Ativos'` |
| `'T A'` | `'Informatica'` |
| qualquer outro valor (`'Cellsystem'`, `'Expert'`, `'Registros Ativos'`, `'Informatica'`, etc.) | cópia direta |

## 7. Resolução de `destinatario` (polimórfico) — cascata, sem auto-criação

O legado resolve `bd.destinatario` (string) contra três tabelas em cascata:
`assistencia_tecnica.nome` → `fornecedor.nome` → `fabricante.nome`
(`inventario-banco-rma-v2.md`). **Decisão adotada para a migração:** reproduzir a mesma
cascata (comparação normalizada — trim + case-insensitive, correção sobre o bug de
comparação exata do legado), na mesma ordem. **Sem auto-criação** se nenhuma das três
bater — diferente de `cliente`/`fabricante`/`fornecedor` (§4 do documento, que usam
`EncontrarOuCriar*` com criação automática). Justificativa: o legado também nunca
auto-cria para `destinatario` (não há código de criação em nenhuma das cascatas lidas em
`modelo-dominio-rma-legado.md`) — se o nome não bate em nenhuma tabela, o legado
simplesmente não consegue vincular (sem FK, o campo fica "solto"). Reproduzir
auto-criação aqui inventaria um comportamento que o legado nunca teve, e pior: criaria
ambiguidade real (em qual das 3 tabelas criar, se o nome pode logicamente pertencer a
qualquer uma?). Quando não resolve: `destinatario_type`/`destinatario_id` ficam `NULL`,
o nome original é preservado em `destinatario_nome_legado` (nova coluna, mesma migration
de §1.2), e a linha é listada no relatório de reconciliação como "destinatário não
resolvido" — correção manual pós-migração, não bloqueia o import do RMA.

## 8. Nota de coordenação — coluna `valor`

`app/Rma/Aplicacao/Alertas/UrgenciaPorThreshold.php` (Fase 5, RN-12,
`openspec/changes/rma-alertas-e-prioridade/design.md`) já usa `->where('valor', '>',
75.00)` no código de exemplo, mas a migration listada em `INV-RMA-05` §10 /
`rma-alertas-e-prioridade/tasks.md` **não lista `valor` entre as colunas adicionadas**.
Isso é uma inconsistência já existente na especificação da Fase 5 (não introduzida por
este documento) — registrada aqui porque a Fase 9 depende de saber para onde `bd.valor`
vai. **Não é decisão desta fase corrigir o `design.md` da Fase 5** (Fase 5 pertence a
outro fluxo de trabalho em andamento); a migração assume que a coluna `rmas.valor`
(`float`, nullable) existirá até a Fase 9 rodar — se a Fase 5 implementada não a criar, é
um bloqueador a resolver antes do migrador rodar (adicionar a coluna faltante), não uma
razão para o migrador inventar um destino alternativo.

## 9. Tradução de `lancadoretorno`

| `bd.lancadoretorno` | `StatusDeLancamento` (V3) |
|---|---|
| `''` (string vazia) ou `NULL` | `NULL` (sem cast — nenhum estado ainda) |
| `'pendente'` | `StatusDeLancamento::Pendente` |
| `'nf_devolucao'` | `StatusDeLancamento::NfDevolucao` |
| `'sem_movimentacao'` | `StatusDeLancamento::SemMovimentacao` |
| `'nao'` | `StatusDeLancamento::Nao` |
| `'sim'` | `StatusDeLancamento::Sim` |
| qualquer outro valor | anomalia, reportada, `NULL` |

## 10. `usuario` (e-mail de quem operou o RMA)

`bd.usuario` é sempre preservado em `operador_email_legado` (string, §1.2). **Adicional:**
o importador tenta um "soft match" — se `email_informado` bate (case-insensitive) com o
`email` de um `User` já migrado (§11), grava também `operador_id` (FK nullable, nova
coluna da mesma migration de §1.2) apontando pra esse usuário. Se não bate, `operador_id`
fica `NULL`, mas `operador_email_legado` preserva o dado bruto — nenhuma informação é
perdida mesmo sem o vínculo. Não é usado por nenhuma regra de negócio hoje (RN-consultada:
nenhuma), é só enriquecimento de auditoria histórica, mesma categoria de "campo
preservado por completude" de §1.2.

## 11. `usuario` → `users` (Fase 1, já implementado)

Dedup natural: `users.email` já é `UNIQUE` desde a migration original do Laravel — o
importador de usuário usa `updateOrCreate(['email' => ...], [...])`, que já é idempotente
por construção (não precisa de coluna `id_legado`).

| Coluna `usuario` (legado) | Destino `users` | Transformação |
|---|---|---|
| `nome` | `name` | cópia direta |
| `email` (UNIQUE) | `email` | cópia direta — chave de dedup |
| `Key1461`/`Key1581` (SHA1 sem salt) | **não migrado como senha** | ver decisão de segurança abaixo |
| `anotacao` (text NOT NULL) | `anotacao` | cópia direta |
| `permissao` (int, domínio `-1/1/2/3/4`) | `papel` (enum `Papel`) | tabela de tradução abaixo |
| `app` (varchar 11, `'14.6.1'`/`'15.8.1'`) | `tema_preferido` (enum `TemaPreferido`) | tabela de tradução abaixo |
| `data_de_cadastro` (date) | `created_at` | cópia direta, valor histórico (sem componente de hora — meia-noite) |
| `quantidade_login`, `ultimo_login` | **não migrado** | ver justificativa abaixo |

**Tradução de `permissao` → `Papel`** (fecha o "número mágico" citado desde `INV-RMA-05`
§1.1 — primeira vez que a tabela completa aparece por escrito):

| `usuario.permissao` | `Papel` (V3) |
|---|---|
| `-1` | `Papel::Bloqueado` |
| `1` | `Papel::Leitura` |
| `2` | `Papel::Operador` |
| `3` | `Papel::Supervisor` |
| `4` | `Papel::SuperAdministrador` |
| qualquer outro valor | anomalia, reportada, usuário importado com `Papel::Bloqueado` (fail-safe — nunca conceder acesso além do confirmado) |

**Tradução de `app` → `tema_preferido`:**

| `usuario.app` | `TemaPreferido` (V3) |
|---|---|
| `'14.6.1'` | `TemaPreferido::V1` |
| `'15.8.1'` | `TemaPreferido::V2` |
| `''`/valor vazio (pendência já registrada em `inventario-banco-rma-v2.md`: coluna sem `DEFAULT`, risco de string vazia) | `TemaPreferido::V1` (mesmo default da coluna V3) |

**Decisão de segurança — senhas não são migradas como hash:** `Key1461`/`Key1581` são
SHA1 sem salt, herança de dois sistemas de autenticação históricos. `INV-RMA-05` §4 já
fixa que a V3 usa `Hash` nativo do Laravel (bcrypt) e nunca SHA1 — não existe conversão
reversível de SHA1 para bcrypt (não se recupera a senha original para re-hashear
corretamente). O importador gera uma senha aleatória temporária para cada usuário
migrado e dispara o fluxo nativo de "esqueci minha senha" do Laravel (e-mail de reset)
para todos os usuários migrados, em vez de tentar preservar qualquer credencial antiga.
Esta é a única forma de manter a garantia de segurança já fixada sem inventar mecanismo
novo — é aplicação direta de uma decisão já tomada, não uma decisão nova desta fase.

**`quantidade_login`/`ultimo_login` não migrados:** são contadores derivados de login,
redundantes com a tabela `tentativas_de_acesso` (Fase 1, já grava cada tentativa com
resultado). O valor histórico agregado exato (quantas vezes logou antes da V3 existir) se
perde, mas nenhuma `LEG-RMA-NNN`/RN depende desse número — é aceito como simplificação,
não pendência.

## 12. `log` → `tentativas_de_acesso` (Fase 1, já implementado)

| Coluna `log` | Destino `tentativas_de_acesso` | Transformação |
|---|---|---|
| `email` | `email_informado` | cópia direta |
| `nome` | **não migrado** | ver justificativa abaixo |
| `data` | `created_at`/`updated_at` | valor histórico |
| `sistema_operacional` | **não migrado** | ver justificativa abaixo |
| `ip` | `ip` | cópia direta |
| `navegador` | `user_agent` | cópia direta (perde `sistema_operacional` como campo separado, ver abaixo) |
| `retorno` (`permitido`/`negado`/`bloqueado`) | `resultado` | cópia direta — domínio já bate 1:1 com o enum `ResultadoDeAcesso` da Fase 1 |
| `app` | **não migrado** | tema não é atributo de tentativa de acesso na V3 |
| — | `user_id` | soft match por `email` contra `users` já migrados (mesmo padrão de §10); `NULL` se não bate |

**Decisão, não pendência:** `nome` e `sistema_operacional` não ganham coluna nova. O
schema de `tentativas_de_acesso` já está implementado (Fase 1 concluída) com
`email_informado`/`ip`/`user_agent`/`resultado` — o que basta para `LEG-RMA-043`
(auditoria de autenticação) já estar `PARIDADE` na matriz. Adicionar coluna a uma tabela
de uma fase já implementada e testada só para reter dois campos que nenhuma tela ou
regra jamais consultou é o tipo de acréscimo "porque o dado existe" que o princípio de
não-antecipação já rejeita para as Fases 1-8 — aqui a diferença é que não há sequer
regra futura conhecida que precise deles. Se essa necessidade aparecer, é `EVO-AUD`
(backlog evolutivo), não Fase 9.

## 13. `modificacao` → `modificacoes_de_rma` (Fase 7, ainda não implementado)

Fase 7 (`INV-RMA-05` §12) já desenha `modificacoes_de_rma`: `rma_id` (FK real),
`user_id` (FK real), `acao` (enum `AcaoDeModificacao`), `ip`, `user_agent`,
`estado_apos` (json), timestamps.

| Coluna `modificacao` (legado) | Destino `modificacoes_de_rma` | Transformação |
|---|---|---|
| `numero` | `rma_id` | resolve via `rmas.numero_legado` (mesma chave de §1) — se o RMA correspondente não foi migrado (órfão), a linha de modificação é descartada e listada no relatório como "modificação órfã" |
| `nome`, `email` | `user_id` | soft match por `email` (mesmo padrão de §10/§12); `nome` não persistido à parte, redundante com o relacionamento |
| `dta` | `created_at` | valor histórico |
| `descricao`, `app`, `so`, `fabricante`, `modelo`, `sn` | `estado_apos` (json) | agrupados no snapshot json — é exatamente a forma que o legado já usava (snapshot desnormalizado dos campos-chave), só migrando o formato de armazenamento (colunas → chaves json), não o conteúdo |
| `ip`, `navegador` | `ip`, `user_agent` | cópia direta |
| — | `acao` | **todas as linhas migradas recebem `AcaoDeModificacao::Edicao`** — o legado nunca discriminava o tipo de ação em `modificacao` (só registrava "uma edição aconteceu"), então não há informação de origem para inferir `Criacao`/`Receber`/`Encaminhar`/etc. Isso é uma limitação conhecida e aceita só dos registros históricos migrados; todo registro **novo**, criado pela V3 depois da migração, já grava a ação granular correta (Fase 7 usa eventos de domínio específicos por verbo) |

## 14. `relatorio` — PENDÊNCIA-3, não decidida

`relatorio.informacaoadicional` (nota de texto livre por relatório fixo, ex.: id `'RCRD'`)
**não tem destino definido em nenhuma das Fases 1-8.** O Fase 6 (`INV-RMA-05` §11) já
implementa RCD/RPEC/RMPE como consultas de leitura puras, sem tabela própria — não há
"onde" gravar essa nota na V3 atual. `checklist-master-v3.md` Parte 4 já registrava isso
como pendência ("decidir se vira config genérica ou é descartado") — continua sem
decisão, carregada para cá sem inventar resposta. Duas opções ficam registradas para o
usuário decidir quando a Fase 9 virar corrente:

- (A) Criar uma tabela nova, pequena, `notas_de_relatorio` (`id` string, `nota` text) só
  para preservar esse dado histórico, sem nenhuma tela nova consumindo-a ainda (mesma
  categoria de "preservação sem regra", como §1.2).
- (B) Descartar — o dado é uma anotação manual de acompanhamento financeiro, não uma
  regra de negócio; se o financeiro precisar reconstituir isso, o backup do banco legado
  continua preservado no repositório Legacy (regra de preservação histórica já em vigor).

## 15. `assistencias` (tabela órfã) — não migrar dados

Já decidido em `paridade-v2-v3.md` (`LEG-RMA-035`, "RETOMAR IDEIA, não código"): a tabela
é funcionalmente abandonada (só referenciada por TEMA V1, `menujs-right/fornecedores.php`,
inconsistente com a cascata de política de garantia). **Nenhuma linha é migrada.** A
*ideia* (parceiro com papel único, polimórfico) fica só como `EVO-DOM-001` no backlog
evolutivo — não implementada na reconstrução fiel.

## 16. `cliente`/`fabricante`/`fornecedor`/`assistencia_tecnica` → tabelas V3 (Fase 2, já implementado)

Dedup por nome normalizado (`EncontrarOuCriarCliente` e as 3 generalizações, §4) — sem
coluna `id_legado`, o nome já é a chave de negócio usada pelo próprio runtime da V3.

### `cliente` → `clientes`

| Coluna legado | Destino V3 | Nota |
|---|---|---|
| `nome` | `nome` | chave de dedup |
| `representante` | `representante` | direto |
| `rgie` | **não migrado** | `clientes` não tem coluna equivalente (nem nenhuma das 4 tabelas de parceiro V3 tem — ver nota geral abaixo) |
| `cpfcnpj` | `cpf_cnpj` | direto |
| `email` | `email` | direto |
| `fone` | `telefone` | direto |
| `fone2` | `telefone2` | direto |
| `cep`,`logradouro`,`numero`,`complemento`,`bairro`,`cidade`,`uf` | mesmos nomes | direto |
| `observacaoSGV` + `observacaoFR` | `observacao` | **concatenados** — V3 tem um único campo de observação; migrador grava `"SGV: {observacaoSGV}\nFR: {observacaoFR}"` (só as partes não vazias) |
| `maior_interesse`, `compras` | **não migrado** | nenhum `LEG-RMA-NNN`/RN referencia estes 2 campos — tratados como experimentação livre do legado, fora do escopo da reconstrução fiel (não é `EVO`, é simplesmente não reconstruído por falta de funcionalidade documentada que dependa deles) |
| `data_de_cadastro` | `created_at` | valor histórico |

### `fabricante` → `fabricantes`, `fornecedor` → `fornecedores`, `assistencia_tecnica` → `assistencias_tecnicas`

As 3 têm o mesmo shape entre si (confirmado em `INV-RMA-05` §7, `trait
TemEnderecoEContato`) e mapeiam quase idênticas:

| Coluna legado | Destino V3 | Nota |
|---|---|---|
| `nome` | `nome` | chave de dedup |
| `representante` | `representante` | direto |
| `rgie` | **não migrado** | mesma nota acima |
| `cpfcnpj` | `cpf_cnpj` | direto |
| `email` | `email` | direto |
| `email2` | `email_secundario` | direto |
| `fone` | `telefone` | direto |
| `fone2` | `telefone2` | direto |
| `cep`,`logradouro`,`numero`,`complemento`,`bairro`,`cidade`,`uf` | mesmos nomes | direto |
| `www` | `www` | direto |
| `frete` | `frete` | direto |
| `cfop` | `cfop` | direto |
| `observacao` (fabricante/assistência) **ou** `observacaoSGV`+`observacaoFR` (fornecedor) | `observacao` | fornecedor usa a mesma concatenação de `cliente` acima; fabricante/assistência copiam direto (só têm 1 campo) |
| `politicadegarantia` | `politica_de_garantia` | cópia direta, texto livre, sem parsing (igual ao legado) |
| `data_de_cadastro` | `created_at` | valor histórico |

**Nota geral sobre `rgie`:** nenhuma das 4 migrations V3 (já implementadas, Fase 2)
inclui inscrição estadual/RG. Não há `LEG-RMA-030` a `033` (CRUD de parceiro) que
documente uso funcional desse campo além de exibição em formulário — perda aceita,
mesma categoria de "campo sem regra dona" de §1.2, não pendência de decisão (é fato já
consumado pela Fase 2 implementada, alterá-lo agora significaria migrar a tabela já em
produção — fora do escopo desta fase).

## 17. Generalização de `EncontrarOuCriarCliente` (§4 — resumo consolidado)

`app/Parceiros/Aplicacao/EncontrarOuCriarFabricante.php`,
`EncontrarOuCriarFornecedor.php`, `EncontrarOuCriarAssistenciaTecnica.php` — mesmo padrão
de `EncontrarOuCriarCliente` (Fase 2, já implementado): busca por nome normalizado
(trim + case-insensitive), cria se não existir. **Importante — escopo desta
generalização é só a camada de migração, não o runtime da Fase 2/3:** o legado só
auto-cria `cliente` ao salvar um RMA pela tela (`modelo-dominio-rma-legado.md`,
confirmado); fabricante/fornecedor são sempre selecionados de uma lista fechada na
tela de criação de RMA. A V3 **não muda esse comportamento de runtime** — `CriarRma`/
`EditarRma` (Fase 3) continuam exigindo fabricante/fornecedor de uma lista já
cadastrada, só `cliente` auto-cria via `EncontrarOuCriarCliente`. As 3 classes novas
(`EncontrarOuCriarFabricante` etc.) existem **só para o migrador** — porque o legado
nunca teve FK, o `bd.fabricante`/`bd.fornecedor` histórico pode conter nomes que não
batem exatamente com nenhuma linha de `fabricante`/`fornecedor`, e a migração precisa de
alguma resolução para não perder o RMA inteiro por causa de um nome não cadastrado
formalmente.

---

## Pendências registradas nesta fase (não decididas, aguardando dado real ou decisão de produto)

1. **Formato de data de `recebido`/`encaminhado`/`concluido`/`arquivado`/
   `nfcompra_emissao`/`nfvenda_emissao`/`nf_remessa_emissao`/`nf_retorno_emissao`**
   (todos `varchar`, não `date`/`datetime` no schema legado) — RN-02 já documenta que o
   `Diferenca_de_dias()` do legado espera `d/m/Y`, mas também documenta que o campo é
   digitação livre sem máscara (`maxlength=10`, sem `type=date`), então nem todo valor
   real necessariamente obedece esse formato. O parser do importador precisa: (a) tentar
   `d/m/Y` primeiro (formato esperado); (b) se falhar, tentar `Y-m-d` (formato que o
   MySQL aceitaria via outra via de gravação); (c) se ambos falharem, marcar a data como
   não-parseável, gravar `NULL`, e listar a linha no relatório de reconciliação com o
   valor bruto original — **nunca lançar exceção que aborte a linha inteira**. Não há
   evidência ainda de quantas linhas reais caem no caso (c); só se sabe depois de rodar
   contra o banco legado real.
2. **`bd.status = 'retornou'` / `bd.retornou IS NOT NULL` em dado real** — `LEG-RMA-016`/
   RN-20 afirmam "nunca gravado por nenhuma transição em nenhum app", mas essa afirmação
   vem de leitura de código, não de consulta ao dado. Se o relatório de reconciliação
   encontrar ocorrência real, a decisão de o que fazer (status importado como `NULL` +
   anomalia? Adicionar de volta um case morto só para não perder o registro?) fica para
   quando isso acontecer de fato — não decidir preventivamente sem saber se o caso existe.
3. **`relatorio.informacaoadicional`** — sem destino definido em nenhuma fase 1-8 (§14).
   Decisão de produto explícita necessária (opção A/B) antes do migrador rodar sobre essa
   tabela — ou aceitar a opção B (descartar) por omissão se o usuário não se manifestar,
   já que o dado permanece recuperável no backup do repositório Legacy.
4. **Coluna `rmas.valor` ausente da migration listada da Fase 5** (§8) — bloqueador
   técnico a resolver antes do migrador rodar, não uma decisão de produto; registrado
   aqui para não ser esquecido quando a Fase 9 virar corrente.
