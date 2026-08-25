# Design — Apresentação (Temas V1/V2)

## Revisão 2026-08-24 — inspeção direta do LEGACY-RUNTIME (`:8094`)

Esta revisão releu o HTML/CSS/JS reais (não só os inventários) e corrigiu vários pontos
do `design.md` anterior. Achados detalhados, com evidência de arquivo/linha, nas seções
"Mecanismo de navegação por tema", "RN-11 em TEMA V1", "Estrutura de diretórios",
"Fontes" e "Pendências" abaixo. Resumo do que mudou:

- **Fundo real do TEMA V2 é `#262626` (escuro), não `#FFF`.** `pattern/15.8.1.css`
  linha 12-18 define `body { background-color: #262626; }` — o mesmo tom do TEMA V1.
  `#FFF`/`#EEE` são usados em painéis de conteúdo (`.box-content`, `.blocos`,
  `.linhasuperior`) sobre o fundo escuro, não a página inteira. Corrigido abaixo.
- **Existe uma TERCEIRA folha de estilo compartilhada pelos dois temas**,
  `pattern/15.9.7.css`/`.js` (296/181 linhas), carregada por `14.6.1/index.php` E por
  `15.8.1/inc/menu.php` (ambos incluem `<link href=".../pattern/15.9.7.css">` além do
  CSS próprio do tema). É nela que vivem as classes de alerta de linha
  (`TrInconformidade`, `TrUrgente`, `TrZebrada1/2`, `TrSemGarantia1/2`),
  `.breadcrumb`, `.centrodeavisos`, `.formSelect`, `.designedby`, `.pmo`. O
  `_compartilhado.scss` do plano anterior (só `$cor-alerta`) é raso demais — precisa
  portar esse arquivo real.
- **Pendência 1 (âncoras TEMA V2) RESOLVIDA:** são abas nativas do Bootstrap 3.3.5
  (`data-toggle="tab"` + `.tab-pane`), sem AJAX/fetch — ver seção dedicada abaixo.
- **Pendência 2 (RN-11 em TEMA V1) RESOLVIDA:** TEMA V1 usa as mesmas classes
  `TrInconformidade`/`TrUrgente`/`TrZebrada1`/`TrZebrada2` (via o CSS compartilhado
  15.9.7.css) em `page/entrada.php`, `page/encaminhados.php`, `page/localizar.php` — ver
  seção dedicada abaixo.
- **2 pendências novas registradas:** (1) fonte Open Sans do TEMA V2 nunca carrega de
  fato (URL absoluta morta) — ver seção "Fontes"; (2) comportamento pós-login
  assimétrico entre o login-gateway compartilhado e o login próprio de TEMA V1 — ver
  "Estrutura de diretórios".

## Paleta (fonte: `inventario-visual-tema-{v1,v2}.md` + confirmação direta em `pattern/{14.6.1,15.8.1,15.9.7}.css`)

```scss
// v1.scss (entry point Vite — sem underscore, ao contrário de _compartilhado.scss que
// só é importado via @use; ver "Organização Vite/Sass por tema" abaixo)
$fundo: #262626;
$acento: #C3FF00;
$texto: #FFF;
$fonte: "Open Sans", "Arial", "Fira Sans";
// TEMA V1 não depende de nenhum framework CSS (sem Bootstrap) — layout de formulário
// é HTML <table> autoral (`.tablenovo`, `.novo_formInput`), confirmado em
// `14.6.1/index.php` (painel "Novo" via JS show/hide, não Bootstrap grid).
$largura-fixa-tema-v1: 984px;  // #BASE/#TOPO/#CONTEUDO no legado — layout FIXO, sem
                                // nenhum @media. Nomeada aqui para nunca reaparecer como
                                // "984px" solto em Blade/CSS/teste — é a única fonte da
                                // verdade para o breakpoint (na verdade ausência de
                                // breakpoint) de TEMA V1.

// v2.scss (entry point Vite, mesma convenção acima)
$azul-petroleo: #224A5D;
$azul-marinho: #18354B;
$fundo: #262626;          // CORRIGIDO: mesmo fundo escuro do V1 (pattern/15.8.1.css:12-18)
$fundo-painel: #FFF;      // painéis/cards de conteúdo sobre o fundo escuro (.box-content, .blocos)
// TEMA V2 depende de verdade de Bootstrap 3.3.5 (grid `col-md-*`, `.form-group`,
// `.form-control`, plugin de abas) + AdminLTE 2.2.0 (só CSS de base, nenhuma skin de
// cor `.skin-*` ativa) — confirmado em `15.8.1/inc/menu.php` e no HTML renderizado.
$breakpoints-tema-v2: (            // fonte: 15.8.1/css/media.php — únicos breakpoints
  "sm": 568px,                     // reais do tema (Bootstrap 3 contribui os seus
  "md": 800px,                     // próprios 768/992/1200 à parte, não sobrepor).
  "lg": 992px,                     // Mapa nomeado para que nenhum destes 6 valores
  "xl": 1080px,                    // apareça solto em Blade/JS/Playwright — qualquer
  "xxl": 1280px,                   // consumidor (inclusive os testes de comparação
  "xxxl": 1366px,                  // visual) referencia este mapa, nunca um literal.
);

// _compartilhado.scss (porta pattern/15.9.7.css + .js, carregado pelos DOIS temas hoje)
$cor-alerta: #904141;
// Classes de estado de linha (RN-11) — mesma definição para os dois temas, fonte real:
// .TrInconformidade, .TrUrgente, .TrZebrada1, .TrZebrada2, .TrSemGarantia1, .TrSemGarantia2
// .breadcrumb, .centrodeavisos, .formSelect, .designedby (rodapé), .pmo (toggle mostrar/ocultar)
```

