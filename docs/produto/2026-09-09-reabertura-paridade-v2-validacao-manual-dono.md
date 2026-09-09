# Reabertura - paridade V2 / regressoes validadas manualmente pelo dono

Data: 2026-09-09. Natureza: addendum de investigacao e checkpoint obrigatorio ANTES
de qualquer mudanca em Blade/SCSS/JS/PHP. Fonte viva: `PLANO-ATAQUE.md`.

## 1. Baseline real (reconferido nesta sessao, nao confiado em checkboxes antigos)

- Repositorio atual: `~/github/08.24.1-gerenciador-de-rma`.
- Legacy de referencia (SOMENTE LEITURA): `~/github/08.24.4-legacy-gerenciador-de-rma`.
- `git status --short --branch`: `## main...origin/main`, working tree limpo.
- `git rev-parse HEAD`: `24ffd4e5fb9968762cb083713748ab2e413927da`.
- `git rev-parse origin/main`: `24ffd4e5fb9968762cb083713748ab2e413927da` (sincronizado).
- `git diff --check`: limpo.
- Runtime V3 local: `http://localhost:8095` (container `rma-v3-laravel.test-1`).
- Runtime Legacy: `http://localhost:8094` (container `rma-legacy-php-legacy-1`).
- Usuario QA V3: `superadministrador@rma.local` / `password`.
- Usuario QA Legacy: `lab@localhost` / `rma-lab-2026` (preferencia 15.8.1 no snapshot).
- Probes Playwright temporarios (NAO versionados) usados so como evidencia:
  `tests/Browser/_tmp_rodada_probe{,2,3,4}.spec.ts`, removidos antes do commit.

Escopo desta rodada (ordem do dono): interromper o avanco do Tema V3/T3-12 e
corrigir regressoes/paridade dos temas legados, com prioridade no Tema V2/15.8.1.
Nenhum push; nenhum commit alem de checkpoints locais pequenos; handoff como
ultimo commit.

## 2. Evidencias novas do dono (validacao manual no navegador)

O dono comparou o sistema atual com o Legacy e encontrou:

1. Troca V1 <-> V2 nao muda visualmente quando feita em rotas QA prefixadas.
2. Navbar V2/15.8.1: textos da barra vermelha nao centralizados verticalmente
   como no Legacy.
3. Dropdown Menu V2: moldura/faixa branca ao redor/dentro dos itens.
4. Controles tipo combo sem cursor pointer em runtime (reabertura UI-09.3).
5. Detalhe RMA V2 foi desenhado em leitura e nao reproduz o formulario
   operacional editavel do Legacy 15.8.1 (A6 fechado cedo demais).
6. Novo RMA V2 esta longe do Legacy: a aba `#novo_rma` virou so um link para
   `/create` com formulario vertical generico.
7. Pedido de varredura sistematica 15.8.1 x Tema V2 nas superfícies listadas.

## 3. Metodo de reproducao usado nesta sessao

- Chromium headless (Playwright), viewport 1440x1000 (e faixas de 568 a 2048 na
  nav), zoom 100%, DPR 1.
- Medicao por `getBoundingClientRect()`/`getComputedStyle()` nos dois runtimes.
- Login nos dois sistemas conforme secao 1.
- Nenhuma alteracao em Blade/SCSS/JS/PHP aconteceu antes deste addendum.

## 4. PAR-V2-THEME-01 - troca V1 <-> V2 quebrada em rota prefixada (BUG-CONFIRMADO)

