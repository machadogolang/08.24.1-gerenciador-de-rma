# Addendum - Paridade dos detalhes RMA V1/V2 e residuos visuais do Tema V2

Data: 2026-09-09. Baseline: HEAD `8939c8f`, origin/main `8939c8f`, working tree
limpa no inicio. Fonte viva: `PLANO-ATAQUE.md`.

Escopo desta rodada (ordem obrigatoria do dono): A5 (detalhe RMA V1), A6 (detalhe
RMA V2), residuos visuais V2 (listagens/Encaminhado e Pesquisar) e somente depois
A7/T3-11. Este addendum e o registro documental da reproducao antes de qualquer
mudanca em Blade/SCSS/PHP.

## 1. Metodo de reproducao

Ambiente:

- Legacy V1: `http://localhost:8094/14.6.1/` + `index.php?page=detalhes&id=1446211262`.
- Legacy V2: `http://localhost:8094/15.8.1/` (indice com abas) e rota dinamica
  `/15.8.1/encaminhado`/`/15.8.1/pesquisar` quando aplicavel.
- V3 forca tema: `http://localhost:8095/v1/rma/1`, `http://localhost:8095/v2/rma/3`,
  `http://localhost:8095/v2/rma` com aba ativa via clique real no plugin Bootstrap.
- Login Legacy: `lab@localhost`/`rma-lab-2026` (laboratorio). Login V3:
  `superadministrador@rma.local`/`password`.
- Chromium headless, zoom 100%, DPR 1, viewports 2048x1152 e 1440x1000. Ambos os
  lados mediram `devicePixelRatio=1`, `zoom=1`, sem overflow de viewport.

Evidencia gravada:

- `docs/produto/screenshots-paridade-detalhe-rma-2026-09-09/` (pares
  `legacy-*.png`/`v3-*.png`).
- `docs/produto/screenshots-paridade-detalhe-rma-2026-09-09/metricas-2026-09-09.json`
  (detalhes V1/V2, Encaminhado e Pesquisar nos dois viewports).
- `.../metricas-legacy-index-v2-2026-09-09.json` (Legacy 15.8.1 indice estatico com a
  aba Encaminhado ativa x V3 indice, mesmo par do print do dono).

Observacao de dados: o ambiente V3 atual tem 110 RMAs de QA local, todos sem
`numero_legado` e sem colunas historicas de migracao preenchidas (somente `pn`/`snid`
em 8 registros; `numero_da_empresa` em 2). As colunas preservadas existem no schema
(migracao `2026_09_02_000001`), mas a MIG-V3 nao foi executada neste banco. Onde o
dado existe no schema e esta vazio no runtime, a correcao renderiza celula vazia e
documenta a lacuna - nunca inventa valor para "parecer igual".

## 2. PAR-DET-V1-01 - detalhe RMA Tema V1

| Campo | Valor |
|---|---|
| Rota | `GET /v1/rma/{id}` (`v1.rmas.show`) |
| Sintoma | Tela atual: link Editar + bloco de acoes de ciclo de vida no topo e tabela
  plana de duas colunas (Status/Descricao/Fabricante/...) ocupando a pagina. Legacy:
  titulo visual "BOLETIM DE DEFEITO" com icone e grupos de 4 colunas densas. |
| Legacy | `legacy-source/14.6.1/page/detalhes.php` |
| V3 | `resources/views/temas/v1/rma/show.blade.php` |
| Evidencia 2048x1152 | Legacy: `p.title-comicone` y=85; primeira linha de grupo
  (`tr.TRD`) y=138.69, altura 30; primeira linha de dados y=168.69, altura 29. V3:
  `h1.titulo-v1` "RMA #1" y=65; tabela plana y=253; linha V3 altura 21. |
| Evidencia 1440x1000 | Mesmos deltas: V3 tabela y=253 vs Legacy primeiro grupo
  y=138.69 (deslocamento +114,31px por causa das acoes no topo); V3 body altura 942
  vs Legacy conteudo 2112 (sem grupos/secao historica, pagina drasticamente menor). |
| Computed style relevante | Legacy `.title-comicone` 14px Open Sans; `tr.TRD` 30px,
  fonte Arial 12px, fundo `rgb(45,43,43)`; `tr.formTRDetailD` 29px, fundo
  `rgba(0,0,0,.3)`. V3 h1 14px/14px; linha plana 21px, fundo zebra generico. |
| Causa raiz | O `show` foi simplificado (view plana label/valor) e nao usa a gramatica
  historica (titulo BOLETIM DE DEFEITO, grupos `TRD`/`formTRDetailD`, 4 colunas,
  celulas 29/30px). O h1 generico "RMA #id" substitui o titulo historico. |
| Correcao proposta | Restaurar identidade visual do detalhe V1: titulo
  "BOLETIM DE DEFEITO" + icone, grade 4 colunas por grupo com os grupos confirmados,
  acoes de ciclo de vida na posicao compativel com a pagina historica, campos com
  dado real preenchidos e lacunas vazias documentadas. Manter `GET show` separado de
  `GET edit` (decisao moderna ja registrada; nao virar form editavel). |
