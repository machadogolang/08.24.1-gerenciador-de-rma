# Auditoria residual - paridade visual/funcional Legacy x V1 x V2

Data: 2026-09-09. Frente aberta por nova instrucao do dono: o handoff PAR-V2
anterior (`93fd4e4`) e um checkpoint, nao o fim do trabalho. O dono considera que
ainda existem detalhes de layout/paridade incorretos em V1/14.6.1 e V2/15.8.1,
mesmo sem screenshot apontado nesta nova sessao.

## Baseline real (reconferido nesta sessao)

- `git status --short --branch`: `## main...origin/main [ahead 3]`, working tree
  limpa no inicio.
- `git rev-parse HEAD`: `b49bda00426ee8e1f498563157df275852267524`.
- `git rev-parse origin/main`: `93fd4e4387140be3d2395717b53dad3fec944136`.
- Local HEAD contem tres commits alem do main remoto (abertura T3-12, front
  T3-12 e doc T3-12), feitos na sessao anterior com autorizacao do dono; nenhum
  push foi executado por mim.
- `git diff --check`: limpo.

## Metodo

- Comparacao ativa Legacy (`:8094`) x V3 (`:8095`), pagina por pagina.
- Medicoes via `getBoundingClientRect()` e `getComputedStyle()` em viewports de
  referencia e faixas historicas (568/768/800/992/1080/1280; 1366/1440/1600/
  1920).
- Fonte Legacy sempre como verdade para V1/V2, respeitando seguranca moderna
  (CSRF/Policy/tenant e metodos corretos).
- Cada achado recebe ID PAR-RES-NNN com classificacao obrigatoria.

## Matriz de achados

| ID | Tema | Tela | Legacy | Atual | Diferenca | Causa | Classificacao | Status | Teste |
|---|---|---|---|---|---|---|---|---|---|
| PAR-RES-001 | V2 | Entrada | Linhas sem garantia e prioridade alta usam `TrInconformidade`; nao existe `TrUrgente`; zebra `TrZebrada1/2` | `classe_css_de_alerta()` pode gerar `TrUrgente` (prioridade alta/prazo) e `TrSemGarantia1/2` | Classes de destaque incorretas na aba Entrada | Partial `_tabela_entrada` reutiliza regra generica de alerta que mistura criterios de Recebido/Encaminhado/Concluido | BUG-CONFIRMADO | [x] | Playwright de classes/cores por linha |
| PAR-RES-002 | V2 | Recebido | Sem garantia e sem NF usam `TrInconformidade`; prioridade alta e prazo de 30 dias usam `TrUrgente` | Sem garantia vira `TrSemGarantia1/2`; criterio "sem NF compra/venda" ausente | Classes incorretas e criterio faltante | Mesma regra generica + dominio nao carrega sem-NF como classe | BUG-CONFIRMADO | [x] | Playwright de classes/cores por linha |
| PAR-RES-003 | V2 | Encaminhado | Sem garantia usa `TrInconformidade`; prioridade alta e prazo de 30 dias usam `TrUrgente` | Sem garantia vira `TrSemGarantia1/2` | Classes incorretas no destaque sem garantia | Mesma regra generica | BUG-CONFIRMADO | [x] | Playwright de classes/cores por linha |
| PAR-RES-004 | V2 | Concluido | Zebra binaria `TrSemGarantia1/2`/`TrZebrada1/2` sem alertas | Mesma estrutura no partial proprio | Sem diferenca confirmada | - | SEM-PROBLEMA | [R] | ampliar prova com fixture SemGarantia |
| PAR-RES-005 | V2 | listagens | Linhas de uma linha medem 26px; linhas com quebra medem ~32px | Linhas com quebra medem ~32px; base depende do conteudo | Sem diferenca sistematica confirmada alem de dados de QA mais longos | Conteudo de QA diferente do banco Legacy | SEM-PROBLEMA | [R] | fixture curto em Playwright |
| PAR-RES-006 | V2 | Centro de Avisos e relatorios | Cada grupo tem tabela propria | Lista generica compartilhada | Composicao por grupo ainda nao reproduzida | gap documentado desde CP22/CMP-V2-004 | PARIDADE-LEGACY | [x] | PAR-RES-006 (ParidadeCentroDeAvisosV2) |
| PAR-RES-007 | V2 | Anotacoes | Pagina propria em `15.8.1/page/anotacoes.php` | Menu aponta para perfil | Pagina dedicada ausente | gap documentado desde CP17 | PARIDADE-LEGACY | [x] | PAR-RES-E-04 |
| PAR-RES-008 | V1/V2 | Novo RMA | Autocomplete historico (datalist) | Selects modernos com mesmos valores | Implementacao moderna segura, visual funcional equivalente | decisao arquitetural registrada (NOVO-01.4/PAR-V2-NOVO-01) | FIDELIDADE-INTENCIONAL | [x] | coberto por Playwright |
| PAR-RES-009 | V1/V2 | diversos | Layout historico | Layout moderno | Nao e espaco de redesign | regra do projeto | MELHORIA-V3 | - | nao corrigir em V1/V2 |