| Campo | Valor |
|---|---|
| Rota repro | `GET /v2/rma/3` (V3) e `GET /v1/parceiros/fornecedores` (V3) |
| Sintoma do dono | Clicou "Trocar p/ 14.6.1" estando em `/v2/rma/97`; nada mudou visualmente |
| Fonte atual | `app/Http/Controllers/Identidade/TemaPreferidoController.php` (`update()` persiste e faz `return back()`); `app/Http/Middleware/ResolverTemaAtivo.php` (forca `v1.*` -> V1 e `v2.*` -> V2) |
| Fonte Legacy | `15.8.1/inc/menu.php` (`Trocar p/ 14.6.1` era um link real para `../trocarapp.php`, que redireciona para a interface do outro app) |
| Reproducao runtime | Probe 1: de `/v2/rma/3` clique no POST; URL continuou `/v2/rma/3`, `.header-v2` continuou visivel e `#FIXADO` ausente. De `/v1/parceiros/fornecedores` clique no POST; URL continuou `/v1/...`, `#FIXADO` continuou visivel e `.header-v2` ausente. A preferencia persiste no banco; so o redirecionamento volta para a rota que o middleware forcou para o tema antigo |
| Causa raiz | `back()` devolve para o mesmo URL prefixado; o middleware de QA (`ResolverTemaAtivo`) entao forca o tema do prefixo, independentemente da preferencia recem-gravada. O mecanismo de rotas forcadas e util para QA e nao deve ser removido |
| Impacto | Usuario ve "nada aconteceu"; retorna na mao para o outro tema ou faz logout/login |
| Proposta | `TemaPreferidoController::update()` deve redirecionar para a contraparte funcional do tema NOVO: rota `v1.*` -> equivalente `v2.*`; rota `v2.*` -> equivalente `v1.*`; rota canonica sem prefixo -> permanece canonica (a preferencia passa a mandar); fallback seguro para a pagina inicial canonica do tema novo quando a contraparte nao existir. Preservar parametros (ex.: `{rma}`) e hash quando possivel. V3 nunca entra no seletor |
| Teste esperado | PHPUnit + Playwright: (1) canonica V1 -> V2; (2) canonica V2 -> V1; (3) `/v1/rma/{id}` -> V2; (4) `/v2/rma/{id}` -> V1; (5) persistencia apos novo request/relogin. O FLOW-RMA-003 existente cobre so rota canonica de parceiro, nao esse caso |

Observacao: o botao "Trocar p/ 14.6.1" do Tema V2 fica sempre visivel no dropdown;
o botao "Trocar p/ 15.8.1" do Tema V1 so esta visivel quando o menu de sessoes
(`#JS-Sessao`) esta aberto (superfícies de cadastro/relatorios). Por isso a prova
V1 prefixada usa `/v1/parceiros/fornecedores` (botao visivel) e a prova de rota
V1 de detalhe deve abrir o menu antes do clique, quando o teste pedir `/v1/rma/{id}`.

## 5. PAR-V2-NAV-02 - alinhamento vertical da navbar V2 (BUG-CONFIRMADO)

| Campo | Valor |
|---|---|
| Sintoma | Textos da barra vermelha nao ficam centralizados como no 15.8.1 |
| Fonte Legacy | `pattern/15.8.1.css:174-180` (`.nav-tabs li a` com `height:39px`, SEM `line-height`); Bootstrap 3 fornece `line-height:20px` + `padding:10px` |
| Fonte atual | `resources/sass/temas/_v2-base.scss` (`.nav-tabs.nav-v2 li > a` com `height:39px` E `line-height:39px`) e `v2.scss` |
| Reproducao runtime | Probe 2 (1440px): item V3 `y=-3..36`, texto `y=17..36` (centro 26,5 vs 16,5 do item; deslocado ~10px para baixo); item Legacy `y=-3..36`, texto `y=9..25` (centro ~17 vs 16,5; centralizado). Computed: V3 `line-height:39px`; Legacy `line-height:20px` |
| Causa raiz | `line-height:39px` somado ao `padding:10px` do Bootstrap empurra o texto para baixo da caixa de 39px (box-sizing com padding). O Legacy nao declara line-height proprio |
| Impacto | Percepcao visual de desalinhamento entre os itens; Logout/Menu tambem desalinhados |
| Proposta | Remover `line-height:39px` dos itens da barra e deixar Bootstrap/padding definir o centro, como no Legacy; o botao POST de Logout (sem `<a>` por seguranca) deve receber a MESMA geometria/estilo de um `a` da navbar (incluindo padding e line-height do Bootstrap) |
| Breakpoints | Legacy `css/media.php`: ate 1279px item 12,5%; >=1280px item 11,1%. `.nav` 990px em 992/1080 e 1190px a partir de 1280. O SCSS atual fixa `.nav-tabs.nav-v2` em 1190px e `li` em 11,1% para todas as faixas (overflow e largura errada abaixo de 1280). Probe: V3 em 568/800/992/1080 mantem nav 1190px e item 132px; Legacy em 992/1080 usa nav 990px e item 123,75px; em 568/800 usa 148,75px (12,5% da base do wrapper); >=1280 usa 132,08px |
| Teste esperado | Playwright de geometria por viewport (568/768/800/992/1080/1280/1366/1440/1600/2048): top, height, bounding box de cada item, centerY do texto, diferenca vertical <= tolerancia pequena; Logout e Menu alinhados com Inicio/Novo/etc. |

