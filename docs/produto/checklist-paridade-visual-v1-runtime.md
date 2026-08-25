# Checklist de paridade visual/interação — TEMA V1 runtime (reabertura 2026-08-25)

Documento auxiliar indexado pela seção F10 visual do `checklist-master-v3.md`, criado
por `docs/investigacoes-pendente/INV-RMA-BUG-LAYOUT-problemas-no-layout.md`. Fonte de
verdade: runtime real `http://localhost:8094/14.6.1/` (Legacy) × `http://localhost:8095`
(V3), nunca suposição por nome de classe. Só marca `[x]` quando existir runtime Legacy +
runtime V3 + comparação depois da correção + teste.

## VIS-V1-001 — menu superior incompleto

- ID: VIS-V1-001
- Tela: cabeçalho, todas as páginas autenticadas do TEMA V1
- Legacy: `Pag. Inicial / Novo / Localizar / Entrada / Encaminhado / Aguardando credito / Concluido! / MENU / SIGN OUT` (`legacy-source/14.6.1/index.php:162-168`)
- V3: `Pag. Inicial / Novo / Localizar / MENU / SIGN OUT`
- Categoria: [x] estrutura [x] controle ausente [x] funcionalidade
- Evidência: `docs/investigacoes-pendente/INV-RMA-BUG-LAYOUT/(Legado) Tela Inicial do Tema 14.6.1.png`, `(V3) Tela Inicial do Tema 14.6.1.png`
- Problema: faltam 4 atalhos de navegação superior, cada um abrindo uma listagem
  filtrada por status com colunas e regras de cor próprias (não é só um link — ver
  achado funcional abaixo).
- Fonte Legacy: `legacy-source/14.6.1/page/{entrada,encaminhados,aguardandocredito,concluidos}.php`
  — cada um consulta `bd` filtrando por `status`, com 8-10 colunas próprias e regras de
  destaque de linha (`TrInconformidade`/`TrUrgente`/`TrZebrada1`/`TrZebrada2`) baseadas em
  solução, prioridade, origem e prazo de 30 dias.
- Fonte V3: `app/Http/Controllers/Rma/RmaController.php` (só tem `index`/busca genérica,
  sem rota por status); `routes/web.php:59-102` (recurso `rmas`, sem rota equivalente a
  `?page=entrada` etc.)
- Arquivos candidatos: nova rota `GET /rmas-entrada|encaminhados|aguardando-credito|concluidos`
  (paralelo a `rmas-alertas`), controller próprio ou método adicional, view
  `resources/views/temas/v1/rma/{entrada,encaminhados,...}.blade.php`
- Critério de aceite: os 4 itens aparecem no header do TEMA V1, na mesma posição/ordem
  do legado, cada um abre uma listagem real com as colunas e regras de destaque
  equivalentes (não precisa copiar CSS pixel a pixel de cor, mas a lógica de
  destaque — garantia/urgência/prazo — precisa ser a mesma regra de negócio já provada
  por `RecebidosSemEncaminhar30DiasTest`/`NaoVaiDarGarantiaTest`/etc.)
- Screenshot antes: `(V3) Tela Inicial do Tema 14.6.1.png` (nesta pasta)
- Screenshot depois: pendente
- Teste: pendente (Playwright + Feature)
- Status: **NÃO CORRIGIDO NESTA SESSÃO** — escopo maior que um link de menu (4 páginas de
  listagem com regra de destaque própria); registrado para a próxima sessão. Risco de
  fazer rápido e mal: um link para `/rmas?tipo=...` sem a listagem real seria "link
  morto" (proibido pela própria investigação) ou uma tela sem paridade (mesmo problema
  que motivou a reabertura).

## VIS-V1-002 — "Novo" perdeu a interação inline

- ID: VIS-V1-002
- Tela: header (`Novo`) + qualquer superfície onde é aberto
- Legacy: `NovoMaximize()` expande `#JS-Novo` sobre a superfície atual sem navegar
  (`legacy-source/14.6.1/index.php:163`, `menujs-top/*` monta o formulário)
