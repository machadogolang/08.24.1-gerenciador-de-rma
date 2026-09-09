# Plano de ataque — CellSystem RMA (Trilha B / EVO-SAAS-001)

Última atualização: 2026-09-09 (America/Sao_Paulo).
Fonte granular: `docs/produto/checklist-master-v3.md` e
`openspec/changes/saas-multiempresa/tasks.md`.
Handoff: `docs/produto/handoff-sessao-2026-09-09.md`.

## AGORA

Execução controlada da primeira iniciativa da Trilha B: **EVO-SAAS-001 — fundação SaaS
multiempresa**.

Ondas concluídas e commitadas nesta rodada:
- S0 — reconciliação/baseline e pequenos bloqueadores reais.
- S1 — OpenSpec `openspec/changes/saas-multiempresa/` + mapa tenant-scoped
  (`docs/produto/mapa-tenant-scoped-2026-09-09.md`).
- S2 — `companies`, `company_user` (papel por vínculo), models/factories/relações.
- S3 — tenant semente `CellSystem`, colunas `tenant_id` e backfill (migrations 000003/000004).
- S4 — `ContextoDeTenant` + `ResolverTenantAtivo` (403 sem vínculo; multi-vínculo no backend).
- S5 — `PertenceATenant`/`EscopoDeTenant`: Global Scope, observer, mass assignment e
  route binding tenant-aware por membership.
- S6 — parceiros isolados por tenant com suíte A×B (16 testes).
- S7 — RMA isolado via escopo + repositório com suíte A×B (4 testes).
- S8 — auditoria/histórico isolados; listener herda tenant do RMA; decisão de não
  escopar `TentativaDeAcesso` nesta fase.

Suíte atual: **431 testes / 1023 assertions PHPUnit verdes** (última execução
2026-09-09).

## DEPOIS

- S9 — migrar leitura de Papel para o vínculo `company_user` (Policies/Gates).
- S10 — numeração transacional de RMA por empresa (nunca `MAX+1`).
- S11 — migrador histórico carimbando `CellSystem`.
- S12 — suíte arquitetural permanente de isolamento (relatórios/alertas/inventário de models).
- S13 — regressão/segurança completa.
- S14 — relatório/checkpoint da fundação multiempresa e fechamento do primeiro marco.
- Endurecimento `tenant_id` NOT NULL/FK (`S3.6`) após prova zero-órfãos em dado real.
- Pendências pequenas de baseline fora do SaaS (PAR/UX/ARQ-005/007) quando não
  bloquearem o marco atual.

## DEPENDÊNCIAS

- S9 depende de S2-S5 (vínculo/contexto/policies existentes).
- S10 depende de S7 (RMA escopado) e S9 (papel do vínculo, se exigir).
- S11 depende de S3 (tenant semente) e S7.
- S12 depende de S5-S8 (isolamento implementado).
- S14 depende de S9-S13 e do endurecimento S3.6 auditado.

## DECISÕES ADIADAS

- Representação do administrador de plataforma (ortogonal ao `Papel` de tenant).
- UI de seletor de empresa para usuários multi-vínculo (backend pronto).
- `EVO-SAAS-002/003`, Tema V3 e demais evoluções.
- Agregação de segurança cross-tenant.
- Gatilho futuro de isolamento físico por tenant.

## CRITÉRIO DE SAÍDA (gate de isolamento)

- Nenhuma entidade tenant-scoped lista/busca/abre/edita dado de outra empresa A×B.
- Migração sem linha órfã; rollback/compatibilidade comprovados.
- Numeração por empresa sem `MAX+1`.
- Migrador histórico idempotente carimba `CellSystem`.
- Suíte arquitetural de isolamento permanente verde + PHPUnit completo + segurança S13.

## NÃO FAZER AINDA

- `EVO-SAAS-002/003`; Tema V3; anexos/configuração antes do gate de isolamento.
- `git push`, PR, merge remoto ou reescrita de história.
- Alterar fontes históricas/backups.
- Usar `tenant_id` vindo de request; confiar só em Policy ou só em Global Scope.
- Usar `MAX+1` em numeração; biblioteca de tenancy externa sem necessidade.
