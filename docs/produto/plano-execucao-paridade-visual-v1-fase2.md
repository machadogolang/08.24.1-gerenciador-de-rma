# Plano de execução — paridade visual do Tema V1, fase 2

Data: 2026-08-25. Estado: **não iniciado**. Continuação de
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

- [ ] CP6-01 — ler `legacy-source/14.6.1/inc/startpage.php` por inteiro (topo da
      página, antes do quadro de anotações).
- [ ] CP6-02 — medir V3 atual: confirmar que `rma/index.blade.php` renderiza H1 "RMAs"
      e link "Novo RMA" que não existem no Legacy.
- [ ] CP6-03 — remover "RMAs"/"Novo RMA" da composição visual (H1 pode virar
      `sr-only`, mantendo semântica; o atalho Novo já existe no menu superior).
- [ ] CP6-04 — medir a distância header→primeira superfície útil (Localizar) nos dois
      lados; confirmar que o V3 não tem mais os ~45-50px extras artificiais.
- [ ] CP6-05 — capturar/reabrir/comparar Página Inicial, registrar diário.
- [ ] CP6-06 — testes focados/build e commit local.

## CP7 — Localizar como painel inline histórico

Maior item desta fase. Fonte:
`legacy-source/14.6.1/menujs-top/localizar.php`, `pattern/14.6.1.css`,
`pattern/14.6.1.js`.

- [ ] CP7-01 — ler os 3 arquivos fonte por inteiro.
- [ ] CP7-02 — medir geometria real no Legacy via `getBoundingClientRect()` +
      computed style (não assumir os valores do prompt sem medir):
      `#JS-Localizar` (`min-height:25px; padding:10px; margin-bottom:10px`),
      `.JSformLocalizarInput` (`width:422px; height:30px; padding:10px;
      font-size:18px; letter-spacing:1px`), `.JSformLocalizarSelect`
      (`margin-left:15px; height:52px; font-size:12px`),
      `.JSformLocalizarButton` (`height:52px; width:100px; margin-left:15px;
      font-size:14px; letter-spacing:1px; background:#106D78`).
- [ ] CP7-03 — extrair partial `resources/views/temas/v1/rma/_form_localizar.blade.php`
      (mesmo padrão consciente já usado para `_form_novo.blade.php`, incluído uma vez
      pelo layout, sem duplicar entre páginas).
- [ ] CP7-04 — montar select SOLUÇÃO com as opções históricas: `QUALQUER UMA SOLUCAO`,
      `GERADO CREDITO`, `SEM GARANTIA`, `REPARO`, `TROCA DO PRODUTO`,
      `TROCA DE PECA INTERNA`, `DEVOLUCAO DO PRODUTO`, `REEMBOLSO DO DINHEIRO`,
      `REPARO PELO RMA`, `TESTADO TUDO OK`, `ORCAMENTO PAGO`, `PROCON` — mapear cada
      rótulo para o valor real do enum `Solucao` do domínio (não inventar valor).
- [ ] CP7-05 — montar select CAMPO com as opções históricas: `TODOS OS CAMPOS`,
      `ORDEM DE SERVICO`, `FABRICANTE`, `DESCRICAO`, `S/N, P/N OR ID/SNID/ETC`,
      `MODELO`, `ORIGEM`, `EMPRESA`, `CLIENTE`, `CODIGO DE RASTREIO`, `PROTOCOLO`,
      `NF`, `DESTINATARIO`, `CHAVE` — mapear para os critérios que o caso de uso de
      busca atual realmente aceita; documentar `[BUG-LEGADO]`/gap se algum campo
      histórico não tiver equivalente moderno (mesmo tratamento dado a "NF R" na
      fase 1 — não simular busca que não funciona).
- [ ] CP7-06 — adapter na camada de apresentação/aplicação que traduz os parâmetros
      da UI V1 para o caso de uso de busca existente; não portar parâmetros HTTP
      antigos para o domínio, não colocar query no Blade.
- [ ] CP7-07 — comportamento inline: `#JS-Localizar` presente no DOM, oculto por
      padrão exceto na Página Inicial (que já inicia com Localizar aberto no
      Legacy), clique em "Localizar" expõe o painel e deixa o item do menu em
      negrito, sem navegar — mesmo padrão do `NovoMaximize()` já portado para Novo.
- [ ] CP7-08 — não introduzir jQuery só para isso; reaproveitar o padrão vanilla já
      usado no toggle do painel Novo.
- [ ] CP7-09 — capturar/reabrir/comparar geometria completa (outerWidth/outerHeight
      de cada campo), registrar diário.
- [ ] CP7-10 — testes focados/build e commit local.

## CP8 — Painel Novo: divergências visuais restantes

A mecânica inline (abrir sem navegar, manter a tela abaixo, POST funcional) já está
correta e **não deve ser mexida**. Fonte:
`legacy-source/14.6.1/menujs-top/novo.php`, `pattern/15.9.7.css`.

