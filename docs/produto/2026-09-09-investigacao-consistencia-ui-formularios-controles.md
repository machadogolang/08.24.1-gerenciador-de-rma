# Investigação - consistência visual e de interação (formulários, controles e temas)

Data: 2026-09-09. Frente: auditoria transversal de UI (V1 e V2). Objetivo: inventário
executável, com IDs `UI-AUD-*`, antes de qualquer correção de
Blade/SCSS/JS/PHP. Este documento é o marco imutável da investigação
(regra do dono desta rodada).

## 0. Baseline executado antes de qualquer alteração

- Repositório V3 local: `~/github/08.24.1-gerenciador-de-rma`
- Repositório Legacy (somente leitura): `~/github/08.24.4-legacy-gerenciador-de-rma`
- HEAD inicial: `7397594f0f77be642cd8118b667f10864bcd16c3`
  (`#DOC-RMA - Consolida P4 P5 e P6 no plano e handoff`)
- origin/main inicial: `13e4c3a3beb444892d1b852224d627631b247f80`
- Working tree inicial: limpo; branch `main` **ahead 2** de origin/main
  (commits locais `b14a6a8` e `7397594`, já autorizados em sessões anteriores).
- Legacy runtime `:8094` estava parado; comparações de fonte usadas
  diretamente em `legacy-source/`, sem alterar o repositório Legacy.
- V3 runtime local em `:8095` (container `rma-v3-laravel.test-1`), base de QA
  com usuário `superadministrador@rma.local` / `password` (tema preferido V1).
- Nenhum arquivo não rastreado ou de outra sessão foi encontrado no working tree.

Nenhum arquivo de código (views/sass/js/app/routes/database) foi alterado até o
commit documental que fecha a Fase A.

## 1. Reconciliacão P5/P6 - código, plano, OpenSpec e handoff

Resultado: **P5/P6 continuam presentes e concluídos no HEAD; não há nada a
reimplementar.**

### Código/git

- P5 (`12b74f0`) - “Adiciona detalhe de parceiros e RMAs relacionados”:
  presente em HEAD e em `origin/main` (merge-base já contém).
- P6 (`13e4c3a`) - “Completa busca textual por contrapartes”:
  presente em HEAD; é o próprio `origin/main`.
