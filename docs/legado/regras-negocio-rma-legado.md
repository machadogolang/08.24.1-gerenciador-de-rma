# Regras de negócio do CellSystem RMA (legado)

Data: 2026-08-24. Cada regra documentada com origem, condição, resultado, evidência e
situação. Fonte majoritária: leitura integral de `metodo.php` (compartilhado) e
`15.8.1/banco.php`/`page/rma.php` — ver notas de cobertura no fim.

Tags de situação: **VIGENTE** (funciona no app 15.8.1, referência) · **HERDADA** (14.6.1
reaproveita via include, mesma regra) · **QUEBRADA** (código existe mas não executa /
produz erro) · **MORTA** (rota/arquivo inalcançável) · **DÚVIDA**.

---

## RN-01 — Recebido há mais de 30 dias e não encaminhado

- **Origem:** `metodo.php:3` (`listar_naoencaminhadoprazoestourado`) + view
  `15.8.1/subp/listar_naoencaminhadoprazoestourado.php`.
- **Cadeia funcional completa:** SQL filtra só `status='recebido'` → PHP calcula
  `Diferenca_de_dias(recebido, hoje)` linha a linha → se `> 30`, a linha entra na lista
  exibida (o `SELECT` já trouxe todos os `recebido`, o filtro real é em PHP).
- **Consequência:** aparece no painel de alertas da home; nenhuma ação automática.
- **Por que existe (hipótese fundamentada):** produto parado dentro da própria empresa,
  sem protocolo aberto — gargalo interno puro, consome janela de garantia do fornecedor
  e (se `origem='Cliente'`) o prazo do CDC art. 18 §1º.
- **Situação:** VIGENTE em 15.8.1. HERDADA em 14.6.1 (via include cruzado, ver matriz de
  comparação — 14.6.1 não tem implementação própria).
- **Bug confirmado:** `num_rows` do `SELECT` conta todos os `recebido`, não só os
  vencidos — se o filtro PHP zerar o resultado, a tela ainda renderiza a tabela com
  cabeçalho vazio em vez de "nenhum item encontrado".

## RN-02 — Não vai dar garantia

- **Origem:** `metodo.php:325` (`listar_naovaidargarantia`).
- **Cadeia funcional completa:** SQL traz todos `status IN ('entrada','recebido')` →
  PHP aplica DOIS critérios independentes, OR entre si:
  1. `nfvenda_emissao` preenchida e `Diferenca_de_dias(nfvenda_emissao, hoje) > 365`
     (garantia ao consumidor final expirada).
  2. `fabricante == "MARKVISION"` **E** (`fornecedor == "Receita"` **OU**
     `nfcompra_emissao` preenchida e diferença `> 365`).
- **Consequência:** entra no painel de alertas; é uma triagem preventiva para evitar
  gastar frete/protocolo num produto que vai ser negado.
- **Por que existe:** o ramo MARKVISION é conhecimento tácito cristalizado em código —
  aquele fabricante, historicamente, não honra garantia de produto vindo de leilão da
  Receita Federal (mercadoria apreendida, sem cadeia de NF válida) nem fora da janela de
  compra.
- **Situação:** VIGENTE em 15.8.1. HERDADA em 14.6.1. **[DÚVIDA]** se a regra MARKVISION
  já existia em 14.6.1 de forma independente — não verificado (14.6.1 usa o include
  cruzado, então funcionalmente herda a mesma regra, mas não foi confirmado se essa
  função já existia antes de existir o app 15.8.1).
- **Bug confirmado:** `Diferenca_de_dias()` espera datas em formato `d/m/Y`, mas
  `nfcompra_emissao`/`nfvenda_emissao` são preenchidas por **digitação livre** em campo
  texto sem máscara nem validação (`page/rma.php`, `maxlength=10`, sem `type=date`) — se
  o operador digitar em outro formato, o cálculo produz lixo silenciosamente.

## RN-03 — NF de retorno pendente de lançamento

- **Origem:** `metodo.php:270` (`listar_nfpendentelancar`).
- **Cadeia funcional completa:** puramente SQL — `status='concluido' AND
  lancadoretorno='pendente'`. Sem filtro adicional em PHP (uma das únicas 100% no banco).
