# Proposal — Rma núcleo (criação, busca, detalhe)

Fase 3 de 10 (ver `docs/arquitetura/INV-RMA-05-arquitetura-proposta.md` §8).

## Por quê

É o domínio real do produto. Diferente de `Identidade`/`Parceiros`, aqui a fronteira
completa de domínio (`Dominio`/`Aplicacao`/`Infraestrutura`, com interface de
repositório) se justifica de verdade: a Fase 9 (migração) vai precisar ler o schema
muito diferente de `rma_legacy` (`bd`, ~56 colunas, sem FK) e alimentar este módulo —
sem uma fronteira, o código de migração vazaria para dentro de toda a aplicação.

## O que entra

- Migration inicial de `rmas` — só os campos que criação/busca/detalhe precisam (não
  a tabela inteira de uma vez; `status`/`solucao`/NF/crédito entram nas fases seguintes)
- `Rma` (objeto de domínio puro, não Eloquent) + `RepositorioDeRmas` (interface) +
  `CriterioDeBusca` (value object, substitui os `campo=TUDO/NF/SNPNSNID` do legado)
- `RmasEmBanco` (implementação Eloquent da interface, uso interno da infra)
- Casos de uso: `CriarRma`, `BuscarRmas`, `VerDetalheDoRma`
- Controller + views mínimas (sem fidelidade visual — Fase 8)

## O que não entra

- `status`/transições (Fase 4), `solucao`/regras de alerta (Fase 5), crédito/relatórios
  (Fase 6), auditoria de modificação (Fase 7).
- Qualquer coisa de apresentação fiel ao legado (Fase 8).

## Decisão registrada

Identificador: `id` incremental do Eloquent (sem caso de uso de exposição pública/API
ainda — UUID/ULID fica como `EVO` se isso mudar). `CriarRma` usa
`EncontrarOuCriarCliente` (módulo `Parceiros`, Fase 2) quando o cliente informado for
novo — dependência direta entre módulos, aceitável (não é dependência circular).

## Rastreabilidade com o legado

| Este OpenSpec | Legado |
|---|---|
| `CriarRma` | `LEG-RMA-007` |
| `BuscarRmas`/`CriterioDeBusca` | `LEG-RMA-008` (unifica os 4 arquivos idênticos `pesquisar_{rma,nf,sn,descricao}.php` do legado numa única busca parametrizada por critério nomeado, não por string `campo=`) |
| `VerDetalheDoRma` | `LEG-RMA-009` |