| Teste necessario | Feature/Blade: grupos criticos e rotulos presentes. Playwright:
  bounding boxes e composicao em 2048x1152 e 1440x1000. |

### Campos V1 (prova A-E)

Legenda de fonte: A = agregado/runtime atual; B = snapshot legado preservado em
coluna; C = relacionamento; D = nao migrado/inexistente; E = codigo morto no Legacy.

| Campo legacy | Fonte V3 atual | Prova | Tratamento |
|---|---|---|---|
| numero (NUMERO DO BD) | `id`, `numero_legado`, `numero_da_empresa` | A/C (colunas existem) | mostrar `numero_legado ?? numero_da_empresa ?? id` |
| fabricante/descricao/modelo | `$fabricante?->nome`, `descricao`, `modelo` | A/C | mostrar |
| OS/origem/SN/empresa | colunas primeiras classe | A | mostrar |
| P/N, SNID | colunas primeiras classe (`pn`, `snid`) | A (8 registros no runtime) | mostrar quando houver |
| TEMPO | calculo de `createdAt` ate hoje | A (calculado no Legacy) | mostrar calculo com mesma regra |
| CLIENTE | `cliente_id` resolvido | A/C | mostrar nome; sem nome quando null |
| NF entrada/saida cliente | `nf_entrada_cliente_legado`, `nf_retorno_cliente_legado` | B (schema existe, runtime vazio) | mostrar quando houver |
| rastreio_ida/retorno | `rastreio_ida`, `rastreio_retorno` | B | mostrar quando houver |
| NF compra/venda + datas | `nfcompra*`, `nfvenda*` | A | mostrar; chaves (DANFE) existem e devem entrar |
| NF remessa/retorno + datas + chaves | `nf_remessa*`, `nf_retorno_numero*` | B | mostrar quando houver |
| NF devolucao de venda | `nf_devolucao_de_venda` | B | mostrar quando houver |
| VALOR | `valor` | A | mostrar quando > 0 |
| DESTINATARIO | morph `destinatario_type/id` ou `destinatario_nome_legado` | A/C/B | resolver por morph ou nome legado |
| S/N RETORNO | `snretorno` | A | mostrar |
| EMAIL/FONE | `cliente_email_legado`, `destinatario_email_legado`, `destinatario_fone_legado` | B | mostrar quando houver |
| PROTOCOLO/RESOLUCAO | `protocolo`, `solucao` | A | mostrar |
| DEFEITO/OBSERVACAO | colunas primeiras classe | A | mostrar |
| marcarestoque/credito disponivel | `marcarestoque`, `credito_disponivel` | A | mostrar |
| DATA entrada/recebido/encaminhado/concluido | `createdAt`, `recebidoEm`, `encaminhadoEm`, `concluidoEm` | A | mostrar |
| Politica de garantia | `politica_de_garantia` de destinatario/fabricante/fornecedor | C (entidades parceiras) | seguir selecao historica: destinatario, senao fabricante, senao fornecedor |

Sem criacao de coluna: nenhuma correcao precisa de migration nova.

## 3. PAR-DET-V2-01 - detalhe RMA Tema V2

| Campo | Valor |
|---|---|
| Rota | `GET /v2/rma/{id}` (`v2.rmas.show`) |
| Sintoma | Tela atual: acoes soltas no topo (Editar + transicoes) e depois tabela
  label/valor. Legacy: cabecalho operacional (breadcrumb "NUMERO DO BD ..."), acao
  Salvar/OK integrada ao cabecalho e formulario organizado em colunas/grupos com
  paineis fiscais, parceiros, logistica, datas e solucao. |
| Legacy | `legacy-source/15.8.1/page/rma.php` |
| V3 | `resources/views/temas/v2/rma/show.blade.php` |
| Evidencia 2048x1152 | Legacy: `ol.breadcrumb` y=37, primeiro `form[role="form"]`
  y=84.14, primeiro `.row.formgroupnf` y=84.14. V3: link Editar y=40, bloco
  `.acoes-de-transicao` y=80..270, tabela plana y=278. |
| Evidencia 1440x1000 | Mesma composicao; V3 tabela y=278, Legacy grupos y=84.14.
  Delta de inicio do conteudo principal +194px. |
| Computed style relevante | Legacy `.row.formgroupnf` fundo `rgb(39,40,34)`, labels
  `Label1` Arial 12px; V3 tabela usa zebra compartilhada. |
| Causa raiz | `show` V2 perdeu a identidade de formulario/grupos do 15.8.1 e o
  cabecalho operacional; o partial compartilhado de acoes foi empilhado no topo sem
  wrapper tematico. |
