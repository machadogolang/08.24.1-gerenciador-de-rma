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

## Fase 3 — Rma núcleo

**Data:** 2026-08-25.

**Implementado:** migration incremental de `rmas` (inclui `fornecedor_id`, ajuste da
revisão); `App\Rma\Dominio\{Rma,RepositorioDeRmas,CriterioDeBusca}` (objeto de domínio
puro, sem Eloquent); `App\Rma\Infraestrutura\RmasEmBanco` (implementação Eloquent
interna, nunca exposta fora da infra) + binding em `AppServiceProvider`;
`App\Rma\Aplicacao\{CriarRma,EditarRma,BuscarRmas,VerDetalheDoRma}` — `CriarRma`/
`EditarRma` chamam `EncontrarOuCriarCliente` (Fase 2) e aplicam
`Rma::comNormalizacaoDeGravacao()` (RN-13/RN-14) antes de persistir; `RmaController`
(index/create/store/show/edit/update) + `RmaPolicy` (mesmo padrão de `ClientePolicy`);
views mínimas; `RmaFactory`; 6 arquivos de teste (4 feature + 2 unit).

Esta é a única fase com a fronteira completa `Dominio`/`Aplicacao`/`Infraestrutura` com
interface de repositório — decisão já tomada em `INV-RMA-05` §7/§8, confirmada correta
na implementação (a Fase 9/migração vai usar essa fronteira para não vazar o schema
`rma_legacy` pro resto do app).

**Desvios do OpenSpec (documentados no código):**
- `RepositorioDeRmas` ganhou um método `atualizar(Rma $rma): Rma` não presente no
  snippet literal do `design.md` (que antecedia o ajuste da revisão que trouxe
  `EditarRma`/`LEG-RMA-010` para esta fase) — necessário para `EditarRma` não furar a
  fronteira de domínio tocando o Eloquent model diretamente. Mesmo padrão de
  `criar()`/`buscarPorId()`, sem quebrar a pureza do objeto de domínio.
- `CriterioDeBusca::porNotaFiscal()` busca no campo `os` (ordem de serviço) nesta fase,
  não em campos de nota fiscal reais (`nfcompra`/`nfremessa`/`nfvenda`), que só entram
  na Fase 6 (crédito/NF). Decisão registrada em comentário no
  `RmasEmBanco.php` — revisitar quando os campos reais existirem.

**Testes:** 85/85 verdes, 189 assertions (`sail test`), mantendo os 61 das Fases 1-2.
`RmaTest::comNormalizacaoDeGravacao` cobre todos os casos do `design.md` (HGST→Hitachi,
origem=fabricante/fornecedor→Unknown, origem=cliente/empresa→Cliente,
Cellsystem→Loja, Leilao/Receita/Receita Federal→Leilão, valor fora do domínio
inalterado). `CriarRmaTest`/`EditarRmaTest` confirmam a normalização de ponta a ponta
via HTTP, não só no unit test isolado.

**Pendências que ficaram de fora:** nenhuma da Fase 3 propriamente dita. `origem` segue
como string solta nesta fase (deliberado — o enum de domínio fechado só nasce na Fase
4/5, quando o conjunto de valores usado pelas regras de alerta estiver fixado por
completo).

**Nota de processo:** esta fase foi implementada por um agente em background que foi
interrompido por um limite de sessão da API antes de concluir os testes Feature
(`EditarRmaTest`/`BuscarRmasTest`/`VerDetalheDoRmaTest` e a atualização de
documentação). O trabalho já commitável (domínio, aplicação, infraestrutura,
controller, views, `CriarRmaTest`, testes unit) estava correto e completo; a
continuação (os 3 testes Feature restantes + atualização de `paridade-v2-v3.md`/
`checklist-master-v3.md`/`tasks.md`) foi concluída diretamente nesta sessão principal.

**Commit:** `#F3 - Rma nucleo (criacao, busca, detalhe)` (ver hash abaixo, aplicado
junto com este log).

---