`ClasseDeAlerta` (Fase 5) mapeia para as classes acima — confirmado que os DOIS temas
compartilham a MESMA definição CSS (`pattern/15.9.7.css`), não duas paletas paralelas.
TEMA V1 usa um subconjunto: `TrInconformidade`/`TrUrgente`/`TrZebrada1`/`TrZebrada2`
aparecem em `page/entrada.php`, `page/encaminhados.php`, `page/localizar.php`, mas
`TrSemGarantia1`/`TrSemGarantia2` não são usados nessas páginas — a solução "SEM
GARANTIA" cai em `TrInconformidade` em vez de ganhar uma classe própria. TEMA V2 usa o
conjunto completo, incluindo `TrSemGarantia1/2` (confirmado em `15.8.1/subp/pesquisar_rma.php`
e outros `subp/`). `ClasseDeAlerta::SemGarantia` deve continuar existindo como valor de
enum (é uma `Solucao` de domínio, Fase 4/5), mas sua representação visual diverge por
tema: V2 tem cor própria, V1 cai no mesmo estilo de `Inconformidade`.

## `ResolverTemaAtivo` (middleware)

```php
final class ResolverTemaAtivo
{
    public function handle(Request $request, Closure $next): mixed
    {
        $tema = $request->user()?->tema_preferido ?? TemaPreferido::V2;
        View::share('temaAtivo', $tema);
        // Controllers continuam únicos; a resolução de view por tema acontece
        // no retorno da action (helper `view_do_tema('rma.index')` resolve para
        // resources/views/temas/{v1,v2}/rma/index.blade.php)
        return $next($request);
    }
}
```

Não duplica Controller — o mesmo `RmaController@index` (Fase 3) responde a
`GET /v1/rma` e `GET /v2/rma` (`routes/tema-{v1,v2}.php`), só a view retornada muda.

## Mecanismo de navegação por tema (achado confirmado, resolve Pendência 1)

Inspecionado com sessão autenticada real (`curl` com cookie de sessão, usuário de
laboratório) em `http://localhost:8094/15.8.1/`:

- O HTML de `GET /15.8.1/` (ou `/inicio`) renderiza **TODAS** as seções principais no
  MESMO documento, cada uma como um `<div id="..." class="tab-pane fade">`:
  `#inicio`, `#pesquisar`, `#novo_rma`, `#entrada`, `#recebido`, `#encaminhado`,
  `#concluido` (7 painéis confirmados via `grep -c tab-pane` = 7). O menu é
  `<ul class="nav nav-tabs">` com `<a href="#entrada" data-toggle="tab">` — é o plugin
  de abas NATIVO do Bootstrap 3.3.5 (`bootstrap.min.js`, CDN). Troca de aba é
  JS puro (mostra/esconde `.tab-pane`), **sem** fetch/AJAX e **sem** reload — os dados de
  TODAS as abas já vieram no HTML inicial (confirmado: linha de tabela real com
  `class="TrZebrada2"` dentro de `<div id="entrada">`).
- Páginas de detalhe/CRUD (`/info/{id}`, `/clientes`, `/rma/novo` fora do contexto do
  dashboard, etc.) SÃO reload completo de página — confirmado batendo em
  `GET /15.8.1/info/603971`: devolve um `<html>` completo novo, não um fragmento.
  As URLs limpas (`/entrada`, `/info/123`, `/clientes/novo`, ...) já existem como
  `RewriteRule` em `15.8.1/.htaccess` — mapeiam para `index.php?p=...&subp=...`.
