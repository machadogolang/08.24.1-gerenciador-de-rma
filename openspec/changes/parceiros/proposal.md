# Proposal — Parceiros (Cliente, Fabricante, Fornecedor, Assistência Técnica)

Fase 2 de 10 (ver `docs/arquitetura/INV-RMA-05-arquitetura-proposta.md` §7).

## Por quê

`Rma` (Fase 3) referencia parceiros por FK real — o cadastro precisa existir primeiro.
Schema estável (confirmado em `inventario-banco-rma-v2.md`), única regra de negócio real
é a deduplicação na auto-criação de cliente (achado do legado: `adicionar_cli()` compara
nome exato, gera duplicata por variação de digitação).

## O que entra

- 4 migrations (`clientes`, `fabricantes`, `fornecedores`, `assistencias_tecnicas`)
- 4 Eloquent models, 3 deles compartilhando `trait TemEnderecoEContato`
- `EncontrarOuCriarCliente` (único caso de uso real desta fase)
- 4 Policies (delegam a `Papel::podeGravar()`, já existe da Fase 1)
- 4 Controllers (resource padrão)
- Views genéricas (parcial de formulário compartilhada — sem fidelidade visual ainda)
- Testes de CRUD ×4 + teste da deduplicação

## O que não entra

- Unificação em `Parceiro` polimórfico único — ideia registrada em `EVO-DOM-001`
  (backlog evolutivo), não implementada agora (é exatamente o que o próprio legado
  tentou e abandonou pela metade — tabela órfã `assistencias`).
- Qualquer referência de `Rma` a parceiro (Fase 3).
- Fidelidade visual (Fase 8).

## Decisão registrada (regra de evolução do banco)

FK real desde a baseline, não string — ver tabela ANTES/PROBLEMA/DEPOIS/MIGRAÇÃO/
COMPATIBILIDADE/TESTE completa em `INV-RMA-05-arquitetura-proposta.md` §7.

## Rastreabilidade com o legado

| Este OpenSpec | Legado |
|---|---|
| `ClienteController` | `LEG-RMA-030` |
| `FabricanteController` | `LEG-RMA-031` |
| `FornecedorController` | `LEG-RMA-032` |
| `AssistenciaTecnicaController` | `LEG-RMA-033` |
| `EncontrarOuCriarCliente` | corrige o comportamento de `adicionar_cli()` (RN correlata em `modelo-dominio-rma-legado.md` §Cliente) — dedup real é melhoria de engenharia interna, invisível ao usuário, entra na baseline |
| **Fora do escopo, registrado** | `LEG-RMA-034` ("autorizada", código morto) — não reconstruir; `LEG-RMA-035` (tabela `assistencias` órfã) — só a ideia vira `EVO-DOM-001` |