## Plano de ondas

- [x] ONDA A - Shell/navbar/menu/dropdown/footer (V1/V2). Reconciliada nesta continuacao (PAR-RES-A-01/A-02 corrigidos em codigo+JS, provados por `ParidadeShellV2.spec.ts` 3/3).
- [x] ONDA B - Listagens/pesquisa/tabelas/zebra/sidebar (77ec2ce; PAR-RES-001..003
  corrigidos e testados; PAR-RES-004/005 seguem como prova residual).
- [x] ONDA C - Create/show/edit RMA e ciclo (V1/V2). PAR-RES-C-01 corrigido (1007ad0); PHPUnit dirigido 35/35 + `ParidadeDetalheRmaV2Funcional` 1/1 verdes.
- [x] ONDA D - Parceiros/admin/Controle/usuarios. PAR-RES-D-01..04 corrigidos (24c7c3a); PHPUnit dirigido 35/35 + `ParidadeParceirosV2` 1/1 verdes.
- [x] ONDA E - Relatorios/Avisos/Anotacoes/secundarias. PAR-RES-006 (Centro de Avisos) e
  PAR-RES-007 (Anotacoes/senha V2) fechados; RCD/RPEC/RMPE em bbea068.
- [ ] ONDA F - Viewport/print/regressao residual.

Cada onda: investigar -> corrigir -> PHPUnit dirigido -> Playwright/browser ->
atualizar matriz/plano -> `git diff --check` -> commit atomico -> proxima onda.

## Reconciliacao documental desta sessao

- T3-11: confirmado `[x]` por codigo/testes/commits (ver A7/T3-11).
- T3-12: confirmado `[x]` localmente (57f1c13; 536/1619 e Playwright 2/2) e
  registrado como local-only nesta auditoria (nao remoto).
- Textos antigos de "V3 nao implementado" foram localizados em PLANO/PLAN/matriz
  e serao reconciliados narrativamente junto deste checkpoint; o V3 permanece
  oculto e nao selecionavel ate T3-GATE.
- Baseline de suíte atual: 536 testes / 1619 assertions (nao 515/1421).

## Resultado - ONDA B (commit 77ec2ce)

- PAR-RES-001/002/003 corrigidos: `classe_css_linha_v2()` reproduz por tela os
  destaques do PHP fonte 15.8.1 (Entrada sem TrUrgente/TrSemGarantia; Recebido e
  Encaminhado com sem-NF/sem-garantia corretos; Pesquisa sem TrSemGarantia fora
  de concluido).
- Teste novo: `ParidadeListagensV2Test` (6 testes / 14 assertions); PHPUnit
  dirigido 36/36; Playwright abas V2 3/3 e consistencia dirigida verde.
- Teste D de Usuarios V1 ficou deterministico com `/v1/usuarios` (estado de tema
  compartilhado causava falsa falha).
- Pendente na onda: PAR-RES-004/005 sao prova residual; nao foram encontradas
  diferencas sistematicas alem do conteudo de QA.

## 2. Novos sintomas manuais do dono (continuacao da sessao)

Baseline: origin/main = HEAD = `effb101`; working tree limpa. Novo push do dono
recebido e confirmado.

### PAR-RES-C-01 - Rodape do detalhe RMA V2

