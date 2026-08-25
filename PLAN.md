# CellSystem RMA V3 — estado macro

Última atualização: 2026-08-25. **RMA V2 FINAL** = container 15.9.7; **TEMA V1** =
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
| F10 | QA de paridade | em execução |

A matriz funcional tem 48 itens: 44 `PARIDADE`, 2 `NÃO RECONSTRUIR`, 1 `RETOMAR
IDEIA` e 1 (`LEG-RMA-002`) aguardando decisão. A última suíte confirmada antes desta
consolidação tem 310 testes/608 assertions.

### Gate da Trilha A

1. Funcional: cada `LEG-RMA-*` com teste ou roteiro manual executado.
2. Visual: V2×V3, dois temas, telas principais, 390/768/1440.
3. Dados: migração histórica real e reconciliação sem divergência inexplicada.
4. Decisões residuais implementadas, recusadas ou explicitamente adiadas.
5. Suíte completa verde e relatório final aprovado.

## Trilha B — evolução

Investigação e especificação estão autorizadas; implementação permanece bloqueada pelo
gate da Trilha A. Abrange SaaS/multiempresa, tema V3, arquivos, configuração, domínio,
automação, relatórios, segurança, auditoria, performance e IA. Decisões de
`INV-RMA-07/08/09` não equivalem a autorização para codificar.

## Restrições permanentes

- Não inventar comportamento histórico nem alterar fontes/backups históricos.
- Não iniciar código da Trilha B antes do gate da Trilha A.
- Não fazer push, PR, merge ou mudar visibilidade remota sem autorização explícita.