- **Implicação para a V3:** a Fase 8 deve implementar o dashboard do TEMA V2 como UMA
  view Blade que renderiza os 7 painéis (dados de todas as abas já resolvidos pelo
  Controller/casos de uso — nenhuma regra de negócio nova, já existem desde as Fases
  1-7) e usar Bootstrap 3 (ou reprodução equivalente do plugin de abas, ver seção Vite
  abaixo) só para a troca visual client-side. Páginas de detalhe continuam sendo
  rotas/views separadas, full-page, como já estava assumido.

## RN-11 em TEMA V1 (achado confirmado, resolve Pendência 2)

Contrário à suposição do inventário anterior ("CSS 4× menor sugere sistema mais
simples"), TEMA V1 **usa as mesmas classes de alerta** do TEMA V2, porque `14.6.1/index.php`
carrega `pattern/15.9.7.css` **além de** `pattern/14.6.1.css` (confirmado, linha 127 do
arquivo). Evidência de uso real:

```php
// 14.6.1/page/entrada.php:39-50 (idêntico em encaminhados.php, localizar.php)
if (($linha['solucao']=="SEM GARANTIA") ...) { echo 'class="TrInconformidade"'; ... }
else if ($linha['prioridade'] == "urgente") { echo 'class="TrInconformidade"'; }
else if (...prazo 30 dias vencido...) { echo 'class="TrUrgente"'; }
else if ($linha['prioridade'] == "alta") { echo 'class="TrUrgente"'; }
else if (...) { echo 'class="TrInconformidade"'; }   // origem Cliente/Licitação fora de estoque
else { echo 'class="TrZebrada1"' / 'class="TrZebrada2"'; }  // zebra neutra
```

Diferença real (não suposição): TEMA V1 não usa `TrSemGarantia1`/`TrSemGarantia2` nessas
3 páginas — "SEM GARANTIA" cai em `TrInconformidade`. TEMA V2 usa o conjunto completo
incluindo `TrSemGarantia1/2` (confirmado em `15.8.1/subp/pesquisar_rma.php`, que também
mapeia "SEM GARANTIA" só quando `status=="concluido"` para `TrSemGarantia1/2`, e usa
`TrInconformidade` para os outros critérios). `concluidos.php`/`aguardandocredito.php`
em TEMA V1 não usam nenhuma classe `Tr*` — RMAs já resolvidos não recebem destaque, igual
ao padrão observado em TEMA V2. `ClasseDeAlerta` (Fase 5) pode ficar como está
(um enum único), mas a Blade de TEMA V1 deve mapear `SemGarantia` → mesma classe CSS de
`Inconformidade`, enquanto TEMA V2 mapeia para a classe própria.

## Estrutura de diretórios

**Correção (inspeção direta):** o legado NÃO tem dois logins simétricos, um por tema —
tem TRÊS superfícies de login assimétricas, confirmadas no HTML/PHP real:

1. **Login-gateway compartilhado** (`http://localhost:8094/` = `index.php` na raiz do
   legado) — caixa de login estilo AdminLTE (Bootstrap 3 + `pattern/15.9.7.css`, NENHUM
   CSS de tema próprio), é a porta de entrada padrão. `POST` vai para
   `15.8.1/pp/senha.php`, cuja `SignIn()` (compartilhada, `15.8.1/banco.php:1079`)
   aceita `Key1461` OU `Key1581` (credencial de qualquer tema) e redireciona para
   `$caminho.'../'.$app` — ou seja, decide o tema pós-login pela coluna `usuario.app`
   (equivalente ao `tema_preferido`/Fase 1). Este é o comportamento que
   `ResolverTemaAtivo` deve reproduzir no pós-login.
2. **Login próprio do TEMA V1** (`14.6.1/index.php`, formulário inline
   `.SignInCenterForm`, tabela HTML, tema escuro/FIR) — acessível navegando direto para
   `/14.6.1/`. Tem sua PRÓPRIA função `SignIn()` (`14.6.1/index.php:9`, único ponto do
   código que checa `Key1461` de forma independente) e, ao logar com sucesso, SEMPRE
   fica em `14.6.1` (`header('location: index.php')`) — não respeita `usuario.app`.
3. **TEMA V2 não tem login próprio** — `15.8.1/login.php` só reencaminha para a raiz
   (não logado) ou para `inicio` (já logado). Depende inteiramente do login-gateway (1).

Consequência para a árvore de Blade: `identidade/login.blade.php` não deve existir
simetricamente em `temas/v1/` e `temas/v2/`. Estrutura corrigida:

```
resources/views/
├── identidade/
│   └── login.blade.php          # login-gateway compartilhado (visual próprio, nem V1 nem V2)
└── temas/
    ├── v1/
    │   ├── layout.blade.php
    │   ├── rma/{index,create,edit,show}.blade.php
    │   ├── parceiros/{index,_form}.blade.php
    │   ├── identidade/login.blade.php   # login próprio do V1 (SignInCenterForm), além do gateway
    │   └── identidade/{usuarios,perfil}.blade.php
    └── v2/
        ├── layout.blade.php
        ├── (mesma árvore de rma/parceiros)
        └── identidade/{usuarios,perfil}.blade.php   # SEM login.blade.php — usa o gateway
```

Pendência real herdada do achado: qual comportamento pós-login o V3 deve reproduzir por
padrão — o do gateway (respeita `tema_preferido`) ou permitir também o atalho V1 que
ignora a preferência? Decisão de produto, não decidida aqui; registrar em
`checklist-master-v3.md` para confirmar com o usuário antes de implementar Fase 8.

## Organização Vite/Sass por tema

Achado adicional relevante para esta seção: TEMA V2 depende de verdade de Bootstrap
3.3.5 (grid, `.form-group`/`.form-control`, plugin de abas) + AdminLTE 2.2.0 CSS base
(hoje via CDN — `maxcdn.bootstrapcdn.com`, contra o princípio "nunca CDN solto, sempre
Vite" do projeto); TEMA V1 usa **zero** framework CSS (formulários são `<table>` autoral,
confirmado em `14.6.1/index.php`, painel "Novo"). Isso significa que os dois bundles
Vite têm dependências NPM genuinamente diferentes — não é só uma questão de paleta.

```
resources/js/temas/
├── v1.js            # sem framework — só o JS autoral equivalente a pattern/14.6.1.js
│                     # + pattern/15.9.7.js (base compartilhada, ver abaixo)
└── v2.js            # importa bootstrap@3 (grid + plugin de abas, `import 'bootstrap/js/tab'`
                      # ou equivalente) + o mesmo JS base compartilhado

resources/sass/temas/
├── _compartilhado.scss   # porta pattern/15.9.7.css: $cor-alerta, .TrInconformidade/
│                         # .TrUrgente/.TrZebrada1/.TrZebrada2/.TrSemGarantia1/.TrSemGarantia2,
│                         # .breadcrumb, .centrodeavisos, .formSelect, .designedby, .pmo
├── v1.scss           # @use 'compartilhado'; fundo #262626, acento #C3FF00, SEM @import
│                     # de framework nenhum
└── v2.scss           # @use 'compartilhado'; fundo #262626, painéis #FFF, azul-petroleo;
                       # @import de bootstrap (scss) só aqui — grid/tabs, escopado a este
                       # entry point para não vazar pro bundle de v1
```

`vite.config.js` precisa de 2 `input` distintos (`resources/js/temas/v1.js` e `v2.js`,
cada um importando seu próprio `.scss`) para gerar 2 bundles CSS/JS separados — é assim
que se evita CSS de um tema vazar pro outro (cada `<link>`/`<script>` do Blade de
`temas/v1/layout.blade.php` aponta só pro bundle `v1`, idem `v2`).

### Fontes — como o legado carrega hoje (confirmado, não suposição)

- **"Open Sans" (corpo, TEMA V2):** `@font-face` em `css/font-opensans.css` aponta para
  URL ABSOLUTA `https://cellsystem.com.br/app/15.8.7/framework/fonts/OpenSans/*.{eot,woff,ttf,svg}`
  — domínio de produção fora do ar (`curl` falha, connection refused) e caminho de versão
  errado (`15.8.7`, não `15.9.7`/`15.8.1`). **A fonte nunca carrega de fato — nem no
  LEGACY-RUNTIME nem, plausivelmente, em produção nos últimos anos** — o texto cai no
  fallback `"Arial","Fira Sans"`. Os arquivos físicos da fonte EXISTEM em
  `framework/fonts/OpenSans/` no repo legado, só a URL do `@font-face` está errada.
  **Pendência nova, não decidida aqui:** reproduzir o fallback quebrado literalmente
  (fiel ao que renderiza hoje) ou self-hostar a fonte corretamente via Vite (os arquivos
  já existem, é só apontar certo) — isso muda a tipografia percebida, é decisão de
  produto, perguntar ao usuário antes de implementar.
- **"Fira Mono"/"Fira Sans":** `@import` do Google Fonts (`fonts.googleapis.com`) dentro
  do próprio `pattern/15.8.1.css`/`15.9.7.css`, e `//code.cdn.mozilla.net/fonts/fira.css`
  linkado direto no `<head>` de `15.8.1/inc/menu.php`. Ambos são CDN solto — a V3 deve
  self-hostar via Vite (arquivo `.ttf` de Fira Mono já existe em
  `framework/fonts/Fira_Mono/`) para não depender de CDN externo, mantendo o resultado
  visual idêntico (ao contrário do caso do Open Sans acima, aqui o CDN funciona hoje,
  então não há ambiguidade sobre "qual é o resultado percebido correto").
- **TEMA V1, corpo:** `font-family:"Open Sans","Arial","Fira Sans"` no CSS, mas o único
  `WebFont.load()` do arquivo carrega a família **"Cantarell"** (Google Fonts, via
  `webfontloader`) — que não é usada em nenhuma regra CSS visível (`14.6.1/index.php:131`).
  Achado preservado como está no inventário — parece código morto/remanescente, não
  investigado a fundo aqui; não bloqueia a Fase 8 porque o fallback funcional
  (`"Arial","Fira Sans"`) já é o que renderiza.

## Testes

- `RenderizaTemaV1Test`, `RenderizaTemaV2Test` — smoke: cada tela principal (login,
  home/painel de alertas, novo RMA, detalhe, cadastros) renderiza sem erro no tema
  certo, para um usuário com `tema_preferido` correspondente. Cobrir também o
  login-gateway compartilhado (não pertence a nenhum dos dois temas) e o login próprio
  de TEMA V1 (ver "Estrutura de diretórios" acima).
- Playwright (`tests/Browser/`) — comparação lado a lado com LEGACY-RUNTIME (`:8094`).
  **Breakpoints corrigidos por achado real, não mais só 390/768/1440:** TEMA V1 não tem
  NENHUM `@media` query em `pattern/14.6.1.css` — é layout fixo (`#BASE{width:984px}`),
  então o teste correto para TEMA V1 é confirmar que ele **continua fixo/não-responsivo**
  nos 3 breakpoints (isso é fidelidade — "consertar" a responsividade seria a melhoria
  vetada pelo princípio 4). TEMA V2 tem breakpoints PRÓPRIOS em
  `15.8.1/css/media.php` (568/800/992/1080/1280/1366px, larguras de `.container`/`.nav`
  fixas por faixa, não fluido) além dos breakpoints padrão do Bootstrap 3
  (768/992/1200). Os 3 breakpoints de QA (390/768/1440, já fixados em
  `checklist-master-v3.md` Parte 3/Fase 10) continuam válidos como grade de teste, mas
  a asserção em TEMA V2 deve usar a largura de `.container` esperada PARA aquele
  breakpoint específico (ex.: a 768px, `.container` deveria estar em algum lugar entre a
  regra de 568px e a de 800px — a mais próxima abaixo, comportamento normal de
  `min-width`). Os testes Playwright não devem re-digitar 568/800/992/1080/1280/1366
  como literais soltos — devem ler de uma única fonte nomeada compartilhada com
  `$breakpoints-tema-v2` (ver "Organização Vite/Sass por tema" acima), por exemplo um
  `tests/Browser/Support/breakpoints-tema-v2.json` gerado a partir do mesmo mapa Sass,
  para não haver dois lugares divergentes definindo o mesmo conjunto fechado de
  valores.

## Pendências (atualizado — 2 resolvidas por evidência direta, 2 novas registradas)

1. ~~Mecanismo das âncoras de TEMA V2~~ **RESOLVIDO** — plugin de abas nativo do
   Bootstrap 3.3.5 (`data-toggle="tab"`), sem AJAX, todos os painéis pré-renderizados no
   mesmo HTML. Ver seção "Mecanismo de navegação por tema" acima.
2. ~~RN-11 em TEMA V1~~ **RESOLVIDO** — TEMA V1 usa as mesmas classes de alerta via CSS
   compartilhado (`pattern/15.9.7.css`), confirmado em 3 páginas reais. Ver seção "RN-11
   em TEMA V1" acima.
3. **NOVA — Fonte Open Sans do TEMA V2 nunca carrega (URL de produção morta).** Decisão
   de produto pendente: reproduzir o fallback quebrado ou self-hostar corretamente. Ver
   "Fontes" acima.
4. **NOVA — Comportamento pós-login assimétrico.** O login-gateway respeita
   `tema_preferido`; o login embutido em TEMA V1 sempre fica em V1 independente da
   preferência salva. Decidir se a V3 reproduz essa assimetria ou unifica o
   comportamento (mudança de regra de negócio perceptível, não só visual — avaliar com
   cuidado antes de decidir). Ver "Estrutura de diretórios" acima.