Detalhe da causa do Logout/Menu: no `_v2-base.scss` atual o seletor aninhado em
`li { > a, .logoutx .link-como-item-dropdown { ... } }` compila para
`.nav-tabs li .logoutx .link-como-item-dropdown`, que NAO casa com o HTML real
(`li.logoutx > form > button`). Como `.logoutx` e o proprio `li`, o botao interno
so e atingido pela regra generica `.link-como-item-dropdown`, sem a geometria da
navbar. Correcao deve usar `li.logoutx .link-como-item-dropdown` ou equivalente
direto, mantendo POST/CSRF.

## 6. PAR-V2-DROPDOWN-02 - moldura/faixa branca no dropdown (BUG-CONFIRMADO)

| Campo | Valor |
|---|---|
| Sintoma | Moldura/faixa branca no dropdown Menu |
| Fonte Legacy | `inc/menu.php` usa `<a><li class="lidropdown">...</li></a>` (HTML invalido, NAO reproduzir); `.dropdown-menu` no runtime Legacy: padding 0, border 0, borderRadius so inferior, boxShadow do Bootstrap; itens 25px, sem folga (probe: aY=liY, 9 itens -> 225px) |
| Fonte atual | HTML valido `<li><a>`/`<li><form><button>`; `.dropdown-menu` herda do Bootstrap: padding 5px 0, borda 1px rgba(0,0,0,.15), radius, boxShadow, fundo branco. Probe V3: `.dropdown-menu` branco com padding 5px 0 e 12 itens de 25px com `a` comecando 3px abaixo do `li`; altura total 312px |
| Causa raiz | Camada Bootstrap do `.dropdown-menu` continua visivel (fundo branco, borda, padding, sombra) e o item interno (`<a>`) nao cobre 1:1 o `li` de 25px (offset de ~3px por causa do padding/hover duplicado entre `li` e `a`); a regra `.nav-v2 li > a` da barra tambem alcanca itens do dropdown |
| Impacto | Faixa branca entre/ao redor dos itens; area clicavel e hover nao coincidem com o item |
| Proposta | Manter HTML valido. Uma unica camada deve ser dona dos 25px, padding, fundo e hover; resetar no escopo da nav V2 o `padding/border/background/border-radius/box-shadow` do `.dropdown-menu`; item POST "Trocar p/..." deve ter exatamente a mesma geometria dos links comuns. Nao trocar POST por GET |
| Teste esperado | Nenhuma faixa/pixel branco residual entre/ao redor dos itens; areas clicaveis sem sobreposicao; hover somente no item; Tab/Enter; Menu abre/fecha |

## 7. PAR-V2-CURSOR-02 - reabertura UI-09.3 (varredura real)

Estado atual confirmado por codigo e probe:

- Regras universais existem nos dois temas: `select:not(:disabled) {cursor:pointer}`
  e `select:disabled {cursor:not-allowed}` (`_v1-base.scss`, `v2.scss`).
- Probe 3 varreu 38 rotas V1/V2 (detalhe, edicao, create, usuarios, parceiros
  create/edit/lista, relatorios, credito, controle, historico, perfil, listagens).
  Resultado: 0 selects habilitados com cursor errado; nenhum select desabilitado
  com cursor errado nas rotas com elemento vivo.
- Probe 4 encontrou um residuo real de contrato: no `/v2/rma`, o link ativo
  "Inicio" (aba ativa do indice) apresentou `cursor: default`, enquanto os demais
  links da mesma barra apresentaram `pointer`. Nenhuma regra no SCSS atual declara
  esse default; e um residuo de cascata/estado que precisa ser corrigido na origem
  e coberto por teste.
- Superfícies que o dono citou ainda nao existem como controles no V2 atual
  (Novo RMA inline e detalhe editavel), portanto a varredura de "combos" so pode
  ser fechada depois das ondas 3 e 4.

Conclusao: UI-09.3 volta para `[R]`; contrato alvo ampliado (sem duplicar regra):

- select habilitado -> pointer;
- select desabilitado -> not-allowed/default coerente;
- botao e menu/link clicaveis -> pointer;
- input de texto editavel e datalist de digitacao -> text, nunca pointer global;
- custom select-like nao editavel -> pointer quando implementado no Novo/detalhe V2.

## 8. PAR-V2-DETAIL-02 - A6 reaberto: detalhe RMA V2 funcional, nao so leitura

Prioridade alta. Evidencia objetiva:

