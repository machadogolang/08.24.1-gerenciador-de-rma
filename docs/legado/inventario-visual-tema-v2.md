# Inventário visual — TEMA V2 (15.8.1)

Data: 2026-08-24. Evidência combinada: CSS estático (`pattern/15.8.1.css`, 905 linhas —
mais de 4× o do TEMA V1) + HTML real renderizado pelo LEGACY-RUNTIME (login e dashboard
autenticados, `http://localhost:8091/15.8.1/`). Sem screenshot de imagem ainda — próximo
passo, ARQ-07/LR-04.

## Identidade

- **Título de página:** `RMA 15.8.1  Build: 2.5 | Data de hoje: <data>` — confirma
  `$build="2.5"` já achado no código, agora validado em runtime.
- **Framework de base:** Bootstrap 3.3.5 (via CDN `maxcdn.bootstrapcdn.com`) + AdminLTE
  2.2.0 (`lib/LTE2.2.0`) — nenhuma classe `skin-*` do AdminLTE encontrada referenciada no
  HTML renderizado, então a skin de cor do AdminLTE **não está ativa**; a identidade
  visual real vem do CSS autoral `pattern/15.8.1.css` por cima do Bootstrap base.
- **Tipografia:** Open Sans (`css/font-opensans.css`), Fira (`code.cdn.mozilla.net/
  fonts/fira.css`).

## Paleta confirmada (já levantada em sessão anterior, ver `matriz-comparacao-apps-rma.md`; CORRIGIDA em 2026-08-24 por leitura direta de `pattern/15.8.1.css`)

| Cor | Uso |
|---|---|
| `#262626` | **fundo real da página (`body`), CORRIGIDO** — `pattern/15.8.1.css:12-18` define `body { background-color: #262626; }`, o MESMO tom escuro do TEMA V1. Achado anterior ("fundo `#FFF`") estava errado — branco é usado só nos painéis de conteúdo (ver linha abaixo), não na página inteira. |
| `#FFF`, `#EEE` | texto sobre fundo escuro **e** fundo dos painéis/cards de conteúdo (`.box-content`, `.blocos`, `.linhasuperior` — confirmado em `pattern/15.8.1.css:124,205,213,474`) — não é o fundo da página |
| `#F67D7D` | destaque rosado |
| `#224A5D` | azul petróleo — cor institucional dominante |
| `#904141` | vermelho — mesmo tom de alerta do TEMA V1 |
| `#3F3D3D`, `#3E3B3A`, `#414040` | tons escuros neutros |
| `#18354B` | azul marinho profundo |
| `#75B3E4` | azul claro — **mesma cor** usada na paleta legada do site institucional Scripting (achado cruzado, ver o outro projeto desta sessão) |

### Estados visuais de linha (RN-11, confirmado no CSS)

```css
.TrInconformidade { background-color:#303033; color:#FFF; height:18px; }
.TrInconformidade:hover { background-color:#904141; }
.TrUrgente { background-color:#382830; color:#FFF; height:18px; }
.TrUrgente:hover { background-color:#904141; }
.TrSemGarantia1 { background-color:#232320; color:#FFF; height:18px; }
.TrSemGarantia2 { background-color:#272723; color:#FFF; height:18px; }
```
`TrZebrada1`/`TrZebrada2` (não capturado o valor exato ainda) alternam listras neutras
para linhas sem alerta. Todos os estados de alerta convergem para o mesmo vermelho
`#904141` no hover — sinalização consistente de "isso precisa de atenção" independente
da causa raiz (inconformidade/urgente/sem garantia).

## Navegação confirmada (extraída do HTML real, usuário autenticado)

Menu com: Início (`#inicio`), Novo RMA (`#novo_rma`), Pesquisar (`#pesquisar`, com
submenu NF/SN), Entrada/Recebido/Encaminhado/Concluído (âncoras de aba: `#entrada`,
`#recebido`, `#encaminhado`, `#concluido`), Anotações, Assistências técnicas, Clientes,
Controle, Créditos, Fabricantes, Fornecedores, Logout, link para trocar para TEMA V1
(`../trocarapp.php`).

**Diferença de padrão de navegação vs. TEMA V1:** TEMA V2 usa **âncoras de aba** (`#tab`,
provável JS de troca de painel sem reload — a confirmar) para os estados do RMA,
enquanto TEMA V1 usa **páginas completas** (`index.php?page=entrada`, recarrega a
página). Este é um achado funcional real, não só estético — muda a experiência de
navegação entre os dois temas.

