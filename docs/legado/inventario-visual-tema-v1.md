# Inventário visual — TEMA V1 (14.6.1)

Data: 2026-08-24. Evidência combinada: CSS estático (`pattern/14.6.1.css`, 207 linhas) +
HTML real renderizado pelo LEGACY-RUNTIME (login e dashboard autenticados,
`http://localhost:8094/14.6.1/`, ver `legacy-runtime-ambiente.md`). Sem screenshot de
imagem ainda (não gerado nesta sessão — próximo passo, ARQ-07/LR-04).

## Identidade

- **Título de página:** `Intranet : FIR 1.3 - <data>` — confirma o codinome interno
  **"FIR"** (achado do runtime, não visível em leitura estática de código) e a versão
  `1.3` (já confirmada em `config.php`).
- **Fundo:** escuro, `#262626`.
- **Tipografia:** "Open Sans"/"Arial"/"Fira Sans" no corpo; "Fira mono" em alertas —
  `body { font-family:"Open Sans","Arial","Fira Sans"; font-size:12px; color:white;
  font-weight:300; }`.

## Paleta confirmada (contagem de ocorrência no CSS)

| Cor | Uso aproximado |
|---|---|
| `#FFF` (26×) | texto principal sobre fundo escuro |
| `#26251F` (15×) | tom escuro secundário |
| `#B9B4B0` (6×) | texto/borda neutro claro |
| `#363636`, `#222`, `#2A2A2A`, `#000` | variações de fundo escuro |
| `#C3FF00` (4×) | **acento verde-limão vibrante** — cor de destaque marcante, provável cor de ação/CTA |
| `#9B3949`, `#904141`, `#cd5c5c` | tons de vermelho — alerta/urgência (mesmo `#904141` usado em TEMA V2) |
| `#F8C18B`, `#C89F92` | tons quentes secundários |

## Navegação confirmada (extraída do HTML real, usuário autenticado)

Menu lateral/superior com:
- `index.php` (início)
- `index.php?page=entrada`
- `index.php?page=encaminhados`
- `index.php?page=concluidos`
- `index.php?page=aguardandocredito`
- `index.php?page=relatorios&id=RCRD` / `RMPE` / `RPEC`
- **Filtro rápido "localizar por solução"** — submenu com um link por valor do domínio
  `solucao` (achado não catalogado antes com este nível de detalhe): DEVOLUÇÃO DO
  PRODUTO, GERADO CRÉDITO, ORÇAMENTO PAGO, PROCON, REEMBOLSO DO DINHEIRO, REPARO, REPARO
  PELO RMA, SEM GARANTIA, TESTADO TUDO OK, TROCA DE PEÇA INTERNA, TROCA DO PRODUTO — cada
  um pré-preenche `index.php?page=localizar&find=&campo=TUDO&solucao=<valor>`.
- `../trocarapp.php` (trocar para TEMA V2)
- link de rodapé para `scripting.com.br` (designed by)

## Classes CSS estruturais confirmadas (HTML real)

`breadcrumb submenutitulo`, `centrodeavisos`, `defaultTR`, `formButtonEnviarNovo`,
`formButtonEnviarPanel`, `formButtonMENU`, `formButtonSIGNOUT`, `formInputObservacao`,
`formInputPanel`, `formInputStats`, `formLabelPanel`, `formLabelStats`,
`formSelectPanel`, `formTitlePanel`, `alertaIndexMSG` (fundo `#cd5c5c`, fonte
"Fira mono", usado para mensagem de aviso no topo).

## Atualização 2026-08-24 — inspeção direta (sessão autenticada, HTML/CSS/PHP reais)

- **CSS compartilhado com TEMA V2 confirmado:** `14.6.1/index.php` carrega
  `pattern/14.6.1.css` **E** `pattern/15.9.7.css` (linha 127) — e `pattern/14.6.1.js` E
  `pattern/15.9.7.js` (linha 222-223). O CSS de 207 linhas catalogado aqui é só a MARCA
  própria do tema (fundo, acento, tipografia); boa parte da estrutura (breadcrumb,
  alerta de linha, rodapé) vem do arquivo de 296 linhas compartilhado com TEMA V2.
- **Estados visuais de linha (RN-11) — RESOLVIDO, não é mais dúvida.** TEMA V1 usa as
  MESMAS classes que TEMA V2 (`TrInconformidade`, `TrUrgente`, `TrZebrada1`,
  `TrZebrada2`), confirmado por leitura direta de `14.6.1/page/entrada.php` (linhas
  39-50), `page/encaminhados.php` e `page/localizar.php`. Única diferença real: TEMA V1
  não usa `TrSemGarantia1`/`TrSemGarantia2` como classe própria nessas 3 páginas — a
  solução "SEM GARANTIA" cai em `TrInconformidade`. `page/concluidos.php` e
  `page/aguardandocredito.php` não usam nenhuma classe `Tr*` (RMA já resolvido não
  ganha destaque, mesmo padrão do TEMA V2).
- **Layout de formulário confirmado (telas internas capturadas):** o painel "Novo RMA"
  (`onclick="NovoMaximize()"`, mostra/esconde `<div id="JS-Novo">`) é uma tabela HTML
  autoral (`<table class="tablenovo">`, classes `.novo_formInput`/`.novo_formInputSmall`/
  `.novo_formInputDATE`) — **sem nenhuma dependência de Bootstrap ou outro framework
  CSS**, ao contrário de TEMA V2 (que usa grid Bootstrap `col-md-4`/`.form-group`). Essa
  é uma divergência estrutural real entre os temas, não só de cor.
- **Responsividade: confirmado que NÃO existe.** `pattern/14.6.1.css` não tem nenhuma
  `@media` query. Layout é fixo: `#BASE{width:984px}`, `#TOPO{width:984px}`,
  `#CONTEUDO{width:984px}`. Fidelidade correta na V3 é reproduzir esse comportamento
  fixo/não-responsivo, não adicionar responsividade que não existe no legado.
## Pendente

- Screenshots reais (PNG) das telas principais — ainda não capturados (fora do escopo
  desta rodada de inspeção via `curl`).
- Fonte "Open Sans"/"Fira Sans" — a chamada `WebFont.load({google:{families:['Cantarell']}})`
  em `14.6.1/index.php:131` carrega uma família não referenciada em nenhuma regra CSS
  visível; parece código morto, não investigado a fundo.
