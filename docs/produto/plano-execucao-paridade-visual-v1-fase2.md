# Plano de execução — paridade visual do Tema V1, fase 2

Data: 2026-08-25. Estado: **em execução (CP6 fechado)**. Continuação de
`docs/produto/plano-execucao-paridade-estrutural-v1.md` (CP0–CP5, **fechado** —
cascata/fontes, primitivas de tabela, as 4 listagens por status com resumo e
propagação). Esta fase cobre o restante da superfície do Tema V1 que os dois prompts
originais pediam e que CP0–CP5 não cobria: Página Inicial, Localizar, painel Novo,
Quadro de Anotações, sidebar de Contadores e Centro de Avisos.

Fonte da consolidação: seção 3 do parecer em
`docs/investigacoes-pendente/INV-RMA-BUG-LAYOUT-problemas-encontrados.md`.

Regra de gate (igual à fase 1): toda comparação entra no Diário de comparação abaixo.
Um item só recebe `[x]` depois de os prints Legacy/V3 normalizados terem sido abertos
e inspecionados, com ambiente e medidas registrados. Não reabrir CP0–CP5 sem print
sincronizado + `getBoundingClientRect()` que prove divergência nova (ver seção 1 do
parecer sobre a percepção de largura — já investigada e descartada como bug de CSS).

## Ambiente fixo

- Chromium; zoom 100%; `deviceScaleFactor: 1`; sem bloquear `fonts.googleapis.com`
  (decisão da fase 1, CMP-V1-004 — bloquear mascara a fonte real do Legacy).
- Viewport primária: 1440×1000; secundárias: 1562×1400 e 1700×1000.
- Legacy: `http://localhost:8094/14.6.1/`. V3: `http://localhost:8095`.
- Medidas: `getBoundingClientRect()`, computed style e CDP
  `CSS.getPlatformFontsForNode` para fonte realmente rasterizada.
- Specs de paridade do V1 rodam no host (não no container — ver
  `docs/produto/handoff-sessao-2026-08-25.md`, "Topologia importante dos testes").
  Scripts de diagnóstico Playwright são ferramentas descartáveis (criar em
  `scripts/_tmp-*.mjs`, rodar, apagar depois — não versionar), como já praticado em
  CMP-V1-002 a CMP-V1-007.

## CP6 — Página Inicial sem conteúdo artificial

- [x] CP6-01 — ler `legacy-source/14.6.1/inc/startpage.php` por inteiro (topo da
      página, antes do quadro de anotações).
- [x] CP6-02 — medir V3 atual: confirmar que `rma/index.blade.php` renderiza H1 "RMAs"
      e link "Novo RMA" que não existem no Legacy.
- [x] CP6-03 — remover "RMAs"/"Novo RMA" da composição visual (H1 pode virar
      `sr-only`, mantendo semântica; o atalho Novo já existe no menu superior).
- [x] CP6-04 — medir a distância header→primeira superfície útil (Localizar) nos dois
      lados; confirmar que o V3 não tem mais os ~45-50px extras artificiais.
- [x] CP6-05 — capturar/reabrir/comparar Página Inicial, registrar diário.
- [x] CP6-06 — testes focados/build e commit local.

### CMP-V1-2-001 — CP6, Página Inicial sem conteúdo artificial

- Ambiente: Chromium headless (Playwright), zoom 100%, DPR 1, viewport 1440×1000, sem
  bloquear fontes remotas. Specs rodadas do host (`PLAYWRIGHT_BASE_URL`/
  `LEGACY_BASE_URL` explícitos — ver nota operacional da fase 1/CMP-V2-008 sobre por
  que este spec não roda via `docker compose exec`).
- Fonte: `legacy-source/14.6.1/inc/startpage.php` lido por inteiro — a Página Inicial
  não tem H1/heading nenhum, começa direto por `<div style="clear:both;
  padding-top:12px;"></div>` seguido do painel Localizar (já aberto, `display:block`
  via script inline). Também não existe link "Novo RMA" próprio — o atalho "Novo" já
  é item do menu superior (`inc/topmenu.php`, já portado em `layout.blade.php`).
- **Achados reais, corrigidos:**
  1. `rma/index.blade.php` tinha um `<p><a>Novo RMA</a></p>` sem fonte no Legacy,
     duplicando o item "Novo" do menu — removido.
  2. `layout.blade.php` renderizava sempre o H1 `$titulo` ("RMAs") dentro de
     `#CONTEUDO` na Página Inicial — sem equivalente no Legacy. Reaproveitado o
     mecanismo `$ocultarTituloVisual` já existente (criado em VIS-V1-007 para
     `/rmas/create`), estendendo para `rmas.index`/`v1.rmas.index` — H1 continua no
     DOM (`sr-only`, `position:absolute`), só some visualmente.
- Tabela de medidas (`getBoundingClientRect`, Legacy × V3, antes/depois desta
  correção):

  | Medida | Legacy | V3 antes | V3 depois | Delta depois |
  |---|---|---|---|---|
  | `#TOPO`/header, `bottom` | `51` | `51` | `51` | `0` |
  | H1 visível | ausente | `26px` de altura, `position:static` | `position:absolute` (sem altura no fluxo) | — |
  | `#JS-Localizar`/`.JS-Localizar`, `top` | `65` | `123` | `65` | `0` |
  | gap header→Localizar | `14px` | `72px` | `14px` | `0` |
- Diferença perceptível restante: nenhuma na composição do topo da Página Inicial. A
  parte funcional do painel Localizar (só 1 select "tipo" + 1 input + botão, contra os
  2 selects históricos SOLUÇÃO/CAMPO + input + botão do Legacy) é escopo do CP7, não
  deste item.
- Screenshots versionados (sem dado real de cliente/produto — só contadores agregados
  e texto de teste, mesma régua já aplicada às evidências desta sessão):
  `docs/produto/screenshots-vis-v1-001/{17-legacy-pagina-inicial-sem-heading-artificial,18-v3-pagina-inicial-cp6-corrigido}.png`.
- Decisão: **CP6 APROVADO**.
- Testes/build: `php artisan test` (363/818, verde); `npm run build` (ok);
  `ParidadeVisualTemaV1.spec.ts` rodado do host (4/4, verde).
- Commit: a seguir (`#ARQ-RMA - Remove o titulo e o link artificiais da pagina inicial do tema V1`).

## CP7 — Localizar como painel inline histórico

Maior item desta fase. Fonte:
`legacy-source/14.6.1/menujs-top/localizar.php`, `pattern/14.6.1.css`,
`pattern/14.6.1.js`.

- [x] CP7-01 — ler os 3 arquivos fonte por inteiro.
- [x] CP7-02 — medir geometria real no Legacy via `getBoundingClientRect()` +
      computed style (não assumir os valores do prompt sem medir):
      `#JS-Localizar` (`min-height:25px; padding:10px; margin-bottom:10px`),
      `.JSformLocalizarInput` (`width:422px; height:30px; padding:10px;
      font-size:18px; letter-spacing:1px`), `.JSformLocalizarSelect`
      (`margin-left:15px; height:52px; font-size:12px`),
      `.JSformLocalizarButton` (`height:52px; width:100px; margin-left:15px;
      font-size:14px; letter-spacing:1px; background:#106D78`).
