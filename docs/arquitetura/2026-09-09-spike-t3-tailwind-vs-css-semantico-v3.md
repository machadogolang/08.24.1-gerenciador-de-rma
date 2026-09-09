# T3-SPIKE-01 - Tailwind 4 x Sass/CSS semantico para o Tema V3

Data: 2026-09-09. Frente: EVO-UX-001. Natureza: comparacao documental, sem
implementacao. Status: [R] REVISADO (conclusao registrada; decisao final fica no
gate de implementacao).

## Evidencia do estado atual

- `package.json` ja declara `tailwindcss@^4.0.0` e `@tailwindcss/vite@^4.0.0`.
- `vite.config.js` ja registra o plugin `tailwindcss()`.
- `resources/css/app.css` e `@import 'tailwindcss'` com bloco `@theme` minimo.
- Nenhuma view de V1/V2 usa classe utilitaria Tailwind (verificado por auditorias
  anteriores de `INV-RMA-08`).
- V1 usa `resources/sass/temas/_v1-base.scss` + `_compartilhado.scss` (sem
  framework).
- V2 usa `_v2-base.scss` + Bootstrap 3.3.5 real importado.
- O projeto irmão CONAHOM usa Sass semantico (BEM-like, tokens nomeados,
  componentes e contextos), sem Tailwind.

## Criterios de comparacao

| Criterio | Tailwind 4 | Sass/CSS semantico | Observacao |
|---|---|---|---|
| Bundle | CSS gerado por uso; depende de @source | CSS declarado por componente | ambos aceitaveis |
| Isolamento V1/V2 | bundle V3 proprio via Vite; preflight fica no bundle V3 | bundle V3 proprio via Vite; classes semanticas | equivalentes |
| Legibilidade Blade | classes utilitarias longas | classes curtas semanticas | Sass melhor para leitura |
| Tokens | @theme no CSS | $tokens.scss | equivalentes |
| Reuso | utilities + @apply | mixins + BEM | Sass mais explicito |
| Manutencao | risco de drift de utilidades | risco menor por componente | vantagem Sass |
| Curva do projeto | nao usado hoje; curva media | ja usado em V1/V2/CONAHOM | vantagem Sass |
| Mobile-first | breakpoints sm/md/lg prontos | breakpoints nomeados com min-width (evidencia CONAHOM) | equivalentes |
| Componentizacao | Blade + classes | Blade + BEM/components | equivalentes |
| Risco de vazamento CSS | utilidades em markup podem aparecer sem estilo em V1/V2 | nomes BEM unicos por tema | vantagem Sass |
| Vite | plugin ja instalado | Sass ja funciona | equivalentes |

## Recomendacao

Recomendacao documental para a fundacao do Tema V3:

**Sass/CSS semantico moderno (BEM-like com tokens nomeados)**, seguindo a
metodologia comprovada do CONAHOM e o vocabulario ja usado por V1/V2:

- menor risco de vazamento e de markup preso a utilidades;
- legibilidade de Blade superior;
- tokens, breakpoints e alvo de toque centralizados;
- reuso por componentes Blade + classes semanticas;
- isolamento por bundle V3 proprio, sem depender de convencao para nao usar
  utilidades em outras arvores;
- curva menor para este time, que ja conhece Sass/V1/V2.

Tailwind 4 permanece candidato viável caso a implementacao encontre ganho real de
velocidade ou preferencia do time; a decisao final ocorre no gate de
implementacao, nunca agora.

## Consequencia estrutural recomendada

Se mantida a recomendacao, o Tema V3 usa:

- `resources/sass/temas/v3.scss` (entry point proprio);
- `resources/sass/temas/v3/_tokens.scss`, `_base.scss`, `_components/` e
  `_contextos/`;
- `resources/js/temas/v3.js`;
- entrada adicional no `input` do Vite.

Isso diverge do layout de arquivos previsto em INV-RMA-08 (que apontava
`resources/css/temas/v3.css` para Tailwind); a divergencia e registrada como
addendum, nao reescrita do marco.
