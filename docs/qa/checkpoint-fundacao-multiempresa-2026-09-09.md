# Checkpoint — Fundação multiempresa (EVO-SAAS-001)

Data: 2026-09-09. Estado: **marco parcialmente fechado (S1–S8, S9–S12, S3.6)**;
EVO-SAAS-001 permanece aberto até S9.8/S10.4/S11.4/S13/S14.

## Arquitetura entregue

- Banco compartilhado com `tenant_id` (Modelo A do `INV-RMA-07`).
- `companies` e `company_user` (pivot tipado com `Papel` por vínculo).
- Tenant semente `CellSystem` + backfill idempotente (migrations 000003/000004).
- `ContextoDeTenant` + `ResolverTenantAtivo` (403 sem vínculo; multi-vínculo backend).
- Isolamento por construção: `PertenceATenant`, Global Scope, Observer, route binding
  por membership, `tenant_id` fora de `$fillable`.
- Parceiros, RMA e auditoria isolados A×B.
- Papel de autorização lido do vínculo ativo; `users.papel` mantido como
  compatibilidade residual (S9.8 pendente).
- Numeração operacional por empresa via `contadores_de_rma` (migration 000005/000006);
  id técnico global; `numero_legado` preservado.
- Migrador histórico define CellSystem explicitamente e vincula usuários.
- Hardening `tenant_id NOT NULL` + FK restritiva (migration 000007).

## Migrations novas

- `2026_09_09_000001`/`000002` — companies / company_user.
- `2026_09_09_000003`/`000004` — tenant_id + CellSystem/backfill.
- `2026_09_09_000005`/`000006` — contadores + numero_da_empresa.
- `2026_09_09_000007` — NOT NULL/FK restritiva.

## Testes

- PHPUnit completo: **448 testes / 1070 assertions verdes**.
- Gate arquitetural permanente: `tests/Feature/Tenant/GateDeIsolamentoTest.php`.

## Pendências reais (não escondidas)

- `S9.8` — remover `users.papel` apenas quando consumidores legacy sumirem.
- `S10.4` — prova de concorrência real fora do fork do PHPUnit.
- `S11.4` — relatório do migrador com coluna de tenant (opcional).
- `S13.2` — Playwright relevante/build (sem assets tocados nesta rodada).
- `S14` — relatório de gate formal quando os pendentes acima fecharem.
