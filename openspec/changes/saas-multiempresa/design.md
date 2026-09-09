# Design - EVO-SAAS-001: fundação SaaS multiempresa

## Modelo de dados

### `companies` (nova, global da plataforma)

```
companies
  id            bigint pk
  nome          string unique
  documento     string nullable
  ativa         boolean default true
  created_at / updated_at
```

`CellSystem` é o tenant semente. O nome é a única chave natural necessária nesta fase
(documento entra nullable para não travar future hardening).

### `company_user` (nova, pivô User ↔ Company)

```
company_user
  id             bigint pk
  company_id     bigint fk -> companies
  user_id        bigint fk -> users
  papel          string default 'Leitura'   -- mesmo enum Papel de hoje
  ativo          boolean default true
  created_at / updated_at

  unique(company_id, user_id)
  index(user_id)
  index(company_id)
```

`Papel` continua no enum `App\Identidade\Dominio\Papel`; o pivô guarda o papel do
vínculo. `users.papel` permanece até S9 remover os consumidores; na fundação, o valor de
`users.papel` é copiado para o vínculo no backfill.

### Coluna `tenant_id` em entidades tenant-scoped

As tabelas abaixo ganham `tenant_id bigint nullable` inicial (nullable durante backfill;
endurecer NOT NULL + FK depois, em S3.6):

- `clientes`
- `fabricantes`
- `fornecedores`
- `assistencias_tecnicas`
- `rmas`
- `modificacoes_de_rma` (escopo via RMA; coluna própria ou apenas associação segura -
  decisão de implementação registrada nas tasks)

Globais nesta fase: `users`, `companies`, `company_user`, `tentativas_de_acesso`
(auditoria de acesso fica por usuário; agregação cross-tenant é fase futura), tabelas de
framework/cache/sessão/jobs.

## Módulo `App\Compartilhado\Tenant` (contexto)

Proporcional ao resto do projeto: sem fronteira `Dominio/Aplicacao/Infraestrutura`
artificial; o que existe de concreto:

- `App\Compartilhado\Tenant\ContextoDeTenant` - objeto de request com
  `definir(Company)`, `empresaAtiva(): ?Company`, `empresaId(): ?int`; registrado como
  singleton no container (uma instância por request).
- `App\Http\Middleware\ResolverTenantAtivo` - roda depois de `auth`, lê o usuário
  autenticado e:
  - nenhum vínculo ativo → falha explícita (403 ou redirect com mensagem, conforme a
    rota - comportamento consistente definido nas tasks);
  - exatamente um vínculo ativo → define esse tenant sem seletor;
  - múltiplos vínculos ativos → suporta seleção no backend via query/sessão de empresa
    ativa (`[DECISAO-PENDENTE]`: UI de seletor só quando necessário; chave de sessão
    reservada, sem desenho de tela).
  - tenant explícito em rota (futuro) precisa pertencer ao usuário.
- `App\Compartilhado\Tenant\PertenceATenant` (trait Eloquent): define relação
  `empresa()`, aplica Global Scope `EscopoDeTenant`, e no `creating` preenche
  `tenant_id` a partir do `ContextoDeTenant` (nunca de input). Coluna `tenant_id` fica
  fora do `$fillable` de todo model tenant-scoped (proteção de mass assignment).
- `App\Compartilhado\Tenant\EscopoDeTenant` - escopo que injeta `tenant_id = ?` sempre
  que o contexto tem empresa ativa.

## Isolamento por construção (ordem das camadas)

1. **Contexto**: `ResolverTenantAtivo` define `ContextoDeTenant` depois do login.
2. **Escrita**: Observer preenche `tenant_id` automaticamente (trait).
3. **Leitura**: Global Scope filtra toda query Eloquent.
4. **Binding**: models tenant-scoped sobrescrevem route binding (`resolveRouteBinding`)
   aplicando o escopo; URL manual de outro tenant vira 404/403.
5. **Ação**: Policies continuam as autorizações atuais.
6. **RMA**: `RmasEmBanco` aplica escopo na query Eloquent; o agregado puro não muda.

## Parceiros

`Cliente`, `Fabricante`, `Fornecedor`, `AssistenciaTecnica` adotam a trait e passam a
ser criados/listados/buscados/alterados dentro do tenant corrente. As policies atuais
permanecem para ação; tenant é resolvido por contexto+escopo.

## RMA e ciclos

- `RmasEmBanco` recebe o `ContextoDeTenant` e aplica `tenant_id` em toda query
  (criar/atualizar/buscar/listar/alertas/report). Nenhuma regra de negócio muda.
- Todas as listagens, alertas, relatórios, histórico, logística e painel de arquivados
  herdam o escopo por construção (via model Eloquent `App\Models\Rma`).
- Numeração: tabela `contadores_de_rma` com `company_id`, `proximo_numero`, lock
  transacional; o "número" exibido/operacional usa o contador, id técnico permanece
  global; `numero_legado` continua preservado para migração.

## Migrador histórico

- Comando `rma:migrar-legado` passa a carimbar `tenant_id = CellSystem` em tudo que
  importa, sem redesenho dos 8 importadores.
- Dry-run continua traduzindo sem gravar; reconciliação/idempotência continuam com o
  mesmo relatório (coluna de tenant quando útil).
- Nenhum dado histórico original é alterado.

## Papel no vínculo (transição)

- S2 cria `company_user.papel`; S3 copia `users.papel` para o vínculo no backfill.
- S9 passa Policies/Gates a lerem o papel do vínculo ativo; `users.papel` fica como
  compatibilidade até não haver consumidor; só então cleanup.
- `SuperAdministrador` de tenant não vira administrador de plataforma
  (`[DECISAO-PENDENTE]` sobre representação de plataforma).

## Gate de isolamento (suíte própria)

`tests/Feature/Tenant/IsolamentoDeTenantTest.php` e suíte parametrizada permanente:

- Empresa A cria registro; B não lista/busca/abre/edita/remove;
- relatório/alerta/histórico de B não soma A;
- RMA de B não referencia parceiro de A;
- tenant_id nunca vem de payload (mass assignment) e nunca é alterável por request;
- usuário sem vínculo falha explicitamente; papel diferente por vínculo respeita policy;
- novo model tenant-scoped sem cobertura fica difícil de adicionar (contrato trait + o
  teste parametrizado lista esperada de models).

## Decisões adiadas (registradas, não bloqueiam)

- Representação/coluna/tabela do administrador de plataforma - ortogonal ao Papel de
  tenant; só exigida quando a plataforma tiver ação real.
- UI de seletor de empresa para usuários multi-vínculo (backend suporta múltiplos).
- Trigger futuro para isolamento físico de um tenant específico.
- Curadoria da wiki global (`EVO-SAAS-002`).
- Nome/momento de endurecimento de `tenant_id` NOT NULL por tabela (deve ser S3.6, com
  prova de zero órfãos antes).
