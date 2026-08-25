# Plano de execução — paridade visual do Tema V2

Data: 2026-08-25. Estado: **em execução**. Frente independente da paridade do Tema V1
(fechada em `plano-execucao-paridade-estrutural-v1.md`, CP0–CP5) — **não reabrir V1
nesta frente**. Matriz correta desta comparação:

```text
LEGADO 15.8.1  <->  V3 TEMA V2  (http://localhost:8095/v2/rma — NUNCA /rmas)
```

Regra de gate (igual às frentes V1): toda comparação entra no Diário abaixo. Um item
só recebe `[x]` depois de os prints Legacy/V3 normalizados terem sido abertos e
inspecionados, com ambiente e medidas registrados via `getBoundingClientRect()` +
computed style — nunca por inspeção visual isolada.

## Ambiente fixo

- Chromium; zoom 100%; `deviceScaleFactor: 1`; sem bloquear fontes remotas.
- Viewport primária: 2048×1152; secundárias: 1440×1000, 1562×1400, 1700×1000. Todas
  ≥1280px — cai sempre no mesmo breakpoint de `media.php` (`min-width:1280px`, ver
  achado CP16-02), então a geometria de nav/sidebar não varia entre as 4 viewports
  testadas (só o espaço vazio ao redor do shell de 1190px varia).
- Legacy: `http://localhost:8094/15.8.1/`. V3: `http://localhost:8095/v2/rma`.
- Capturar sempre `window.devicePixelRatio`,
  `getComputedStyle(document.documentElement).zoom`,
  `getComputedStyle(document.body).zoom` e confirmar neutros antes de comparar
  qualquer geometria (mesma disciplina que fechou a dúvida de largura do Tema V1).
- Scripts de diagnóstico Playwright são descartáveis (`scripts/_tmp-*.mjs`, apagar
  depois de usar — não versionar).

## CP16 — shell global (1190 / 990 / 195)

Achado confirmado por leitura direta do código-fonte nesta sessão (não é suposição
do prompt original — foi lido e conferido):

- `legacy-source/15.8.1/index.php`: `<header class="row"><div style="margin:0
  auto;width:1190px;"><?php include("inc/menu.php"); ?></div></header>`, depois
  `<div style="width:1190px;margin:0 auto;padding:0px;"><div class="container"
  style="float:left;">` (conteúdo principal) `...</div>` e, como **irmão** desse
  wrapper (fora dele, não dentro), `<div id="menuright" class="upmenuright"
  style="display:none;...margin-top:44px;">` com filho `<div style="min-height:100px;
  width:195px;margin:0px;padding:0px;float:left;margin-left:5px;margin-top:3px;
  border:1px solid #444;"><?php include("inc/rightmenu.php"); ?></div>` — **confirmar
  com Playwright se `#menuright` participa ou não da centralização do wrapper de
  1190px** (a indentação do PHP não deixa claro se fica dentro ou fora; medir `x`
  real, não assumir).
- `legacy-source/pattern/15.8.1.css:815`: `.upmenuright { width:195px;
  margin-left:5px; float:left; margin-top:46px; ...}` — o `style` inline de
  `#menuright` (`margin-top:44px`) e de `#loader_r` (`margin-top:45px`) sobrepõe essa
  regra da classe (inline vence cascata); reproduzir os valores inline, não os 46px
  da classe.
- `legacy-source/15.8.1/css/media.php`: `.container` já está correto no
  `v2.scss` atual (549/730/990/990/990/990px por breakpoint, migrado 1:1). **Falta**:
  `.nav { width:990px }` nos breakpoints 992-1080, `.nav { width:1190px }` nos
  breakpoints 1280/1366, `.nav-tabs li { width:12.5% }` até 1279px e `{ width:11.1% }`
  a partir de 1280px. Nenhuma dessas 3 regras existe hoje em `v2.scss`.

Tarefas:

- [x] CP16-01 — medir no Legacy, `getBoundingClientRect()`, viewport 2048×1152:
      wrapper de 1190px (`x`, `width`), `.container` (`x`, `width`), `#menuright`
      (`x`, `width`, `margin-top` computado).
- [x] CP16-02 — confirmar que as 4 viewports do plano caem todas no mesmo bloco de
      `media.php` (≥1280px) — se sim, implementar só esse bloco primeiro e não gastar
      ciclo com os breakpoints menores (fora do escopo desta frente, que é desktop).
- [x] CP16-03 — criar a composição do shell em `_v2-base.scss` (arquivo novo, mesmo
      padrão de `_v1-base.scss`): wrapper 1190px centralizado, `.container` mantido
      (990px, já correto), sidebar 195px com a geometria medida em CP16-01. Pode usar
      flex/grid moderno desde que `x`/`width`/`y`/`height` batam com o Legacy — não
      copiar `float` por obrigação.
- [x] CP16-04 — adicionar as regras que faltam de `.nav`/`.nav-tabs li` por
      breakpoint (mapa `$breakpoints-tema-v2` já existe em `v2.scss`, só falta
      estender com essas 2 propriedades).
- [x] CP16-05 — ajustar `resources/views/temas/v2/layout.blade.php` para a árvore
      estrutural correta (wrapper 1190 → header + container + sidebar), sem ainda
      mexer no conteúdo do header/nav (isso é CP17) nem no conteúdo do
      `tab-content`/`.container` interno (isso é CP19+).
- [x] CP16-06 — inspecionar o CSS FINAL compilado pelo Vite (não só o SCSS fonte)
      para confirmar que a ordem final de cascata é Bootstrap → base V2 →
      compartilhado → media V2, igual à ordem do `index.php` legado (Bootstrap →
      font-opensans → Fira → `15.8.1.css` → `15.9.7.css` → `media.php`) — mesmo tipo
      de achado que gerou CP1 na fase 1 do Tema V1; não presumir que já está certo.
- [x] CP16-07 — capturar/reabrir/comparar shell (sem header/nav ainda finalizados),
      registrar diário com a tabela `shell width/x`, `main width/x`, `sidebar
      width/x`.
- [x] CP16-08 — testes/build e commit local. Rodar também os testes V1 (CP16 não
      deveria tocar `_v1-base.scss`/`_compartilhado.scss`, mas confirmar).

