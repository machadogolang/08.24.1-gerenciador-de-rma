# Proposal — EVO-SAAS-001: fundação SaaS multiempresa

Primeira grande iniciativa da Trilha B, liberada para execução controlada em 2026-09-09.
Decisões de arquitetura já investigadas e vigentes em
`docs/arquitetura/INV-RMA-07-evolucao-saas-multiempresa.md`; este OpenSpec converte
a investigação em contrato de implementação por ondas pequenas. Nada aqui copia a
investigação — o repositório continua sendo a fonte de verdade.

## Por quê

O legado (15.9.7) opera múltiplas empresas do mesmo grupo econômico sob um único banco,
com `bd.empresa` como texto livre nunca usado para isolar nada. O RMA V3 está com a
Trilha A encerrada e precisa virar plataforma SaaS multiempresa sem perder as fronteiras
modulares já reconstruídas.

## O que entra

- Fundação de tenant: `companies`, `company_user` (vínculo User↔Company), tenant
  `CellSystem`, backfill determinístico das tabelas tenant-scoped atuais.
- `TenantContext` como fonte central de tenant corrente e middleware de resolução.
- Isolamento por construção (Global Scope + Observer + route binding + Policies), sem
  biblioteca de tenancy externa.
- Parceiros, RMA (via `RmasEmBanco`), históricos/auditoria e numeração por empresa.
- Migrador histórico carimbando tenant `CellSystem`.
- Papel migrando para o vínculo `company_user`, com estratégia de transição que não
  remove `users.papel` prematuramente.
- Suíte arquitetural permanente de isolamento e gate próprio da Trilha B.

## O que não entra (nesta fase)

- `EVO-SAAS-002` (wiki/catálogo de referência com importação seletiva).
- `EVO-SAAS-003` (comunidade/fórum inter-tenant).
- Tema V3, anexos e configuração de admin.
- Agregação de segurança cross-tenant e observabilidade de plataforma.
- Billing, convites auto-onboarding, UI complexa de troca de empresa.
- Isolamento físico (banco por empresa) — permanece decisão adiada, não implementada.

## Decisões arquiteturais vigentes

1. Banco compartilhado com `tenant_id` (Modelo A do `INV-RMA-07` §5).
2. `User` ↔ `Company` muitos-para-muitos via `company_user`.
3. `Papel` pertence ao vínculo `company_user`; `users.papel` é removido só no fim da
   transição, nunca no início.
4. `TenantContext` é a fonte central de tenant corrente.
5. Global Scope automático para entidades tenant-scoped.
6. Middleware resolve tenant após autenticação; ausência de vínculo falha explicitamente.
7. Route model binding tenant-aware rejeita registro de outro tenant.
8. Observer preenche `tenant_id` no `creating`; `tenant_id` nunca vem do request.
9. Policies continuam cuidando de ação/autorização (defesa em profundidade).
10. `App\Rma\Dominio\Rma` permanece puro; scoping de RMA mora na infraestrutura
    (`RmasEmBanco`) e nos models Eloquent.
11. Primeiro tenant = `CellSystem`; migrador carimba esse tenant.
12. Numeração de RMA por empresa via contador transacional dedicado; nunca `MAX+1`.
13. Sem biblioteca de tenancy externa sem necessidade demonstrada.
14. SuperAdministrador de tenant NÃO é administrador de plataforma; representação da
    administração de plataforma é `[DECISAO-PENDENTE]` quando exigida.

## Critério de aceite (gate de isolamento)

Ver `design.md` §Gate. Resumo: nenhuma entidade tenant-scoped lista/busca/abre/edita
dado de outra empresa em cenário A×B testado de ponta a ponta; migração sem linha órfã;
idempotência do migrador preservada; suíte completa verde.

## Rastreabilidade

- Investigação: `docs/arquitetura/INV-RMA-07-evolucao-saas-multiempresa.md`.
- Backlog: `docs/produto/backlog-evolutivo.md` (`EVO-SAAS-001`).
- Design e tasks: arquivos irmãos deste diretório.
