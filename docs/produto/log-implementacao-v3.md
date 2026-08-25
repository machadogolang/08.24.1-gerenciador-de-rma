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

## Fase 2 — Parceiros

**Data:** 2026-08-24.

**Implementado:** 4 migrations (`clientes`, `fabricantes`, `fornecedores`,
`assistencias_tecnicas`); enum `App\Compartilhado\Uf` (27 UFs, cast nativo puro,
mesmo padrão de `TemaPreferido` da Fase 1); `App\Models\{Cliente,Fabricante,
Fornecedor,AssistenciaTecnica}` (Eloquent direto, sem interface de repositório —
decisão de `INV-RMA-05` §7: só `Rma`, na Fase 3, ganha essa fronteira); trait
`App\Parceiros\Concerns\TemEnderecoEContato` compartilhada pelos 3 análogos
(Fabricante/Fornecedor/AssistenciaTecnica — `Cliente` tem schema genuinamente
diferente, não usa a trait); `App\Parceiros\Aplicacao\EncontrarOuCriarCliente` (único
caso de uso real desta fase — corrige o `adicionar_cli()` do legado, que duplicava
cliente por variação de digitação, comparando nome exato sem trim/case-fold); 4
Policies (`ClientePolicy` + 3 análogas, delegando a `Papel::podeGravar()` da Fase 1,
leitura liberada a qualquer autenticado); 4 Controllers resource
(`app/Http/Controllers/Parceiros/`) + rotas `parceiros/{clientes,fabricantes,
fornecedores,assistencias-tecnicas}`; views mínimas compartilhadas
(`_form.blade.php`/`index.blade.php`, sem fidelidade visual — Fase 8); 4 Factories;
testes de CRUD ×4 + `EncontrarOuCriarClienteTest`.

**Desvios do OpenSpec (documentados no código):**
- `Fornecedor` e `AssistenciaTecnica` precisaram de `protected $table` explícito: a
  pluralização automática do Eloquent (inglês) gera `fornecedors` e
  `assistencia_tecnicas`, que não batem com os nomes de tabela em português definidos
  no `design.md` (`fornecedores`, `assistencias_tecnicas`). Detectado pelos testes de
  CRUD (erro `Base table or view not found`), corrigido declarando o nome da tabela
  explicitamente em cada model — nenhuma mudança de schema, só a resolução do nome.
- Rotas: `Route::resource` também singulariza em inglês para o nome do parâmetro de
  route model binding (`fornecedores` → `fornecedore`, `assistencias-tecnicas` →
  `assistencias-tecnica`), incompatível com os nomes de parâmetro dos controllers
  (`$fornecedor`, `$assistenciaTecnica`). Corrigido com `->parameters([...])`
  explícito nas duas rotas afetadas.

**Testes:** 61/61 verdes, 143 assertions (`sail test`) — os 36 da Fase 1 continuam
passando. Confirmado manualmente via `tinker`: `EncontrarOuCriarCliente` reaproveita
`"Cliente Teste Manual"` ao receber `"  cliente   TESTE MANUAL  "` (espaço duplo +
maiúscula diferente) em vez de criar um segundo registro — `Cliente::count()`
permaneceu em 1 antes da limpeza do dado de teste manual.

**Pendências que ficaram de fora:** nenhuma da Fase 2 propriamente dita. Unificação em
`Parceiro` polimórfico e a tabela órfã `assistencias` do legado permanecem fora de
escopo por decisão já registrada em `proposal.md` (`EVO-DOM-001`, backlog evolutivo).
Fidelidade visual das views fica para a Fase 8.

**Commit:** `628475d` — `#F2 - Parceiros (cliente/fabricante/fornecedor/assistencia
tecnica)`.

---