### CMP-V2-001 — CP16/CP17, shell (1190/990/195) + header/nav reais

- Ambiente: Chromium headless (Playwright), zoom 100%, DPR 1, sem bloquear fontes
  remotas. Medido em **4 viewports**: 2048×1152, 1440×1000, 1562×1400, 1700×1000 —
  todas caem no mesmo bloco de `media.php` (`min-width:1366px`, o último e mais
  específico que casa com todas), confirmando CP16-02.
- Login: `localhost:8094/` sempre pousa na preferência salva do usuário
  (`app($email)`), que hoje é `14.6.1`. Alternar exige um GET em
  `http://localhost:8094/trocarapp.php` (achado novo, documentado aqui porque nenhum
  script anterior desta sessão precisava acessar `15.8.1`).
- **Bug real encontrado e corrigido, fora do escopo original do CP16/17 mas
  bloqueante para ele:** `bootstrap/js/tab` e `bootstrap/js/dropdown` (Bootstrap 3,
  pacote npm) são IIFEs que fecham sobre o identificador global `jQuery` no momento em
  que o módulo é avaliado (`}(jQuery)`). Como `import` em ESM é içado para o topo do
  módulo, `window.jQuery = $` escrito depois dos `import` dos plugins no mesmo
  arquivo (`v2.js`) executava tarde demais — os plugins lançavam `jQuery is not
  defined` ao carregar. Isso já quebrava silenciosamente a troca de abas
  Início/Pesquisar/Entrada/etc. **antes desta sessão** (bug pré-existente, não
  introduzido pela correção de paridade). Corrigido extraindo
  `resources/js/temas/_jquery-global.js` (import próprio, importado antes dos
  plugins — ordem entre módulos é respeitada pelo ESM, só a ordem *dentro* de um
  módulo é içada). Confirmado via Playwright: `$(document).find('#entrada')` ganha
  `.active` corretamente ao clicar, dropdown abre.
- Achado de composição: `.container` do Legacy tem `y=0` (sem margem própria); quem
  tem `margin-top:40px` é `.tab-content`, um DESCENDENTE dele. O V3 fundiu
  `.container`/`.tab-content` em um único elemento (`margin-top:40` direto), então o
  conteúdo real começa em `y=40` no V3 contra `y≈37` no Legacy (a folha de estilo
  original tem mais uma camada de aninhamento que produz esse deslocamento de 3px) —
  diferença mínima, documentada, não perseguida (mesmo padrão de tolerância já usado
  na fase 1 do Tema V1 para diferenças ≤3px).
- Tabela de medidas (`getBoundingClientRect`, Legacy × V3), idêntica nas 4 viewports
  testadas (só o `x` muda com a centralização, sempre `(viewport-1190)/2`):

  | Elemento | Legacy | V3 | Resultado |
  |---|---|---|---|
  | `header` (`x,y,width,height`) | `0,-3,2048,40` | `0,-3,2048,40` | OK |
  | wrapper do header (1190px) | `x=429,width=1190,right=1619` | idêntico | OK |
  | `.container`/`.shell-v2 > .container` (990px) | `x=429,width=990,right=1419` | idêntico | OK |
  | sidebar (195px) | `x=1424,y=47,width=195,right=1619` | idêntico | OK |
  | `.nav`/`.nav-tabs.nav-v2` (1190px) | `x=429,width=1190,right=1619` | idêntico | OK |
  | largura de cada `<li>` (9 itens, 11.1%) | `132px` cada, `x` em passos de 132 | idêntico | OK |
  | cor do header | `#C20F41` | `#C20F41` | OK |
  | cor da aba ativa | `#FE0048` | `#FE0048` | OK |
  | H1 artificial | ausente (nunca existiu) | removido | OK |
  | navbar duplicada (RMAs/Clientes/...) | não existe | removida | OK |
  | "Bem-vindo(a), usuário" | não existe | removido | OK |
  | rodapé "TEMA V2 — CellSystem RMA (reconstrução V3)" | não existe | removido | OK |
  | dropdown "Menu" (9 itens, fundo escuro `.lidropdown`/`.menuz` alternado) | presente | presente, abre/fecha (bug de jQuery corrigido) | OK |

- Diferença perceptível restante: nenhuma nas primitivas de shell/header/nav. Sidebar
  ainda **vazia** (CP19 não iniciado — só a geometria do container foi provada, não o
  conteúdo das 14 seções).
- Screenshots versionados (sem dado real de cliente/produto — só chrome estrutural e
  títulos de seção, seguindo a mesma regra já aplicada às evidências do Tema V1):
  `docs/produto/screenshots-vis-v2-001/{01,02,03}-*.png`.
- Decisão: **CP16 e CP17 APROVADOS** para shell/header/navegação/dropdown/rodapé/
  ausência de H1. Pendências explícitas para checkpoints futuros: conteúdo da sidebar
  (CP19), páginas próprias vs. tab-panes para Entrada/Recebido/Encaminhado/Concluído
  fora do índice (decisão de arquitetura tomada aqui: reaproveitar os tab-panes
  existentes com link+hash em vez de criar rotas novas — ver comentário em
  `layout.blade.php`), gap de "Anotacoes" sem página própria (documentado inline).
- Testes/build: `php artisan test` (363/818, verde); `npm run build` (ok);
  `ParidadeVisualTemaV1.spec.ts` (4/4, confirma **zero regressão no Tema V1** — nenhum
  arquivo compartilhado com V1 foi tocado nesta rodada).
- Commit: a seguir (`#ARQ-RMA - Restaurada a composicao original do cabecalho e menu
  do Tema V2` + correção do bug de jQuery).

## CP17 — header e navegação reais

Fonte: `legacy-source/15.8.1/inc/menu.php` (lido por inteiro nesta sessão — 9 itens:
Inicio, Pesquisar, Novo, Entrada, Recebido, Encaminhado, Concluido, dropdown Menu,
Logout), `legacy-source/pattern/15.8.1.css:132-192` (`.nav`, `.nav-tabs`, `header`).