- Sintoma: no final do detalhe aparecem textarea de Informacao adicional, select
  branco SALVAR + OK deslocados a direita, depois "Abrir pagina de edicao" e
  "Mais acoes de ciclo de vida" antes do footer, empurrando o rodape.
- Legacy `15.8.1/page/rma.php`: formulario unico com Informacao adicional, depois
  bloco interno `.fr` com select `formSelect2` (fundo azul #224A5D) + botao OK
  `buttonSalvar`, dentro do proprio form; nao ha "Abrir pagina de edicao" nem
  bloco extra "Mais acoes de ciclo de vida" nessa tela.
- Atual: `_form_detalhe.blade.php` inclui link "Abrir pagina de edicao" apos a
  acao do rodape e `show.blade.php` inclui `<details>Mais acoes de ciclo de vida`
  com partial `_acoes_de_transicao`; o select do rodape nao recebe o fundo
  historico `.formSelect2`/`#224A5D`, ficando branco.
- Causa: elementos modernos introduzidos sem equivalencia historica no V2 e
  estilo do select ausente.
- Classificacao: BUG-CONFIRMADO/PARIDADE-LEGACY.
- Correcao: remover link e bloco avancado do V2 (rotas continuam disponiveis),
  manter select+OK dentro do form e estilizar select como `.formSelect2`.
- Teste: geometria final do detalhe V2 + presenca/ausencia de marcadores.

### PAR-RES-D-01..04 - Parceiros V2 edit/create/show genericos

- Sintoma: `/parceiros/fornecedores/7/edit` mostra formulario vertical de uma
  coluna com inputs claros/grandes, sem a grade historica.
- Legacy `15.8.1/inc/novo_fornecedor.php`/`novo_fabricante.php`: formulario em
  `col-md-4` (3 colunas na pratica), labels acima dos campos, inputs escuros
  `form-control Input1 cb`, endereco/contato, textareas de Observacao/Política
  lado a lado e botao Cadastrar; o mesmo modelo existe para cliente e assistencia.
- Atual V2 `parceiros/_form.blade.php`: `form-horizontal` Bootstrap vertical de 1
  coluna, generico.
- Causa: partial moderno compartilhado entre telas impondo geometria unica;
  falta markup/grade V2.
- Classificacao: PARIDADE-LEGACY/BUG-CONFIRMADO para o tema V2.
- Correcao: reescrever apenas o form de parceiros V2 em grade historica,
  preservando casos de uso/Policies e sem vazar para V1.
- Teste: create/edit dos 4 tipos com bounding boxes/colunas e persistencia.

### PAR-RES-E-01..03 - Relatorios RCD/RPEC/RMPE V2

- Sintoma: no V2 as tres rotas exibem titulo grande, tabela branca e visual
  generico, pouco integrado ao tema 15.8.1.
- Fonte Legacy: RCD/RPEC/RMPE sao paginas reais do Tema V1
  (`14.6.1/page/relatorios.php`) com titulo historico, descricao, tabela e bloco
  de informacao adicional; no V2 Legacy nao ha essas paginas, so `Relatorios`
  como pagina de contagem/estatisticas. Verificar se a exibicao V2 deve manter
  a tabela branca de impressao (possivel) mas com o shell/cabecalho do tema.
- Classificacao: PARIDADE-LEGACY/investigacao; decisao visual de tema necessaria
  mas nao de produto (nao bloquear demais ondas).
- Correcao planejada: garantir cabecalho/largura/zebra do tema e impressao
  correta sem redesenhar regra.

### PAR-RES-E-04 - Perfil/Anotacao pessoal V2

- Sintoma: `/perfil` no V2 agrupa identificacao, troca de tema, troca de senha e
  anotacao pessoal numa unica tela generica.
- Legacy V2: `page/anotacoes.php` e pagina propria "QUADRO DE ANOTACOES" com
  textarea autosave e sem formulario de senha; `subp/senha.php` (alterar senha) e
  uma subpaginacao separada. Troca de tema no V2 Legacy e link `trocarapp.php`.
- Classificacao: PARIDADE-LEGACY com avaliacao de organizacao historica.
- Correcao: reproduzir organizacao quando viavel sem quebrar seguranca; manter
  POST/CSRF; se juntar telas for decisao de produto, registrar DECISAO-PENDENTE e
  seguir ondas independentes.


### Resultado - ONDA C/D/E parcial (commits 1007ad0/24c7c3a/bbea068)

- PAR-RES-C-01 corrigido: rodape do detalhe V2 sem link/bloco moderno extra e com
  select `#224A5D` historico (1007ad0).
- PAR-RES-D-01..04 corrigido: formularios V2 de parceiros em grade historica com
  controles escuros (24c7c3a).
- PAR-RES-E-01..03 corrigido: relatorios com largura integrada e titulo 18px
  (bbea068); folha branca mantida como superfície de relatorio/impressao.
- PAR-RES-E-04 Perfil/Anotacoes: no Legacy V2 Anotacoes e pagina propria
  (`page/anotacoes.php`) e Alterar senha e subpaginacao separada
  (`subp/senha.php`); juntar tudo em /perfil e decisao de produto. Classificacao:
  DECISAO-PENDENTE, registro mantido; tarefas independentes continuam.


## Resultado - reconciliacao C/D (2026-09-10, continuacao)

- Working tree limpa e `git diff --check` limpo no baseline `d5c9463`
  (= `origin/main`).
- PHPUnit dirigido dos blocos C/D/E (`ParidadeDetalheRmaV2Test`,
  `ParidadeNovoRmaV2Test`, `ContratoVisualAcoesParceirosTest`, `RelatoriosShellTest`,
  `DescobribilidadeRelatoriosTest`, `RelatorioControllerTest`, `DetalheDoParceiroTest`):
  35 testes / 250 assertions, OK.
- `tests/Feature/Temas` (shell/temas): 112 testes / 640 assertions, OK.
- Playwright dirigido: `ParidadeDetalheRmaV2Funcional`, `ParidadeNovoRmaV2`,
  `ParidadeParceirosV2`, `ParidadeRelatoriosV2` - 4/4 verdes.
- ONDA C e ONDA D fechadas como `[x]` (criterios satisfeitos e provados). ONDA E
  segue `[R]` ate PAR-RES-E-04 (Anotacoes/senha dedicadas) e PAR-RES-006
  (Centro de Avisos).

## Resultado - ONDA A (reconciliacao do shell, 2026-09-10)

Evidencia ja existente (nao reimplementada): `ParidadeNavbarDropdownV2` 4/4,
`ParidadeTrocaTemaPrefixada` 1/1, `ParidadeVisualTemaV1` 12/12, `tests/Feature/Temas`
112/112. Isso cobre navbar, Menu, Logout, troca V1<->V2, shell V1 e rodape V1.

Residuo encontrado na reconciliacao (V2, nao coberto por teste anterior):

- PAR-RES-A-01 - sequencia de `LRTOP1`/`LRTOP2` do painel lateral V2
  (`temas/v2/rma/_painel_lateral.blade.php`) era alternancia simples com paridade
  invertida; o Legacy (`inc/rightmenu.php`) tem `LRTOP1, LRTOP2, LRTOP1, LRTOP2,
  LRTOP1, LRTOP1, LRTOP1, LRTOP2, LRTOP1, LRTOP2, LRTOP1, LRTOP2, LRTOP1, LRTOP2`
  (tres `LRTOP1` seguidos em DESTINATARIOS/PORTO A/URGENTE). As linhas tambem
  comecavam em `LiRight2` no V3 e em `LiRight1` no Legacy. Classificacao:
  BUG-CONFIRMADO/PARIDADE-LEGACY. Correcao: mapa explicito por chave + inicio em
  `LiRight1`.
- PAR-RES-A-02 - o handler generico `[data-pmo-alvo]` (`temas/v2.js`) reescrevia
  `textContent` do cabecalho do painel lateral, trocando o titulo da secao por
  "Ocultar"/"Mostrar" ao expandir; no Legacy o titulo permanece. Classificacao:
  BUG-CONFIRMADO. Correcao: so reescreve o rotulo quando o gatilho tem a classe
  `.pmo` (V2 e V1).

Teste novo: `tests/Browser/ParidadeShellV2.spec.ts` (3/3) prova a sequencia exata,
o titulo preservado, o Logout POST e o rodape historico. ONDA A fechada `[x]`.

## Resultado - PAR-RES-E-04 (anotacoes e senha V2 dedicadas, 2026-09-10)

Decisao do dono aplicada: o TEMA V2 volta a organizacao historica. `/perfil` deixa
de ser a unica tela que mistura perfil+senha+anotacao.

- Superficies restauradas:
  - `/v2/anotacoes` (`temas/v2/identidade/anotacoes.blade.php`) - "QUADRO DE
    ANOTACOES", textarea propria; fonte `15.8.1/page/anotacoes.php`.
  - `/v2/perfil/senha` (`temas/v2/identidade/senha.blade.php`) - "Alterar senha"
    separada, com icone `senha2.png` vendorizado; fonte `15.8.1/subp/senha.php`.
- Seguranca moderna preservada: POST + CSRF + `_method=PUT`, validacao
  (`min:8`, `confirmed`) e `TrocarPropriaSenha` exigindo senha atual (o legado nao
  exigia). Nenhum JS legado copiado; a anotacao usa o endpoint
  `identidade.perfil.anotacao.update` por botao, sem o autosave sem CSRF do legado.
- `/perfil` do V2 continua existindo como rota moderna/compatibilidade (identidade
  + troca de tema), agora com atalhos para as duas superficies dedicadas.
- Rotas canonicas (`/anotacoes`, `/perfil/senha`) adicionadas em `routes/web.php`
  alem das prefixadas `/v2/...`; com tema ativo V1 o controller redireciona para
  `/perfil` (V1 nao recebe esta organizacao). Tema V3 nao foi tocado.
- Testes: PHPUnit dirigido 41/41 (`RenderizaTemaV2Test` + `AnotacaoPessoalTest` +
  `TrocarPropriaSenhaTest` + `RenderizaTemaV1Test`); Playwright
  `ParidadeAnotacoesSenhaV2` 4/4 (pagina propria, persistencia, /perfil sem
  mistura, senha separada com POST/CSRF/validacao, V1 sem regressao).
- `DECISAO-PENDENTE` do PAR-RES-E-04 removida: decisao do dono implementada.

## Resultado - PAR-RES-006 (Centro de Avisos V2, 2026-09-10)

A classificacao "lista generica compartilhada" estava desatualizada: o partial
`rma/_centro_de_avisos.blade.php` ja mapeia os 10 grupos para partials proprios
(`_abertos_nao_encaminhados`, `_sem_nota`, `_prazo_destinatario`,
`_nao_vai_dar_garantia`, `_nf_retorno_pendente`, `_garantia_fornecedor_expirada`,
`_garantia_fornecedor_expirando`) e reproduz titulos, "Mostrar/Ocultar", tabelas e
estado vazio. O que faltava era espacamento do TEMA V2:

- Passo recolhido entre grupos: 96px no V2 x 90px no Legacy 15.8.1 - o
  `line-height:20px` global do tema inflava a linha do `.pmo` (inline). Corrigido
  normalizando `line-height: normal` + base `font-size: 12px` no `.regra-de-alerta`
  (`_compartilhado.scss`); V1 ja herdava normal e nao mudou.
- Linha expandida da tabela: 26px no V2 x 30px no Legacy - o reset `td,th{padding:0}`
  do tema zerava o padding e o `img{vertical-align:middle}` do Bootstrap elevava a
  ancora `Ver`. Corrigido com `padding: 1px 1px 1px 0` e `vertical-align: baseline`
  escopados a `.regra-de-alerta-dados`.
- Ordem dos grupos confirmada identica ao `page/inicio.php` (prioridadealta,
  pabertonaoencaminhado, semsn, semnota, prazodestinatario,
  naoencaminhadoprazoestourado, pgarantiafornecedorexpirado, pmenosde30,
  naovaidargarantia, nfpendentelancar).

Teste novo: `tests/Browser/ParidadeCentroDeAvisosV2.spec.ts` (3/3) compara titulos,
tabela por grupo, passo recolhido e altura de linha contra o Legacy 15.8.1.
`ParidadeVisualTemaV1` segue 12/12 (nenhuma regressao no V1). ONDA E fechada `[x]`.
