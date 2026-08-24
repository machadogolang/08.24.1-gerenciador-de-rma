# Proposal — Rma núcleo (criação, busca, detalhe)

Fase 3 de 10 (ver `docs/arquitetura/INV-RMA-05-arquitetura-proposta.md` §8).

## Por quê

É o domínio real do produto. Diferente de `Identidade`/`Parceiros`, aqui a fronteira
completa de domínio (`Dominio`/`Aplicacao`/`Infraestrutura`, com interface de
repositório) se justifica de verdade: a Fase 9 (migração) vai precisar ler o schema
muito diferente de `rma_legacy` (`bd`, ~56 colunas, sem FK) e alimentar este módulo —
sem uma fronteira, o código de migração vazaria para dentro de toda a aplicação.

## O que entra

- Migration inicial de `rmas` — só os campos que criação/busca/detalhe/edição precisam
  (não a tabela inteira de uma vez; `status`/`solucao`/NF/crédito entram nas fases
  seguintes), incluindo `fornecedor_id` (**ajuste da revisão**, ver
  `docs/arquitetura/revisao-fases-1-2-3.md` — ausente do desenho original apesar de ser
  campo de "Partes" do mesmo grupo de `fabricante_id`/`cliente_id`)
- `Rma` (objeto de domínio puro, não Eloquent) + `RepositorioDeRmas` (interface) +
  `CriterioDeBusca` (value object, substitui os `campo=TUDO/NF/SNPNSNID` do legado)
- `RmasEmBanco` (implementação Eloquent da interface, uso interno da infra)
- Casos de uso: `CriarRma`, `EditarRma` (**ajuste da revisão** — `LEG-RMA-010` não tinha
  fase dona no plano original), `BuscarRmas`, `VerDetalheDoRma`
- **Normalizações de gravação (ajuste da revisão):** RN-13 (HGST→Hitachi) e RN-14
  (cascata de `origem`), aplicadas por `CriarRma`/`EditarRma` — confirmadas em ambos os
  temas, disparam na criação/edição, não dependem de `status`/`solucao`; adiar para
  Fase 4/5 faria o primeiro RMA já nascer sem a normalização que o legado sempre
  aplicou. RN-17 (`marcarestoque`) é reproduzida como "só o valor do formulário" — o
  cálculo por `origem` do legado é código morto (nunca muda o resultado observável, ver
  `regras-negocio-rma-legado.md`), não é reproduzido.
- Controller + views mínimas (sem fidelidade visual — Fase 8)

## O que não entra

- `status`/transições (Fase 4), `solucao`/regras de alerta (Fase 5), crédito/relatórios
  (Fase 6), auditoria de modificação (Fase 7).
- RN-15 (`snretorno` auto-preenchido, `LEG-RMA-047`) — depende de `solucao`, entra na
  Fase 4.
- Qualquer coisa de apresentação fiel ao legado (Fase 8).

## Decisão registrada

Identificador: `id` incremental do Eloquent (sem caso de uso de exposição pública/API
ainda — UUID/ULID fica como `EVO` se isso mudar). `CriarRma` usa
`EncontrarOuCriarCliente` (módulo `Parceiros`, Fase 2) quando o cliente informado for
novo — dependência direta entre módulos, aceitável (não é dependência circular).

## Rastreabilidade com o legado

| Este OpenSpec | Legado |
|---|---|
| `CriarRma` | `LEG-RMA-007`, RN-13/RN-14/RN-17 (normalização na gravação) |
| `EditarRma` | `LEG-RMA-010`, RN-13/RN-14/RN-17 (mesmas normalizações da criação) |
| `BuscarRmas`/`CriterioDeBusca` | `LEG-RMA-008` (unifica os 4 arquivos idênticos `pesquisar_{rma,nf,sn,descricao}.php` do legado numa única busca parametrizada por critério nomeado, não por string `campo=`) |
| `VerDetalheDoRma` | `LEG-RMA-009` |
| **Fora do escopo desta fase, registrado** | RN-15/`LEG-RMA-047` (`snretorno`) — entra na Fase 4, depende de `solucao` |