Achado confirmado: `header { padding:0; height:40px; z-index:999; position:fixed;
width:100%; top:-3px; background-color:#C20F41; border-bottom:1px solid #333
!important; }`. Aba ativa: `background-color:#FE0048;color:#EEE;font-weight:700`
(inline, só quando a página é "estática", isto é, `$page` não setado — nas páginas
dinâmicas via `?p=`, o item ativo usa `class="active"` da própria `<li>`, sem o
inline — reproduzir os dois casos, não só um). `#224A5D`/`#18354B` (azul petróleo/
marinho) **não pertencem a este componente** — são de outros controles
(`.buttonSalvar`, `.formSubmit`) e não devem aparecer na barra/nav principal.

- [x] CP17-01 — remover a navbar dupla atual (`temas/v2/layout.blade.php`, a lista
      RMAs/Clientes/Fabricantes/Fornecedores/Assistências/Usuários/perfil/Sair) e a
      navegação duplicada de `rma/index.blade.php` (Início/Pesquisar/Novo
      RMA/Entrada/Recebido/Encaminhado/Concluído) — unificar numa única barra.
- [x] CP17-02 — montar a barra única com os 9 itens históricos na ordem exata,
      mapeando rota moderna para cada um: Inicio→`v2.rmas.index`(ou equivalente),
      Pesquisar→aba/seção de busca, Novo→formulário de criação, Entrada/Recebido/
      Encaminhado/Concluido→filtros por status, Menu→dropdown, Logout→logout.
      Implementado como link+hash para as âncoras `data-toggle="tab"` existentes
      (`temas/v2/rma/index.blade.php`) quando fora do índice — decisão de
      arquitetura registrada em CMP-V2-001, não cria rotas novas por status.
- [x] CP17-03 — dropdown "Menu" com os itens históricos na ordem:
      Creditos/Assistencias/Fabricantes/Fornecedores/Clientes/Relatorios/Anotacoes/
      Controle/"Trocar p/ 14.6.1" — rotas modernas, texto/posição/apresentação
      históricos. Usuários adicionado (gated) por não ter lugar histórico óbvio,
      documentado inline como acréscimo V3 (mesmo critério de VIS-V1-008).
      "Anotacoes" aponta para o perfil por falta de página própria — `[GAP]`
      documentado inline, não fabricado.
- [x] CP17-04 — importado `bootstrap/js/dropdown` — necessário (o Menu histórico é
      mesmo um dropdown Bootstrap real, `data-toggle="dropdown"`).
- [x] CP17-05 — aplicada a geometria do `header` (fixed, 40px, `#C20F41`,
      `border-bottom:1px solid #333`) e o estado ativo (`#FE0048`) — sem usar
      `#18354B`/`#224A5D` nesta superfície (removida a regra antiga que usava essas
      cores para o `.active`).
- [x] CP17-06 — aplicado `.nav-tabs li`/`.nav-tabs li a` (altura 39px, `font-size:14px`,
      `font-weight:300`, hover/active `#F67D7D`) e as larguras por item (11.1%,
      medidas ≈132px cada em todas as 4 viewports testadas).
- [x] CP17-07 — `.container`/`.tab-content` fundidos em um só elemento com
      `margin-top:40px; min-height:450px; padding:0` — ver diferença de 3px
      documentada em CMP-V2-001 (aceitável, mesma tolerância da fase 1 V1).
- [x] CP17-08 — H1 automático removido do layout V2 (nunca existia no Legacy, nem
      como `sr-only` — a árvore de `index.php` não tem heading nenhum aqui).
- [x] CP17-09 — capturar/reabrir/comparar header+nav completo (incluindo dropdown
      aberto), registrar diário com `x`/`width`/`height` de cada `<li>`.
- [x] CP17-10 — testes focados/build e commit local.

## CP18 — cascata e fontes do V2

Fonte: `legacy-source/15.8.1/index.php` (ordem de `<link>`/`<script>`),
`legacy-source/pattern/15.9.7.css` (overrides finais com `!important` em
`body`/`tr`/`td`/headings/breadcrumb/`.nav-tabs li`/`.upmenuright`).

- [x] CP18-01 — inspecionar cada seletor citado no achado 12 do prompt original
      (`_compartilhado.scss` atual tem `.breadcrumb { font-family:"Fira Mono",
      "Arial", "Fira Sans"; }` — comparar contra o computed style real do Legacy
      para breadcrumb, não assumir que a regra está errada sem medir primeiro).
- [x] CP18-02 — medir `getComputedStyle` + CDP `CSS.getPlatformFontsForNode` para:
      `body`, `header`, `.nav-tabs`, `.nav-tabs li`, `.nav-tabs li a`, breadcrumb de
      Pesquisar, input de busca, botão de busca, títulos do Centro de Avisos, `th`,
      `td`, menu direito, footer — Legacy × V3. `th`/`td`/títulos do Centro de
      Avisos/input de busca ainda não têm elemento vivo no V3 (dependem de CP20/22/23)
      — remedir quando existirem.
- [x] CP18-03 — corrigir só os seletores onde a medição confirmar divergência —
      preservar os que já baterem (não redesenhar o que está certo, mesma disciplina
      do achado 26 da fase 2 V1).
- [x] CP18-04 — confirmar ordem de cascata do CSS final compilado (Bootstrap → base
      V2 → compartilhado → media) — **achado real: estava invertida** (compartilhado
      antes de v2-base), corrigida.
- [x] CP18-05 — capturar/reabrir/comparar, registrar diário.
- [x] CP18-06 — testes focados/build e commit local.

### CMP-V2-002 — CP18, cascata e fontes

- Ambiente: Chromium headless (Playwright), zoom 100%, DPR 1, viewport 1440×1000, sem
  bloquear fontes remotas. Medido `getComputedStyle` + CDP
  `CSS.getPlatformFontsForNode` em `body`, `.nav-tabs li a`, `.upmenuright`/
  `#menuright`, `.designedby` — Legacy × V3.
- **Achado 1 (real, corrigido):** ordem de `@use` em `v2.scss` estava invertida —
  `compartilhado` (equivalente a `15.9.7.css`) carregava ANTES de `v2-base`
  (equivalente a `15.8.1.css`), quando o Legacy carrega `15.8.1.css` primeiro e
  `15.9.7.css` por último (camada final de override `!important`). Confirmado no CSS
  compilado (não só no SCSS fonte, `.btn-default` @43380 → `.shell-v2` @113420 →
  `.TrZebrada1` @116444, ordem correta depois da correção). Mesmo tipo de achado que
  gerou CP1 na fase 1 do Tema V1.