- V3: `Novo` é `<a href="/rmas/create">`, navega e troca a tela inteira
- Categoria: [x] comportamento [x] estrutura
- Evidência: `(Legado) Funcionalidade de adicionar Novo RMA.png`, `(V3) Funcionalidade de adicionar Novo RMA.png`
- Problema: perde o contexto da tela onde o operador estava (ex.: lista de "Aguardando
  crédito" continua visível abaixo do formulário expandido no legado).
- Fonte Legacy: `legacy-source/14.6.1/menujs-top/*`, função JS `NovoMaximize()`
- Fonte V3: `resources/views/temas/v1/rma/index.blade.php` (ou view equivalente),
  rota `rmas.create`
- Arquivos candidatos: extrair o formulário de criação para um partial incluído (não
  navegado) em toda página do TEMA V1, com JS de expandir/recolher equivalente
- Critério de aceite: abrir "Novo" em qualquer tela do TEMA V1 mantém o conteúdo da
  tela visível abaixo do formulário expandido; submissão continua validando/persistindo
  pelo caso de uso Laravel real (`CriarRmaTest.php`), sem PHP procedural
- Status: **NÃO CORRIGIDO NESTA SESSÃO** — reestruturação de layout compartilhado,
  requer decidir o mecanismo (Livewire/Alpine/fetch parcial) antes de implementar;
  registrado para a próxima sessão.

## VIS-V1-003 — formulário "Novo RMA" estruturalmente diferente

- ID: VIS-V1-003
- Tela: formulário de criação de RMA
- Legacy: composição horizontal compacta de 5 colunas com campos
  `DESCRICAO/SNID/S-N/EMPRESA/CLIENTE` × `ORIGEM/FABRICANTE/MODELO/DEFEITO/OBS` ×
  `NF C+DATA/NF V+DATA/P-N/OS`, mais checkbox de item em estoque
  (`legacy-source/14.6.1/menujs-top/*`)
- V3: lista vertical de 11 campos (Descrição/Fabricante/Fornecedor/Modelo/SN/OS/Origem/
  Empresa/Cliente/Defeito/Observação), sem `SNID`, `NF de compra/venda` + data, `P/N`,
  marcação de estoque
- Categoria: [x] estrutura [x] geometria [x] controle ausente [x] funcionalidade
- Evidência: `(Legado) Funcionalidade de adicionar Novo RMA.png`, `(V3) Funcionalidade de adicionar Novo RMA.png`
- Problema: além da geometria, há campos do domínio potencialmente ausentes do
  formulário atual — precisa verificar coluna a coluna se já existem em `rmas` (schema)
  antes de decidir se é lacuna de apresentação ou de funcionalidade.
- Achado rápido desta sessão (leitura do schema, `DB::table('rmas')->first()`):
  `snid`, `nfcompra`+`nfcompra_emissao`, `nfvenda`+`nfvenda_emissao`, `pn` e
  `marcarestoque` **já existem como colunas em `rmas`** — não é lacuna de domínio, é
  campo existente não exposto no formulário do TEMA V1. Precisa conferir
  `app/Http/Requests`/caso de uso de criação para saber se já aceitam esses campos ou
  se também faltam na camada de aplicação.
- Fonte V3: `resources/views/temas/v1/rma/_campos.blade.php` (formulário atual),
  request/caso de uso de `CriarRmaTest.php`
- Critério de aceite: formulário reproduz a composição de 5 colunas do legado, todos os
  campos citados presentes e funcionais (persistem, aparecem no detalhe), sem simular
  campo que não persiste de verdade
- Status: **NÃO CORRIGIDO NESTA SESSÃO** — precisa primeiro confirmar até onde a camada
  de aplicação (request/caso de uso) já aceita `snid`/`nfcompra`/`nfvenda`/`pn`/
  `marcarestoque` antes de desenhar o formulário; registrado para a próxima sessão.

## VIS-V1-004 — CSS do "Novo RMA" não corresponde ao original

- ID: VIS-V1-004
- Tela: formulário de criação de RMA (mesma superfície de VIS-V1-003)
- Legacy: `.tablenovo` fixa em `700px`, `.novo_formInput` `22px` de altura com cor
  `#C3FF00`, mais `.novo_formInputDATE/.novo_formInputSmall/.formSelectempresa/
  .formButtonEnviarNovo/.novo_defeito/.formInputObservacao`
- V3: `.tablenovo` generalizada para `width:100%`, sem os seletores específicos
- Categoria: [x] geometria [x] tipografia [x] cor
- Status: **NÃO CORRIGIDO NESTA SESSÃO** — depende de VIS-V1-003 (a estrutura de campos
  precisa existir antes do CSS fazer sentido); registrado para a próxima sessão.

## VIS-V1-005 — Quadro de Anotações com botão "Salvar anotação" que não existia

- ID: VIS-V1-005
- Tela: Home (`Pag. Inicial`) e listagem `/rmas`
- Legacy: textarea ~675px, salvamento pelo comportamento do campo (sem botão
  permanente); sidebar ~280px
- V3: textarea com botão `[ Salvar anotação ]` sempre visível
- Categoria: [x] comportamento [x] estrutura
- Status: **investigado nesta sessão, não corrigido** — ver nota no rodapé deste
  documento (achado de implementação atual).

## VIS-V1-006 — Home/Localizar com composição diferente

- ID: VIS-V1-006
- Tela: `/rmas` (Home/Localizar)
- Legacy: campo de pesquisa + `QUALQUER UMA SOLUCAO` + `TODOS OS CAMPOS` + `FILTRAR`,
  depois quadro de anotações + sidebar de contadores
- V3: `Buscar por [Texto] [campo] [Buscar]` → "Nenhum RMA encontrado." antes do quadro
  de anotações
- Categoria: [x] estrutura [x] controle ausente
- Achado confirmado nesta sessão (runtime real, ver `docs/qa/roteiro-paridade-funcional.md`
  M-03): a busca da V3 tem 3 tipos reais (`texto`/`serial`/`nota_fiscal`, todos
  funcionando), mas falta o segundo seletor "solução" (`QUALQUER UMA SOLUCAO` no
  legado) e o combo "TODOS OS CAMPOS" — no legado a busca é campo+solução combinados,
  na V3 é só um tipo por vez.
- Status: **NÃO CORRIGIDO NESTA SESSÃO** — registrado para a próxima sessão.

## VIS-V1-007 — títulos `<h1>` que não existiam na composição original

- ID: VIS-V1-007
- Tela: várias (ex.: "Novo RMA" acima do formulário)
- Legacy: sem heading `<h1>` padronizado nas superfícies auditadas
- V3: `<h1 class="titulo-v1">` injetado
- Categoria: [x] estrutura [x] tipografia
- Status: **NÃO AUDITADO TELA A TELA NESTA SESSÃO** — precisa passar por cada view do
  TEMA V1 conferindo se o legado tinha heading equivalente; registrado para a próxima
  sessão.

## VIS-V1-008 — MENU administrativo incompleto

- ID: VIS-V1-008
- Tela: dropdown `MENU`, todas as páginas autenticadas do TEMA V1
- Legacy: `Fornecedores / Fabricantes / Assistencias / Clientes / Controle / Creditos /
  Relatorios / Usuarios / Trocar p/ 15.8.1`
- V3 (antes desta sessão): `Fornecedores / Fabricantes / Assistências / Clientes /
  Usuários`
- Categoria: [x] controle ausente
- Achado confirmado nesta sessão: as rotas de `Controle`(histórico), `Créditos` e
  `Relatórios` **já existem** no V3 (`rmas.historico.index`, `rmas.credito.index`,
  `rmas.relatorios.{rcd,rpec,rmpe}`, `routes/web.php:80-96`) — não é lacuna funcional,
  só faltavam os links no MENU do TEMA V1.
- Classificação:
  - `Controle` → **MANTER**, aponta para `rmas.historico.index`
  - `Créditos` → **MANTER**, aponta para `rmas.credito.index`
  - `Relatórios` → **MANTER**, aponta para `rmas.relatorios.rcd` (RCD como entrada;
    RPEC/RMPE ficam dentro da própria tela de relatórios, como já é a composição V3)
  - `Trocar p/ 15.8.1` → **NÃO RECONSTRUIR** — decisão explícita: a arquitetura V3 não
    tem um "outro tema" 15.8.1 irmão navegável por link cru como o legado; a troca de
    tema já existe por outro mecanismo (`/perfil`, "Alternar tema"), documentado em
    `docs/qa/roteiro-paridade-funcional.md` M-01.
- Status: **CORRIGIDO NESTA SESSÃO** — ver commit.

---

## Nota sobre VIS-V1-005 (Quadro de Anotações)

Investigado nesta sessão: `app/Http/Controllers/QuadroDeAnotacoesController.php` (ou
equivalente) e a view do TEMA V1 mostram um botão de submit explícito. Restaurar o
salvamento "pelo comportamento do campo" do legado (sem botão, provavelmente
`onblur`/debounce) é uma mudança de mecanismo de persistência, não só de CSS — a própria
investigação pede autosave seguro (debounce, feedback discreto, proteção contra chamada
concorrente, tratamento de erro) em vez de reproduzir o JS antigo inseguro. Isso é
trabalho de funcionalidade nova (mesmo que pequeno), não de reclassificação de link, e
por isso fica registrado para a próxima sessão junto de VIS-V1-001/002/003/004/006/007.

## Resumo de status desta rodada

| ID | Status |
|---|---|
| VIS-V1-001 | Registrado, não corrigido — escopo de 4 páginas de listagem |
| VIS-V1-002 | Registrado, não corrigido — reestruturação de layout compartilhado |
| VIS-V1-003 | Registrado, não corrigido — achado: campos já existem no schema |
| VIS-V1-004 | Registrado, não corrigido — depende de VIS-V1-003 |
| VIS-V1-005 | Registrado, não corrigido — mudança de mecanismo de persistência |
| VIS-V1-006 | Registrado, não corrigido — falta segundo seletor "solução" |
| VIS-V1-007 | Não auditado tela a tela |
| VIS-V1-008 | **Corrigido e testado nesta sessão** |
