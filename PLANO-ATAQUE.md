# Plano de ataque — CellSystem RMA (Trilha B / EVO-SAAS-001)

Última atualização: 2026-09-09 (America/Sao_Paulo).
Fonte granular: `docs/produto/checklist-master-v3.md` e
`openspec/changes/saas-multiempresa/tasks.md`.
Handoff: `docs/produto/handoff-sessao-2026-09-09.md`.

## AGORA

Execução controlada da Trilha B: **EVO-SAAS-001 — fundação SaaS multiempresa**.

Concluído e commitado:
- S0/S1 — reconciliação, OpenSpec e mapa tenant-scoped.
- S2–S8 — Company/company_user, tenant CellSystem/backfill, TenantContext,
  isolamento por construção, parceiros/RMA/auditoria A×B.
- S9 — papel por vínculo ativo (policies/casos de uso/gestão/UI) com prova A×B;
  compatibilidade `users.papel` registrada.
- S10 — contador transacional por empresa + `numero_da_empresa` (sem MAX+1).
- S11 — migrador histórico define CellSystem e vincula usuários; zero órfãos.
- S12 — gate arquitetural permanente (`GateDeIsolamentoTest`) + auditoria console.
- S3.6 — hardening `tenant_id NOT NULL` com FK restritiva (auditoria zero-órfãos).

Suíte atual: **448 testes / 1070 assertions PHPUnit verdes**.
Checkpoint: `docs/qa/checkpoint-fundacao-multiempresa-2026-09-09.md`.

## DEPOIS

- S9.8 — remover `users.papel` quando consumidores legacy forem eliminados.
- S10.4 — prova de concorrência real por processo externo (não fork do PHPUnit).
- S11.4 — coluna/tenant no relatório do migrador (opcional).
- S13 — Playwright relevante/build e auditoria final de segurança.
- S14 — relatório e gate formal de isolamento da fundação multiempresa.

## DEPENDÊNCIAS

- S14 depende de S9.8 ou de decisão de manter compatibilidade, S10.4, S13.
- S9.8 depende da eliminação dos consumidores legacy (matriz S9.1).
- S10.4 não depende de produto; requer harness/processo externo.

## DECISÕES ADIADAS

- Representação do administrador de plataforma (ortogonal ao Papel de tenant).
- UI de seletor de empresa para multi-vínculo (backend pronto).
- `EVO-SAAS-002/003`, Tema V3 e demais evoluções.
- Agregação de segurança cross-tenant e isolamento físico futuro.
- `[DECISAO-PENDENTE-S9-AUTH]`: semântica fina de login multi-vínculo se produto exigir.

## CRITÉRIO DE SAÍDA (gate de isolamento)

- Nenhuma entidade tenant-scoped vê/soma/altera dado de outra empresa (gate A×B verde).
- `tenant_id NOT NULL` com FK restritiva e auditoria zero-órfãos.
- Numeração por empresa sem MAX+1; migrador idempotente carimba CellSystem.
- Papel por vínculo sem fallback perigoso (S9.8) ou decisão registrada.
- PHPUnit completo + Playwright relevante + segurança S13.

## NÃO FAZER AINDA

- `EVO-SAAS-002/003`; Tema V3; anexos/configuração antes do gate.
- `git push`, PR, merge remoto ou reescrita de história.
- Alterar fontes históricas/backups.
- Usar `tenant_id` de request; confiar só em Policy ou só em Global Scope.
- Usar `MAX+1`; biblioteca de tenancy externa sem necessidade.