- [ ] CP8-01 — ler `novo.php` e a folha `15.9.7.css` (seção de checkbox/toggle) por
      inteiro.
- [ ] CP8-02 — portar o toggle histórico do campo "item é do estoque": `label` com
      `data-text-true="O ITEM E DO ESTOQUE"`/`data-text-false="ITEM NAO E DO
      ESTOQUE"`, `<i></i>` deslizante, `background-color:#DB574D` desmarcado /
      `#67B04F` marcado, `width:475px; height:30px`. Escopar a regra ao painel Novo
      do TEMA V1 (não alterar outros checkboxes do sistema). Preservar semântica de
      envio (marcado=true/desmarcado=false).
- [ ] CP8-03 — trocar `type="date"` por `type="text" placeholder="00/00/2015"` nos
      campos de data; converter `dd/mm/YYYY` → formato interno na camada HTTP antes
      de validar/persistir, sem enfraquecer validação; não alterar TEMA V2.
- [ ] CP8-04 — trocar `<select name="fabricante_id">` por input/datalist visualmente
      igual ao legado (`novo_formInput` + `list="fabricantes"`); resolver
      nome→`fabricante_id` na camada de apresentação/aplicação antes do caso de uso,
      sem query no Blade, sem alterar a modelagem do banco.
- [ ] CP8-05 — auditar `box-sizing:border-box` introduzido pelo V3 em
      `novo_formInput`, `novo_formInputDATE`, `novo_formInputSmall`, `novo_defeito`,
      `formInputObservacao`: medir `outerWidth`/`outerHeight` Legacy×V3 para cada
      um; se o legado não tinha `border-box` e isso muda a geometria, reproduzir o
      box model histórico (a regra é `outerWidth`/`outerHeight` iguais, não "qual
      prática é mais moderna").
- [ ] CP8-06 — capturar/reabrir/comparar painel Novo completo, registrar diário.
- [ ] CP8-07 — testes focados/build e commit local.

## CP9 — Quadro de Anotações

Fonte: `legacy-source/14.6.1/inc/startpage.php`, `pattern/14.6.1.css`.

- [ ] CP9-01 — ler a seção do quadro de anotações nos dois arquivos por inteiro.
- [ ] CP9-02 — medir geometria real no Legacy: container ~675px, `margin-left:1px`,
      textarea `rows=20` width ~674px, `padding:5px`, `font-size:12px`,
      `letter-spacing:1px`, `line-height:1.5` (medir para confirmar, não assumir).
- [ ] CP9-03 — trocar `rows="14"` por `rows="20"` e ajustar a geometria do
      container/textarea para bater com o medido.
- [ ] CP9-04 — restaurar o estilo real do título (`panotacao`/`imganotacao`): ícone
      deslocado com margins negativas, `margin-top:-16px`, `padding:10px`,
      `letter-spacing:3px`, `font-weight:bold` — não reduzir a
      `.quadro-de-anotacoes-titulo { font-weight:300; }` genérico atual.
- [ ] CP9-05 — remover o botão "Salvar anotação" da composição visual (o Legacy
      salva durante a digitação, sem botão). Implementar salvamento moderno:
      evento de input/change na textarea, debounce, `fetch` para o endpoint Laravel
      existente, CSRF, tratamento de erro discreto — sem portar o AJAX antigo.
- [ ] CP9-06 — capturar/reabrir/comparar quadro de anotações, registrar diário.
- [ ] CP9-07 — testes focados/build e commit local.

## CP10 — Sidebar de contadores

Fonte: `legacy-source/14.6.1/inc/startpage.php`, `pattern/14.6.1.css`.

- [ ] CP10-01 — ler a seção da sidebar nos dois arquivos por inteiro.
- [ ] CP10-02 — medir geometria real no Legacy: container `width:280px; float:right;
      margin-right:-8px; margin-top:-15px`; `.formLabelStats` `width:198px;
      padding:5px; border:1px` (sem `border-box`); `.formInputStats` `width:45px;
      padding:5px; border:1px` (sem `border-box`).
- [ ] CP10-03 — remover `box-sizing:border-box` desses elementos se a medição
      confirmar que altera a geometria; reproduzir semanticamente com
      `<p class="formLabelStats">`/`<input class="formInputStats" disabled>` ou
      produzir os mesmos `outerWidth`/`outerHeight` por outro meio.
- [ ] CP10-04 — confirmar/restaurar que cada contador é link (`<a>`) para a
      listagem/filtro correspondente: `ENTRADA`→Entrada, `PENDENTE CREDITO`→
      Aguardando crédito, `ENCAMINHADO`→Encaminhado, `CONCLUIDO`→Concluído, e os
      filtros por solução (`SEM GARANTIA`, `GERADO CREDITO` etc.) para onde o
      Legacy realmente aponta — mapear caso a caso, não assumir.
- [ ] CP10-05 — capturar/reabrir/comparar sidebar, registrar diário.
- [ ] CP10-06 — testes focados/build e commit local.

## CP11 — Separador antes do Centro de Avisos

- [ ] CP11-01 — localizar `separador2.png` no repositório Legacy
      (`legacy-source/images/`), igual ao já feito para os ícones das 4 listagens.
- [ ] CP11-02 — portar para `public/images/tema-v1/`, validar hash byte a byte.
- [ ] CP11-03 — inserir com `float:right; margin-top:50px; height:40px` e o
      `clear`/`hr` que o Legacy usa antes/depois, na posição real (entre
      anotação/contadores e o Centro de Avisos).
- [ ] CP11-04 — capturar/reabrir/comparar, registrar diário.
- [ ] CP11-05 — testes focados/build e commit local (pode ser junto do CP12 se os
      dois ficarem pequenos o suficiente para um commit coerente).

## CP12 — Centro de Avisos

Fonte: `legacy-source/14.6.1/inc/startpage.php` (lista de includes) e cada
`subp/listar_*.php` referenciado por ele.

- [ ] CP12-01 — mapear a lista COMPLETA de includes de `startpage.php` até o fim do
      arquivo (os prompts originais só citam os 10 primeiros — não parar aí),
      registrando a ordem exata.
- [ ] CP12-02 — para cada `subp/listar_*.php`: ler o arquivo e classificar a
      apresentação real (lista genérica ícone+título+Mostrar+itens / tabela com
      colunas próprias / mensagem "Nenhum item foi encontrado" quando vazio).
- [ ] CP12-03 — comparar com `ListarGruposDeAlertas::listar()` e
      `_centro_de_avisos.blade.php` atuais: confirmar quais grupos existem, faltam
      ou estão fora de ordem.
- [ ] CP12-04 — criar presenter/ordenação específica da apresentação V1 que respeite
      a ordem histórica, sem alterar a ordem usada por outros consumidores do caso
      de uso (ex.: TEMA V2), se eles dependerem de ordem diferente.
- [ ] CP12-05 — criar partials/presenters só para os grupos cuja apresentação real
      diverge do genérico atual (ex.: o grupo com tabela de colunas próprias visto
      no achado "PROTOCOLO ESTA ABERTO E O PRODUTO NAO ENCAMINHADO"); reaproveitar
      os casos de uso modernos existentes, sem SQL/PHP procedural portado.
- [ ] CP12-06 — verificar estado inicial real (Mostrar/Ocultar) de cada grupo no
      runtime Legacy; não assumir que todos começam ocultos — reproduzir por grupo.
- [ ] CP12-07 — capturar/reabrir/comparar Centro de Avisos completo (todos os
      grupos), registrar diário.
- [ ] CP12-08 — testes focados/build e commit local.

## CP13 — Fixture de QA com comprimento de dado realista

Só depois de CP6–CP12, para não misturar "tamanho do texto fictício" com defeito de
CSS na comparação visual final (ex.: `OS-QA-00059` vs `5947`, `EQUIPAMENTO FICTICIO
QA 059` vs `INTELBRAS`).

- [ ] CP13-01 — ajustar o seed de QA (`scripts/v3-reset-qa.sh` ou factory
      correspondente) para gerar OS, fabricantes, modelos, descrições e seriais com
      comprimento semelhante ao observado no Legacy, continuando 100% fictício (não
      copiar dado real).
- [ ] CP13-02 — garantir pelo menos um registro fictício com `solucao=PENDENTE
      CREDITO` no seed padrão, para permitir captura direta de Aguardando Crédito
      (pendência deixada pela fase 1 — CMP-V1-005 comparou essa tela só por teste
      automatizado, sem par de screenshot).
- [ ] CP13-03 — regenerar screenshots das 4 listagens com a fixture nova e comparar
      densidade de linha com o Legacy.
- [ ] CP13-04 — registrar diário e commit local.

## CP14 — Investigação da máquina de estados `$TR1` (não é correção às cegas)

Item de investigação, não de implementação garantida — só alterar
`Rma::classeDeAlerta()`/`classe_css_de_alerta()` se houver evidência de runtime.

- [ ] CP14-01 — reproduzir localmente uma sequência real de RMAs em Entrada com pelo
      menos 2 tipos de alerta diferentes intercalados (ex.: `SEM GARANTIA` seguido
      de `Cliente`/`marcarestoque=0` fora do prazo).
- [ ] CP14-02 — comparar a classe CSS de cada linha, na mesma sequência, entre
      Legacy (`$TR1` como estado compartilhado do PHP) e V3
      (`Rma::classeDeAlerta()` + índice).
- [ ] CP14-03 — se divergir: documentar o achado com evidência (sequência exata +
      classes obtidas nos dois lados) e decidir a correção com o usuário antes de
      implementar — pode exigir um presenter de linha V1 com máquina de estado
      própria, não alteração do domínio.
- [ ] CP14-04 — se não divergir: registrar a prova negativa (não é bug) e fechar o
      item sem alteração de código.

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