| Correcao proposta | Detalhe V2 read-only com cabecalho do RMA (numero/descricao/
  fabricante/modelo), posicao da acao compativel e grupos em grid Bootstrap historico:
  produto, origem/prioridade/protocolo/defeito, estoque/empresa/credito, datas,
  fiscal (venda/compra/remessa/retorno), destinatario/cliente/logistica, solucao,
  observacao e politicas. Nao duplicar Policy/CSRF/rotas. |
| Teste necessario | Feature/Blade: cabecalho e grupos criticos. Playwright: bounding
  boxes vs Legacy. |

## 4. PAR-V2-GEO-01 - listagens/aba Encaminhado do Tema V2

| Campo | Valor |
|---|---|
| Rota | Legacy `GET /15.8.1/` (indice) com `#encaminhado` ativo x V3 `GET /v2/rma`
  com `#encaminhado` ativo (mesmo par do print do dono) |
| Sintoma relatado | Conteudo/tabela "deslocado para baixo" em relacao ao Legacy. |
| Medicao 2048x1152 | Legacy: tabela y=47, `.SuperTr` y=47.5, segunda linha y=81.5,
  sidebar `#menuright` y=44, rodape y=512. V3: tabela y=40, `.SuperTr` y=40.5,
  segunda linha y=74.5, sidebar y=47, texto `.designedby` y=690.72. |
| Delta 2048x1152 | Tabela -7px (V3 acima, nao abaixo); sidebar +3px; corpo
  +27,44px (footer estende abaixo). |
| Medicao 1440x1000 | Mesmos deltas: tabela -7px, sidebar +3px. |
| Causa raiz | O shell portado nao reproduz o deslocamento vertical de 7px que no
  Legacy vem da camada `col-md-12` (padding-top 7px sobre `.tab-content`), e o
  rodape V2 tem dois paragrafos fora do container `.designedby` com y maior que o
  rodape historico. Nao foi reproduzido deslocamento para baixo; o delta real e de
  7px para cima. |
| Correcao proposta | Alinhar o conteudo das abas V2 a y=47 (7px) sem quebrar
  detalhe/edit, e reconferir rodape. |
| Teste necessario | Playwright de geometria por aba nos viewports do dono. |

## 5. PAR-V2-PESQ-01 - Pesquisar do Tema V2

| Campo | Valor |
|---|---|
| Rota | Legacy `GET /15.8.1/pesquisar` (fonte `page/pesquisar.php` +
  `inc/menu_pesquisar.php` + `subp/pesquisar_rma.php`) x V3 aba `#pesquisar` de
  `/v2/rma` |
| Sintoma | Composicao breadcrumb/"Pesquisar:"/input/botao nao corresponde ao Legacy:
  breadcrumb a esquerda em vez de direita, h3 grande demais e form empurrado para
  baixo, input e botao com cores claras em vez das historicas. |
| Evidencia 2048x1152 | Legacy: primeiro li do breadcrumb x=1088 (direita), h3
  y=91.2 h=15.59 (12px), form y=124.8, input x=427.89 fundo `rgb(45,45,45)`, botao
  x=638.78 fundo `rgb(51,51,51)`. V3: li x=444 (esquerda), h3 y=106.98 h=26.39
  (24px), form y=151.38, input x=444 fundo `#fff`, botao x=651 fundo `#efefef`. |
| Delta | h3 +10,8px de altura; form +26,58px para baixo; input/botao com cores
  invertidas em relacao ao tema escuro. |
| Causa raiz | Classes historicas ausentes no CSS V2 compilado:
  `.submenu-subpage`, `.boxtop-subpage`, `.boxtop-subpage h3`, `.box-subpage`,
  `.buttonSearch`, `.InputSeek` nao existem no bundle final do Vite
  (`public/build/assets/v2-*.css`). Sem `.submenu-subpage` o breadcrumb fica a
  esquerda; sem `.boxtop-subpage h3` o Bootstrap 3 aplica h3 24px; sem os overrides
  de input/botao o `form-control` branco do Bootstrap vence. A estrutura V3 tambem
  nao tem o wrapper `submenu-subpage` do Legacy. |
| Correcao proposta | Ajuste estrutural minimo em `_pesquisar_conteudo`/`_breadcrumb`
  (wrapper `submenu-subpage`) + port das regras V2 do 15.8.1 no SCSS do tema (apos o
  Bootstrap, como o Legacy carrega 15.8.1/15.9.7 depois do CDN). Nao usar
  `margin-top:-XXpx` sem causa. |
| Teste necessario | Playwright de bounding boxes da composicao Pesquisar + fontes/
  cores dos controles. |

## 6. Decisoes desta fase

- Nenhuma migration nova; nenhuma criacao de coluna.
- Show V1/V2 permanece separado de edit (arquitetura atual consciente).
- Partial compartilhado `_acoes_de_transicao` permanece como regra unica; cada tema
  ganha wrapper/posicionamento proprio.
- Regra de comparacao obedecida: 14.6.1 x Tema V1 e 15.8.1 x Tema V2.
- V3/T3-11 somente depois de A5/A6 verdes.
- PUSH NAO REALIZADO.
