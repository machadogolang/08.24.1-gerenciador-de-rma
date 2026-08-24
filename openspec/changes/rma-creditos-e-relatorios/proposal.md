# Proposal — Créditos e relatórios

Fase 6 de 10 (ver `docs/arquitetura/INV-RMA-05-arquitetura-proposta.md` §11).

## Por quê

O fluxo de crédito e os 3 relatórios fiscais/contábeis (RCD/RPEC/RMPE) são consultas de
leitura e um controle de flag sobre o agregado `Rma` já maduro depois da Fase 5 — não
introduzem entidade nova, coerente com `INV-RMA-05` §3 ("Créditos"/"Relatórios" não são
módulo próprio).

## O que entra

- `MarcarCreditoDisponivel` (`LEG-RMA-036`) — fluxo único de crédito, reconstruindo só a
  intenção do legado (`paridade-v2-v3.md`: `LEG-RMA-048` "reconstruir só a intenção").
- 3 relatórios (`LEG-RMA-037/038/039`).

## O que não entra

- Automação de transição de crédito (`EVO-AUT-002`, backlog evolutivo).
- PDF real de relatório (`EVO-REL-001`, backlog evolutivo) — impressão via `Ctrl+P`,
  igual ao legado.
- Dashboard de recorrência de defeito (`EVO-REL-002`, backlog evolutivo).
- `LEG-RMA-048` (3 sub-rotas `pendentes/usados/disponíveis`) não é reconstruído como
  3 telas — está quebrado em TEMA V2 (rotas sem arquivo de destino, RN-18) e nunca
  existiu em TEMA V1; só a intenção (um fluxo de crédito) é reconstruída.
- `LEG-RMA-040` (consolidação de frete Porto Alegre) e `LEG-RMA-041` (boletins
  relacionados) — cobertos pela Fase 7 (`rma-logistica-e-historico`), conforme já
  registrado em `checklist-master-v3.md` Fase 7 antes desta rodada de planejamento;
  não duplicados aqui apesar de também serem consultas de leitura sobre `Rma`.

## Rastreabilidade com o legado

| Este OpenSpec | Legado |
|---|---|
| `MarcarCreditoDisponivel` | `LEG-RMA-036` |
| `RelatorioCreditosDisponiveis` | `LEG-RMA-037` (RCD) |
| `RelatorioProdutosEmEstoqueParaContagem` | `LEG-RMA-038` (RPEC) |
| `RelatorioProdutosEncaminhados` | `LEG-RMA-039` (RMPE) — corrige intervalo hardcoded para 2014 (bug de manutenção, não RN documentada) |
| **Fora do escopo, coberto na Fase 7** | `LEG-RMA-040`/RN-16 (frete Porto Alegre), `LEG-RMA-041` (boletins relacionados) |
