# Tasks — EVO-SAAS-001: fundação SaaS multiempresa

Pré-requisito cumprido: Trilha A encerrada (`F10-GATE-07`, 2026-09-04) e Trilha B
liberada para execução controlada (2026-09-09). Cada onda abaixo é um ciclo fechado:
código/teste/documento → commit local. Se uma sub-task depender de decisão não resolvida
do usuário, marque `[DECISAO-PENDENTE]`, pule somente ela e siga.

## S0 — Reconciliação e baseline

- [x] S0.1 — confirmar Git/runtime e reconciliação documental pós-gate (commits desta
      sessão até 2026-09-09).
- [x] S0.2 — PLAN/PLANO-ATAQUE/checklist/OpenSpec F10 reconciliados por evidência.
- [x] S0.3 — pendências antigas classificadas (diagnóstico + checklist mestre).
- [x] S0.4 — pequenos bloqueadores reais corrigidos antes do SaaS: FRONT-004/D-06,
      ARQ-004/PAR-RMA-001, PAR-RMA-002/003 parcial.
- [x] S0.5 — baseline renovada: 396 testes / 967 assertions PHPUnit verdes.
- [ ] S0.6 — registrar baseline pré-SaaS no handoff desta sessão (atualizado no fim).

## S1 — OpenSpec / mapa tenant-scoped

- [x] S1.1 — criar `proposal.md`.
- [x] S1.2 — criar `design.md`.
- [x] S1.3 — criar `tasks.md`.
- [x] S1.4 — inventariar tabelas/models atuais e classificar tenant-scoped × globais
      (`docs/produto/mapa-tenant-scoped-2026-09-09.md`).
- [x] S1.5 — definir quais ficam globais (users, companies, company_user,
      tentativas_de_acesso e tabelas de framework — mapa acima).
- [x] S1.6 — mapear consumidores por model (grep por `use App\Models\...`; resultado
      no mapa acima).
- [ ] S1.7 — registrar decisões adiadas (design §Decisões adiadas).
- Commit documental sugerido: `#DOC-RMA - Especifica fundacao SaaS multiempresa`.

## S2 — Fundação de Company

- [ ] S2.1 — migration `create_companies_table`.
- [ ] S2.2 — model `App\Models\Company`.
- [ ] S2.3 — migration `create_company_user_table` (unique company/user, índices).
- [ ] S2.4 — relacionamento `User::empresas()`/`Company::usuarios()`.
- [ ] S2.5 — coluna `papel` no pivô (mesmo enum Papel).
- [ ] S2.6 — constraints/índices e testes de relacionamento
      (`tests/Feature/Tenant/CompanyUserTest.php`).
- [ ] S2.7 — factories/seeds determinísticas (`CompanyFactory`, `CompanyUserFactory`).
- [ ] S2.8 — `users.papel` permanece; transição compatível registrada.
- Commit: `#ARQ-RMA - Introduz Company e vinculo de usuarios`.

## S3 — Tenant inicial e backfill

- [ ] S3.1 — criar tenant `CellSystem` deterministicamente (seeder/up).
- [ ] S3.2 — vincular usuários existentes a CellSystem.
- [ ] S3.3 — preservar `Papel` atual no `company_user`.
- [ ] S3.4 — adicionar `tenant_id` nullable às tabelas tenant-scoped.
- [ ] S3.5 — backfill dos dados existentes para CellSystem (uma migration/command
      idempotente).
- [ ] S3.6 — endurecer NOT NULL/FKs quando provado zero órfãos.
- [ ] S3.7 — prova: nenhuma linha operacional sem tenant (teste/assert).
- [ ] S3.8 — rollback/compatibilidade das migrations.
- Commit: `#ARQ-RMA - Cria tenant CellSystem e faz backfill dos dados existentes`.

## S4 — TenantContext

- [ ] S4.1 — `ContextoDeTenant` (singleton por request).
- [ ] S4.2 — binding no container.
- [ ] S4.3 — `ResolverTenantAtivo` middleware (depois de auth).
- [ ] S4.4 — um vínculo ativo funciona sem seletor.
- [ ] S4.5 — múltiplos vínculos suportados no backend (chave de sessão; UI é
      `[DECISAO-PENDENTE]`).
- [ ] S4.6 — ausência de vínculo falha explicitamente (403/redirect consistente).
- [ ] S4.7 — tenant inválido/não pertencente rejeitado.
- [ ] S4.8 — testes do contexto (`tests/Feature/Tenant/ContextoDeTenantTest.php`).
- Commit: `#ARQ-RMA - Adiciona TenantContext e resolucao de empresa`.

## S5 — Isolamento por construção

- [ ] S5.1 — `EscopoDeTenant` + trait `PertenceATenant`.
- [ ] S5.2 — contrato/interface para modelos tenant-scoped.
- [ ] S5.3 — Observer criando com tenant do contexto.
- [ ] S5.4 — `tenant_id` fora de `$fillable`; teste de mass assignment.
- [ ] S5.5 — route binding tenant-aware nos models aplicáveis.
- [ ] S5.6 — 404/403 consistente.
- [ ] S5.7 — testes A×B básicos.
- Commit: `#ARQ-RMA - Adiciona isolamento automatico de tenant`.

## S6 — Parceiros tenant-scoped

