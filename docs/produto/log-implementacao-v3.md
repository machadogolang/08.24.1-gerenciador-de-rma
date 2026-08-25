# Log de implementação — RMA V3

Registro cronológico do que foi implementado de verdade (código, não planejamento),
fase por fase, para permitir uma revisão final por fora — comparando este log contra
`docs/produto/checklist-master-v3.md` e `docs/produto/paridade-v2-v3.md` — e detectar
o que ficou faltando antes de considerar o projeto concluído.

Cada entrada é escrita pelo agente que implementou a fase, ao final do trabalho, e
commitada junto com o resto da fase. Formato fixo por entrada: **Fase**, **Data**,
**Implementado**, **Desvios do OpenSpec** (se houver, com justificativa), **Testes**,
**Pendências que ficaram de fora**, **Commit(s)**.

---

## Fase 1 — Identidade

**Data:** 2026-08-24.

**Implementado:** migrations (`papel`/`tema_preferido`/`anotacao` em `users`,
`tentativas_de_acesso`); enums `App\Identidade\Dominio\{Papel,TemaPreferido,
ResultadoDeAcesso}`; casos de uso `AutenticarUsuario`, `AlternarTemaPreferido`,
`TrocarPropriaSenha` (TEMA V1 como especificação, corrige a regressão RN-21),
`ResetarSenhaDeUsuario`, `AtualizarAnotacaoPessoal`; `App\Models\{User,
TentativaDeAcesso}`; `UserPolicy`; controllers `SessaoController`,
`TemaPreferidoController`, `UsuarioController`, `AnotacaoPessoalController`; rotas;
views mínimas (sem fidelidade visual — isso é Fase 8); `UserFactory`/`UserSeeder`.

**Desvios do OpenSpec (documentados no código):**
- `UsuarioController::index`: o snippet do `design.md` misturava `Builder::when()` com
  `->get()` dentro do callback (tipo de retorno inconsistente); corrigido para
  `Collection::when()` pós-`get()`, mesma condição booleana.
- Validação de `papel` no `UsuarioController::update`: `Rule::enum()` exige backing
  type (`Papel` deliberadamente não tem, por causa do princípio "sem número mágico");
  trocado por `Rule::in(array_column(Papel::cases(), 'name'))`.

**Testes:** 36/36 verdes, 91 assertions (`sail test`). Confirmado manualmente via
`curl` (login real, redirecionamento por papel, bloqueio sem revelar motivo).

**Pendências que ficaram de fora:** `LEG-RMA-002` (autocadastro público com convite) —
decisão de produto não tomada (opção A: segredo em `.env`; opção B: só admin cria
usuário). Registrado em `tasks.md` e `paridade-v2-v3.md`, aguardando decisão do
usuário.

**Commit:** `586513f` — `#F1 - Identidade (autenticacao, papeis, tema preferido)`.

---
