# Proposal — Identidade (autenticação, papéis, tema preferido)

Fase 1 de 10 (ver `docs/arquitetura/INV-RMA-05-arquitetura-proposta.md` §5).

## Por quê

Nada do RMA funciona sem usuário autenticado. É a fase de menor risco para começar: o
schema já é estável (`users` padrão do Laravel + 2 colunas), a regra de negócio (5
papéis, tema preferido) já foi validada nos dois temas do legado com evidência direta
(`docs/legado/regras-negocio-rma-legado.md` — achado da regressão de troca de senha
entre TEMA V1/TEMA V2 confirma exatamente qual comportamento é o pretendido).

## O que entra nesta change

- Migrations: colunas `papel`/`tema_preferido` em `users`, tabela `tentativas_de_acesso`
  (equivalente à `log` do legado).
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

## O que não entra (fica pra próxima fase)

- Qualquer coisa de `Rma`/`Parceiros` (Fase 2/3).
- Fidelidade visual do login aos dois temas (Fase 8) — nesta fase o login é funcional,
  não fiel ao legado visualmente.
- Cadastro público de usuário (o legado tem autocadastro com convite em TEMA V1 —
  `LEG-RMA-002` — decisão de incluir ou não fica para quando o admin de usuários for
  desenhado; login/logout/permissão não dependem disso).

## Rastreabilidade com o legado

| Este OpenSpec | Legado |
|---|---|
| `AutenticarUsuario` | `LEG-RMA-001` (login/logout) |
| `Papel` (5 casos) | Domínio de `usuario.permissao` confirmado idêntico nos dois temas |
| `AlternarTemaPreferido` | `LEG-RMA-006` (`trocarapp.php`), smoke-testado de verdade no Legacy |
| `TentativaDeAcesso` | `LEG-RMA-043` (tabela `log`) |
| **Fora do escopo desta fase, registrado para não esquecer** | `LEG-RMA-004` (trocar a própria senha) — funciona em TEMA V1, quebrado em TEMA V2; **usar TEMA V1 como especificação** quando esta funcionalidade entrar (fase de gestão de usuários, não decidido ainda em qual fase cai) |
