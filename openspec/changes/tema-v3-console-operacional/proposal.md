# Proposal - Tema V3 / Console Operacional Adaptativa

## Por que

V1 e V2 existem para preservar o legado (14.6.1 e 15.8.1). Nenhum dos dois atende
bem telefone/tablet, e o uso real do sistema sugere uma mesa de trabalho orientada
a fila, excecao, status e proxima acao. O Tema V3 e um produto visual novo que
estuda V1/V2 e usa o melhor de cada um, sem copiar as limitacoes estruturais.

## O que entra nesta rodada

Somente investigacao e especificacao:

- refinamento EVO-UX-001 (addendum a INV-RMA-08/INV-RMA-10);
- matriz de aproveitamento V1 x V2;
- mapa de telas V3;
- wireframes textuais;
- spike documental Tailwind x CSS semantico;
- OpenSpec do Tema V3;
- plano de ataque com status [ ]/[R]/[x];
- handoff commitado por ultimo.

## O que NAO entra

- Implementar qualquer view, SCSS/CSS, JS, rota, enum ou controller do V3;
- tornar o V3 selecionavel;
- alterar V1/V2;
- mudar regra, permissao, informacao, tenant ou capacidade funcional.

## Gate

[GATE-PENDENTE] Tema V3 ainda nao implementado e nao selecionavel.

A implementacao futura depende de: frente atual de UI/paridade segura; matriz
funcional sem lacuna critica; OpenSpec aprovado; direcao visual/arquitetural
definida; e gates de qualidade (E2E, mobile, desktop, acessibilidade, Policy,
tenant, erros/vazios, performance, build).

## Fontes

- `docs/arquitetura/INV-RMA-08-tema-v3-mobile-first.md`
- `docs/investigacoes-pendente/INV-RMA-10-arquitetura-front-paridade-temas.md`
- `docs/produto/backlog-evolutivo.md` (EVO-UX-001)
- `docs/produto/matriz-paridade-temas-v1-v2-v3.md`
- `docs/arquitetura/2026-09-09-refinamento-evo-ux-001-tema-v3-console-operacional.md`
- `docs/produto/2026-09-09-matriz-aproveitamento-v1-v2-para-v3.md`
- `docs/produto/2026-09-09-mapa-telas-tema-v3.md`
- `docs/produto/2026-09-09-wireframes-tema-v3.md`
- `docs/arquitetura/2026-09-09-spike-t3-tailwind-vs-css-semantico-v3.md`