- **Achado 2 (real, corrigido):** `.breadcrumb` tinha `font-family:"Fira Mono",
  "Arial","Fira Sans"` sem `!important`, contra `"Arial","Open Sans","Fira
  mono" !important` do Legacy (`pattern/15.9.7.css:180`). Na prática o valor
  computado já era o correto mesmo antes da correção (a regra `body{...!important}`
  já vencia por importância, independente de especificidade) — corrigido mesmo assim
  por fidelidade de código-fonte (a regra antiga era enganosa: parecia efetiva mas
  nunca era). `.breadcrumb` não tem elemento vivo hoje (só volta a ser usado no
  CP20, Pesquisar).
- **Achado 3 (real, corrigido, o mais substancial):** a sidebar (`.shell-v2__sidebar`
  ← `.upmenuright`) tinha só a geometria portada no CP16, faltando
  background/cor/peso/espaçamento. A medição revelou uma pegadinha de cascata que a
  leitura isolada do CSS não capturava: `#menuright` (o `<div>` real do Legacy) tem
  `style` INLINE que neutraliza boa parte da classe `.upmenuright`
  (`background-color:rgba(0,0,0,0)`, `padding:0px`, `font-weight:normal`, todos
  inline — vencem a classe mesmo sem `!important`, porque estilo inline sempre
  vence CSS de classe). O filho direto tem SUA PRÓPRIA borda inline `1px solid
  #444` (não a `#443E3D` da classe, que nunca chega a ser aplicada). Resultado real
  computado — fundo transparente, sem padding, peso normal, borda `#444`, só
  `color:#E3E1DA`/`letter-spacing:3px`/`text-align:center` da classe sobrevivem —
  é o que foi portado, não a leitura ingênua da regra CSS isolada.
- Tabela de medidas (Legacy × V3):

  | Seletor | Propriedade | Legacy | V3 (antes) | V3 (depois) |
  |---|---|---|---|---|
  | `body` | `font-family` | `Arial, Open Sans, Fira mono` | idêntico | idêntico (já correto) |
  | `.breadcrumb` | `font-family` (computado) | N/A (sem elemento vivo) | já correto por herança | agora correto também por regra própria |
  | `#menuright` | `font-family` | `Arial, Open Sans, Fira mono` | `Open Sans, Arial, Roboto` (herdava do `html *`) | `Arial, Open Sans, Fira mono` |
  | `#menuright` | `font-weight` | `400` (normal, inline vence classe) | `300` (herdado do body) | `400` |
  | `#menuright` | `background-color` | `transparent` (inline vence) | não definido (herdava dark do body ok mas sem regra própria) | `transparent` (correto, sem regra) |
  | `#menuright` filho | `border` | `1px solid #444` | `1px solid #444` (já estava certo por acaso) | mantido `#444` |
  | `#menuright` | `letter-spacing` | `3px` | ausente | `3px` |
  | `#menuright` | `color` | `#E3E1DA` | ausente | `#E3E1DA` |
- Diferença perceptível restante: nenhuma nos seletores medidos. `th`/`td`/títulos do
  Centro de Avisos/input de busca ficam pendentes de CP20/22/23 (sem elemento vivo
  ainda).
- Decisão: **CP18 APROVADO** para os seletores com elemento vivo hoje.
- Testes/build: `php artisan test` (363/818, verde); `npm run build` (ok);
  `ParidadeVisualTemaV1.spec.ts` (4/4, zero regressão no V1).
- Commit: a seguir (`#ARQ-RMA - Corrige a cascata de estilos e a fonte da barra
  lateral do Tema V2`).

## CP19 — menu lateral direito (`rightmenu.php`)

Fonte: `legacy-source/15.8.1/inc/rightmenu.php` (lido por inteiro nesta sessão — **14
seções**, cada uma com `LRTOP1`/`LRTOP2` alternado, clique via `right('#id')`
(colapsa/expande, todas começam `display:none` exceto conferir se alguma inicia
aberta), lista de `LiRight1`/`LiRight2` zebrada, uma query `mysqli` própria por
seção): `DEU ENTRADA HOJE` (`right_entrada`), `RECEBIDOS` (`right_recebido`),
`ENCAMINHADOS` (`right_encaminhado`), `LAST 10 CONCLUIDOS` (`right_concluido`),
`DESTINATARIOS` (`right_destinatarios`), `TRANSPORTE P/ PORTO A`
(`right_portoalegre`), `URGENTE` (`right_urgente`), `PENDENTE CREDITO`
(`right_pendentecredito`), `CREDITO DISPONIVEL` (`right_creditodisponivel`),
`FABRICANTES` (`right_fabricantes`), `FORNECEDORES` (`right_fornecedores`),
`CLIENTES` (`right_clientes`), `PRODUTOS DE CLIENTE` (`right_produtosdecliente`),
`TODOS PRODUTOS` (`listar_nome_de_descricoes`). Item maior desta frente.

- [x] CP19-01 — para cada uma das 14 seções: identificar o caso de uso/read model V3
      já existente que produz o dado equivalente (várias já devem existir —
      contadores por status, últimos RMAs, listagem de fabricantes/fornecedores/
      clientes); só criar query nova onde não existir equivalente moderno.
      `PRODUTOS DE CLIENTE`/`TODOS PRODUTOS`/`TRANSPORTE P/ PORTO A` são as mais
      prováveis de não terem equivalente — investigar antes de assumir. **Achado**:
      `URGENTE` e `PENDENTE CREDITO` já tinham classe de leitura idêntica
      (`UrgenciaPorThreshold`/`AguardandoCredito`, Fase 5) — reutilizadas sem
      alteração. As outras 12 são novas, em `app/Rma/Aplicacao/PainelLateral/`.
- [x] CP19-02 — implementar a estrutura visual: `LRTOP1`/`LRTOP2` alternados como
      cabeçalho clicável de cada seção, `LiRight1`/`LiRight2` zebrado nas linhas,
      "Nenhum encontrado" quando vazio (reproduzir exatamente, não um estado vazio
      genérico diferente por seção).