- **Consequência:** aparece na lista fiscal/contábil de pendências.
- **Por que existe:** o produto voltou fisicamente (`concluido`), mas o estoque contábil
  e o livro fiscal ainda não refletem isso — divergência = risco de autuação fiscal.
- **Situação:** VIGENTE em 15.8.1, HERDADA em 14.6.1.

## RN-04 — Protocolo aberto mas não encaminhado

- **Origem:** `metodo.php:352` (`listar_pabertonaoencaminhado`).
- **Cadeia funcional completa:** puramente SQL — `status='recebido' AND protocolo!=''`.
- **Consequência:** alerta de janela de validade do protocolo.
- **Por que existe:** o protocolo (autorização de retorno emitida pelo fabricante/
  fornecedor) tipicamente expira em 15-30 dias. É a lista de "você já pediu permissão,
  use-a antes que expire".
- **Situação:** VIGENTE em 15.8.1, HERDADA em 14.6.1.

## RN-05 — Garantia do fornecedor expirada (mais de 1 ano)

- **Origem:** `metodo.php:339` (`listar_pgarantiafornecedorexpirado`).
- **Cadeia funcional completa:** SQL traz `status IN ('entrada','recebido')` → PHP
  calcula `Diferenca_de_dias(nfcompra_emissao, hoje)`; se `> 365`, entra na lista.
- **Consequência:** define **quem paga a conta** — depois de 365 dias da NF de compra, o
  custo recai sobre a empresa, não sobre o fornecedor.
- **Por que existe:** inventário de exposição financeira já materializada.
- **Situação:** VIGENTE em 15.8.1, HERDADA em 14.6.1. O "365" é hardcoded — o sistema
  assume garantia universal de 1 ano; o campo `politicadegarantia` (texto livre por
  fornecedor) existe mas nunca é parseado por essa regra.

## RN-06 — Menos de 30 dias para expirar a garantia do fornecedor

- **Origem:** `metodo.php:311` (`listar_pmenosde30`).
- **Cadeia funcional completa:** mesma base SQL da RN-05, filtro PHP para janela
  `[336, 365]` dias desde `nfcompra_emissao`; calcula e exibe **dias restantes**
  (`365 - tempodegarantia`), não dias decorridos.
- **Consequência:** alarme preventivo com ~30 dias de antecedência da RN-05.
- **Por que existe:** par preventivo da RN-05 — "encaminhe agora ou perde o direito".
- **Situação:** VIGENTE em 15.8.1, HERDADA em 14.6.1. **Único dos 10 arquivos que
  corrige corretamente o problema do `num_rows` mentiroso** (usa contador `$x` e mostra
  "nenhum item" quando zera).

## RN-07 — Destinatário estourou prazo de 30 dias para retornar

- **Origem:** `metodo.php:366` (`listar_prazodestinatario`).
- **Cadeia funcional completa:** SQL traz `status='encaminhado'` → PHP filtra
  `Diferenca_de_dias(encaminhado, hoje) > 30`.
- **Consequência:** lista de cobrança de terceiros — a única das 10 cujo "culpado" é
  externo à empresa. Exibe protocolo + destinatário juntos (roteiro de ligação de
  cobrança).
- **Por que existe:** espelha o prazo legal do CDC repassado ao fornecedor/assistência.
- **Situação:** VIGENTE em 15.8.1, HERDADA em 14.6.1.

## RN-08 — Prioridade alta sem encaminhamento

- **Origem:** `metodo.php:284` (`listar_prioridadealta`).
- **Cadeia funcional completa:** puramente SQL — `(status='entrada' OR
  status='recebido') AND prioridade='alta'`.
- **Consequência:** primeira lista exibida na home (`page/inicio.php`).
- **Por que existe:** válvula de escape humana — único critério não derivável dos dados
  (cliente VIP, produto caro, ameaça de PROCON).
