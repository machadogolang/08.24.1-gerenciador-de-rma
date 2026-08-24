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

## Paleta confirmada (já levantada em sessão anterior, ver `matriz-comparacao-apps-rma.md`)

| Cor | Uso |
|---|---|
| `#FFF`, `#EEE` | texto/fundo claro |
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

## Pendente

- Confirmar mecanismo das âncoras `#entrada`/`#recebido`/etc. — AJAX/JS de troca de
  painel ou apenas âncora de scroll (não testado ainda).
- Valores exatos de `TrZebrada1`/`TrZebrada2` e demais classes não capturadas nesta
  extração.
- Screenshots reais (PNG) das telas principais.
- Telas internas (novo RMA, detalhes) não renderizadas ainda nesta rodada.
