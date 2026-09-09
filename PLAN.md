# CellSystem RMA V3 — estado macro

Última atualização: 2026-09-09. **RMA V2 FINAL** = container 15.9.7; **TEMA V1** =
14.6.1; **TEMA V2** = 15.8.1; **RMA V3** = este repositório.

## Fontes e ambientes

O estado granular e os gates vivem em `docs/produto/checklist-master-v3.md`;
`PLANO-ATAQUE.md` contém apenas o lote corrente. Código/testes/runtime prevalecem
sobre Git, OpenSpec, investigações e documentos de planejamento.

- V2 preservado em `:8094`, modos sanitizado e histórico; banco histórico validado
  com 1.379 RMAs e 165 clientes.
- V3 em `:8095`, com base local determinística de QA.
- Ambientes simultâneos documentados em `docs/produto/ambientes-locais-v2-v3.md`.
- Baseline auditada em `main`, commit `0b3f72d`.
- Handoff da sessão atual (2026-09-09): `docs/produto/handoff-sessao-2026-09-09.md`;
  `handoff-sessao-2026-08-25.md` e `handoff-sessao-2026-08-26.md` ficam como histórico.

## Trilha A — reconstrução fiel

| Fase | Capacidade | Estado |
|---|---|---|
| F1 | Identidade e usuários | concluída |
| F2 | Parceiros | concluída |
| F3 | Cadastro/localização de RMA | concluída |
| F4 | Ciclo de vida | concluída |
| F5 | Alertas e prioridade | concluída |
| F6 | Créditos e relatórios | concluída |
| F7 | Logística e histórico | concluída |
| F8 | Temas V1/V2 | concluída no escopo aprovado |
| F9 | Migrador V2→V3 | código e testes concluídos; execução real integra F10 |
| F10 | QA de paridade | concluída (Gate Aprovado) |

A matriz funcional tem 48 itens: 44 `PARIDADE`, 2 `NÃO RECONSTRUIR`, 1 `RETOMAR
IDEIA` e 1 (`LEG-RMA-002`) homologado em parecer formal. A suíte completa tem
396 testes/967 assertions no PHPUnit (100% verde nesta sessão) e 58 testes automatizados no Playwright
Browser (100% verde).

### Gate da Trilha A — APROVADO (2026-09-04)

1. Funcional: 48 `LEG-RMA-*` reconciliados, 6 smokes M-01 a M-06 aprovados.
2. Visual: Temas V1 e V2 homologados em 390/768/1440, auditoria navegacional NAV-01..NAV-05 fechada.
3. Dados: migração histórica real de 9 tabelas do MariaDB 15.9.7, reconciliação sem divergência e idempotência provada.
4. Decisões residuais formalmente homologadas em parecer executivo de 2026-09-04.
5. Suíte completa verde e relatório final emitido em `docs/qa/relatorio-paridade-final.md`.

## Trilha B — evolução

Investigação e especificação estão autorizadas; implementação permanece bloqueada pelo
gate da Trilha A. Abrange SaaS/multiempresa, tema V3, arquivos, configuração, domínio,
automação, relatórios, segurança, auditoria, performance e IA. Decisões de
`INV-RMA-07/08/09` não equivalem a autorização para codificar.

## Frente — Arquitetura, Front-end e Paridade de Temas

Aberta em 2026-08-25 e incorporada a este plano, sem criar roadmap principal paralelo.
Parecer e evidências: `docs/investigacoes-pendente/
INV-RMA-10-arquitetura-front-paridade-temas.md`; matriz viva: `docs/produto/
matriz-paridade-temas-v1-v2-v3.md`; tarefas atômicas: checklist mestre, seção H.

- **ARQ / CORREÇÃO de baseline:** preservar todo o estado do agregado nas edições e
  transições; corrigir dry-run/reconciliação; impedir escalada de privilégio. Esses P0
  pertencem à Trilha A e precedem o fechamento da F10.
- **FRONT / PAR / LEG / UX:** fechar busca, filas, campos, navegação, módulos
  secundários, ações e estados. Paridade deixa de significar apenas HTTP 200 ou
  simetria V1×V2.
- **Regra arquitetural:** temas podem divergir em identidade e composição, nunca em
  regra, permissão, informação ou ação disponível.
- **T3 — Console Operacional Adaptativa:** implementação permanece Trilha B, depois de
  G-07/G-08. Pode nascer incrementalmente oculto, mas só entra no seletor com a matriz
  funcional integral, E2E, acessibilidade e performance aprovadas.
- **EVO:** pesquisa global, filtros pessoais e atividade recente foram registradas;
  ações em lote continuam investigação, não feature presumida.

## Restrições permanentes

- Não inventar comportamento histórico nem alterar fontes/backups históricos.
- Não iniciar código da Trilha B antes do gate da Trilha A.
- Não fazer push, PR, merge ou mudar visibilidade remota sem autorização explícita.