- **Situação:** VIGENTE em 15.8.1, HERDADA em 14.6.1.
- **Achado à parte:** o código de destaque visual em ~14 arquivos testa
  `prioridade == "urgente"`, valor que **não existe** no `<select>` atual (domínio real:
  `baixa`/`media`/`alta`) — resquício de um domínio anterior de 4 níveis. Ver RN-11.

## RN-09 — Recebido sem nota fiscal (compra e venda)

- **Origem:** `metodo.php:380` (`listar_semnota`).
- **Cadeia funcional completa:** puramente SQL — `status='recebido' AND nfcompra<1 AND
  nfvenda<1` (comparação `< 1` em campo `varchar` é o truque clássico de MySQL: string
  vazia/não numérica é convertida para 0 na comparação).
- **Consequência:** produto órfão documentalmente — sem NF de compra não há como acionar
  o fornecedor; sem NF de venda não há como determinar se o cliente está no prazo.
- **Por que existe:** sem nota, não há garantia possível contra ninguém; também é risco
  fiscal (mercadoria de terceiro sem documento de entrada).
- **Situação:** VIGENTE em 15.8.1, HERDADA em 14.6.1.

## RN-10 — Recebido sem número de série

- **Origem:** `metodo.php:297` (`listar_semsn`).
- **Cadeia funcional completa:** puramente SQL — `status='recebido' AND sn=''`.
- **Consequência:** alerta de identificação pendente.
- **Por que existe:** S/N é a chave de identidade física do item — fabricantes exigem
  para abrir protocolo; impede troca indevida de peça (garante que voltou o mesmo
  aparelho).
- **Situação:** VIGENTE em 15.8.1, HERDADA em 14.6.1.

## RN-11 — Classificação visual de "inconformidade" (regra composta, 14 arquivos)

- **Origem:** bloco de `else if` replicado quase literalmente em ~14 arquivos de listagem
  do app 15.8.1 (ex.: `page/entrada.php`, `page/encaminhados.php`).
- **Condição composta (ordem de avaliação importa):**
  1. `solucao == "SEM GARANTIA"` → classe `TrInconformidade`
  2. `prioridade == "urgente"` (valor inexistente no domínio atual — ver RN-08) →
     `TrInconformidade`
  3. `origem=="Cliente" AND marcarestoque==0 AND entrada+30d < hoje` →
     `TrInconformidade`
  4. `prioridade == "alta"` → `TrInconformidade`
  5. `marcarestoque==0 AND origem IN ('Cliente','Licitação')` → `TrInconformidade`
  6. senão → `TrZebrada1`/`TrZebrada2` (listras alternadas, só visual)
- **Regra de negócio escondida:** `marcarestoque==0 AND origem IN ('Cliente',
  'Licitação')` significa **"o produto pertence a um terceiro, não é estoque da casa"**.
  Combinada com o prazo de 30 dias, é a materialização visual do CDC art. 18 §1º — cada
  linha nessa condição é exposição jurídica real.
- **Situação:** VIGENTE em 15.8.1. **[DÚVIDA]** se existe equivalente exato em 14.6.1 —
  não verificado; a paleta CSS de 14.6.1 é bem menor (207 linhas vs 905), então é
  provável que a versão de destaque visual seja mais simples ali, mas não confirmado.
- **Variação:** `page/recebido.php`/`page/encaminhado.php` usam `TrUrgente` em vez de
  `TrInconformidade` para os mesmos casos de prazo/prioridade — duas severidades visuais
  que divergiram entre telas do mesmo app.
- **Recomendação para o novo sistema:** promover `propriedade_terceiro` +
  `sla_legal_vencimento` a campos de primeira classe, em vez de recalcular a condição
  composta em cada view.

## RN-12 — Threshold econômico de urgência (R$ 75)

- **Origem:** `15.8.1/banco.php:777` (`right_urgente()`) — **não está em `metodo.php`**,
  é específico do app 15.8.1.
- **Condição:** `status IN ('entrada','recebido','encaminhado') AND ( (origem IN
  ('Cliente','Licitação') AND marcarestoque=0 AND valor > 75.00 AND prazo < NOW()) OR
  prioridade='alta' )`.
