# Inventário visual — TEMA V1 (14.6.1)

Data: 2026-08-24. Evidência combinada: CSS estático (`pattern/14.6.1.css`, 207 linhas) +
HTML real renderizado pelo LEGACY-RUNTIME (login e dashboard autenticados,
`http://localhost:8091/14.6.1/`, ver `legacy-runtime-ambiente.md`). Sem screenshot de
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

## Pendente

- Estados visuais de linha (inconformidade/urgente/zebra) equivalentes ao TEMA V2 —
  **[DÚVIDA]** se `TrInconformidade`/`TrUrgente`/`TrSemGarantia` têm equivalente exato
  aqui (RN-11 em `regras-negocio-rma-legado.md`); CSS de 207 linhas é bem menor que o do
  TEMA V2 (905 linhas), sugerindo sistema de destaque mais simples — não confirmado.
  linha por linha ainda.
- Screenshots reais (PNG) das telas principais — ainda não capturados.
- Comportamento de formulário (novo RMA, detalhes) — navegação mapeada, telas internas
  não renderizadas ainda nesta rodada.