| Fonte | Composicao |
|---|---|
| Legacy `15.8.1/page/rma.php` | Formulario operacional `<form>` com cabecalho SALVAR/OK (superior e inferior), campos reais editaveis, selects reais, textareas, grupos 4 colunas, fiscal, cliente/fornecedor, destinatario, logistica, solucao, observacao, politica (readonly) |
| Probe Legacy | `form[role=form]`: 14 inputs, 3 selects, 2 textareas, 1 botao (metricas do `?page=rma&id=1`) |
| Atual `temas/v2/rma/show.blade.php` | Cabecalho + "Editar" separado + 46 paragrafos `.detalhe-v2__valor`/`.detalhe-v2__texto-longo`; sem formulario de salvamento dos campos |
| Teste atual `ParidadeDetalheRmaV2Test` | Documenta "em leitura" e so faz `assertSeeText` - formaliza comportamento que o Legacy prova errado |

Acoes:

- Reabrir `A6 = [R]` no plano e no OpenSpec de paridade.
- Converter o detalhe V2 em formulario operacional editavel para quem tem Policy de
  escrita, reaproveitando `RmaController::update`/`EditarRma` e as acoes modernas
  (`receber/encaminhar/concluir/reverter/arquivar`), sem copiar PHP legado nem
  duplicar regra de negocio.
- Composicao visual 15.8.1 preservada (grupos 4 colunas, breadcrumb, acao no
  cabecalho, sidebar, header).
- Usuario com Policy de leitura nao altera (controles desabilitados/leitura ou
  formulario nao submetido - decisao a registrar na implementacao).
- QA funcional: alterar campo, salvar, reload, persistencia; checkbox/select
  relevantes; acao operacional; usuario leitura bloqueado.
- QA visual: posicoes, 4 colunas, breadcrumb, controles, acao superior, informacao
  adicional, sidebar, header.

## 9. PAR-V2-NOVO-01 - Novo RMA V2 inline e paridade operacional

| Fonte | Composicao |
|---|---|
| Legacy `15.8.1/page/novo_rma.php` | Formulario direto no painel/aba `#novo_rma`; 3 colunas (descricao/fabricante/modelo/S-N; OS/origem/empresa/prioridade; bloco condicional NF Venda / NF Compra conforme Origem); defeito/observacao/estoque; botao "CRIAR BD"; datalists historicos |
| Probe Legacy | 14 inputs, 3 selects, 2 textareas, 1 botao, 4 `col-md-*` |
| Atual `temas/v2/rma/index.blade.php` | Aba `#novo_rma` contem somente `<a>Abrir novo RMA</a>`; `create.blade.php` e formulario vertical generico |
| Probe atual | Aba `#novo_rma`: 0 inputs/0 selects/0 textareas/0 botoes; 1 link |

Checklist da nova frente (marcadores canonicos):

- [R] PAR-V2-NOVO-01 - Novo RMA 15.8.1
  - [x] NOVO-01.1 - investigacao fonte/runtime (evidencia acima).
  - [ ] NOVO-01.2 - restaurar formulario inline na aba Novo.
  - [ ] NOVO-01.3 - restaurar grade de 3 colunas.
  - [ ] NOVO-01.4 - restaurar controles/valores/opcoes.
  - [ ] NOVO-01.5 - comportamento condicional por Origem.
  - [ ] NOVO-01.6 - estoque.
  - [ ] NOVO-01.7 - validacao/store modernos.
  - [ ] NOVO-01.8 - fallback da rota `/create` coerente.
  - [ ] NOVO-01.9 - QA browser + funcional.

Nao reintroduzir vulnerabilidades/SQL/JS legado; reproduzir comportamento e
aparencia por cima da arquitetura moderna (mesmo caso de uso `CriarRma`/store).

## 10. PAR-V2-SWEEP-01 - varredura residual 15.8.1 x Tema V2

Superfícies a varrer (apos ondas 1-4):

- Shell: header, navbar, Menu, Logout, troca de tema, sidebar direita, footer.
- RMA: Inicio, Pesquisar, Novo, Entrada, Recebido, Encaminhado, Concluido,
  detalhe, edicao, acoes de ciclo, credito.
- Parceiros: clientes, fabricantes, fornecedores, assistencias; list/create/show/edit.
- Admin: Controle, usuarios.
- Outros: relatorios, anotacoes/perfil, estados vazios, mensagens,
  selects/botoes/hover/focus.