- **Regra de negócio:** abaixo de R$ 75, um produto de terceiro com prazo estourado não é
  tratado como urgente — o custo de tratar excede o valor do bem. Único uso do campo
  `prazo` (`entrada + 30 dias`, gravado na criação) em todo o sistema.
- **Situação:** VIGENTE em 15.8.1. **[DÚVIDA]** se existe em 14.6.1 — não verificado
  (função vive em `15.8.1/banco.php`, não na camada compartilhada; 14.6.1 não foi
  checado por uma função equivalente).

## RN-13 — Regra HGST → Hitachi (normalização de marca)

- **Origem:** `15.8.1/pp/salvar_rma.php`/`pp/novo_rma.php`.
- **Condição:** ao salvar, se `fabricante` ou `destinatario` == "HGST", substitui por
  "Hitachi".
- **Por que existe:** consolidação de marca pós-aquisição empresarial (HGST foi
  adquirida pela Western Digital/associada à Hitachi historicamente), codificada como
  find-and-replace em vez de cadastro corrigido.
- **Situação:** VIGENTE em 15.8.1. **[DÚVIDA]** se existe em 14.6.1.

## RN-14 — Normalização/cascata do campo `origem`

- **Origem:** `15.8.1/pp/salvar_rma.php:151-161`.
- **Condição (sequencial, ordem importa):** `origem == fabricante` → `"Unknown"`;
  `origem == fornecedor` → `"Unknown"`; `origem == cliente` → `"Cliente"`;
  `origem == empresa` → `"Cliente"`; `"CELLSYSTEM"`/`"Cellsystem"` → `"Loja"`;
  `"Leilao"`/`"Receita Federal"`/`"Receita"` → `"Leilão"`.
- **Regra de negócio escondida:** "Receita" (Receita Federal) vira "Leilão" — mercadoria
  apreendida vendida em leilão público. `origem == empresa` vira "Cliente" — quando uma
  empresa do grupo é a origem, ela é tratada como cliente para fins de regra.
- **Bug confirmado:** implementado com `str_replace`, não comparação — se `$fabricante`
  for string vazia, é no-op (ok), mas se um cliente real se chamar "Loja" ou um
  fornecedor for substring de outro valor de origem, ocorre corrupção silenciosa de
  dados.
- **Situação:** VIGENTE em 15.8.1. **[DÚVIDA]** se existe em 14.6.1.

## RN-15 — S/N de retorno auto-preenchido (anti-fraude de troca de peça)

- **Origem:** `15.8.1/pp/salvar_rma.php:163-167`.
- **Condição:** se `snretorno` vazio E `solucao` ∈ {`TROCA DE PECA INTERNA`, `REPARO`,
  `ORCAMENTO PAGO`, `ORCAMENTO NEGADO`, `REPARO PELO RMA`, `TESTADO TUDO OK`} → copia
  `sn` original para `snretorno` automaticamente.
- **Regra de negócio:** existem duas classes de resolução — nas de **reparo**, o
  aparelho que volta é fisicamente o mesmo (auto-preenchido); nas de **troca** (`TROCA DO
  PRODUTO`, `DEVOLUCAO DO PRODUTO`, crédito, reembolso), volta outro aparelho, campo
  fica em branco para digitação manual. Rastreia serial-in ≠ serial-out — controle de
  custódia, prevenção de "troca de gato por lebre".
- **Situação:** VIGENTE em 15.8.1. **[DÚVIDA]** se existe em 14.6.1.

## RN-16 — Consolidação de frete por cidade (Porto Alegre)

- **Origem:** `15.8.1/banco.php:803-837` (`right_portoalegre()`), com versão anterior
  comentada logo acima no próprio arquivo (refatorada in loco).
- **Condição:** `bd.status IN ('entrada','recebido')` com LEFT JOIN em `fornecedor`,
  `fabricante`, `assistencia_tecnica` por nome, filtrando `cidade='PORTO ALEGRE'` em
  qualquer uma das três.
- **Regra de negócio:** agrupar itens destinados a Porto Alegre para consolidar uma única
  viagem/frete. Única query de todo o sistema com JOINs reais; única com cidade
  hardcoded.