- [x] CP7-03 — extrair partial `resources/views/temas/v1/rma/_form_localizar.blade.php`
      (mesmo padrão consciente já usado para `_form_novo.blade.php`, incluído uma vez
      pelo layout, sem duplicar entre páginas).
- [x] CP7-04 — montar select SOLUÇÃO com as opções históricas: `QUALQUER UMA SOLUCAO`,
      `GERADO CREDITO`, `SEM GARANTIA`, `REPARO`, `TROCA DO PRODUTO`,
      `TROCA DE PECA INTERNA`, `DEVOLUCAO DO PRODUTO`, `REEMBOLSO DO DINHEIRO`,
      `REPARO PELO RMA`, `TESTADO TUDO OK`, `ORCAMENTO PAGO`, `PROCON` — mapear cada
      rótulo para o valor real do enum `Solucao` do domínio (não inventar valor).
- [x] CP7-05 — montar select CAMPO com as opções históricas: `TODOS OS CAMPOS`,
      `ORDEM DE SERVICO`, `FABRICANTE`, `DESCRICAO`, `S/N, P/N OR ID/SNID/ETC`,
      `MODELO`, `ORIGEM`, `EMPRESA`, `CLIENTE`, `CODIGO DE RASTREIO`, `PROTOCOLO`,
      `NF`, `DESTINATARIO`, `CHAVE` — mapear para os critérios que o caso de uso de
      busca atual realmente aceita; documentar `[BUG-LEGADO]`/gap se algum campo
      histórico não tiver equivalente moderno (mesmo tratamento dado a "NF R" na
      fase 1 — não simular busca que não funciona).
- [x] CP7-06 — adapter na camada de apresentação/aplicação que traduz os parâmetros
      da UI V1 para o caso de uso de busca existente; não portar parâmetros HTTP
      antigos para o domínio, não colocar query no Blade.
- [x] CP7-07 — comportamento inline: `#JS-Localizar` presente no DOM, oculto por
      padrão exceto na Página Inicial (que já inicia com Localizar aberto no
      Legacy), clique em "Localizar" expõe o painel e deixa o item do menu em
      negrito, sem navegar — mesmo padrão do `NovoMaximize()` já portado para Novo.
- [x] CP7-08 — não introduzir jQuery só para isso; reaproveitar o padrão vanilla já
      usado no toggle do painel Novo.
- [x] CP7-09 — capturar/reabrir/comparar geometria completa (outerWidth/outerHeight
      de cada campo), registrar diário.
- [x] CP7-10 — testes focados/build e commit local.

### CMP-V1-2-002 — CP7, Localizar como painel inline histórico

- Ambiente: Chromium headless (Playwright), zoom 100%, DPR 1, viewport 1440×1000, sem
  bloquear fontes remotas. Spec de regressão rodada do host.
- Fonte: `menujs-top/localizar.php` (42 linhas, lido por inteiro), `pattern/14.6.1.css`
  (seletores `.JS-Localizar`/`.JSformLocalizar*`), `pattern/14.6.1.js`
  (`LocalizarMaximize()`).
- **Achado de ordem visual (confirmado por medição, não por suposição):** o HTML fonte
  tem `input(fl)` seguido de 3 blocos `float:right` na ordem
  botão/CAMPO/SOLUÇÃO — com `float:right`, cada bloco novo entra à ESQUERDA do
  anterior, então a ordem visual final é `input, SOLUÇÃO, CAMPO, FILTRAR` (confirmado
  nos dois lados via `getBoundingClientRect().x` de cada elemento).
- **Achado real, corrigido:** `.JSformLocalizarSelect` nunca tinha sido portada para
  `_v1-base.scss` (só existia como classe morta, nenhum `<select>` a usava antes desta
  correção) — sem ela, os selects renderizavam com a altura padrão do browser (24px)
  em vez de `height:52px`, e sem o `margin-left:15px` que define o espaçamento entre
  os 3 blocos flutuados. Adicionada com os valores medidos no Legacy.
- **Achado de arquitetura:** o painel Localizar antes só existia dentro de
  `rma/index.blade.php` (só a Página Inicial tinha o form). Portado para
  `temas/v1/layout.blade.php` como painel global (`#JS-Localizar`, sempre no DOM,
  oculto por padrão), mesmo padrão já usado por `#JS-Novo`/`_form_novo.blade.php` —
  agora abre inline em QUALQUER página (ex.: Entrada) sem navegar, clicando
  "Localizar" no menu superior, replicando `LocalizarMaximize()` (que não chama
  `MinimizeMenuRight()`, omissão preservada).
- **Adapter de busca (`RmaController::index()`):** `campo`→tipo de busca com encaixe
  literal onde existe (`os`→`nota_fiscal`, mesma coluna; `SNPNSNID`→`serial`, só a
  coluna `sn` — `pn`/`snid` sem cobertura, `[GAP]`); os campos sem coluna de busca
  própria no schema atual (`fabricante`/`cliente`/`destinatario`/`rastreio_ida`/
  `protocolo`/`NF`/`numero`) caem no fallback `texto` (mesmo tratamento já aceito
  para `os` antes desta fase) — `[GAP]` documentado no código, rótulo histórico
  preservado, comportamento real não inventado. `solucao` virou filtro aditivo
  genuíno: `CriterioDeBusca` ganhou um 3º parâmetro opcional (`?Solucao`),
  `RmasEmBanco::buscar()` aplica `WHERE solucao = ?` quando presente — testado
  funcionalmente (`solucao=REPARO` retornou 12 linhas, batendo com o contador da
  sidebar). Mudança 100% aditiva (parâmetro opcional, default `null`) — TEMA V2 (que
  também usa `RmaController::index()`) continua com o comportamento antigo intacto.
- Tabela de medidas (`getBoundingClientRect`/computed style, Legacy × V3, viewport
  1440×1000):

  | Elemento | Legacy | V3 antes | V3 depois | Delta depois |
  |---|---|---|---|---|
  | `#JS-Localizar` (`x,y,width,height`) | `228,65,984,72` | painel não existia fora do índice | `228,65,984,72` | `0` |
  | `.JSformLocalizarInput` (`x,y,width,height`) | `240,75,444,52` | idêntico (já existia) | `240,75,444,52` | `0` |
  | select CAMPO (`x,width,height`) | `901,186,52` | `901,186,24` | `901,186,52` | `0` |
  | select SOLUÇÃO (`x,width,height`) | `700,186,52` | `715,186,24` | `700,186,52` | `0` |
  | `.JSformLocalizarButton` (`x,y,width,height`) | `1102,75,100,52` | idêntico (já existia) | `1102,75,100,52` | `0` |
  | toggle em página não-índice (Entrada) | painel oculto→visível ao clicar, sem navegar | painel inexistente fora do índice | oculto→visível ao clicar, sem navegar (`url` inalterada) | `0` |
- Diferença perceptível restante: nenhuma na geometria/composição do painel. Gaps de
  precisão de busca por campo documentados inline (`[GAP]`), não fabricados.
- Screenshots versionados (fictício QA + contadores agregados, mesma régua já usada
  nesta sessão): `docs/produto/screenshots-vis-v1-001/{19-legacy-localizar-pagina-inicial,20-v3-localizar-pagina-inicial-cp7,21-v3-localizar-toggle-inline-entrada}.png`.