- O HEAD local adiciona apenas `b14a6a8` (#QA-RMA, TAB após Ver) e `7397594`
  (#DOC-RMA, consolidação), sem reverter P5/P6.

### Estado documental atual

| Fonte | Estado antes desta sessão | Reconciliado |
|---|---|---|
| `PLANO-ATAQUE.md` | P5/P6 marcados concluídos; “515 testes / 1421 assertions” | OK - nenhuma mentira documental encontrada |
| `openspec/changes/paridade-fluxos-legado-v3/tasks.md` | P5/P6 `[x]` com SHA | OK |
| `docs/produto/handoff-sessao-2026-09-09.md` | P5/P6 concluídos, próximo P7 | OK - mantém-se como linha de frente de paridade; esta rodada é uma frente transversal nova (FRONT-003/UI-09) |
| `openspec/changes/front-003-shell-telas-secundarias/tasks.md` | UI-05 aberta; UI-06/07/08 abertas; sem UI-09 | Será atualizada nesta rodada (ver seção “Plano de ondas”) |

Conclusão: nenhuma pendência de P5/P6 foi reaberta; os testes dirigidos já
existentes continuam como prova. A contagem “515/1421” será reconferida na fase de
QA desta rodada (o dono apontou que a antiga baseline de 493/1318 estava
desatualizada; o plano já reflete 515/1421, mas só uma execução real pode
confirmar).

## 2. Método usado na Fase A

1. Baseline Git (`git status --short --branch`, `git log`, SHAs).
2. Leitura de plano/handoff/OpenSpec e das views/SCSS/controllers das superfícies
   auditadas.
3. Reprodução no runtime V3 com Chromium headless (Playwright), viewport 1440×1000,
   zoom 100%, DPR 1.
4. Medição por `getBoundingClientRect()`/`getComputedStyle()`: geometria, cursor,
   overflow e duplicidade de títulos.
5. Comparação de fonte com o Legacy (`legacy-source/14.6.1` e
   `legacy-source/15.8.1`) quando havia risco de diferença deliberada.
6. Registro abaixo.

Viewports adicionais (1366/1440/1600 e, no V2, 390/768) ficam para os testes da
Fase B/C; a Fase A usou 1440 como vista primária.

## 3. Inventário canônico de achados

Classificação permitida: `BUG-CONFIRMADO`, `INCONSISTENCIA`,
`PARIDADE-LEGACY`, `MELHORIA-UX`, `DOCUMENTACAO-DESATUALIZADA`,
`NAO-CORRIGIR-FIDELIDADE`, `INVESTIGAR`, `ORFAO-CANDIDATO`.

### UI-AUD-001 - RCD V1: título duplicado

- **Rota:** `/rmas-relatorios/rcd`
- **Tema/superfície:** V1 / relatório dentro do painel de sessão
- **Sintoma:** dois títulos visíveis equivalentes - “Relatório de Créditos
  Disponíveis (RCD)”.
- **Reprodução:** login com tema V1 e abrir a rota. Medido: H1
  `.titulo-v1` em `y=62` (font 14px) e H2 `.relatorio-titulo` em `y=96`
  (font 18px), ambos visíveis.
- **Evidência runtime:** headings visíveis 2; mesmo texto normalizado
  (whitespace/case); layout não estoura (`.JS-DivLEFT` 838px).
- **Blade:** `resources/views/temas/v1/layout.blade.php` (H1 em
  `.JS-SessaoLEFT`, bloco `$painelSessao`); `temas/v1/rma/relatorios/rcd.blade.php`
  (`@section('omitirTituloPadrao')`); partial compartilhado
  `resources/views/rma/relatorios/_conteudo_rcd.blade.php` (H2).
- **SCSS/JS:** `_v1-base.scss` (`.titulo-v1`), `_compartilhado.scss`
  (`.relatorio-titulo`), `v1.js` (sem papel aqui).
- **Regra CSS atual:** H1 do painel sempre visível quando `$painelSessao` é
  verdadeiro; `@section('omitirTituloPadrao')` só é respeitado no ramo
  `@unless ($painelSessao)` (`#CONTEUDO`), nunca no ramo do painel.
- **Causa raiz:** a rota `rmas.relatorios.*` entra em `$painelSessao`, e o H1 do
  painel ignora a seção `omitirTituloPadrao`; o conteúdo compartilhado adiciona o
  H2 real do relatório.
- **Legacy V1:** `14.6.1/page/relatorios.php` tem um único título H1 próprio por
  relatório, sem H1 extra do layout.
- **Legacy V2:** sem H1 automático no layout; relatório atual tem H2 único.
- **Comportamento esperado:** um único título visível por relatório, no screen e
  no print; semântica pode continuar no H1 oculto (`sr-only`).
- **Classificação:** `BUG-CONFIRMADO`
- **Prioridade/risco:** alta / baixo (mudança de classe visual pontual no layout
  V1; não afeta regra de negócio).
- **Correção proposta:** no H1 do painel (`$painelSessao`), adicionar
  `sr-only` quando `$omitirTituloPadrao` estiver presente - ou extrair o título
  para um heading por tela. **Não remover o H2** (é o título real compartilhado
  que o Tema V2 também usa).
- **Teste de regressão:** browser - contagem de headings visíveis com o mesmo
  texto deve ser 1; DOM pode manter o H1 `sr-only`.

### UI-AUD-002 - RPEC V1: título duplicado

- **Rota:** `/rmas-relatorios/rpec`
- Mesma causa raiz de UI-AUD-001 (mesmo layout V1; H1 `y=62` + H2 `y=96`,
  título “Relatório de Produtos em Estoque para Contagem (RPEC)”).
- **Blade:** `temas/v1/rma/relatorios/rpec.blade.php` +
  `resources/views/rma/relatorios/_conteudo_rpec.blade.php`.
- **Classificação:** `BUG-CONFIRMADO` (mesmo tratamento).
- **Correção proposta:** mesma de UI-AUD-001.
- **Teste:** idem.

### UI-AUD-003 - RMPE V1: título duplicado

- **Rota:** `/rmas-relatorios/rmpe?data_inicio=2026-01-01&data_fim=2026-12-31`
- Mesma causa raiz de UI-AUD-001; título “Relatório de Produtos Encaminhados
  (RMPE)”.
- **Blade:** `temas/v1/rma/relatorios/rmpe.blade.php` +
  `resources/views/rma/relatorios/_conteudo_rmpe.blade.php`.
- **Classificação:** `BUG-CONFIRMADO`.
- **Correção proposta:** mesma de UI-AUD-001.
- **Teste:** idem.

### UI-AUD-004 - Parceiros V1 (4 tipos, create/edit): geometria divergente de controles

- **Rotas:** `/parceiros/{clientes,fabricantes,fornecedores,
  assistencias-tecnicas}/{create,{id}/edit}` (tema V1) e equivalentes `/v1/...`
  para QA forçado.
- **Tema/superfície:** V1 / formulário de cadastro de parceiro
- **Sintoma:** input de texto com ~394px úteis; select UF com 186px fixo e
  centralizado; textarea Observação/Política com ~181px (largura padrão do
  browser) - controles equivalentes não parecem pertencer à mesma grade.
- **Reprodução:** abrir `/v1/parceiros/fornecedores/create`; medir caixas.
  Exemplo medido (fornecedor):
  - `input.novo_formInput`: `x=549,2`, `w=393,8`;
  - `select.formSelect[name=uf]`: `x=650,1`, `w=186` (centralizado na célula
    larga);
  - `textarea[name=observacao]` e `textarea[name=politica_de_garantia]`:
    `x=652,6`, `w=181`.
- **Evidência runtime:** `.tablenovo` = 700px; 2ª coluna ~390–440px conforme o
  tipo; `td` global centraliza o conteúdo; `.novo_formInput` `width:100%`
  preenche a célula; `select` global `width:186px` e textarea sem regra de
  largura ficam com o tamanho intrínseco.
- **Blade:** `resources/views/temas/v1/parceiros/_form.blade.php`.
- **SCSS:** `_v1-base.scss` (`select { width:186px }`,
  `.novo_formInput { width:100% }`, textarea sem geometria) e
  `_compartilhado.scss` (`td { text-align:center }`, etc.).
- **Causa raiz:** ausência de um contrato de geometria do formulário de
  parceiros; cada tipo de controle herda regras globais distintas.
- **Legacy V1:** V1 legado não tinha formulário vertical idêntico com esses
  campos; a gramática `.tablenovo`/`.novo_formInput` é do painel Novo. **Não
  mudar `select { width:186px }` globalmente** - existem telas históricas onde
  isso é deliberado.
- **Legacy V2:** formulário próprio (15.8.1) com outra composição; não copiar.
- **Comportamento esperado:** dentro do mesmo formulário, controles com o mesmo
  papel (input principal, select UF, textareas principais) compartilham a mesma
  largura útil e coluna de início; campos semanticamente pequenos podem ser
  menores se houver motivo.
- **Classificação:** `INCONSISTENCIA` (evidência objetiva) com impacto de
  `BUG-CONFIRMADO` na percepção do dono.
- **Prioridade/risco:** alta / baixo se escopado por classe semântica do
  formulário de parceiro.
- **Correção proposta:** classe semântica no `form`/`table` (ex.:
  `form-parceiro-v1`) + SCSS V1 escopado que (a) fixe largura das colunas de
  rótulo/controle ou use largura útil consistente para input/select/textarea do
  mesmo contrato; (b) evite `box-sizing` novo; (c) não altere outras telas.
- **Teste de regressão:** browser - no mesmo formulário, `outerWidth` de input
  principal, select UF e textareas principais dentro de tolerância pequena
  (border/padding), para os 4 tipos em create/edit.

### UI-AUD-005 - RMA edição V1 (`_campos`): mesma divergência de geometria

- **Rotas:** `/rmas/{id}/edit` (tema V1) e `/v1/rma/{id}/edit`
- **Sintoma:** no formulário vertical de edição, `select` fabricante/fornecedor
  com 186px centralizado e textarea Observação com ~181px, enquanto inputs têm
  ~394px.
- **Blade:** `resources/views/temas/v1/rma/edit.blade.php` +
  `temas/v1/rma/_campos.blade.php`.
- **Causa raiz:** mesma de UI-AUD-004 - o formulário é `<table class="tablenovo">`
  com as mesmas classes globais.
- **Classificação:** `INCONSISTENCIA`.
- **Correção proposta:** classe semântica compartilhada com UI-AUD-004 (mesma
  gramática `.tablenovo`), escopada ao formulário de edição/campos do RMA V1;
  ou reuso da classe do contrato de formulário V1.
- **Teste:** idem.

### UI-AUD-006 - Usuários V1: ações comprimidas e botões com largura inconsistente

- **Rota:** `/usuarios` (tema V1)
- **Sintoma:** célula de ações no limite; `.formButtonEnviarPanel` “SALVAR” com
  75px e “RESETAR” encolhido para ~51px pelo flex; campos de senha com ~70px;
  layout funcional mas espremido e sem grade limpa.
- **Reprodução:** login tema V1, rota `/usuarios`. Medido: linha com 31px de
  altura; colunas 20/28/20/32 da `.tabela-usuarios-v1`; conteúdo da célula de
  ações termina ~5px antes da borda da tabela.
- **Evidência:** `.form-usuario-v1 { display:flex; align-items:center;
  justify-content:center; gap:4px }`; `.formButtonEnviarPanel { width:75px }`
  encolhe no segundo form por falta de espaço (flex shrink padrão).
- **Blade:** `resources/views/temas/v1/identidade/usuarios.blade.php`
- **SCSS:** `_v1-base.scss` (`.formSelectPanel`, `.formInputPanel`,
  `.formButtonEnviarPanel`, `.form-usuario-v1`, `.tabela-usuarios-v1`)
- **Legacy V1:** `14.6.1/menujs-right/usuarios.php` era somente listagem
  (sem formulários inline); a gestão inline é decisão V3 (LEG-RMA-003/005). Não
  há referência histórica para espremer.
- **Classificação:** `INCONSISTENCIA`
- **Correção proposta:** redimensionar colunas da tabela (permissão/ações) e
  regras de flex escopadas que impeçam encolhimento de ações; manter densidade
  histórica (30px) e fontes legíveis; não alterar Policy/rotas.
- **Teste:** browser - controles da mesma linha não ultrapassam a célula;
  botões de mesma classe com mesma largura; TAB navegável.

### UI-AUD-007 - Usuários V2: ações empilhadas e linhas excessivamente altas

- **Rota:** `/v2/usuarios`
- **Sintoma:** célula Papel empilha select + “Salvar papel”; célula Ações
  empilha “Nova senha”, “Confirmar” e “Resetar senha” em três linhas; linha tem
  85px de altura.
- **Reprodução:** rota `/v2/usuarios`, viewport 1440. Medido:
  - Papel: `form-inline` com 211px; select `x=569,8 w=170` e botão
    `y=115,7` (segunda linha);
  - Ações: senha `y=81,7`, confirmação `y=115,7`, botão `y=117,7`;
  - `<tr>` com 85px.
- **Causa raiz:** conteúdo solicitado (select 170 + botão 88; senha 203 +
  confirmação 203 + botão 88 + espaços) excede a largura das células de tabela
  (~228px e ~346px); `.form-inline` apenas quebra inline-block quando não cabe.
- **Blade:** `resources/views/temas/v2/identidade/usuarios.blade.php`
- **SCSS:** `v2.scss`/Bootstrap 3 (`.form-inline`, `.form-control`,
  `.formSubmit`)
- **Legacy V2:** `15.8.1/subp/usuarios.php` usa links/ícones para
  resetar/mudar permissão/apagar em subpáginas, com linha de 30px - não há
  formulários inline no histórico; porém a arquitetura V3 (post+CSRF+validação)
  é decisão já tomada. A meta aqui é apresentação compacta sem mudar negócio.
- **Classificação:** `INCONSISTENCIA`
- **Correção proposta:** larguras de coluna maiores para Papel/Ações e
  contenção escopada por célula (ex.: larguras fixas/mínimas para os inputs de
  senha e `white-space:nowrap` quando couber), mantendo 1 linha por ação quando
  houver espaço; sem flex global em todos os forms.
- **Teste:** browser - controles da mesma ação na mesma linha (ou em no máximo
  2 quando a tabela for estreita), altura de linha <= ~50px em 1440/1600.

### UI-AUD-008 - Todos os selects habilitados exibem `cursor: default`

- **Rotas:** amostra em `/rmas-relatorios/rpec`, `/v1/parceiros/*/create`,
  `/v2/parceiros/*/create`, `/usuarios`, `/v2/usuarios`, `/v1/rma/create`,
  `/v2/rma/create`, detalhe RMA V1/V2.
- **Sintoma:** todo `select` habilitado medido tem
  `getComputedStyle().cursor === 'default'`, inclusive `.formSelect`,
  `.formSelectPanel`, `.form-control.formSelect` e selects sem classe.
- **Causa raiz:** nenhum SCSS dos temas define cursor para `select`; só
  `button:not(:disabled)` tem contrato universal.
- **Classificação:** `BUG-CONFIRMADO` (contrato explícito do dono desta rodada)
- **Correção proposta:** uma regra base por tema -
  `select:not(:disabled) { cursor:pointer }` e `select:disabled {
  cursor:not-allowed }` (manter `default` se contrato atual disser o contrário) -
  sem criar dezenas de regras duplicadas.
- **Teste:** em cada rota coberta, todo select habilitado visível tem cursor
  `pointer`; disabled permanece não-pointer.

### UI-AUD-009 - Dropdown “Menu” V2: âncoras com 39px sobrepostas (herança do nav)

- **Rota:** páginas V2 com header (medido em `/v2/rma/create`)
- **Sintoma:** itens `<li class="lidropdown">` com 25px, mas `<a>` internos com
  39px de altura (regra de `.nav-v2 li a`), gerando sobreposição de área
  clicável de ~14px entre itens consecutivos e altura total do dropdown de
  312px para 12 itens.
- **Reprodução:** clicar no “Menu” do header e medir:
  `li { h:25 }`, `a { h:39 }`, passo vertical de 25px.
- **Evidência runtime:** dropdown visível, `z-index:1000`, sem clipping e dentro
  do viewport (x 1049–1213 em 1440); problema é a área clicável interna.
- **Causa raiz:** em `_v2-base.scss`, `.nav-v2 li a { height:39px;
  line-height:39px }` usa descendente e alcança os links dentro do
  `.dropdown-menu` do próprio `li.dropdown` - deveria ser limitado aos filhos
  diretos da navegação (`> li > a` ou similar).
- **Legacy V2:** fonte `pattern/15.8.1.css`/`inc/menu.php` usa itens de dropdown
  com `height:25px`; a regra de altura 39px pertence aos itens da barra superior,
  não aos itens do dropdown.
- **Classificação:** `BUG-CONFIRMADO`
- **Correção proposta:** escopar a regra de altura/line-height da nav para os
  links diretos da barra (e revisar `.logoutx`); manter aparência `.lidropdown`
  com 25px.
- **Teste:** dropdown aberto: item clicável não invade a área do item seguinte;
  TAB percorre os itens; item ativo visível; sem clipping.

### UI-AUD-010 - Hífen longo “-” em textos operacionais renderizados

- **Escopo:** partes operacionais da interface (labels, combos/opções,
  listagens, pendências curtas), nos dois temas.
- **Inventário quantitativo:** 201 ocorrências de “-” em
  `resources/views` (total bruto, incluindo comentários); 371 nas fontes
  candidatas somando `app/` e `resources/js`. Após classificação manual, o
  subconjunto **operacional renderizado** é o listado abaixo (~30 ocorrências
  efetivas em views consumidas; as demais são comentários/prosa/documentação
  ou views órfãs - ver UI-AUD-016).

#### Operacionais confirmados para trocar por “-”

| Arquivo | Ocorrência | Consumido? |
|---|---|---|
| `temas/v1/parceiros/_form.blade.php:76` | `<option value="">-</option>` | Sim |
| `temas/v2/parceiros/_form.blade.php:76` | `<option value="">-</option>` | Sim |
| `temas/v1/rma/_campos.blade.php` (2×) | options “-” | Sim (edição RMA V1) |
| `temas/v2/rma/_campos.blade.php` (2×) | options “-” | Sim (edição RMA V2) |
| `temas/v1/rma/_form_novo.blade.php:60` | option “-” (fabricante) | Sim (painel Novo V1) |
| `rma/_centro_de_avisos.blade.php:99` | `#{{ id }} - {{ descricao }}` | Sim (home V1/V2) |
| `rma/credito/_conteudo.blade.php` | `#{{ id }} - {{ descricao }}` | Sim |
| `rma/alertas/_conteudo_painel.blade.php` (2×) | `#{{ id }} - {{ descricao }}` | Sim |
| `temas/v1/rma/controle.blade.php` (labels 3×) | `FORNECEDOR - NOME:`, `FABRICANTE - NOME:`, `ASSISTÊNCIA - NOME:` | Sim |
| `temas/v1/rma/controle.blade.php` (2×) | “Pendente - exclusão definitiva...” | Sim (texto curto de status) |
| `temas/v1/identidade/perfil.blade.php` e `temas/v2/identidade/perfil.blade.php` (2× cada) | `nome - email - papel: ...` | Sim (linha de contexto) |
| `temas/v1/rma/show.blade.php` (2×) | “NF de compra/venda - emissão” | Sim |
| `identidade/historico-de-acesso/_conteudo.blade.php` (2×) | fallback `'-'` em células | Sim (quando dados ausentes) |
| `emails/rma-concluido.blade.php` (3×) | fallback `'-'` no corpo do e-mail | Sim (mensagem operacional) |

#### Prosa/documentação exibida - manter por ora

- Prosa da “Central de Ajuda” do Controle V1 (fidelidade ao texto legado).
- Textos de documentação/títulos de página fora da interface operacional curta.

#### Views órfãs com “-” - não tocar (remoção é UI-07/FRONT-006)

- `parceiros/_form.blade.php` (option “-”)
- `rma/relatorios/*.blade.php`, `rma/_painel_de_alertas.blade.php`,
  `identidade/historico-de-acesso/index.blade.php`,
  `identidade/usuarios/index.blade.php`, `identidade/perfil/senha.blade.php`,
  `rma/credito/index.blade.php`, etc. - ver UI-AUD-016.

- **Classificação:** `INCONSISTENCIA` (pontuação), com fronteira explícita para
  não alterar prosa/comentários/histórico.
- **Correção proposta:** trocar somente os itens da tabela “operacionais
  confirmados” acima, sem `sed` global.
- **Teste:** browser/texto - nenhum texto curto operacional dos itens listados
  contém “-”.

### UI-AUD-011 - Controle V1 (UI-05 reaberta): bloco “ADICIONAR REPRESENTANTE” desalinhado

- **Rota:** `/rmas-controle`
- **Sintoma:** labels de tamanhos diferentes (“FORNECEDOR - NOME:”,
  “FABRICANTE - NOME:”, “ASSISTÊNCIA - NOME:”) fazem os inputs começarem em x
  diferentes (372/365/366) e botões em x diferentes (527/520/521), sem grade.
- **Reprodução:** expandir todos os `<details>` e medir cada linha.
- **Evidência runtime:** ver valores acima; painel `.JS-SessaoLEFT` 838px sem
  overflow; texto de ajuda “Pendente...” respeita 818px e quebra.
- **Blade:** `resources/views/temas/v1/rma/controle.blade.php`
- **SCSS:** `_v1-base.scss` (`.formLabelPanel` sem largura fixa; `.fl` sem
  grade; `.formInputPanel`/`.formButtonEnviarPanel`).
- **Causa raiz:** labels em `<p class="fl formLabelPanel">` com largura
  automática; o V3 mantém 3 forms separados (decisão arquitetural documentada)
  e cada label tem comprimento próprio.
- **Legacy V1:** `14.6.1/menujs-right/controle.php` tinha 1 form com label
  “NOME:” + select de tipo; a geometria não era grade de rótulo fixo. A
  separação por tipo é decisão V3 já registrada.
- **Classificação:** `BUG-CONFIRMADO` (percepção do dono; task FRONT-003/UI-05
  já aberta para isto).
- **Correção proposta:** dentro do bloco, dar largura fixa ao label
  (ou usar grade em linha única com colunas semânticas) e manter inputs/botões
  alinhados na mesma posição nos três formulários; preservar `<details>`/resumo
  e ações; sem scroll horizontal global.
- **Teste:** linhas dos três formulários com x inicial de input e botão iguais
  (ou diferença <=1px); overflow local apenas se necessário na tabela de
  arquivados.

### UI-AUD-012 - V2: formulários `.form-horizontal` ultrapassam `.container` em 15px

- **Rotas:** `/v2/parceiros/*/create`, `/v2/rma/create`, e edits equivalentes.
- **Sintoma:** `.shell-v2 > .container` tem `clientWidth 990` e `scrollWidth
  1005`.
- **Causa:** Bootstrap 3 `.form-group` (row) usa margens -15px; no V3 o
  `.container` não tem padding de 15px nem wrapper `row`/painel compensador
  (o Legacy V2 usava wrapper próprio). Não há scroll horizontal da página
  (`bodyScrollWidth 1440`), nem vazamento visível sobre a sidebar; rótulos
  começam 15px antes da borda do container.
- **Classificação:** `INVESTIGAR`/baixa prioridade - impacto visual não
  observado nas vistas testadas; não bloquear as ondas C1–C7.
- **Correção opcional:** envolver o formulário em um wrapper com a mesma
  compensação de padding do legacy V2 (sem redesenhar).
- **Teste (se corrigir):** `scrollWidth == clientWidth` no container das rotas
  V2 cobertas.

### UI-AUD-013 - Ações de ciclo de vida (detalhe RMA): selects/inputs sem classe nos dois temas

- **Rotas:** `/v1/rma/{id}` e `/v2/rma/{id}` (detalhe; ações de transição).
- **Sintoma:** no partial compartilhado `rma/_acoes_de_transicao.blade.php`, os
  selects “destinatario_tipo”, “solucao” e o input “destinatario_id” não têm
  classe de tema. No V1 os selects herdam `select {width:186px}` do base; no V2
  ficam com aparência nativa (~188px) divergente dos `.form-control` dos demais
  formulários V2.
- **Medição:** detalhe V1/V2 (RMA em Entrada): label com 984/990px de largura;
  select “solucao” `w=186/188`, `cursor=default`.
- **Blade:** `resources/views/rma/_acoes_de_transicao.blade.php`.
- **Causa raiz:** partial compartilhado sem classes semânticas dos temas.
- **Classificação:** `INCONSISTENCIA`.
- **Correção proposta:** adicionar classes/escopo do tema no partial (ex.:
  `formSelect` + classe de geometria do contrato do tema), sem alterar
  rotas/validação; no V1 respeitar gramática `.formInputPanel`/seletor histórico;
  no V2 usar `.form-control` em largura contida (col-sm-6) dentro da ação.
- **Teste:** browser nos detalhes - selects habilitados com cursor pointer,
  geometria consistente com o contrato do tema e sem estourar o container.

### UI-AUD-014 - Listagens/abas V2: auditadas, sem bug confirmado

- **Rotas:** `/v2/rma` (abas #entrada, #recebido, #encaminhado, #concluido).
- **Resultado:** colunas históricas presentes (`DATA/ORIGEM/T/NF...`), tabela
  990px, `clientWidth == scrollWidth`, sem overflow da página nas abas testadas
  (1440×1000). Cabeçalhos, altura de linha e links Ver/ícones preservados.
- **Classificação:** `NAO-CORRIGIR-FIDELIDADE` (ok nesta rodada).
- **Observação:** conteúdo QA denso (“Ficticio QA ...”) faz linhas mais altas do
  que os dados curtos do legado; não usar `white-space:nowrap` global para
  comprimir (regra do dono).

### UI-AUD-015 - Listagens V1 por status: auditadas, sem bug confirmado

- **Rotas:** `/rmas-entrada`, `/rmas-encaminhados`, `/rmas-aguardando-credito`,
  `/rmas-concluidos`.
- **Resultado:** tabelas com 984px, sem overflow; colunas/classes históricas
  (`SuperTr`, zebra, links) preservadas.
- **Classificação:** `NAO-CORRIGIR-FIDELIDADE` (ok nesta rodada).

### UI-AUD-016 - Views genéricas/fallback sem consumidor (candidatas UI-07)

- **Critério:** view não referenciada por `view()`/`view_do_tema()` real nem
  incluída por partial consumido.
- **Candidatas com indício forte:** `resources/views/parceiros/_form.blade.php`;
  `resources/views/rma/relatorios/{rcd,rpec,rmpe}.blade.php`;
  `resources/views/rma/_painel_de_alertas.blade.php`;
  `resources/views/rma/{index,show,create,edit,credito/index}.blade.php` (quando
  há versão por tema);
  `resources/views/identidade/{perfil,perfil/senha,usuarios/index,
  historico-de-acesso/index}.blade.php` (versões por tema existem);
  `resources/views/rma/relatorios/_conteudo_*` **não** são órfãs (partials
  compartilhados são consumidos pelos temas).
- **Ação nesta rodada:** nenhuma (não estilizar); registrar como candidatas para
  remoção na onda própria de UI-07/FRONT-006, com prova de zero consumidor e
  teste.
- **Classificação:** `ORFAO-CANDIDATO`

### UI-AUD-017 - Relatórios V2: título único por construção

- **Rotas:** `/rmas-relatorios/{rcd,rpec,rmpe}` com usuário V2 (ou via troca de
  tema).
- **Resultado esperado/verificado por código:** layout V2 não injeta H1 global;
  o conteúdo compartilhado tem H2 único. Sem duplicidade.
- **Classificação:** `NAO-CORRIGIR-FIDELIDADE`/`PARIDADE-LEGACY`.

### UI-AUD-018 - Painel “Novo”/Create RMA V1: composição histórica preservada

- **Rota:** `/v1/rma/create` e painel `#JS-Novo` inline.
- **Resultado:** estrutura de 5 colunas `.tablenovo` (700px) com inputs alinhados
  na mesma linha de grade; sem overflow global; título H1 oculto por design
  (`sr-only`); o select fabricante usa `.novo_formInput` (largura compatível com
  a coluna), mas permanece com `cursor: default` (coberto por UI-AUD-008).
- **Classificação:** `NAO-CORRIGIR-FIDELIDADE`; cursor tratado na onda C1.

## 4. Composição vertical dos relatórios V1 (medição para a onda C5)

Antes de corrigir “a olho”, valores medidos em `/rmas-relatorios/rcd`
(viewport 1440):

| Elemento | Box |
|---|---|
| `#FIXADO/#TOPO` (header) | altura fixa; conteúdo começa em ~65px |
| H1 `.titulo-v1` (painel) | `x=244, y=62, w=813, h=24` (line-height 14; box cresce por font) |
| H2 `.relatorio-titulo` | `x=239, y=96, w=818, h=21` |
| Tabela `.relatorio-tabela` | `x=239, y≈150, w=818` |

Após a remoção visual do H1 duplicado, o H2 continuará no topo do conteúdo e a
tabela não muda de lugar; a eventual margem extra do painel deve ser medida de
novo, não compensada por palpite.

## 5. Plano de correção por ondas (após o commit documental)

Ondas temáticas e pequenas, com testes antes/junto e commit por onda. Status no
padrão canônico `[ ]`/`[R]`/`[x]`.

- [x] C1 - Cursor de selects + pontuação operacional
  - UI-AUD-008 e UI-AUD-010;
  - implementado e commitado em `22c48a7`;
  - contrato A do browser verde.
- [x] C2 - Geometria dos formulários V1
  - UI-AUD-004/005 (parceiros + edição RMA), classe `.tabela-form-v1`;
  - implementado e commitado em `22c48a7`;
  - contrato B do browser verde (4 tipos de parceiro).
- [x] C3 - Gestão de usuários V1/V2
  - UI-AUD-006/007;
  - implementado e commitado em `22c48a7`;
  - contratos D/D2 do browser verdes.
- [x] C4 - Controle V1 (FRONT-003/UI-05)
  - UI-AUD-011: causa raiz confirmada em `16c1913`;
  - implementado e commitado em `22c48a7`;
  - contrato E do browser verde;
  - [ ] regressão viewport/UI-08 ainda em aberto.
- [x] C5 - RCD/RPEC/RMPE (título único V1)
  - UI-AUD-001/002/003/017: causa raiz confirmada em `16c1913`;
  - correção commitada em `61222c8` + `1938247`;
  - contrato C do browser verde.
- [x] C6 - Dropdown V2 e ações de ciclo de vida
  - UI-AUD-009/013 (+ UI-AUD-012 observado sem correção);
  - implementado e commitado em `22c48a7`;
  - contrato F do browser verde.
- [ ] C7 - Varredura residual
  - executar nas mesmas classes de inconsistência em mais superfícies e
    viewports; UI-AUD-016 fica para a onda própria de UI-07, com prova de zero
    consumidor.

A task transversal nova entra em FRONT-003 como **UI-09**; UI-05 permanece
UI-05.

## 6. Evidência complementar

Medições completas em arquivos temporários fora do repositório
(`/tmp/audit-metrics.json`, `/tmp/audit-metrics-foco.json`,
`/tmp/audit-tabelas.json`, `/tmp/audit-acoes-transicao.json`) - não versionados,
apenas ferramenta da Fase A. Nenhum screenshot novo foi necessário para fechar
os achados; screenshots podem ser usados na Fase B/C como evidência adicional.


---

## 7. Status pós-ondas C1–C6 (mesma sessão)

- UI-AUD-001/002/003 (título duplicado V1): corrigidos em `61222c8`/`1938247`;
  contrato no browser 9/9 inclui um único título visível.
- UI-AUD-004/005 (formulários V1): corrigidos em `22c48a7` via `.tabela-form-v1`;
  contrato B do browser valida os 4 tipos de parceiro.
- UI-AUD-006/007 (usuários V1/V2): corrigidos em `22c48a7`; contratos D/D2 verdes.
- UI-AUD-008 (cursor): corrigido nos dois temas; contrato A verde.
- UI-AUD-009 (dropdown V2): corrigido em `22c48a7`; contrato F verde.
- UI-AUD-010 (hífen operacional): substituições executadas apenas nos arquivos
  listados; contrato verificado por busca dirigida.
- UI-AUD-011 (Controle/UI-05): corrigido em `22c48a7`; contrato E verde; aceite
  viewport final fica na UI-08.
- UI-AUD-012: observado sem impacto visual, sem correção nesta rodada.
- UI-AUD-013 (ciclo de vida): corrigido em `22c48a7`.
- UI-AUD-014/015/017/018: sem alteração (fidelidade/OK).
- UI-AUD-016: candidatas registradas para UI-07, sem alteração.
- PHPUnit completo real: 515 testes / 1421 assertions verdes; Vite build verde.
- PUSH NÃO REALIZADO.

Pendências reais para a próxima continuação: C7 (varredura residual em mais
superfícies), UI-08 (regressão viewport/print) e fechamento formal de handoff.
