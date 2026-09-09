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
- [x] S0.6 — registrar baseline pré-SaaS no handoff desta sessão (seção própria).

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
- [x] S1.7 — registrar decisões adiadas (design §Decisões adiadas).
- Commit documental sugerido: `#DOC-RMA - Especifica fundacao SaaS multiempresa`.

## S2 — Fundação de Company

- [x] S2.1 — migration `create_companies_table`.
- [x] S2.2 — model `App\Models\Company`.
- [x] S2.3 — migration `create_company_user_table` (unique company/user, índices).
- [x] S2.4 — relacionamento `User::empresas()`/`Company::usuarios()` com pivot tipado `CompanyUser`.
- [x] S2.5 — coluna `papel` no pivô (mesmo enum Papel).
- [x] S2.6 — constraints/índices e testes de relacionamento
      (`tests/Feature/Tenant/CompanyUserTest.php`, 4 testes verdes).
- [x] S2.7 — factories/seeds determinísticas (`CompanyFactory`; pivot é tipado e testado sem
      factory própria nesta onda — seeds/tenant aparecem na S3).
- [x] S2.8 — `users.papel` permanece; transição compatível registrada no design.
- Commit: `#ARQ-RMA - Introduz Company e vinculo de usuarios`.

## S3 — Tenant inicial e backfill

- [x] S3.1 — criar tenant `CellSystem` deterministicamente (migration 000004 + testes).
- [x] S3.2 — vincular usuários existentes a CellSystem (migration/UserSeeder).
- [x] S3.3 — preservar `Papel` atual no `company_user` (mesma migration).
- [x] S3.4 — adicionar `tenant_id` nullable às tabelas tenant-scoped (migration 000003).
- [x] S3.5 — backfill dos dados existentes para CellSystem (migration 000004, idempotente).
- [ ] S3.6 — endurecer NOT NULL/FKs quando provado zero órfãos. **Adiada por
      segurança nesta rodada**: código de escrita já preenche (observer/factory/
      listener), mas o hardening depende de auditoria em dado real e do gate S12.
- [x] S3.7 — prova: migrations carimbam CellSystem; factory/observer preenchem; prova
      definitiva de zero órfãos fica no gate S12.
- [x] S3.8 — rollback/compatibilidade: `down` remove só vínculos criados pela migration
      e a migration 000003 reverte colunas/índices/FKs.
- Commit: `#ARQ-RMA - Cria tenant CellSystem e faz backfill dos dados existentes`.

## S4 — TenantContext

- [x] S4.1 — `ContextoDeTenant` (singleton por request).
- [x] S4.2 — binding no container.
- [x] S4.3 — `ResolverTenantAtivo` middleware (web).
- [x] S4.4 — um vínculo ativo funciona sem seletor.
- [x] S4.5 — múltiplos vínculos suportados no backend (sessão `empresa_ativa_id`); UI de
      seletor é `[DECISAO-PENDENTE]`.
- [x] S4.6 — ausência de vínculo falha explicitamente (403).
- [x] S4.7 — empresa da sessão não pertencente ao usuário é rejeitada (ignorada).
- [x] S4.8 — testes do contexto (4 testes verdes).
- Commit: `#ARQ-RMA - Adiciona TenantContext e resolucao de empresa`.

## S5 — Isolamento por construção

- [x] S5.1 — `EscopoDeTenant` + trait `PertenceATenant`.
- [x] S5.2 — contrato via trait aplicada aos seis models tenant-scoped.
- [x] S5.3 — Observer preenche tenant do contexto no `creating`.
- [x] S5.4 — `tenant_id` fora de `$fillable`; teste de mass assignment.
- [x] S5.5 — route binding tenant-aware por membership (resolveRouteBindingQuery);
      achado: binding roda antes do middleware, então usa vínculos do usuário.
- [x] S5.6 — 404/403 consistente (404 para registro de outra empresa; 403 sem vínculo).
- [x] S5.7 — testes A×B básicos (IsolamentoBasicoTest).
- Commit: `#ARQ-RMA - Adiciona isolamento automatico de tenant`.