- Decisão: **CP7 APROVADO**.
- Testes/build: `php artisan test` (363/818, verde, antes e depois da extensão de
  `CriterioDeBusca`); `npm run build` (ok); `ParidadeVisualTemaV1.spec.ts` rodado do
  host (4/4, verde); checagem funcional dedicada do filtro `solucao` (12 linhas para
  `REPARO`, sem exceção).
- Commit: a seguir (`#ARQ-RMA - Reconstroi o painel Localizar inline do tema V1 com geometria e filtros reais`).

## CP8 — Painel Novo: divergências visuais restantes

A mecânica inline (abrir sem navegar, manter a tela abaixo, POST funcional) já está
correta e **não deve ser mexida**. Fonte:
`legacy-source/14.6.1/menujs-top/novo.php`, `pattern/15.9.7.css`.

- [x] CP8-01 — ler `novo.php` e a folha `15.9.7.css` (seção de checkbox/toggle) por
      inteiro.
- [x] CP8-02 — portar o toggle histórico do campo "item é do estoque": `label` com
      `data-text-true="O ITEM E DO ESTOQUE"`/`data-text-false="ITEM NAO E DO
      ESTOQUE"`, `<i></i>` deslizante, `background-color:#DB574D` desmarcado /
      `#67B04F` marcado, `width:475px; height:30px`. Escopar a regra ao painel Novo
      do TEMA V1 (não alterar outros checkboxes do sistema). Preservar semântica de
      envio (marcado=true/desmarcado=false).
- [x] CP8-03 — trocar `type="date"` por `type="text" placeholder="00/00/2015"` nos
      campos de data; converter `dd/mm/YYYY` → formato interno na camada HTTP antes
      de validar/persistir, sem enfraquecer validação; não alterar TEMA V2.
- [ ] CP8-04 — **deixado em aberto, não implementado.** Trocar `<select
      name="fabricante_id">` por input/datalist exigiria `EncontrarOuCriarFabricante`
      em runtime de criação — mas o docblock dessa classe diz explicitamente que ela é
      "SÓ pelo migrador" e que a criação em runtime "continua exigindo fabricante de
      uma lista já cadastrada" (decisão de uma fase anterior). Implementar isso às
      cegas reverteria essa decisão sem revalidar com quem a tomou — ver nota em
      `_form_novo.blade.php`.