- [x] CP19-03 — comportamento de expandir/colapsar por clique — reaproveitado
      `data-pmo-alvo` (já existia em `v1.js`/`v2.js`), sem JS novo.
- [x] CP19-04 — truncamento de nome em 16 caracteres (`mb_substr`, todas as 14
      seções) e formato de data por seção. **`H:m` de "Deu entrada hoje" era mesmo
      `[BUG-LEGADO]`** (`m`=mês, não minuto) — corrigido para `H:i`, decisão
      registrada no código (`ListarPainelLateral::listar()`), não é ambiguidade
      cosmética como acento/encoding.
- [x] CP19-05 — geometria: container 195px dentro do shell (CP16), `LiRight1/2`
      `min-height:28px`, `padding:6px`/`6px 5px` conforme a seção (lista/contagem),
      `font-size:12px`, `letter-spacing:1px` em `LRTOP1/2`/`LiRight1/2` (aplicado
      uniforme nas 14 — a inconsistência do PHP fonte, onde só a 1ª seção tem o
      `style` inline explícito, não é uma diferença visual real: todas herdam o
      mesmo `letter-spacing` da classe).
- [x] CP19-06 — capturar/reabrir/comparar sidebar completa (várias seções
      expandidas simultaneamente, incluindo um caso "Nenhum encontrado" real —
      `TRANSPORTE P/ PORTO A`), registrar diário.
- [x] CP19-07 — testes focados/build e commit local.

### CMP-V2-003 — CP19, menu lateral direito (14 seções)

- Ambiente: Chromium headless (Playwright) + inspeção manual do HTML/SQL fonte
  (`15.8.1/banco.php:708-939`, `metodo.php:99`). Viewport 2048×1152/1440×1000.
- Arquitetura: 12 classes de leitura novas (`app/Rma/Aplicacao/PainelLateral/`, mesmo
  padrão de `Alertas/*`, uma classe por seção, `listar(): Collection`), 2 reutilizadas
  (`Alertas\UrgenciaPorThreshold`, `Alertas\AguardandoCredito` — já implementavam
  exatamente `right_urgente()`/`right_pendentecredito()` desde a Fase 5, nenhuma regra
  de negócio nova). Agregador `ListarPainelLateral` compõe as 14 e normaliza cada item
  para `{nome, valor, id?}` — a view não faz nenhum cálculo. Injeção via
  `View::composer('temas.v2.layout', ...)` (`AppServiceProvider`), mesmo padrão já
  usado para o painel "Novo" do TEMA V1 (a sidebar aparece em toda página V2, não só
  numa rota).
- Achados de tradução SQL→Eloquent (grupos agregados: `GROUP BY` do legado vira
  `Collection::groupBy()` em PHP após buscar os registros filtrados — sem SQL bruto na
  view, mesma disciplina já usada em `Alertas/*`):
  - `DESTINATARIOS`/`CREDITO DISPONIVEL`: legado agrupa por texto solto
    (`destinatario`); V3 tem relação polimórfica (`destinatario_type`+`id`) — agrupado
    pelo par tipo+id (mesmo conceito de
    `ListagensPorStatusController::mapaDeDestinatarios()`) para não colidir ids de
    tabelas diferentes.
  - `CLIENTES`: `Rma` (Eloquent) não tem relação `cliente()` própria — resolvido por
    mapa de nomes (`Cliente::whereIn('id',...)->pluck('nome','id')`), sem adicionar
    relação nova ao model por enquanto (fora do escopo desta correção visual).
  - `TRANSPORTE P/ PORTO A`: o legado faz 3 `LEFT JOIN` por NOME de texto solto contra
    fornecedor/fabricante/assistência técnica; o V3 tem FK reais — virou `whereHas`/
    `whereHasMorph` direto (mais correto que o legado, sem inventar dado).
  - `URGENTE`: conferido que `UrgenciaPorThreshold` já é exatamente
    `right_urgente()` (mesmos status, mesma condição
    origem+marcarestoque+valor+prazo OU prioridade alta) — reaproveitado sem tocar.
- **[BUG-LEGADO] confirmado e corrigido:** `right_entrada()` formata a hora com
  `date('H:m', ...)` — `m` é mês, não minuto (`i`). Exibiria algo como "14:08"
  parecendo "14h08" quando na verdade é "hora 14, mês 08" — informação
  ativamente errada, não uma diferença cosmética. Corrigido para `H:i`.
- Screenshots versionados (dado fictício de QA, sem cliente/produto real):
  `docs/produto/screenshots-vis-v2-001/04-v3-sidebar-14-secoes-expandidas.png`.
- Verificação funcional real (não só visual): as 14 seções renderizam os 14 títulos
  na ordem correta; clique expande/colapsa; nomes truncados em 16 caracteres;
  `FABRICANTES`/`DESTINATARIOS`/`CREDITO DISPONIVEL` mostram nome+contagem reais do
  seed de QA; `TRANSPORTE P/ PORTO A` mostra "Nenhum encontrado" (nenhum
  fornecedor/fabricante/assistência do seed fica em Porto Alegre — estado vazio real,
  não simulado).
- Diferença perceptível restante: nenhuma na estrutura/geometria/comportamento.
  Conteúdo exato depende do dado real em produção (não comparável 1:1 com o Legacy
  neste ambiente de QA, mesma limitação já registrada para outras seções desta
  frente).
- Decisão: **CP19 APROVADO**.
- Testes/build: `php artisan test` (363/818, verde — nenhum teste quebrou ao injetar
  a sidebar em toda página V2, incluindo os testes de `RenderizaTemaV2Test`);
  `npm run build` (ok); `ParidadeVisualTemaV1.spec.ts` (4/4, zero regressão V1).
- Commit: a seguir (`#ARQ-RMA - Adiciona o menu lateral direito com as 14 secoes do
  Tema V2`).

## CP20 — Home e Pesquisar

Fonte: `legacy-source/15.8.1/page/inicio.php`, `page/pesquisar.php`,
`inc/menu_pesquisar.php` (ler os 3 por inteiro antes de implementar — ainda não
lidos nesta sessão).