## Atualização 2026-08-24 — inspeção direta (sessão autenticada, HTML/CSS/PHP/.htaccess reais)

- **Mecanismo das âncoras — RESOLVIDO.** `GET /15.8.1/` (ou `/inicio`) renderiza os 7
  painéis principais no MESMO documento HTML, cada um `<div id="..." class="tab-pane
  fade">`: `#inicio`, `#pesquisar`, `#novo_rma`, `#entrada`, `#recebido`, `#encaminhado`,
  `#concluido` (confirmado por contagem: 7 ocorrências de `tab-pane`). O menu é
  `<a href="#entrada" data-toggle="tab">` dentro de `<ul class="nav nav-tabs">` — plugin
  de abas NATIVO do Bootstrap 3.3.5 (`bootstrap.min.js`, ainda via CDN no legado). Troca
  de aba é JS client-side puro (mostra/esconde), **sem** fetch/AJAX, **sem** reload —
  dados de TODAS as abas já vêm no HTML inicial (confirmado: linha real com
  `class="TrZebrada2"` dentro de `<div id="entrada">`, já com o produto de teste do
  laboratório). Só páginas de detalhe/CRUD (`/info/{id}`, `/clientes`, etc.) são reload
  completo — confirmado batendo em `GET /15.8.1/info/603971`, que devolve um novo
  `<html>` completo. As URLs limpas já existem, mapeadas em `15.8.1/.htaccess`
  (`RewriteRule ^entrada/?$ index.php?p=entrada`, `^info/([0-9-]+)/?$
  index.php?p=rma&id=$1`, etc. — ~40 regras cobrindo toda a navegação).
- **Classes de estado de linha (RN-11) vivem em CSS compartilhado, não só em
  `15.8.1.css`.** `TrInconformidade`/`TrUrgente`/`TrZebrada1`/`TrZebrada2`/
  `TrSemGarantia1`/`TrSemGarantia2` estão de fato definidas em `pattern/15.9.7.css`
  (linhas 56-67), NÃO em `pattern/15.8.1.css` como a extração anterior sugeria — e esse
  arquivo é carregado por AMBOS os temas (`15.8.1/inc/menu.php` inclui os dois CSS).
  Isso resolve também a dúvida equivalente do TEMA V1 (ver `inventario-visual-tema-v1.md`).
- **Breakpoints responsivos REAIS confirmados (achado novo, não catalogado antes):**
  `15.8.1/css/media.php` injeta um `<style>` inline no `<head>` de toda página com 6
  breakpoints — `568px`, `800px`, `992px`, `1080px`, `1280px`, `1366px` — que alargam um
  `.container`/`.nav` de largura FIXA por faixa (design "desktop-first" por faixas, não
  fluido). Bootstrap 3.3.5 (CDN) também contribui seus próprios breakpoints padrão
  (768/992/1200). Nenhum desses bate exatamente com os 3 breakpoints de QA do projeto
  (390/768/1440) — a comparação visual precisa mapear cada breakpoint de QA para a regra
  `min-width` mais próxima abaixo.
- **Formulário "Novo RMA" usa grid Bootstrap real** (`col-md-4` × 3 colunas,
  `.form-group`/`.form-control`), confirmado no HTML da aba `#novo_rma` — ao contrário
  de TEMA V1, que usa `<table>` autoral sem framework nenhum.
- **Fonte Open Sans nunca carrega de fato** — `css/font-opensans.css` aponta pra URL
  absoluta morta (`https://cellsystem.com.br/app/15.8.7/framework/fonts/OpenSans/...`,
  domínio fora do ar, caminho de versão errado). Texto sempre caiu no fallback
  (`Arial`/`Fira Sans`). Fira Mono/Fira Sans vêm de Google Fonts + `code.cdn.mozilla.net`
  (CDN externo funcional, mas contra o princípio "sem CDN solto" da V3).

## Pendente

- Valores exatos de largura de `TrZebrada1`/`TrZebrada2` e demais classes — já
  cobertos (ver `_compartilhado.scss` proposto em `design.md`), mas não há screenshot
  (PNG) ainda para comparação pixel a pixel.
- Screenshots reais (PNG) das telas principais.
- Decisão de produto (não decidida nesta rodada): reproduzir o fallback de fonte
  quebrado (Open Sans nunca carrega) literalmente, ou self-hostar corretamente — ver
  `design.md`/`proposal.md` da Fase 8.
