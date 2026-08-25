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

- [ ] CP19-01 — para cada uma das 14 seções: identificar o caso de uso/read model V3
      já existente que produz o dado equivalente (várias já devem existir —
      contadores por status, últimos RMAs, listagem de fabricantes/fornecedores/
      clientes); só criar query nova onde não existir equivalente moderno.
      `PRODUTOS DE CLIENTE`/`TODOS PRODUTOS`/`TRANSPORTE P/ PORTO A` são as mais
      prováveis de não terem equivalente — investigar antes de assumir.
- [ ] CP19-02 — implementar a estrutura visual: `LRTOP1`/`LRTOP2` alternados como
      cabeçalho clicável de cada seção, `LiRight1`/`LiRight2` zebrado nas linhas,
      "Nenhum encontrado" quando vazio (reproduzir exatamente, não um estado vazio
      genérico diferente por seção).
- [ ] CP19-03 — comportamento de expandir/colapsar por clique (equivalente moderno de
      `right('#id')`, reaproveitando o padrão `data-pmo-alvo` já usado em
      `v2.js`/`v1.js` se ele servir; senão, um pequeno helper próprio — sem jQuery
      novo).
- [ ] CP19-04 — truncamento de nome em 16 caracteres (`substr($nome,0,16)`) e formato
      de data por seção (`H:m` em Entrada Hoje — provável erro histórico, confirmar
      se é `H:i` pretendido antes de reproduzir literalmente um formato quebrado;
      `d/m` nas demais) — documentar se algum for `[BUG-LEGADO]` antes de decidir
      reproduzir ou corrigir.
- [ ] CP19-05 — geometria: container 195px dentro do shell (CP16), `LiRight1/2`
      `min-height:28px`, `padding:6px`/`6px 5px` conforme a seção, `font-size:12px
      !important`, `letter-spacing:1px` só na primeira seção (conferir se é
      intencional ou inconsistência do legado).
- [ ] CP19-06 — capturar/reabrir/comparar sidebar completa (todas as 14 seções
      expandidas ao menos uma vez), registrar diário.
- [ ] CP19-07 — testes focados/build e commit local. Pode dividir em mais de um
      commit se o volume justificar (ex.: um commit para a estrutura visual + toggle,
      outro para os read models de dado).

## CP20 — Home e Pesquisar

Fonte: `legacy-source/15.8.1/page/inicio.php`, `page/pesquisar.php`,
`inc/menu_pesquisar.php` (ler os 3 por inteiro antes de implementar — ainda não
lidos nesta sessão).

- [ ] CP20-01 — ler os 3 arquivos fonte por inteiro.
- [ ] CP20-02 — remover "Bem-vindo(a), usuário." se não existir no runtime Legacy
      (confirmar por captura, não só pela leitura do PHP).
- [ ] CP20-03 — reproduzir a composição real da Home: inclui `pesquisar.php` no topo,
      depois separador (CP21), depois Centro de Avisos (CP22).
- [ ] CP20-04 — reproduzir o formulário de Pesquisar histórico: breadcrumb "Qualquer
      campo / Nota fiscal / Número de série" (não um `<select>` genérico — troca
      visual confirmada pelo prompt original, validar contra o PHP fonte), label
      "Pesquisar:", campo, botão "Enviar pesquisa", posicionado à direita.
- [ ] CP20-05 — capturar/reabrir/comparar Home+Pesquisar, registrar diário.
- [ ] CP20-06 — testes focados/build e commit local.

## CP21 — separador antes do Centro de Avisos

- [ ] CP21-01 — localizar `separador2.png` no Legacy, portar para
      `public/images/tema-v2/` (ou reaproveitar o já portado em `tema-v1/` se os
      bytes forem os mesmos — conferir hash antes de duplicar).
- [ ] CP21-02 — aplicar `float:right; margin-top:50px; height:40px` na posição real
      (Home, entre Pesquisar e Centro de Avisos) — não usar a classe genérica
      `.separador-alerta { margin:5px 0; }` do compartilhado para esta ocorrência
      específica se a geometria for diferente (achado 19 do prompt original).
- [ ] CP21-03 — capturar/reabrir/comparar, registrar diário e commit (pode ser junto
      de CP22).

## CP22 — Centro de Avisos

Fonte: mesma base já mapeada na fase 2 do Tema V1 (`legacy-source/14.6.1/inc/
startpage.php`) mas **atenção**: o Centro de Avisos do V2 é servido por
`15.8.1/page/inicio.php` e pelos `subp/listar_*.php` do próprio `15.8.1` (ou
compartilhados com `14.6.1` via `ListarGruposDeAlertas`?) — confirmar antes de
reaproveitar cegamente o trabalho da fase 2 V1, que era escopo `14.6.1`.

- [ ] CP22-01 — confirmar se `15.8.1` usa os mesmos `subp/listar_*.php` do `14.6.1`
      ou uma cópia própria; se for o mesmo caso de uso `ListarGruposDeAlertas`, este
      item pode encurtar bastante reaproveitando o que a fase 2 V1 (CP12) já
      resolver — checar a ordem de execução das duas frentes para não duplicar
      trabalho.
- [ ] CP22-02 — mapear ícone (`lembrete.png` 40px), título "CENTRO DE AVISOS E
      RELATORIOS", `hrup` com `divider.png`, `separador.png` entre alertas.
- [ ] CP22-03 — reproduzir composição por grupo (nem todos são lista genérica —
      mesma disciplina do CP12 da fase 2 V1) e estado inicial real de
      Mostrar/Ocultar.
- [ ] CP22-04 — capturar/reabrir/comparar, registrar diário.
- [ ] CP22-05 — testes focados/build e commit local.

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
