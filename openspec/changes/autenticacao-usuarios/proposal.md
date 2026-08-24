# Proposal — Identidade (autenticação, papéis, tema preferido)

Fase 1 de 10 (ver `docs/arquitetura/INV-RMA-05-arquitetura-proposta.md` §5).

## Por quê

Nada do RMA funciona sem usuário autenticado. É a fase de menor risco para começar: o
schema já é estável (`users` padrão do Laravel + 2 colunas), a regra de negócio (5
papéis, tema preferido) já foi validada nos dois temas do legado com evidência direta
(`docs/legado/regras-negocio-rma-legado.md` — achado da regressão de troca de senha
entre TEMA V1/TEMA V2 confirma exatamente qual comportamento é o pretendido).

## O que entra nesta change

- Migrations: colunas `papel`/`tema_preferido`/`anotacao` em `users`, tabela
  `tentativas_de_acesso` (equivalente à `log` do legado).
- `App\Identidade\Dominio\Papel` (enum sem backing numérico, métodos nomeados —
  `podeAutenticar()`, `podeGravar()`, `podeGerenciarUsuarios()`,
  `ocultoDaListagemDeUsuarios()`).
- `App\Identidade\Dominio\TemaPreferido` (enum `V1`/`V2`).
- `App\Identidade\Aplicacao\AutenticarUsuario` (login + bloqueio + auditoria + decide
  tema de redirect).
- `App\Identidade\Aplicacao\AlternarTemaPreferido` (equivalente a `trocarapp.php`).
- `App\Models\User` (Eloquent padrão, cast de `Papel`/`TemaPreferido`).
- `App\Models\TentativaDeAcesso` (Eloquent simples).
- `App\Policies\UserPolicy`.
- `App\Http\Controllers\Identidade\SessaoController` e `TemaPreferidoController`.
- Rotas de sessão em `routes/web.php`.
- View de login mínima (sem fidelidade visual ainda — isso é Fase 8).
- Testes: autenticação, permissão, alternância de tema.
- **Gestão de usuários (ajuste da revisão de fases, ver
  `docs/arquitetura/revisao-fases-1-2-3.md`):** `TrocarPropriaSenha` (`LEG-RMA-004`,
  **usa TEMA V1 como especificação** — SQL correto, único `UPDATE`, corrigindo a
  regressão confirmada de TEMA V2, RN-21), `ResetarSenhaDeUsuario` (`LEG-RMA-003`),
  `AtualizarAnotacaoPessoal` (`LEG-RMA-042`, bloco de notas pessoal),
  `UsuarioController` (listar/editar papel, `LEG-RMA-005`, oculta
  `SuperAdministrador` da listagem para quem não é `SuperAdministrador`),
  `AnotacaoPessoalController`. Estas funcionalidades não tinham fase própria no plano
  original (não eram reivindicadas por nenhuma das Fases 2-10) — ficam em Fase 1 porque
  usam exatamente o `User`/`Papel`/`UserPolicy` que já nascem aqui.

## O que não entra (fica pra próxima fase, ou é pendência registrada)

- Qualquer coisa de `Rma`/`Parceiros` (Fase 2/3).
- Fidelidade visual do login e das telas de usuário aos dois temas (Fase 8) — nesta fase
  tudo é funcional, não fiel ao legado visualmente.
- **Cadastro público de usuário com convite (`LEG-RMA-002`) — PENDÊNCIA, não decidida
  nesta change.** Confirmado só em TEMA V1 (segredo hardcoded em `inc/signup.php`),
  `[DÚVIDA]` em TEMA V2. Não há evidência suficiente para inferir se autocadastro
  público é "comportamento pretendido" a preservar ou uma porta lateral a fechar — decidir
  com o usuário antes de implementar (opção A: manter autocadastro com segredo em
  `.env`; opção B: usuário só é criado por um Supervisor/SuperAdministrador via
  `UsuarioController`).

## Rastreabilidade com o legado

| Este OpenSpec | Legado |
|---|---|
| `AutenticarUsuario` | `LEG-RMA-001` (login/logout) |
| `Papel` (5 casos) | Domínio de `usuario.permissao` confirmado idêntico nos dois temas |
| `AlternarTemaPreferido` | `LEG-RMA-006` (`trocarapp.php`), smoke-testado de verdade no Legacy |
| `TentativaDeAcesso` | `LEG-RMA-043` (tabela `log`) |
| `TrocarPropriaSenha` | `LEG-RMA-004` — **TEMA V1 como especificação** (RN-21) |
| `ResetarSenhaDeUsuario` | `LEG-RMA-003` |
| `UsuarioController` | `LEG-RMA-005` |
| `AtualizarAnotacaoPessoal` | `LEG-RMA-042` |
| **Pendência (não decidida aqui)** | `LEG-RMA-002` (autocadastro com convite) — ver seção acima |
