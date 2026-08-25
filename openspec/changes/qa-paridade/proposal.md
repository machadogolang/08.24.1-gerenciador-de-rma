# Proposal — QA de paridade

Fase 10 de 10, última (ver `docs/arquitetura/INV-RMA-05-arquitetura-proposta.md` §15).
Contínua por natureza — fecha por último, mas o critério já pode ser fixado agora.

## Por quê

É o portão entre a Trilha A (reconstrução, o que este projeto vem fazendo) e a Trilha B
(`docs/produto/backlog-evolutivo.md`, evolução real de produto). Sem um critério
objetivo de "a V3 está em paridade com a V2", a decisão de quando parar de reconstruir e
começar a evoluir fica subjetiva — o que o usuário explicitamente não quer (baseline
antes de evolução é regra fixa do projeto desde o início).

## O que entra

- Critério objetivo por eixo (funcional/visual/dados), já detalhado em `INV-RMA-05`
  §15 — este OpenSpec formaliza a execução, não redecide o critério.
- `docs/qa/roteiro-paridade-funcional.md` — passos manuais para os `LEG-RMA-NNN` sem
  teste automatizável direto.
- `docs/qa/relatorio-paridade-final.md` — relatório final consolidando os 3 eixos.
- `tests/Browser/*.spec.ts` (Playwright) — 3 breakpoints × 2 temas ×
  telas principais, screenshot diff contra o LEGACY-RUNTIME (`:8094`).
- O checklist de "critério de conclusão do projeto" (5 itens, `INV-RMA-05` §15) como
  gate formal antes de a Trilha B começar.

## O que não entra

- Nenhum código de produto novo — esta fase só verifica o que as Fases 1-9 já
  produziram.
- Decidir pendências herdadas por conta própria — o critério de conclusão exige que
  toda pendência registrada ao longo do projeto tenha uma decisão (implementar / adiar
  para `EVO-*` / não fazer), não que todas estejam implementadas.

## Rastreabilidade

Não introduz `LEG-RMA-NNN` novo — verifica os já existentes nas Fases 1-9.