- [ ] S6.1 — `Cliente` adota trait + testes A×B.
- [ ] S6.2 — `Fabricante` adota trait + testes A×B.
- [ ] S6.3 — `Fornecedor` adota trait + testes A×B.
- [ ] S6.4 — `AssistenciaTecnica` adota trait + testes A×B.
- [ ] S6.5 — revisão de controllers/policies de parceiros (criação/listagem/busca/
      edição/remoção) sem alterar regra.
- Commits por grupo pequeno (ex.: `#ARQ-RMA - Isola parceiros por tenant`).

## S7 — RMA tenant-scoped

- [ ] S7.1 — `App\Models\Rma` adota trait/escopo.
- [ ] S7.2 — `RmasEmBanco` aplica tenant no repositório (criar/atualizar/buscar/listar).
- [ ] S7.3 — alertas e painel lateral passam pelo escopo.
- [ ] S7.4 — ciclo de vida, crédito, relatórios, histórico, logística e arquivados
      verificados A×B.
- [ ] S7.5 — agregado `Dominio\Rma` permanece puro (teste de não-conhecimento de
      tenant no domínio).
- Commits por capacidade se necessário (ex.: `#ARQ-RMA - Isola repositorio de RMA por
      tenant`).

## S8 — Auditoria/histórico/acesso

- [ ] S8.1 — `ModificacaoDeRma` escopada por tenant (via RMA e/ou coluna própria).
- [ ] S8.2 — histórico de modificação e detalhe não cruzam tenant.
- [ ] S8.3 — `TentativaDeAcesso`: documento decisão de não escopar por tenant nesta
      fase (usuário global, agregação cross-tenant futura).
- [ ] S8.4 — notificações vinculadas a dado operacional escopadas.
- Commit: `#ARQ-RMA - Escopa auditoria e historico por tenant`.

## S9 — Papel por company_user

- [ ] S9.1 — mapear consumidores de `users.papel`.
- [ ] S9.2 — leitura do papel do vínculo ativo.
- [ ] S9.3 — Policies/Gates adaptados.
- [ ] S9.4 — preservar autorização atual (mesmos papéis, mesmo comportamento).
- [ ] S9.5 — testes: mesmo usuário com papéis diferentes em empresas diferentes.
- [ ] S9.6 — impedir escalada cross-tenant (Supervisor da A não opera na B sem vínculo).
- [ ] S9.7 — compatibilidade antiga removida só quando não houver consumidor.
- [ ] S9.8 — cleanup final de `users.papel` se seguro.
- Commits pequenos (ex.: `#ARQ-RMA - Migra Papel para vinculo company_user`).

## S10 — Numeração RMA por empresa

- [ ] S10.1 — tabela `contadores_de_rma` (company_id, próximo número).
- [ ] S10.2 — serviço/caso de uso de próximo número com lock transacional.
- [ ] S10.3 — nenhuma utilização de `MAX+1` (auditoria por grep).
- [ ] S10.4 — teste concorrente (dois processos/transações não repetem número).
- [ ] S10.5 — A e B têm RMA nº 1 independente; id técnico global preservado.
- [ ] S10.6 — compatibilidade com `numero_legado` no migrador.
- Commit: `#ARQ-RMA - Adiciona numeracao transacional de RMA por empresa`.

## S11 — Migrador histórico

- [ ] S11.1 — comando `rma:migrar-legado` carimba `CellSystem`.
- [ ] S11.2 — relações preservadas (parceiros, usuários, RMAs no mesmo tenant).
- [ ] S11.3 — dry-run, reconciliação e idempotência continuam válidos.
- [ ] S11.4 — relatório registra tenant quando útil; histórico original intacto.
- Commit: `#ARQ-RMA - Integra tenant CellSystem ao migrador historico`.

## S12 — Suíte arquitetural de isolamento (gate da Trilha B)

- [ ] S12.1 — teste permanente parametrizado A×B por model tenant-scoped.
- [ ] S12.2 — relatórios/alertas/histórico não somam A em B.
- [ ] S12.3 — `tenant_id` nunca aceito do request.
- [ ] S12.4 — lista canônica de models tenant-scoped obrigatória (novo model sem
      cobertura falha em teste de inventário).
- Commit: `#QA-RMA - Cria suite arquitetural de isolamento multiempresa`.

## S13 — Regressão e segurança

- [ ] S13.1 — tenancy + domínio afetado + PHPUnit completo.
- [ ] S13.2 — Playwright relevante e build.
- [ ] S13.3 — auditoria CSRF/mass assignment/IDOR/route binding/policies/spoofing.
- [ ] S13.4 — jobs/commands/notifications sem TenantContext — inventariar consumidores
      reais antes de criar mecanismo.
- [ ] S13.5 — `git diff --check` e status limpo.

## S14 — Fechamento do primeiro marco SaaS

- [ ] S14.1 — atualizar PLAN/PLANO-ATAQUE/checklist/backlog/OpenSpec por evidência.
- [ ] S14.2 — relatório/checkpoint específico da fundação multiempresa.
- [ ] S14.3 — EVO-SAAS-001 só fecha com gate de isolamento aprovado e sem migration
      pela metade.
- Commit final: `#QA-RMA - Fecha gate de isolamento multiempresa`.