- **Situação:** VIGENTE em 15.8.1 (com achado: dois aliases de JOIN — `FOD`, `FAD` —
  declarados mas não usados no `WHERE`, evidência de refatoração incompleta).
  **[DÚVIDA]** se existe em 14.6.1.

## RN-17 [BUG-LEGADO, alto impacto] — `marcarestoque` computado e depois sobrescrito

- **Origem:** `15.8.1/pp/novo_rma.php:119-125`.
- **Sequência:**
  ```php
  if ($origem == "Cliente") { $marcarestoque = 0; } else { $marcarestoque = 1; }
  $marcarestoque = $_POST['marcarestoque'];   // sobrescreve tudo
  ```
- **Impacto:** a regra "produto de cliente não é estoque da casa" é computada e
  **imediatamente descartada** em favor do checkbox do formulário (que vem marcado por
  padrão). Como `marcarestoque` é o discriminador central da RN-11 (inconformidade), esse
  override silencioso significa que **produtos de cliente cadastrados sem desmarcar o
  checkbox escapam de todos os alertas de prazo legal**.
- **Situação:** QUEBRADA em 15.8.1 (bug confirmado, provavelmente o de maior impacto
  operacional de todo o levantamento). **[DÚVIDA]** se existe em 14.6.1 — a variável
  equivalente e a lógica de criação de RMA em `14.6.1/post/novo.php` não foi comparada
  linha a linha ainda.

## RN-18 [QUEBRADA] — Módulo de Créditos "pendentes/usados/disponíveis"

- **Origem:** `.htaccess` do app 15.8.1 define rotas `^creditos/pendentes`,
  `^creditos/usados`, `^creditos/disponiveis`, mas `subp/pendentes.php`,
  `subp/usados.php`, `subp/disponiveis.php` **não existem** → erro fatal garantido.
- **Situação:** QUEBRADA em 15.8.1. Só `page/credito.php` (singular, rota
  `^creditos?$`) funciona de fato, chamando `listar_creditos()`.
- **[DÚVIDA]** situação em 14.6.1 — não verificado se existe equivalente.

## RN-19 [MORTA] — "Autorizada" como entidade separada

- Ver `modelo-dominio-rma-legado.md` — arquivos existem, sem rota alcançável, alias morto
  de `assistencia_tecnica`. Situação: MORTA em 15.8.1.

## RN-20 [MORTA] — `retornou` como quinto estado do RMA

- Rota existe no `.htaccess`, `page/retornou.php` está vazio, nenhuma transição em
  nenhum app grava esse valor de `status`. Situação: MORTA/abandonada em ambos os apps
  (mecanismo de rota existe, implementação nunca foi concluída).

## RN-21 [BUG-LEGADO] — Troca de senha pelo usuário nunca funcionou

- **Origem:** `15.8.1/banco.php` (`alterar_senha()`): `"UPDATE usuario SET Key1581 = ?,
  SET Key1461 = ? WHERE ..."` — segundo `SET` é sintaxe SQL inválida.
- **Impacto:** usuários não conseguem trocar a própria senha; só reset por admin
  (`resetar_senha()`, correta) funciona.
- **Situação:** QUEBRADA em 15.8.1. **[DÚVIDA]** em 14.6.1.

---

## Notas de cobertura (o que ainda não foi comparado)

As regras RN-12 a RN-17 (threshold R$75, HGST→Hitachi, cascata de origem, snretorno,
Porto Alegre, bug do marcarestoque) foram confirmadas **apenas no app 15.8.1**. Não foi
verificado se o app 14.6.1 tem implementação própria equivalente, herdada, ausente ou
diferente para nenhuma delas — todas marcadas **[DÚVIDA]**. Isso é trabalho pendente de
uma próxima rodada de arqueologia (comparação direta `14.6.1/post/novo.php` e
`14.6.1/post/processa_detalhes.php` contra `15.8.1/pp/novo_rma.php` e
`15.8.1/pp/salvar_rma.php`), não bloqueante para o parecer inicial porque 15.8.1 é a
referência funcional confirmada (ver matriz de comparação).