## S6 — Parceiros tenant-scoped

- [x] S6.1 — `Cliente` adota trait + testes A×B.
- [x] S6.2 — `Fabricante` adota trait + testes A×B.
- [x] S6.3 — `Fornecedor` adota trait + testes A×B.
- [x] S6.4 — `AssistenciaTecnica` adota trait + testes A×B.
- [x] S6.5 — revisão via suite A×B (16 testes HTTP: listar/abrir/atualizar/criar).
- Commits por grupo pequeno (ex.: `#ARQ-RMA - Isola parceiros por tenant`).

## S7 — RMA tenant-scoped

- [x] S7.1 — `App\Models\Rma` adota trait/escopo.
- [x] S7.2 — `RmasEmBanco` usa RmaEloquent com escopo; criação pelo observer.
- [x] S7.3 — alertas/painéis/relatórios herdam o escopo por construção (queries em
      `App\Models\Rma`).
- [x] S7.4 — suite A×B cobre listagem/edição/ciclo/criação; histórico em S8;
      relatórios/alertas herdam escopo e entram no gate S12.
- [x] S7.5 — agregado puro permanece sem tenant (sem alterações no domínio).
- Commits por capacidade se necessário (ex.: `#ARQ-RMA - Isola repositorio de RMA por
      tenant`).

## S8 — Auditoria/histórico/acesso

- [x] S8.1 — `ModificacaoDeRma` escopada por tenant (coluna própria + listener herda do
      RMA pai).
- [x] S8.2 — histórico não cruza tenant (AuditoriaIsolamentoTest).
- [x] S8.3 — decisão registrada: `TentativaDeAcesso` sem escopo nesta fase.
- [x] S8.4 — notificações seguem dado operacional escopado pelo RMA (sem jobs novos).
- Commit: `#ARQ-RMA - Escopa auditoria e historico por tenant`.

## S9 — Papel por company_user

- [x] S9.1 — mapear consumidores de `users.papel` (`docs/produto/matriz-consumidores-papel-2026-09-09.md`).
- [x] S9.2 — leitura do papel do vínculo ativo (`ContextoDeTenant::papelAtivo`, `User::papelAtivo()`).
- [x] S9.3 — Policies/casos de uso/controllers adaptados ao `papelAtivo()` e alvo `papelNaEmpresa()`.
- [x] S9.4 — autorização single-company preservada (435 testes / 1029 assertions verdes).
- [x] S9.5 — PapelPorVinculoTest: mesmo usuário Supervisor em A e Leitura em B.
- [x] S9.6 — teste HTTP comprova 403 na B para Supervisor de A.
- [~] S9.7 — compatibilidade antiga reduzida; consumidores restantes são intencionais
      (migrador/backfill/factory/fallback sem contexto). Falta remoção final na S9.8.
- [ ] S9.8 — cleanup final de `users.papel`; não executado: ainda há consumidores
      intencionais de runtime e migrador (registrados na matriz).
- Commits pequenos (ex.: `#ARQ-RMA - Migra Papel para vinculo company_user`).

## S10 — Numeração RMA por empresa

- [x] S10.1 — tabela `contadores_de_rma` (migration 000005, unique por company).
- [x] S10.2 — `ReservarNumeroDeRma` com transação e `lockForUpdate`.
- [x] S10.3 — criação de RMA usa contador; sem `MAX+1` (RmasEmBanco).
- [ ] S10.4 — teste concorrente real pendente: fork dentro do PHPUnit derruba a
      conexão MySQL compartilhada; mecanismo usa lockForUpdate, mas falta prova de
      processo externo (registrado como pendência S10.4).
- [x] S10.5 — A/B reservam 1,1 e RMA criados na mesma empresa recebem 1,2 com id
      técnico global.
- [x] S10.6 — `numero_legado` permanece preservado (migrador não usa contador).
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