Classificacao a usar em cada diferenca: `BUG-CONFIRMADO`, `PARIDADE-LEGACY`,
`MELHORIA-UX`, `NAO-CORRIGIR-FIDELIDADE`, `DECISAO-PENDENTE`. Na mesma regra dos
temas de preservacao: nao "melhorar" V1/V2 livremente; corrigir bugs de interacao,
elementos faltantes, fluxo diferente, geometria errada, controle que perdeu funcao
e troca de tema quebrada.

## 11. Plano de execucao por ondas (apos o commit documental)

Marcadores canonicos `[ ]`/`[R]`/`[x]` conforme `docs/operacao/padrao-status-plano.md`.

- [ ] ONDA 1 - troca V1/V2 (PAR-V2-THEME-01) + testes.
- [ ] ONDA 2 - navbar V2 (alinhamento vertical, Logout/Menu, dropdown sem borda
  branca, breakpoints, cursor residual).
- [ ] ONDA 3 - detalhe RMA V2 funcional (A6/PAR-V2-DETAIL-02).
- [ ] ONDA 4 - Novo RMA V2 inline e paridade funcional (PAR-V2-NOVO-01).
- [ ] ONDA 5 - sweep residual nas demais telas V2 (PAR-V2-SWEEP-01) e classificacao.

Por onda: codigo -> teste dirigido -> runtime/browser -> atualizar plano/docs ->
`git diff --check` -> commit atomico -> proxima onda. Nao acumular tudo num commit
gigante.

## 12. Reaberturas formais registradas

- `A6` (paridade detalhe RMA V2): `[x]` -> `[R]` porque o detalhe virou leitura e
  o Legacy 15.8.1 e formulario operacional editavel.
- `UI-09.3` (cursor): `[x]` -> `[R]` ate passar varredura completa apos as ondas
  3/4 e corrigir residuo de cursor do link ativo na navbar V2.
- Qualquer item de paridade V2 cuja conclusao a evidencia nova contradiga: `[R]`.

Nenhuma implementacao de codigo aconteceu antes deste addendum; este documento e o
registro da reproducao (regra do dono desta rodada).

## 13. Resultado pos-correcao - ONDA 1 (PAR-V2-THEME-01)

- Commit local: `9fbeac1` (#FRONT-RMA).
- `TemaPreferidoController::update()` agora redireciona para a contraparte do
  outro tema quando o referer e `/v1/...` ou `/v2/...`, preservando caminho,
  query e fragmento; rota canonica continua com `back()` (a preferencia passa a
  mandar na view).
- Testes novos: `tests/Feature/Temas/TrocarTemaEmRotaPrefixadaTest.php` (4 testes /
  17 assertions, incluindo relogin) e `tests/Browser/ParidadeTrocaTemaPrefixada.spec.ts`.
- PHPUnit dirigido: 4/4 verdes. Playwright dirigido: 1/1 verde.
- PUSH NAO REALIZADO.

Proximo: ONDA 2 (navbar/dropdown/cursor).

## 14. Resultado pos-correcao - ONDA 2 (PAR-V2-NAV/DROPDOWN/CURSOR)

- Commit local: `62cbb27` (#FRONT-RMA).
- Navbar: `line-height:39px` removido; texto centraliza pelo Bootstrap como no
  Legacy (probe pos: item e texto com centro dentro de 2px). Logout (POST) ganhou
  regra propria com a caixa de `<a>` da navbar (height 39, padding 10/15,
  line-height 20). Breakpoints: `.nav` 990/1190px e item 12,5%/11,1% seguindo
  `media.php`.
- Dropdown: `.dropdown-menu` da nav V2 resetado (padding 0, sem borda/sombra,
  fundo transparente); `li` dono dos 25px/fundo/borda/hover; item POST com mesma
  geometria dos links.
- Cursor: causa do default no item ativo era Bootstrap
  `.nav-tabs>li.active>a { cursor:default }`; corrigido no seletor ativo do tema
  (regra universal `a[href]` nao vencia a especificidade do Bootstrap).
- Teste novo: `tests/Browser/ParidadeNavbarDropdownV2.spec.ts` (4 testes).
- Regressao dirigida: ConsistenciaVisualControles 9/9 + ParidadeDetalheRmaV2 3/3 +
  ParidadeNavbarDropdownV2 4/4 = 16/16 verdes. Vite build verde.
- PUSH NAO REALIZADO.

Proximo: ONDA 3 (detalhe RMA V2 funcional - A6/PAR-V2-DETAIL-02).