- [x] CP8-05 — auditar `box-sizing:border-box` introduzido pelo V3 em
      `novo_formInput`, `novo_formInputDATE`, `novo_formInputSmall`, `novo_defeito`,
      `formInputObservacao`: medir `outerWidth`/`outerHeight` Legacy×V3 para cada
      um; se o legado não tinha `border-box` e isso muda a geometria, reproduzir o
      box model histórico (a regra é `outerWidth`/`outerHeight` iguais, não "qual
      prática é mais moderna").
- [x] CP8-06 — capturar/reabrir/comparar painel Novo completo, registrar diário.
- [x] CP8-07 — testes focados/build e commit local.

### CMP-V1-2-003 — CP8, painel Novo: checkbox, datas, box-sizing

- Ambiente: Chromium headless (Playwright), zoom 100%, DPR 1, viewport 1440×1000, sem
  bloquear fontes remotas. Spec de regressão rodada do host.
- Fonte: `menujs-top/novo.php` (146 linhas, lido por inteiro), `pattern/15.9.7.css:
  286-296` (toggle de checkbox, regra global `input[type=checkbox] + label`),
  `pattern/14.6.1.css:109-111,181-182` (5 classes de campo, nenhuma com
  `box-sizing` declarado).
- **Achado real, corrigido:** as 5 classes de campo (`novo_formInput`,
  `novo_formInputDATE`, `novo_formInputSmall`, `novo_defeito`,
  `formInputObservacao`) tinham `box-sizing:border-box` adicionado pelo V3, sem
  fonte no Legacy (que roda em `content-box`, o padrão do browser da época — nenhuma
  das duas folhas de CSS originais declara a propriedade). Isso reduzia a largura
  renderizada em padding+border (2-6px conforme a classe) — removido; `outerWidth`
  bateu exatamente nos dois lados depois (134px/80px/397px/397px, confirmado via
  `getBoundingClientRect()`).
- **Achado de escopo:** o toggle de checkbox (`input[type=checkbox] + label`) é uma
  regra GLOBAL no Legacy (sem classe própria) — portá-la sem escopo afetaria
  qualquer outro checkbox+label do sistema (login, TEMA V2 etc.). Escopada a
  `#JS-Novo input[type="checkbox"] + label` em `_v1-base.scss`.
- **Achado real, corrigido — regressão introduzida nesta mesma sessão (CP6):** ao
  clicar "Novo" na Página Inicial pra medir o painel deste checkpoint, o clique
  NAVEGAVA pra `/rmas/create` em vez de abrir o painel inline — `#JS-Novo` não
  existia mais no DOM da Página Inicial. Causa raiz: CP6 tinha reaproveitado
  `$ocultarTituloVisual` (criada só pra esconder o H1) também como condição pra
  renderizar `#JS-Novo` (`@unless` em `layout.blade.php`); ao somar `rmas.index`/
  `v1.rmas.index` a essa flag (pra esconder o H1 da Home), o painel Novo global
  sumiu junto, sem querer. Corrigido com uma flag própria
  (`$omitirPainelNovoGlobal`, só `/rmas/create`) — H1 continua oculto na Home, painel
  Novo global continua presente. Teste de regressão dedicado adicionado
  (`RenderizaTemaV1Test::test_painel_novo_global_continua_presente_na_pagina_inicial`)
  pra este cenário específico não voltar a quebrar silenciosamente.
- **CP8-04 (fabricante) deixado em aberto**, não implementado — `EncontrarOuCriarFabricante`
  existe mas seu próprio docblock diz que é só pro migrador; a criação em runtime
  "continua exigindo fabricante de uma lista já cadastrada" por decisão de fase
  anterior. Implementar às cegas reverteria essa decisão sem revalidação — registrado
  como pendência explícita, não como gap silencioso.
- Tabela de medidas (`getBoundingClientRect`, Legacy × V3):

  | Elemento | Legacy | V3 antes | V3 depois | Delta depois |
  |---|---|---|---|---|
  | `.novo_formInput[name=descricao]` outerWidth | `134px` | `134px` (border-box já rendia igual aqui por coincidência de %) | `134px` | `0` |
  | `.novo_formInputDATE` outerWidth | `80px` | `80px` | `80px` | `0` |
  | `.novo_defeito`/`.formInputObservacao` outerWidth | `397px` | `397px` | `397px` | `0` |
  | toggle `label` (`width,height,bg` marcado) | `475px,30px,rgb(103,176,79)` | inexistente (checkbox sem estilo) | `475px,30px,rgb(103,176,79)` | `0` |
  | toggle `::before`/`::after` content | `"O ITEM E DO ESTOQUE"`/`"ITEM NAO E DO ESTOQUE"` | inexistente | idêntico | `0` |
  | painel Novo presente no DOM da Página Inicial | sim (`#JS-Novo` global) | **não** (regressão CP6) | sim | `0` |
- Diferença perceptível restante: nenhuma na geometria/estilo do formulário. Campo
  Fabricante continua `<select>` FK (CP8-04, pendência explícita); autocomplete de
  Descrição/Origem/Modelo/Empresa continua fora de escopo (decisão já registrada em
  VIS-V1-003).
- Screenshots versionados (fictício QA, mesma régua desta sessão):
  `docs/produto/screenshots-vis-v1-001/{22-legacy-novo-painel-checkbox-toggle,23-v3-novo-painel-cp8-corrigido}.png`.
- Decisão: **CP8 APROVADO**, com CP8-04 explicitamente pendente (não bloqueante,
  documentado).
- Testes/build: `php artisan test` (364/820, verde — 1 teste novo de regressão);
  `npm run build` (ok); `ParidadeVisualTemaV1.spec.ts` rodado do host (4/4, verde).
- Commit: a seguir (`#ARQ-RMA - Corrige o checkbox as datas e o box model do painel Novo do tema V1`).

## CP9 — Quadro de Anotações

Fonte: `legacy-source/14.6.1/inc/startpage.php`, `pattern/14.6.1.css`.

- [x] CP9-01 — ler a seção do quadro de anotações nos dois arquivos por inteiro.
- [x] CP9-02 — medir geometria real no Legacy: container ~675px, `margin-left:1px`,
      textarea `rows=20` width ~674px, `padding:5px`, `font-size:12px`,
      `letter-spacing:1px`, `line-height:1.5` (medir para confirmar, não assumir).
- [x] CP9-03 — trocar `rows="14"` por `rows="20"` e ajustar a geometria do
      container/textarea para bater com o medido.
- [x] CP9-04 — restaurar o estilo real do título (`panotacao`/`imganotacao`): ícone
      deslocado com margins negativas, `margin-top:-16px`, `padding:10px`,
      `letter-spacing:3px`, `font-weight:bold` — não reduzir a
      `.quadro-de-anotacoes-titulo { font-weight:300; }` genérico atual.
- [x] CP9-05 — remover o botão "Salvar anotação" da composição visual (o Legacy
      salva durante a digitação, sem botão). Implementar salvamento moderno:
      evento de input/change na textarea, debounce, `fetch` para o endpoint Laravel
      existente, CSRF, tratamento de erro discreto — sem portar o AJAX antigo.
- [x] CP9-06 — capturar/reabrir/comparar quadro de anotações, registrar diário.
- [x] CP9-07 — testes focados/build e commit local.

### CMP-V1-2-004 — CP9, Quadro de Anotações

- Ambiente: Chromium headless (Playwright), zoom 100%, DPR 1, viewport 1440×1000, sem
  bloquear fontes remotas. Spec de regressão rodada do host.
- Fonte: `startpage.php` linhas 10-15 (título+textarea), `pattern/14.6.1.css:65-67`
  (`.panotacao`/`.imganotacao`/`.textareaanotacao`). `startpage.php` sobrepõe `style`
  inline em cima das classes (`.panotacao` vira `border:0;width:664px;margin-top:
  -16px`; a textarea vira `display:block;border:0`) — os dois medidos e embutidos
  como valor final das classes em `_v1-base.scss` (mesmo critério já usado pro
  `#menuright` do TEMA V2, CP18), sem inline no Blade.
- **Achado real, corrigido:** `.quadro-de-anotacoes-titulo` era uma classe genérica
  V3 (`font-weight:300`) sem relação com as classes reais do Legacy — trocada pelas
  classes originais `.panotacao`/`.imganotacao` com os valores medidos
  (`padding:10px`, `letter-spacing:3px`, `font-weight:700`, `margin-top:-16px`,
  ícone com margins negativas pra sobrepor o texto). `.textareaanotacao` trocou
  `border:1px solid`/fundo sólido `#26251f` por `border:0`/fundo
  `rgba(0,0,0,0.1)` (real), `rows="14"`→`rows="20"`.
- **Achado de comportamento, implementado como equivalente moderno (não port
  literal):** o Legacy salva a cada `onkeyup` via AJAX próprio (código não
  disponível/não portado); botão "Salvar anotação" removido da composição (sem
  fonte no Legacy). Autosave novo: debounce de 800ms + `fetch` PUT pro mesmo
  endpoint que o formulário tradicional do perfil já usa
  (`identidade.perfil.anotacao.update`) — `AnotacaoPessoalController::update()`
  ganhou uma resposta JSON condicional (`$request->wantsJson()`), aditiva, o form
  tradicional do perfil continua recebendo o redirect de sempre. Erro de rede/HTTP
  marca a textarea com uma borda vermelha sutil (`.textareaanotacao--erro`), sem
  alert/modal.
- Verificação funcional (não só visual): digitado texto na textarea, capturada a
  requisição `PUT .../perfil/anotacao` (`X-CSRF-TOKEN`/`Accept:application/json`
  corretos), recarregada a página numa aba nova — texto persistiu, confirmando o
  ciclo completo `fetch`→controller→banco→re-render. Conta de teste resetada
  (`anotacao=null`) depois da verificação.
- Tabela de medidas (`getBoundingClientRect`/computed style, Legacy × V3):

  | Elemento | Legacy | V3 antes | V3 depois | Delta depois |
  |---|---|---|---|---|
  | `.panotacao` (`width,height,padding,bg,letterSpacing,fontWeight,marginTop`) | `684,34,10px,rgba(0,0,0,.1),3px,700,-16px` | classe genérica, sem essas propriedades | `684,37,10px,rgba(0,0,0,.1),3px,700,-16px` | `0` (altura varia ±3px por causa da fonte real vs fallback, tolerado) |
  | `.textareaanotacao` (`width,padding,bg,letterSpacing`) | `684,5px,rgba(0,0,0,.1),1px` | `681,2px,rgb(38,37,31),normal` | `684,5px,rgba(0,0,0,.1),1px` | `0` |
  | `rows` | `20` | `14` | `20` | `0` |
  | botão "Salvar anotação" | ausente | presente | ausente | `0` |
- Diferença perceptível restante: nenhuma na geometria/estilo do quadro. O AJAX
  antigo do Legacy não foi portado literalmente (decisão já registrada no plano —
  equivalente moderno, não port 1:1).
- Screenshots versionados (fictício/local — "Conta local; nao pertence ao dump
  historico", mesma régua desta sessão):
  `docs/produto/screenshots-vis-v1-001/{24-legacy-quadro-anotacoes,25-v3-quadro-anotacoes-cp9-corrigido}.png`.
- Decisão: **CP9 APROVADO**.
- Testes/build: `php artisan test` (364/820, verde); `npm run build` (ok);
  `ParidadeVisualTemaV1.spec.ts` rodado do host (4/4, verde).
- Commit: a seguir (`#ARQ-RMA - Restaura o quadro de anotacoes com salvamento automatico no tema V1`).

## CP10 — Sidebar de contadores

Fonte: `legacy-source/14.6.1/inc/startpage.php`, `pattern/14.6.1.css`.

- [x] CP10-01 — ler a seção da sidebar nos dois arquivos por inteiro.
- [x] CP10-02 — medir geometria real no Legacy: container `width:280px; float:right;
      margin-right:-8px; margin-top:-15px`; `.formLabelStats` `width:198px;
      padding:5px; border:1px` (sem `border-box`); `.formInputStats` `width:45px;
      padding:5px; border:1px` (sem `border-box`).
- [x] CP10-03 — remover `box-sizing:border-box` desses elementos se a medição
      confirmar que altera a geometria; reproduzir semanticamente com
      `<p class="formLabelStats">`/`<input class="formInputStats" disabled>` ou
      produzir os mesmos `outerWidth`/`outerHeight` por outro meio.
- [x] CP10-04 — confirmar/restaurar que cada contador é link (`<a>`) para a
      listagem/filtro correspondente: `ENTRADA`→Entrada, `PENDENTE CREDITO`→
      Aguardando crédito, `ENCAMINHADO`→Encaminhado, `CONCLUIDO`→Concluído, e os
      filtros por solução (`SEM GARANTIA`, `GERADO CREDITO` etc.) para onde o
      Legacy realmente aponta — mapear caso a caso, não assumir.
- [x] CP10-05 — capturar/reabrir/comparar sidebar, registrar diário.
- [x] CP10-06 — testes focados/build e commit local.

### CMP-V1-2-005 — CP10, sidebar de contadores

- Ambiente: Chromium headless (Playwright), zoom 100%, DPR 1, viewport 1440×1000, sem
  bloquear fontes remotas. Spec de regressão rodada do host.
- Fonte: `startpage.php:17-176` (16 contadores, já lido por inteiro nos CP6/CP9),
  `pattern/14.6.1.css:74-76` (`.formLabelStats`/`.formInputStats`, nenhuma com
  `box-sizing` declarado).
- **Achado real, corrigido (mesmo padrão do CP8):** `.formLabelStats`/
  `.formValorStats` tinham `box-sizing:border-box` no V3 — Legacy roda em
  `content-box`, então `outerWidth` real é `width+padding×2+border×2`
  (198+10+2=210px pro rótulo, 45+10+2=57px pro valor), não a largura declarada
  crua. Corrigido; `outerWidth` bateu exato nos dois lados depois. Container ganhou
  `margin-right:-8px;margin-top:-15px` (ausentes, só tinha `float:right;width:280px`)
  — `x` do container bateu exato (`940px`) depois da correção.
- **Achado real, corrigido — o maior desta rodada:** nenhum dos 16 contadores era um
  link antes desta correção (só `<p>` estático) — `[link_do_contador_v1()]` novo em
  `app/Support/view_do_tema.php` mapeia rótulo→destino: os 4 primeiros
  (`ENTRADA`/`PENDENTE CREDITO`/`ENCAMINHADO`/`CONCLUIDO`) pras 4 rotas dedicadas já
  existentes (`rmas.entrada` etc., sem prefixo de tema — mesmo critério já usado em
  `temas/v2/layout.blade.php` pra rotas sem variante `v1.`/`v2.`); os 11 de solução
  pro Localizar com `solucao=X` (filtro aditivo do CP7). Testado funcionalmente:
  clique em "REPARO" navegou pra `?solucao=REPARO` e retornou 12 linhas, batendo com
  o valor mostrado no próprio contador.
- **Achado de estrutura, confirmado como fiel ao Legacy (não é bug):** o `<a>` que
  envolve `.formLabelStats`/`.formValorStats` colapsa pra `width:0;height:0` nos dois
  lados (Legacy e V3) — os filhos são `float:left` sem clearfix no próprio `<a>`.
  Confirmado que o Legacy tem exatamente o mesmo comportamento (medido
  `getBoundingClientRect()` do `<a>` real em produção) — clique funciona porque cai
  nos filhos visíveis, que borbulham o evento pro `<a>`. Não "corrigido" com
  clearfix — seria modernizar além do que o Legacy realmente faz.
- **`[GAP]` documentado (não bloqueante):** "QUANTIDADE TOTAL DE ITENS" no Legacy usa
  `solucao=%` (curinga SQL) pra listar todo o banco sem filtro; a busca V3 não tem
  modo "listar tudo sem filtro" — aponta pro Localizar vazio em vez de reproduzir a
  listagem completa (ver docblock de `link_do_contador_v1()`).
- Tabela de medidas (`getBoundingClientRect`, Legacy × V3):

  | Elemento | Legacy | V3 antes | V3 depois | Delta depois |
  |---|---|---|---|---|
  | container (`x,width,marginRight,marginTop`) | `940,280,-8px,-15px` | `932,280,0,0` | `940,280,-8px,-15px` | `0` |
  | `.formLabelStats` outerWidth | `210px` | `198px` | `210px` | `0` |
  | `.formValorStats`/`.formInputStats` outerWidth | `57px` | `45px` | `57px` | `0` |
  | contador é link funcional | sim (16/16) | não (0/16) | sim (16/16) | `0` |
  | clique em "REPARO" → linhas retornadas | (histórico, não comparável 1:1) | N/A | `12` (bate com o contador) | — |
- Diferença perceptível restante: nenhuma na geometria/estilo/comportamento de link.
  `[GAP]` de "QUANTIDADE TOTAL DE ITENS" documentado, não bloqueante.
- Screenshots versionados (fictício QA, mesma régua desta sessão):
  `docs/produto/screenshots-vis-v1-001/{26-legacy-sidebar-contadores,27-v3-sidebar-contadores-cp10-corrigido}.png`.
- Decisão: **CP10 APROVADO**.
- Testes/build: `php artisan test` (364/820, verde); `npm run build` (ok);
  `ParidadeVisualTemaV1.spec.ts` rodado do host (4/4, verde).
- Commit: a seguir (`#ARQ-RMA - Restaura os links reais da sidebar de contadores do tema V1`).

## CP11 — Separador antes do Centro de Avisos

- [x] CP11-01 — localizar `separador2.png` no repositório Legacy
      (`legacy-source/images/`), igual ao já feito para os ícones das 4 listagens.
- [x] CP11-02 — portar para `public/images/tema-v1/`, validar hash byte a byte.
- [x] CP11-03 — inserir com `float:right; margin-top:50px; height:40px` e o
      `clear`/`hr` que o Legacy usa antes/depois, na posição real (entre
      anotação/contadores e o Centro de Avisos).
- [x] CP11-04 — capturar/reabrir/comparar, registrar diário.
- [x] CP11-05 — testes focados/build e commit local (pode ser junto do CP12 se os
      dois ficarem pequenos o suficiente para um commit coerente).

### CMP-V1-2-006 — CP11, separador antes do Centro de Avisos

- Ambiente: Chromium headless (Playwright), zoom 100%, DPR 1, viewport 1440×1000.
  Spec de regressão rodada do host.
- Fonte: `startpage.php:182` (`<img style="margin-top:50px;float:right;"
  src=".../separador2.png" height="40"/>`). `separador2.png` já tinha sido portado e
  hash-verificado pro TEMA V2 (CP11/CMP-V2-*) — mesmo arquivo byte-idêntico
  (`md5sum f9d3ecd2...`), só copiado pra `public/images/tema-v1/` (não há por que
  baixar/verificar de novo um asset já confirmado nesta sessão).
- Sem achado de divergência: a classe nova (`.separador2-inicial`) só precisou de
  `float:right;margin-top:50px` — `height="40"` já vem do atributo HTML, igual ao
  Legacy.
- Tabela de medidas (`getBoundingClientRect`, Legacy × V3):

  | Elemento | Legacy | V3 | Delta |
  |---|---|---|---|
  | `x,width,height` | `519,693,40` | `519,693,40` | `0` |
  | `margin-top`/`float` | `50px`/`right` | `50px`/`right` | `0` |
  | `y` | `711` | `648` | `63px` (esperado — conteúdo acima difere em altura por causa do dado real vs fictício, não é defeito de CSS) |
- Screenshot versionado (fictício QA):
  `docs/produto/screenshots-vis-v1-001/28-v3-separador-antes-centro-de-avisos-cp11.png`.
- Decisão: **CP11 APROVADO**.
- Testes/build: `php artisan test` (364/820, verde); `npm run build` (ok);
  `ParidadeVisualTemaV1.spec.ts` rodado do host (4/4, verde).
- Commit: a seguir (`#ARQ-RMA - Insere o separador antes do centro de avisos na pagina inicial do tema V1`).

## CP12 — Centro de Avisos

Fonte: `legacy-source/14.6.1/inc/startpage.php` (lista de includes) e cada
`subp/listar_*.php` referenciado por ele.

- [x] CP12-01 — mapear a lista COMPLETA de includes de `startpage.php` até o fim do
      arquivo (os prompts originais só citam os 10 primeiros — não parar aí),
      registrando a ordem exata.
- [x] CP12-02 — para cada `subp/listar_*.php`: ler o arquivo e classificar a
      apresentação real (lista genérica ícone+título+Mostrar+itens / tabela com
      colunas próprias / mensagem "Nenhum item foi encontrado" quando vazio).
- [x] CP12-03 — comparar com `ListarGruposDeAlertas::listar()` e
      `_centro_de_avisos.blade.php` atuais: confirmar quais grupos existem, faltam
      ou estão fora de ordem.
- [x] CP12-04 — criar presenter/ordenação específica da apresentação V1 que respeite
      a ordem histórica, sem alterar a ordem usada por outros consumidores do caso
      de uso (ex.: TEMA V2), se eles dependerem de ordem diferente.
- [ ] CP12-05 — **deixado em aberto, não implementado — ver justificativa no
      diário.** Classificação completa (CP12-02) feita e documentada; a
      implementação (10 presenters + read-models) fica pra uma frente futura
      dedicada.
- [x] CP12-06 — verificar estado inicial real (Mostrar/Ocultar) de cada grupo no
      runtime Legacy; não assumir que todos começam ocultos — reproduzir por grupo.
- [x] CP12-07 — capturar/reabrir/comparar Centro de Avisos completo (todos os
      grupos), registrar diário.
- [x] CP12-08 — testes focados/build e commit local.

### CMP-V1-2-007 — CP12, ordem/títulos do Centro de Avisos + classificação completa

- Ambiente: Chromium headless (Playwright), zoom 100%, DPR 1, viewport 1440×1000, sem
  bloquear fontes remotas. Spec de regressão rodada do host.
- Fonte: `startpage.php:192-230` (10 includes, já lido por inteiro nos CP6/9/10/11 —
  confirmado de novo aqui: exatamente 10, nenhum a mais até o fim do arquivo) + os 10
  `subp/listar_*.php` (`legacy-source/15.8.1/subp/`, ~100 linhas cada, lidos/varridos
  por inteiro nesta rodada). **Achado que reduziu o trabalho de CP12-01/02/03:** V1 e
  V2 incluem os MESMOS 10 arquivos-fonte (`startpage.php` inclui de
  `"../15.8.1/subp/..."`, o diretório do próprio TEMA V2) — a investigação de
  ordem/título já feita e aprovada pro V2 (CP22/`CMP-V2-005`) é literalmente a mesma
  pro V1, sem necessidade de reinvestigar do zero.
- **Achado real, corrigido (mesmo padrão do CP22 do V2):** `rma/index.blade.php`
  passava `$grupos` direto de `ListarGruposDeAlertas::listar()` pro partial
  compartilhado — chaves descritivas da Fase 5, ordem própria, nenhuma batendo com
  `startpage.php`. Corrigido com o MESMO array de reordenação/relabel já provado no
  V2 (`$ordemHistoricaCentroDeAvisosV1`, literal idêntico ao
  `$ordemHistoricaCentroDeAvisosV2`), duplicado nesta view por decisão consciente —
  não alterar `ListarGruposDeAlertas` (compartilhada com `PainelDeAlertasController`
  e o TEMA V2), mesma justificativa já registrada no CP22. "Urgência por valor"
  confirmado ausente também no V1 (mesmos 10 includes do V2, nenhum 11º grupo).
- **CP12-05 (redesenho por-grupo) deixado em aberto — classificação completa feita,
  implementação não.** Todos os 10 grupos têm tabela de colunas PRÓPRIA (nenhum é
  lista genérica) — achado mais abrangente que o já registrado pro V2 (que citava só
  1 exemplo). Cabeçalhos reais extraídos de cada arquivo (`grep -a` — os 10 arquivos
  são ISO-8859, não UTF-8, `grep` normal os trata como binário e retorna vazio sem
  `-a`, achado operacional registrado aqui pra não se perder de novo):

  | Grupo | Colunas reais (`<th>`, ordem exata) |
  |---|---|
  | Prioridade alta sem encaminhar | ENTRADA\|T\|ORIGEM\|NF C\|NF V\|FORNECEDOR\|FABRICANTE\|DESCRICAO\|MODELO\|OS\|A |
  | Protocolo aberto não encaminhado | RECEBIDO\|T\|ORIGEM\|NF C\|NF V\|FORNECEDOR\|FABRICANTE\|DESCRICAO\|MODELO\|OS\|A |
  | Sem número de série | RECEBIDO\|T\|ORIGEM\|NF C\|NF V\|FORNECEDOR\|FABRICANTE\|DESCRICAO\|MODELO\|OS\|A |
  | Sem nota fiscal | RECEBIDO\|T\|ORIGEM\|FORNECEDOR\|FABRICANTE\|DESCRICAO\|MODELO\|S/N\|OS\|A |
  | Prazo do destinatário estourado | ENCAMINHADO\|T\|ORIGEM\|FABRICANTE\|DESCRICAO\|MODELO\|PROTOCOLO\|DESTINATARIO\|OS\|A |
  | Recebidos +30 dias sem encaminhar | RECEBIDO\|T\|ORIGEM\|FORNECEDOR\|FABRICANTE\|DESCRICAO\|MODELO\|S/N\|OS\|A |
  | Garantia do fornecedor expirada | ENTRADA\|ORIGEM\|NF C\|T C\|NF V\|FORNECEDOR\|FABRICANTE\|DESCRICAO\|MODELO\|OS\|A |
  | Garantia expirando em 30 dias | ENTRADA\|ORIGEM\|NF C\|T E\|NF V\|FORNECEDOR\|FABRICANTE\|DESCRICAO\|MODELO\|OS\|A |
  | Não vai dar garantia | ENTRADA\|ORIGEM\|NF C\|T C\|NF V\|FORNECEDOR\|FABRICANTE\|DESCRICAO\|MODELO\|OS\|A |
  | NF de retorno pendente | CONCLUIDO\|T\|ORIGEM\|NF C\|NF V\|FORNECEDOR\|FABRICANTE\|DESCRICAO\|MODELO\|OS\|A |

  Cada grupo também tem sua própria lógica de zebra/`$TR1` inline (achado relevante
  pro CP14) e sua própria consulta de origem dos dados — implementar as 10 tabelas
  exigiria: (a) 10 read-models novos ou um genérico parametrizado, (b) 10 partials
  Blade, (c) decidir zebra por grupo (entra em atrito direto com CP14, ainda não
  investigado), tudo num componente COMPARTILHADO com o TEMA V2 já fechado/aprovado
  (CP16-25) — risco de regressão real numa superfície que acabou de passar por gate
  final. Fica registrado como pendência de uma frente futura dedicada, com a
  classificação completa acima pronta pra uso direto (não precisa reler os 10
  arquivos de novo).
- Estado inicial (Mostrar/Ocultar): confirmado nos 10 arquivos — todos começam
  `Mostrar` visível/dados ocultos (`style="display:block"` no `pmostrar_*`,
  `style="display:none"` no `pocultar_*`/`dados_*`), já era o comportamento do
  partial existente, nenhuma mudança necessária (mesmo achado do V2/CP22-03).
- Tabela (Legacy × V3), título e posição:

  | # | Legacy (`subp/listar_*.php`) | V3 (renderizado) |
  |---|---|---|
  | 1-10 | (mesma tabela de `CMP-V2-005`, títulos idênticos) | idêntico, ordem e texto batendo 1:1 |
- Diferença perceptível restante: só o `[GAP]` já conhecido (tabelas por-grupo
  genéricas em vez de específicas), documentado, não implementado nesta rodada.
- Screenshot versionado (fictício QA):
  `docs/produto/screenshots-vis-v1-001/29-v3-centro-de-avisos-ordem-titulos-cp12.png`.
- Decisão: **CP12 APROVADO com CP12-05 explicitamente pendente** (não bloqueante,
  documentado com evidência completa).
- Testes/build: `php artisan test` (364/820, verde — uma rodada isolada teve 1 falha
  transitória não relacionada a este código, não reproduzida em 2 reexecuções
  subsequentes); `npm run build` (ok); `ParidadeVisualTemaV1.spec.ts` rodado do host
  (4/4, verde).
- Commit: a seguir (`#ARQ-RMA - Corrige a ordem e os titulos do centro de avisos na pagina inicial do tema V1`).

## CP13 — Fixture de QA com comprimento de dado realista

Só depois de CP6–CP12, para não misturar "tamanho do texto fictício" com defeito de
CSS na comparação visual final (ex.: `OS-QA-00059` vs `5947`, `EQUIPAMENTO FICTICIO
QA 059` vs `INTELBRAS`).

- [x] CP13-01 — ajustar o seed de QA (`scripts/v3-reset-qa.sh` ou factory
      correspondente) para gerar OS, fabricantes, modelos, descrições e seriais com
      comprimento semelhante ao observado no Legacy, continuando 100% fictício (não
      copiar dado real).
- [x] CP13-02 — garantir pelo menos um registro fictício com `solucao=PENDENTE
      CREDITO` no seed padrão, para permitir captura direta de Aguardando Crédito
      (pendência deixada pela fase 1 — CMP-V1-005 comparou essa tela só por teste
      automatizado, sem par de screenshot).
- [x] CP13-03 — regenerar screenshots das 4 listagens com a fixture nova e comparar
      densidade de linha com o Legacy.
- [x] CP13-04 — registrar diário e commit local.

### CMP-V1-2-008 — CP13, fixture de QA com comprimento de dado realista

- Ambiente: Chromium headless (Playwright), zoom 100%, DPR 1, viewport 1440×1000, sem
  bloquear fontes remotas. Spec de regressão rodada do host + V2 rodada no container
  (confirmar zero regressão cruzada, já que `QaSeeder` é compartilhado pelos dois
  temas).
- Fonte da comparação de comprimento: screenshots Legacy já capturados nesta sessão
  (CP23/V2: `os` real como `5947`/`6040`/`6003`, 4 dígitos; `descricao` real como
  `NOBREAK 700VA`/`ESTABILIZADOR 300VA`/`2UND - HD 2,5" SLIM`, 13-20 caracteres).
- **Achado real, corrigido — só comprimento, sem mudar contagens:**
  `descricao`: `"Equipamento ficticio QA 001"` (27 caracteres) → `"Ficticio QA 001"`
  (16 caracteres, ainda claramente fictício); `os`: `"OS-QA-00001"` (11 caracteres,
  formato alfanumérico) → `(string)(5900+$indice)` (4 dígitos puros, `"5901"`..
  `"5960"`, mesmo formato numérico puro do Legacy). `modelo`/`sn` não alterados —
  já estavam em faixa de comprimento comparável (13/12 caracteres, Legacy varia
  8-26).
- **Achado real, corrigido:** nenhum registro do seed anterior tinha
  `solucao=PendenteCredito` — a tela Aguardando Crédito (rota `rmas-aguardando-
  credito`) ficava sempre vazia (achado já registrado na fase 1, CMP-V1-005,
  nunca fechado). Adicionado aditivamente: o 3º registro (1º `Status::Encaminhado`
  do ciclo de 5) ganha `solucao=PendenteCredito` — não remove nenhuma combinação já
  coberta (a contagem de `Status::Concluido`+`Solucao::Reparo`, usada em várias
  medições já aprovadas desta sessão — ex. CP7's teste funcional de 12 linhas —,
  continua em 12, intocada). Testado: `/rmas-aguardando-credito` foi de sempre-vazia
  pra 1 linha real, com todos os campos preenchidos (fabricante, protocolo,
  destinatário etc.).
- Teste unitário do seeder (`QaSeederTest`) atualizado: string literal
  `'Equipamento ficticio QA 001'`→`'Ficticio QA 001'` (mesmo registro, só o valor
  mudou) + nova asserção `assertSame(1, ... where solucao=PendenteCredito ... count())`.
- Screenshots versionados (fictício QA, mesma régua desta sessão):
  `docs/produto/screenshots-vis-v1-001/{30-v3-aguardando-credito-cp13-primeiro-dado,31-v3-entrada-cp13-fixture-realista}.png`
  — a 2ª mostra a densidade/comprimento de linha da listagem Entrada com o dado
  novo, comparável ao Legacy.
- Diferença perceptível restante: nenhuma nova. `fabricante`/`fornecedor`/`cliente`
  (nomes vindos das factories, não deste seeder) já estavam em faixa de comprimento
  razoável, não tocados.
- Decisão: **CP13 APROVADO**.
- Testes/build: `php artisan test` (364/821, verde — 1 assertion nova);
  `ParidadeVisualTemaV1.spec.ts` rodado do host (4/4, verde);
  `ComparacaoVisualTemaV2Test.spec.ts` rodado no container (2 passados/1 skip,
  inalterado — zero regressão no TEMA V2 pela mudança do seed compartilhado).
- Commit: a seguir (`#ARQ-RMA - Ajusta a fixture de QA para comprimento de dado realista e cobre aguardando credito`).

## CP14 — Investigação da máquina de estados `$TR1` (não é correção às cegas)

Item de investigação, não de implementação garantida — só alterar
`Rma::classeDeAlerta()`/`classe_css_de_alerta()` se houver evidência de runtime.

- [x] CP14-01 — reproduzir localmente uma sequência real de RMAs em Entrada com pelo
      menos 2 tipos de alerta diferentes intercalados (ex.: `SEM GARANTIA` seguido
      de `Cliente`/`marcarestoque=0` fora do prazo).
- [x] CP14-02 — comparar a classe CSS de cada linha, na mesma sequência, entre
      Legacy (`$TR1` como estado compartilhado do PHP) e V3
      (`Rma::classeDeAlerta()` + índice).
- [x] CP14-03 — **divergiu — achado real, documentado, NÃO implementado.** Ver
      diário (`CMP-V1-2-009`) pra evidência completa e a decisão em aberto pro
      usuário.
- [ ] CP14-04 — não se aplica (achado positivo, não negativo — ver CP14-03).

### CMP-V1-2-009 — CP14, achado real na máquina de estados `$TR1` (decisão pendente do usuário)

- Ambiente: leitura completa de `page/entrada.php` (14.6.1, branches `$TR1` linhas
  41-49) + `Rma::classeDeAlerta()`/`classe_css_de_alerta()` (domínio) + verificação
  empírica ao vivo (Playwright, `/rmas-entrada`, 24 linhas reais da fixture QA pós-
  CP13, `getComputedStyle` de cada `<tr>`).
- **Achado 1 — `ClasseDeAlerta::Urgente` é código morto, nunca devolvido:**
  `Rma::classeDeAlerta()` mapeia AS QUATRO condições de alerta
  (`SemGarantia`/`prioridade=Alta`/`origemEhTerceiroForaDoPrazo()`/
  `marcarestoque=false+Cliente-ou-Licitação`) pro MESMO caso
  `ClasseDeAlerta::Inconformidade`. O Legacy real (`entrada.php:41-49`) usa DUAS
  classes CSS diferentes, com cores de fundo diferentes
  (`pattern/15.9.7.css:60-63`: `TrInconformidade` `#303033`, `TrUrgente`
  `#382830`):
  - `TrInconformidade`: SEM GARANTIA (linhas 41-42) OU
    `marcarestoque=0 AND origem∈{Cliente,Licitação}` (linhas 46-47).
  - `TrUrgente`: `origem=Cliente AND marcarestoque=0 AND >30 dias fora do prazo`
    (linha 44) OU `prioridade=alta` (linha 45). (A condição `prioridade=="urgente"`
    da linha 43 é sobre um valor de prioridade morto — RN-08, já documentado no
    docblock do enum `Prioridade` — nunca dispara na prática.)
  - Verificado ao vivo: nenhuma das 24 linhas de `/rmas-entrada` (fixture com
    `prioridade=Alta` em 1 a cada 3 registros) rendeu `TrUrgente` — todas as linhas
    de alerta vieram `TrInconformidade` (`rgb(48,48,51)`, confere com `#303033`).
    Confirma o mapeamento incorreto ao vivo, não só por leitura de código.
  - **Achado já apontado, mas não resolvido, no CP23 do TEMA V2** (`[INVESTIGAR]`
    em `plano-execucao-paridade-v2.md`: "Entrada não usa `TrUrgente` nem checa
    prazo de 30 dias") — como `Rma::classeDeAlerta()` é do domínio (compartilhada
    pelos dois temas), esse achado do V2 e o do CP14 são o MESMO bug, confirmado
    agora com evidência completa dos dois lados.
- **Achado 2 — ordem de alternação da zebra diverge quando há linhas de alerta
  intercaladas:** no Legacy, as linhas `TrUrgente` (branches 3-5, priority/30-dias)
  NÃO tocam `$TR1` — o toggle de zebra "pula" essas linhas, mantendo a alternância
  correta pras linhas neutras ao redor. Já as linhas `TrInconformidade` por SEM
  GARANTIA/estoque (branches 1-2/6-7) CONSOMEM um turno do `$TR1` mesmo sem
  renderizar zebra. V3 usa `$indice % 2` (posição bruta no array, avança em TODA
  linha, alerta ou não) — trace manual de uma sequência
  [neutra,alerta-urgente,neutra,neutra]: Legacy produz `Zebrada2,Urgente,Zebrada1,
  Zebrada2` (alternância correta, pula a linha urgente); V3 produz
  `Zebrada1,Urgente,Zebrada1,Zebrada2` (as duas linhas neutras ao redor da linha de
  alerta saem com a MESMA classe de zebra — alternância quebrada). Efeito visual:
  sutil (duas linhas adjacentes-por-conteúdo com o mesmo tom de zebra em vez de
  alternado), mas é uma divergência real e mensurável.
- **Decisão pendente do usuário (CP14-03 explicitamente pede isso antes de
  implementar):** corrigir exige decidir ONDE — `Rma::classeDeAlerta()` é
  compartilhada com o TEMA V2 (que já tem o mesmo achado registrado como
  `[INVESTIGAR]`, não corrigido); mudar o método de domínio conserta os dois temas
  de uma vez (aditivo — `Urgente` já existe no enum e no `classe_css_de_alerta()`,
  só nunca é devolvido), mas exigiria uma máquina de estado própria por
  view/listagem pra reproduzir o "pular linha urgente" da alternância — os
  presenters atuais (`RmaController`/`ListagensPorStatusController`) só passam
  `$indice` do `foreach`, não um contador que ignora linhas de alerta. Opções, sem
  decisão tomada aqui: (a) corrigir só o mapeamento Urgente×Inconformidade
  (fecha o achado 1, mais simples, zero risco de regressão de zebra) e deixar o
  achado 2 (ordem de alternação) documentado como tolerância aceita (efeito visual
  pequeno); (b) corrigir os dois achados juntos, com um contador de zebra próprio
  passado a `classe_css_de_alerta()` em vez do índice bruto — maior, toca
  `RmaController`/`ListagensPorStatusController`/TEMA V2 simultaneamente.
- Screenshots: não capturados nesta rodada — achado é de CLASSE CSS/cor de fundo,
  não de geometria, e não há screenshot Legacy comparável disponível com a MESMA
  sequência de dados (dado real de produção, não reproduzível com a fixture QA).
  Evidência é o trace de código + a verificação ao vivo acima.
- Decisão: **CP14 fechado como investigação — achado real registrado, correção
  NÃO implementada, aguardando decisão do usuário sobre escopo (a) ou (b) acima.**
- Testes/build: nenhuma alteração de código nesta rodada — suíte não rodada de
  novo (sem mudança pra verificar).
- Commit: a seguir, só documentação (`#DOC-RMA - Registra o achado da maquina de estados TrUrgente pendente de decisao`).

## CP15 — Gate final da fase 2

- [ ] CP15-01 — inventariar todos os usos V1 dos componentes corrigidos nesta fase
      (Localizar/Novo/Anotações/Contadores/Centro de Avisos) fora da Página Inicial,
      se algum existir.
- [ ] CP15-02 — rodar suíte PHP completa e Vite build.
- [ ] CP15-03 — rodar Playwright visual completo (specs de paridade no host, demais
      no container) e confirmar V2 sem regressão.
- [ ] CP15-04 — comparar em 1440×1000, 1562×1400 e 1700×1000 (as duas viewports
      secundárias, não executadas na fase 1, ficam obrigatórias aqui).
- [ ] CP15-05 — abrir cada par final e registrar uma entrada no diário.
- [ ] CP15-06 — produzir tabela final por elemento (mesmo formato de CMP-V1-007 da
      fase 1) e caminhos dos screenshots.
- [ ] CP15-07 — atualizar `docs/produto/checklist-paridade-visual-v1-runtime.md` e
      `PLANO-ATAQUE.md`, e criar commit final do checkpoint.

## Diário de comparação

Cada `CMP-V1-2-NNN` deve registrar: checkpoint/tela; fonte PHP/CSS; ambiente;
caminhos dos prints; elementos abertos; tabela de medidas Legacy/V3; fonte
rasterizada; diferença perceptível; seletor responsável; decisão; testes/build;
commit. Sem esses campos, o item permanece aberto. (Nenhuma entrada ainda — plano
recém-criado.)