- [x] CP20-01 — ler os 3 arquivos fonte por inteiro. **Achado que muda o CP17:**
      `page/inicio.php` faz `include("page/pesquisar.php")` por inteiro — Início e
      Pesquisar são a MESMA composição (breadcrumb+busca+tabela), não duas telas
      diferentes como o comentário do CP17 assumia. Corrigido nesta rodada.
- [x] CP20-02 — "Bem-vindo(a), usuário." já não existe no runtime Legacy (confirmado
      por captura no CP16/CP17) e já tinha sido removido do V3 então.
- [x] CP20-03 — reproduzir a composição real da Home: `_pesquisar_conteudo.blade.php`
      (breadcrumb+busca+tabela, compartilhado com #pesquisar) → separador2 → Centro
      de Avisos, nesta ordem exata.
- [x] CP20-04 — breadcrumb "Qualquer campo / Nota fiscal / Número de série"
      (`_breadcrumb_pesquisar.blade.php`) no lugar do `<select>` genérico, mapeado
      para os 3 critérios já existentes (`CriterioDeBusca::porTexto/porNotaFiscal/
      porSerial`) — nenhuma regra de busca nova.
- [x] CP20-05 — capturar/reabrir/comparar Home+Pesquisar, registrar diário.
- [x] CP20-06 — testes focados/build e commit local.

## CP21 — separador antes do Centro de Avisos

- [x] CP21-01 — `separador2.png` (e `lembrete.png`/`separador.png`, usados por
      CP21/CP22) portados para `public/images/tema-v2/`, hash conferido byte a byte
      contra o Legacy.
- [x] CP21-02 — aplicado `float:right; margin-top:50px; height:40px` inline, na
      posição real (entre a tabela de busca e o Centro de Avisos) — não usa a classe
      genérica `.separador-alerta`.
- [x] CP21-03 — capturado/comparado junto do CP20 (mesma tela); commit junto do CP20.

### CMP-V2-004 — CP20/CP21, Início/Pesquisar unificados + separador

- Ambiente: Chromium headless (Playwright), 2048×1152, dado fictício de QA (busca por
  "EQUIPAMENTO", ~60 resultados).
- Achado central (muda uma decisão do CP17): lidos por inteiro
  `15.8.1/page/inicio.php`, `page/pesquisar.php`, `inc/menu_pesquisar.php`,
  `subp/pesquisar_rma.php`. `page/inicio.php` literalmente `include("page/
  pesquisar.php")` — Início não é uma versão "simplificada" da busca (como o
  comentário original do CP17 assumia sem ter lido o fonte ainda); é a MESMA tela,
  com separador+Centro de Avisos anexados depois. Corrigido: `#inicio` e `#pesquisar`
  agora incluem o mesmo partial `_pesquisar_conteudo.blade.php`.
- Implementado nesta rodada (extrapolando um pouco do CP20 para dentro do CP23,
  registrado aqui para não fazer o trabalho duas vezes): a tabela de resultados real
  de `subp/pesquisar_rma.php` — 11 colunas (`DT ENTRADA` 9%, `ORIGEM` 8%, `NF C` 6%,
  `NF V` 6%, `FABRICANTE` 12%, `DESCRICAO` 13%, `MODELO` 20%, `S/N` 16%, `OS` 5%, `S`
  2%, `A` 2%), ícone de status (`entrada`/`recebido`/`encaminhado`/`concluido.png`,
  25px) e ícone de ação (`ver.png`, link para `rmas.show`) — substituindo o
  `_tabela.blade.php` genérico (`#`/Descrição/Defeito/Origem/Ações) só nesta tela.
  `RmaController@index` ganhou `mapaDeFabricantes()` (mesmo padrão de
  `ListagensPorStatusController`) porque a tabela histórica mostra o nome do
  fabricante, que a busca não resolvia antes.
- **[INVESTIGAR] registrado, não corrigido:** a zebra de `pesquisar_rma.php` usa
  `TrSemGarantia1/2` só quando `status=concluido AND solucao=SEM GARANTIA` — fora
  dessa combinação específica, solução "SEM GARANTIA" cai no mesmo `TrInconformidade`
  dos outros critérios. `Rma::classeDeAlerta()` (Fase 5) mapeia solução SemGarantia
  para `Inconformidade` sempre, sem olhar o status. Reaproveitada sem alteração (não
  reescrever regra de negócio sem necessidade) — divergência documentada no código
  (`_tabela_pesquisa.blade.php`) para decisão futura, não corrigida às cegas.
- **Achado sobre `.painel-inicio-fundo-escuro`:** a classe existia para "escapar" de
  um fundo branco que a estrutura antiga aplicaria a `#inicio`. Lendo `pattern/
  15.8.1.css` inteiro, `.tab-content` não tem `background-color` própria (só
  `color:#FFF`) e `.box-content`/`.blocos` (brancas) não são referenciadas por
  `page/inicio.php`/`page/pesquisar.php` — o fundo escuro já é o padrão do
  `.shell-v2 > .container` (herda de `body`) na estrutura nova do CP16. Confirmado
  por captura: removida a classe de escape, fundo continua escuro corretamente.
- Legado não imprime nenhum resumo (`$soma`/`$quantidadetotal` são calculados em
  `pesquisar_rma.php` mas nunca usados no HTML — confirmado por leitura completa do
  arquivo) — nenhum "VALOR TOTAL" nesta tela, diferente de Concluídos no TEMA V1.
  Também não emite nenhuma mensagem quando a busca não retorna nada (`else { }`
  vazio) — reproduzido literalmente (sem HTML nesse caso).
- Assets portados e conferidos por hash: `entrada.png`, `recebido.png`,
  `encaminhado.png`, `concluido.png`, `ver.png`, `separador2.png`, `lembrete.png`,
  `separador.png` → `public/images/tema-v2/`.
- Screenshot versionado (dado fictício de QA):
  `docs/produto/screenshots-vis-v2-001/05-v3-inicio-pesquisar-unificados.png`.
- Diferença perceptível restante: nenhuma na composição/geometria/tabela. A zebra
  `TrSemGarantia` fica para investigação futura (achado acima).
- Decisão: **CP20 e CP21 APROVADOS**.
- Testes/build: `php artisan test` (363/818, verde); `npm run build` (ok);
  `ParidadeVisualTemaV1.spec.ts` (4/4, zero regressão V1).
- Commit: a seguir (`#ARQ-RMA - Unifica Inicio e Pesquisar e restaura a tabela de
  busca historica do Tema V2`).

## CP22 — Centro de Avisos

Fonte: mesma base já mapeada na fase 2 do Tema V1 (`legacy-source/14.6.1/inc/
startpage.php`) mas **atenção**: o Centro de Avisos do V2 é servido por
`15.8.1/page/inicio.php` e pelos `subp/listar_*.php` do próprio `15.8.1` (ou
compartilhados com `14.6.1` via `ListarGruposDeAlertas`?) — confirmar antes de
reaproveitar cegamente o trabalho da fase 2 V1, que era escopo `14.6.1`.

- [x] CP22-01 — confirmado: `15.8.1/page/inicio.php` inclui `subp/listar_*.php`
      DIRETO (são arquivos do próprio `15.8.1`); `14.6.1/inc/startpage.php` inclui os
      MESMOS arquivos via `../15.8.1/subp/listar_*.php` — fonte única, compartilhada
      pelos dois temas, confirma que `ListarGruposDeAlertas` (Fase 5) já é a
      composição certa para os dois. **Achado**: `page/inicio.php` (lido por inteiro)
      inclui só **10 dos 11** grupos de `ListarGruposDeAlertas` — falta
      "Urgência por valor" na Home do V2 (não verificado se aparece em outra tela do
      V2; fora de escopo desta correção visual).
- [x] CP22-02 — ícone/título/hr já estavam corretos (herdados do trabalho do CP20,
      que por engano duplicava esse cabeçalho — removida a duplicação nesta rodada).
- [x] CP22-03 — **parcial, com escopo explícito**: corrigidos ordem (10 grupos, ordem
      literal de `page/inicio.php`) e texto do título (literal de cada
      `subp/listar_*.php`, não o nome descritivo interno) — reordenação/relabel só na
      view do TEMA V2 (`temas/v2/rma/index.blade.php`), sem tocar
      `ListarGruposDeAlertas` (usada também por `PainelDeAlertasController` e pelo
      TEMA V1, cuja própria ordem/título ainda não foi verificada — CP12 da fase 2 V1,
      não iniciado). Estado inicial Mostrar/Ocultar conferido nos 10 arquivos fonte:
      todos começam iguais (`Mostrar` visível, dados ocultos) — já era o comportamento
      do partial existente, nenhuma mudança necessária. **[GAP] não corrigido nesta
      rodada, documentado**: cada `subp/listar_*.php` tem uma tabela de colunas
      própria (ex.: `PROTOCOLO ABERTO`, achado do prompt original, confirma-se ao ler
      o arquivo: `RECEBIDO`/`T`/`ORIGEM`/`NF C`/`NF V`/`FORNECEDOR`/`FABRICANTE`/
      `DESCRICAO`/`MODELO`/`OS`/`A`); o partial compartilhado
      `rma/_centro_de_avisos.blade.php` ainda usa lista genérica (`#id — descrição`)
      para todos. Redesenhar as ~10 tabelas próprias é escopo grande, num componente
      COMPARTILHADO com o TEMA V1 (mudar agora arriscaria a paridade V1 sem a
      verificação própria dela) — registrado como pendência para uma frente futura
      dedicada, não implementado às pressas.
- [x] CP22-04 — capturar/reabrir/comparar, registrar diário.
- [x] CP22-05 — testes focados/build e commit local.

### CMP-V2-005 — CP22, ordem e títulos do Centro de Avisos

- Ambiente: leitura completa dos 10 `subp/listar_*.php` + Playwright (2048×1152, dado
  fictício de QA).
- **Bug próprio encontrado e corrigido:** o CP20 desta sessão introduziu sem querer
  um cabeçalho duplicado do Centro de Avisos (ícone/título/hr) em
  `temas/v2/rma/index.blade.php`, não percebendo que
  `rma/_centro_de_avisos.blade.php` (partial compartilhado, pré-existente) já
  renderiza esse mesmo cabeçalho internamente. Removida a duplicação.
- Achado de ordem/título: `ListarGruposDeAlertas::listar()` usa nomes descritivos
  como chave (`'Recebidos há mais de 30 dias sem encaminhar'`) numa ordem própria da
  Fase 5; nenhum dos dois bate com `page/inicio.php`. Mapeados os 10 títulos literais
  direto de cada `subp/listar_*.php` (`<li ...>TITULO:</li>`) e a ordem exata dos
  `include()`. Reordenação feita SÓ na view do TEMA V2 (array
  `$ordemHistoricaCentroDeAvisosV2` em `index.blade.php`), sem tocar
  `ListarGruposDeAlertas` — a mesma classe alimenta `PainelDeAlertasController` e o
  TEMA V1 (via `RmaController@index`, view diferente), cuja ordem/título própria
  ainda não foi auditada (fica para o CP12 da fase 2 V1). Mudar a classe compartilhada
  agora seria arriscar essas duas superfícies sem prova.
- Achado de composição: `page/inicio.php` inclui só 10 dos 11 grupos —
  "Urgência por valor" (`UrgenciaPorThreshold`) não aparece na Home do TEMA V2.
  Excluído da renderização do V2 (filtro no mesmo array de ordenação).
- Tabela (Legacy × V3), título e posição, confirmados via
  `document.querySelectorAll('.regra-de-alerta-titulo')`:

  | # | Legacy (`subp/listar_*.php`) | V3 (renderizado) |
  |---|---|---|
  | 1 | PRODUTOS COM MAIOR PRIORIDADE SEM ENCAMINHAMENTO | idêntico |
  | 2 | PROTOCOLO ESTA ABERTO E O PRODUTO NAO ENCAMINHADO | idêntico |
  | 3 | NECESSARIO IDENTIFICAR O S/N | idêntico |
  | 4 | SEM NF DE COMPRA E NF DE VENDA | idêntico |
  | 5 | O DESTINATARIO ESTOUROU O PRAZO DE 30 DIAS PARA RETORNAR | idêntico |
  | 6 | RECEBIDO A MAIS DE 30 DIAS E NAO ENCAMINHADO | idêntico |
  | 7 | PRAZO DE GARANTIA COM O FORNECEDOR EXPIRADO MAIS DE 1 ANO | idêntico |
  | 8 | FALTA MENOS DE 30 DIAS PARA EXPIRAR GARANTIA DE 1 ANO COM O FORNECEDOR | idêntico |
  | 9 | NAO VAI DAR GARANTIA | idêntico |
  | 10 | PRODUTOS COM PENDENCIA DE LANCAR NF DO RETORNO | idêntico |

- **[GAP] registrado, não implementado:** cada grupo tem tabela de colunas própria no
  Legacy (confirmado lendo os 10 arquivos), o V3 ainda usa lista genérica
  `#id — descrição` para todos (componente compartilhado com o TEMA V1, escopo grande
  demais para esta rodada — ver justificativa completa no checklist acima).
- Screenshot versionado:
  `docs/produto/screenshots-vis-v2-001/06-v3-centro-de-avisos-ordem-corrigida.png`.
- Diferença perceptível restante: nenhuma na ordem/título/estrutura de abrir-fechar.
  Composição interna de cada grupo (tabela própria vs lista) é o gap registrado.
- Decisão: **CP22 APROVADO** com o gap de composição por grupo explicitamente
  registrado como pendência futura, não como "concluído por completo".
- Testes/build: `php artisan test` (363/818, verde); `npm run build` (ok);
  `ParidadeVisualTemaV1.spec.ts` (4/4, zero regressão V1 — a reordenação é só na view
  V2, `ListarGruposDeAlertas` e o partial compartilhado não mudaram de
  comportamento).
- Commit: a seguir (`#ARQ-RMA - Corrige ordem e titulos do Centro de Avisos do Tema
  V2`).

## CP23 — tabelas das abas (Pesquisar/Entrada/Recebido/Encaminhado/Concluído)

Fonte: `legacy-source/15.8.1/page/{pesquisar,entrada,recebido,encaminhado,
concluido}.php` (nenhum lido ainda nesta sessão — ler cada um por completo antes de
implementar a aba correspondente).

Achado confirmado pelo prompt original (larguras da busca geral, a conferir contra o
PHP fonte antes de aplicar): `DT ENTRADA 9%`, `ORIGEM 8%`, `NF C 6%`, `NF V 6%`,
`FABRICANTE 12%`, `DESCRICAO 13%`, `MODELO 20%`, `S/N 16%`, `OS 5%`, `S 2%`, `A 2%`.

- [ ] CP23-01 — ler os 5 arquivos fonte por completo.
- [ ] CP23-02 — para cada aba: extrair colunas/larguras/ordem/ícones/formatação/
      classe de linha/wrappers de link, mesmo processo já usado em CP3A-D da fase 1
      V1 (não reusar 1:1 os valores do Tema V1 — são temas/telas diferentes, mesmo
      que o padrão de trabalho seja o mesmo).
- [ ] CP23-03 — **não forçar as 5 abas para o `_tabela.blade.php` genérico atual**
      (hoje só tem `#`/Descrição/Defeito/Origem/Ações) se as tabelas históricas
      realmente divergem entre si — decidir por aba, com evidência, como já decidido
      no achado 22 do prompt original. Pode compartilhar células/presenters/
      formatadores/classes de zebra sem compartilhar a tabela inteira.
- [ ] CP23-04 — reaproveitar a regra de destaque de linha já existente
      (`TrZebrada1/2`, `TrSemGarantia1/2`, `TrInconformidade`) sem reescrever a regra
      de negócio — comparar só a classe final/cor/altura/hover para os mesmos
      cenários.
- [ ] CP23-05 — capturar/reabrir/comparar as 5 abas, registrar diário.
- [ ] CP23-06 — testes focados/build e commit local (um commit por aba é aceitável
      dado o volume).

## CP24 — footer

- [ ] CP24-01 — remover a linha "TEMA V2 — CellSystem RMA (reconstrução V3)" do
      `layout.blade.php` (não existe no Legacy).
- [ ] CP24-02 — preservar só "Designed by Scripting Studios Art" e "Cópia licenciada
      para Cellsystem LTDA" com posição/`font-size`/cor/`letter-spacing`/alinhamento
      históricos (medir, não assumir que o que já existe está certo).
- [ ] CP24-03 — capturar/reabrir/comparar, registrar diário e commit (pode ser junto
      de outro checkpoint pequeno).

## CP25 — gate final da paridade V2

- [ ] CP25-01 — rodar suíte PHP completa e Vite build.
- [ ] CP25-02 — rodar Playwright visual completo; confirmar Tema V1 sem regressão
      (obrigatório sempre que `_compartilhado.scss` for tocado — ver regra CP16-08/
      seção 30 do prompt original).
- [ ] CP25-03 — comparar em 2048×1152, 1440×1000, 1562×1400 e 1700×1000.
- [ ] CP25-04 — gerar screenshot + overlay 50% + diff absoluto para pelo menos: Home,
      Pesquisar, Entrada, Encaminhado, Concluído, menu direito expandido, dropdown
      Menu aberto.
- [ ] CP25-05 — abrir cada par final e registrar uma entrada no diário.
- [ ] CP25-06 — produzir a tabela final por elemento (formato da seção 31 do prompt
      original) e caminhos dos screenshots.
- [ ] CP25-07 — atualizar `docs/produto/checklist-paridade-visual-v1-runtime.md` (se
      aplicável ao V2) e `PLANO-ATAQUE.md`, criar commit final do checkpoint.

## Diário de comparação

Cada `CMP-V2-NNN` deve registrar: checkpoint/tela; fonte PHP/CSS; ambiente; caminhos
dos prints; elementos abertos; tabela de medidas Legacy/V3 (`x`/`y`/`width`/`height`,
não só `width`); fonte rasterizada; diferença perceptível; seletor responsável;
decisão; testes/build; commit. Sem esses campos, o item permanece aberto. (Nenhuma
entrada ainda — plano recém-criado, execução começa por CP16.)
