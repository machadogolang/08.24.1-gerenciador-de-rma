# INV-RMA-05 — Arquitetura proposta (monólito modular, referência CONAHOM)

Data: 2026-08-24. Decide como o RMA V3 é organizado, com base no padrão real do CONAHOM
(inspecionado nesta sessão, não copiado de memória), aplicado proporcionalmente ao
tamanho real deste domínio.

## 1. O padrão CONAHOM, como ele é de fato (não como se imagina)

Inspeção direta de `~/github/online-conahom-laravel/app/`:

```
app/
├── {Modulo}/
│   ├── Dominio/          objetos de domínio, interfaces de repositório, exceções
│   ├── Aplicacao/        casos de uso (uma classe por ação de negócio, verbo no nome)
│   └── Infraestrutura/   implementações — convenção de nome "...EmBanco" para Eloquent
├── Compartilhado/        kernel compartilhado entre módulos
├── Http/                 Controllers/Middleware/Requests (Laravel padrão)
├── Console/, Providers/, View/   (Laravel padrão)
```

Achados concretos (não hipótese — código lido):
- `Aplicacao/BloquearIdentidade.php`: classe final, um método público, injeta a
  interface de `Dominio/`, valida regra de negócio (tamanho do motivo) e delega.
- `Dominio/RepositorioDeIdentidades.php`: **interface**, sem saber de Eloquent/SQL —
  só os métodos que o domínio precisa.
- `Infraestrutura/IdentidadesEmBanco.php`: implementação Eloquent da interface acima.
- **CONAHOM não usa `App\Models\User` do Laravel para autenticação** — tem uma
  abstração própria de identidade (`IdentidadeAutenticadaReal`,
  `IdentidadeAdministrativaReal`). Isso existe lá porque o CONAHOM precisa reconciliar
  identidade entre o sistema novo e um sistema irmão legado, com múltiplos tipos de
  vínculo (associado, administrativo) — um problema real que justifica a camada extra.

## 1.1. Princípio fixo: sem número mágico, sem primitiva solta representando conceito de domínio

O legado é cheio de números/strings mágicos sem significado próprio: `permissao` int
`-1/1/2/3/4`, `prioridade` string livre (`"urgente"` usado em código mas inexistente no
form), `status`/`solucao` comparados por igualdade de string espalhada em dezenas de
arquivos. **A V3 não reproduz esse padrão em nenhum lugar novo.** Regra fixa para todo
código escrito a partir daqui:

- Todo conceito de domínio com um conjunto fechado de valores vira `enum` do PHP (com
  ou sem backing type, conforme a necessidade real de persistência), nunca `int`/
  `string` solto comparado por `==`/`in_array` espalhado pelo código.
- Toda regra de precedência/comparação (ex.: "papel X pode mais que papel Y") fica
  **encapsulada em método do próprio enum/objeto de domínio**, nunca em comparação
  numérica no código que usa o valor (`$papel->value >= 3` é exatamente o tipo de coisa
  a não escrever).
- Quando o legado usa um número/string sem significado autoexplicativo, o número
  original só pode aparecer em **um único lugar**: a camada de migração (`MIG-V3`), como
  tabela de tradução explícita — nunca vazando para regra de negócio, view ou teste.

## 2. O que o RMA V3 adota do padrão, e o que não adota (com o porquê)

**Adota:**
- Monólito modular: `app/{Modulo}/{Dominio,Aplicacao,Infraestrutura}` por módulo de
  domínio real (não um módulo por tabela).
- `app/Compartilhado/` para o que várias módulos usam (ex.: enums/value objects sem
  dono único).
- Casos de uso nomeados por verbo em `Aplicacao/` para toda ação que carrega uma regra
  de negócio real (RN-01 a RN-21) — não para CRUD trivial.
- Convenção `...EmBanco` para implementação Eloquent de uma interface de `Dominio/`,
  **onde a interface se justificar** (ver item seguinte).

**Não adota, com justificativa explícita (evitar overengineering, pedido do usuário):**
- **Não** cria uma abstração de Identidade própria substituindo `App\Models\User` — o
  RMA V3 tem um único tipo de usuário, um schema de permissão simples (5 níveis,
  estável nos dois temas, ver `regras-negocio-rma-legado.md`), sem o problema de
  reconciliação multi-sistema que justificou a camada extra no CONAHOM. `Fase 1` usa
  `App\Models\User` padrão do Laravel, Eloquent direto.
- **Não** cria interface de repositório para `Parceiro` (cliente/fabricante/fornecedor/
  assistência técnica) na baseline — é CRUD com pouca regra de negócio própria; abstrair
  cedo demais não paga a complexidade. Reavaliar se `MIG-V3` precisar de uma camada
  anti-corrupção para deduplicação (razão concreta, não especulativa).
- **Repositório de domínio com interface própria SE JUSTIFICA no módulo `Rma`**: é o
  módulo que vai precisar ler o banco `rma_legacy` (schema bem diferente) durante a
  migração, então a fronteira `Dominio` (o que a aplicação precisa) vs. `Infraestrutura`
  (como isso é lido/gravado) tem valor real aqui, não é imitação por estética.

## 3. Módulos do RMA V3

| Módulo | Papel | Justificativa de existir como módulo próprio |
|---|---|---|
| `Identidade` | Usuário, autenticação, papel/permissão (5 níveis), tema preferido | Fronteira natural — nada de RMA depende de como login funciona, só de "usuário autenticado com papel X" |
| `Parceiros` | Cliente, Fabricante, Fornecedor, AssistenciaTecnica | Mesma forma, papéis diferentes (ver `EVO-DOM-001` do backlog — ideia de parceiro polimórfico fica registrada, não implementada agora) |
| `Rma` | O núcleo: entidade RMA, ciclo de vida (status/solução), as 10 regras de alerta, crédito, relatórios, auditoria de modificação | É o domínio real do produto — tudo que hoje vive em `bd` + `modificacao` + `relatorio` do legado |
| `Compartilhado` | Value objects sem dono único (ex.: enum de UF, formatação de data legado→novo) | Só cresce sob demanda — não povoar preventivamente |

**Não vira módulo próprio, avaliado e descartado:**
- "Créditos" — é um sub-fluxo do `Rma` (dois campos de estado dentro do mesmo
  agregado), não uma entidade independente. Fica em `Rma/Aplicacao`.
- "Relatórios" — são queries de leitura sobre `Rma`, não escrevem estado novo. Ficam em
  `Rma/Aplicacao` como casos de uso de consulta, sem `Dominio`/`Infraestrutura` próprios.
- "Temas" (V1/V2) — não é módulo de domínio, é decisão de **apresentação** (Blade +
  rotas), vive em `resources/views/temas/{v1,v2}/` e `app/Http/Controllers/`, não em
  `app/{Modulo}/`.

## 4. Tecnologia (inventário completo, consolidando decisões já tomadas)

| Camada | Escolha | Já confirmado nesta sessão |
|---|---|---|
| Linguagem/Framework | PHP 8.3, Laravel 13 | `08.24.1-gerenciador-de-rma` sobe em `:8095` |
| Banco | MySQL 8.4 (dev, via Sail) | `rma_v3`, container `rma-v3-mysql-1` |
| Ambiente local | Docker/Sail, `compose.yaml` com `name: rma-v3` | Rodando lado a lado com o Legacy (`:8094`) |
| Autenticação | `Auth`/guards nativos do Laravel, Breeze (scaffolding), `Hash` (bcrypt) | Decidido nesta sessão — nunca SHA1/sessão manual |
| Autorização | Policy/Gate do Laravel, mapeado do enum de 5 papéis | A implementar na Fase 1 |
| View | Blade nativo (`@extends`/`@include`/Components), nunca `include()` de PHP puro | Decidido nesta sessão |
| Assets | Vite + Sass + Bootstrap 5.3 (grid/base) + paleta própria por tema | Paleta já capturada em `inventario-visual-tema-{v1,v2}.md` |
| JS | Moderno por padrão; jQuery só se uma interação específica não tiver alternativa simples (nenhuma identificada até agora) | — |
| Testes | PHPUnit (unit/feature), Playwright (E2E dos fluxos críticos) | Suíte padrão já roda 2/2 |
| Migração V2→V3 | Comando Artisan oficial (`rma:migrate-legacy` ou nome melhor), módulo `Rma/Infraestrutura` lê `rma_legacy` via conexão secundária | A desenhar em `INV-RMA-06` |
| SDD | investigação → parecer → decisão → OpenSpec → tasks → implementação → teste → QA | Disciplina já em uso desde ARQ-00 |

## 5. Fases de implementação (ordem por dependência)

```
Fase 1 — Identidade         (fundação: nada funciona sem usuário autenticado)
Fase 2 — Parceiros          (Rma referencia parceiros; cadastro precisa existir antes)
Fase 3 — Rma núcleo         (entidade, criação, busca, detalhe — sem transição ainda)
Fase 4 — Ciclo de vida      (receber/encaminhar/concluir/arquivar/rollback)
Fase 5 — Alertas e regras   (as 10 regras + MARKVISION + threshold, sobre o núcleo pronto)
Fase 6 — Créditos e relatórios
Fase 7 — Auditoria          (log de autenticação já nasce na Fase 1; modificação de RMA aqui)
Fase 8 — Apresentação       (Tema V1 + Tema V2 fiéis, Blade, sobre tudo que já funciona)
Fase 9 — Migração V2→V3     (só depois do schema da V3 estar estável)
Fase 10 — QA de paridade    (funcional + visual + dados, contínuo mas fecha no final)
```

Cada fase = 1 change em `openspec/changes/` (ver catálogo em `PLANO-ATAQUE.md`), escrita
só quando a fase anterior estiver implementada (não escrever todas de uma vez —
decisão pode mudar com o que se aprende implementando).

**Nota (planejamento adiantado, 2026-08-24):** por pedido explícito nesta sessão, as
Fases 4 a 8 foram detalhadas arquivo-por-arquivo e ganharam OpenSpec completo
(`proposal.md`/`design.md`/`tasks.md`) **antes** de as fases anteriores estarem
implementadas — exceção deliberada à regra acima, não uma mudança de prática. Cada
OpenSpec dessas fases carrega decisões que podem precisar de revisão quando a fase
anterior de fato for implementada e algo aprendido contradisser uma suposição (ex.:
granularidade real de compartilhamento de views entre TEMA V1/V2 na Fase 8, ainda
`[DÚVIDA]` mesmo depois deste planejamento). Fases 9 e 10 ficaram só em esqueleto (§14,
§15) — dependem de decisões que só ficam estáveis depois da implementação real.

## 6. Fase 1 em detalhe — Identidade (arquivos exatos)

### Banco

- `database/migrations/2026_08_25_000000_add_identidade_fields_to_users_table.php`
  — adiciona `papel` (tinyint, domínio -1/1/2/3/4), `tema_preferido` (string, domínio
  `v1`/`v2`), `anotacao` (text, nullable — equivalente a `usuario.anotacao`, bloco de
  notas pessoal, `LEG-RMA-042`) à tabela `users` padrão do Laravel.
- `database/migrations/2026_08_25_000001_create_tentativas_de_acesso_table.php`
  — equivalente à tabela `log` do legado: `user_id` (nullable, FK), `email_informado`,
  `ip`, `user_agent`, `resultado` (enum: permitido/negado/bloqueado), `criado_em`.

### Domínio (`app/Identidade/Dominio/`)

- `Papel.php` — **tipo de domínio, não espelho do inteiro legado.** O legado usava
  `-1/1/2/3/4` (número mágico sem significado próprio, ordem só entendível lendo o
  código-fonte espalhado). A V3 não reproduz isso: `enum Papel { case Bloqueado; case
  Leitura; case Operador; case Supervisor; case SuperAdministrador; }` **sem backing
  type** (nenhum caller compara por número). Toda pergunta de negócio vira método
  nomeado no próprio enum, com a ordem de precedência encapsulada dentro dele, nunca
  exposta:
  ```php
  enum Papel
  {
      case Bloqueado;
      case Leitura;
      case Operador;
      case Supervisor;
      case SuperAdministrador;

      public function podeAutenticar(): bool
      {
          return $this !== self::Bloqueado;
      }

      public function podeGravar(): bool
      {
          return match ($this) {
              self::Bloqueado, self::Leitura => false,
              default => true,
          };
      }

      public function podeGerenciarUsuarios(): bool
      {
          return match ($this) {
              self::Supervisor, self::SuperAdministrador => true,
              default => false,
          };
      }

      public function ocultoDaListagemDeUsuarios(): bool
      {
          return $this === self::SuperAdministrador;
      }
  }
  ```
  Persistência: coluna `papel` como `string` (nome do case via `->name`, cast Eloquent
  nativo `papel => Papel::class` — o Laravel serializa/desserializa enum puro pelo nome
  automaticamente, sem precisar de `int`). O número do legado (-1/1/2/3/4) só existe
  dentro do **migrador** (`MIG-V3`), como tabela de tradução isolada num único lugar —
  nunca vaza para o resto da aplicação.
- `TemaPreferido.php` — `enum TemaPreferido: string { V1 = 'v1'; V2 = 'v2'; }` com
  `alternar(): self`.
- `TentativaDeAcessoRegistrada.php` — value object simples (email, ip, resultado,
  momento) — sem interface de repositório própria (é só um INSERT de auditoria, não
  justifica abstração).

### Aplicação (`app/Identidade/Aplicacao/`)

- `AutenticarUsuario.php` — recebe credenciais, verifica `Papel::Bloqueado` (nega antes
  de checar senha, como o legado fazia — RN confirmada), delega ao `Auth` do Laravel,
  registra `TentativaDeAcessoRegistrada`, devolve qual `TemaPreferido` deve receber o
  redirect (equivalente a `app($email)` do legado).
- `AlternarTemaPreferido.php` — equivalente a `trocarapp.php`: lê o tema atual do
  usuário autenticado, grava o oposto.

### Infraestrutura / Laravel padrão

- `app/Models/User.php` — Eloquent padrão, cast de `papel` para `Papel` (enum nativo do
  PHP, suportado nativamente por `casts()` do Eloquent — não precisa de `EmBanco`
  próprio para isso).
- `app/Models/TentativaDeAcesso.php` — Eloquent simples.
- `app/Policies/UserPolicy.php` — `gerenciar(User $ator): bool` (papel ≥ Supervisor),
  usado tanto para a tela de usuários quanto para o filtro de auditoria (RN confirmada:
  supervisor não vê log do super-admin — avaliar se essa regra específica é
  `[COMPORTAMENTO-INTENCIONAL]` a preservar ou dívida técnica a não copiar; registrar
  decisão no `design.md` da change, não aqui).
- `app/Http/Controllers/Identidade/SessaoController.php` — `create()`/`store()`/
  `destroy()` (login/logout), usa `AutenticarUsuario`.
- `app/Http/Controllers/Identidade/TemaPreferidoController.php` — `update()`, usa
  `AlternarTemaPreferido`.
- `routes/web.php` — rotas de sessão (fora de qualquer prefixo de tema, já que login é
  território comum aos dois, igual ao legado).
- `resources/views/auth/login.blade.php` — **sem fidelidade visual ainda** (isso é
  Fase 8); layout mínimo funcional do Laravel/Breeze nesta fase.

### Testes

- `tests/Feature/Identidade/AutenticacaoTest.php` — login válido, senha errada,
  usuário bloqueado (nega antes de checar senha), rate limiting.
- `tests/Feature/Identidade/PermissaoTest.php` — os 5 papéis, guest vs. autenticado,
  `Policy::gerenciar`.
- `tests/Feature/Identidade/AlternarTemaTest.php` — alterna e persiste; login
  subsequente respeita a preferência salva (equivalente ao smoke test já feito no
  Legacy, ver `docs/desenvolvimento/ambiente-v2-v3.md`).

### Gestão de usuários (ajuste da revisão — ver `docs/arquitetura/revisao-fases-1-2-3.md`)

Achado da revisão de Fase 1-3: `LEG-RMA-002` (autocadastro com convite), `LEG-RMA-003`
(resetar senha de outro usuário), `LEG-RMA-004` (trocar a própria senha) e `LEG-RMA-005`
(gerenciar usuários/permissões) não tinham fase dona em nenhum dos 10 itens de `§5` —
ficavam mencionados como "fora do escopo, decisão futura" sem que nenhuma fase seguinte
os reivindicasse. Como o schema (`User`, `Papel`), a `UserPolicy` e o conceito de
"usuário autenticado com papel X" já nascem nesta fase, gestão de usuários é a mesma
fronteira de domínio — não uma fase nova. Passam a fazer parte da Fase 1:

- `app/Identidade/Aplicacao/TrocarPropriaSenha.php` — **usa TEMA V1 como especificação**
  (RN-21: SQL único e válido `UPDATE users SET password = ? WHERE id = ?`, via
  `Hash::make`), não o SQL quebrado de TEMA V2. Exige senha atual correta antes de
  trocar (mesmo fluxo de autoatendimento de `14.6.1/post/mudar_senha.php`).
- `app/Identidade/Aplicacao/ResetarSenhaDeUsuario.php` — equivalente a
  `subp/resetar_senha.php` (a versão correta em ambos os temas — TEMA V1 usa o mesmo
  SQL correto por outro caminho); exige `Papel::podeGerenciarUsuarios()` do ator.
- `app/Identidade/Aplicacao/AtualizarAnotacaoPessoal.php` — equivalente a
  `post/salvarnotas.php` (`LEG-RMA-042`, confirmado em TEMA V1; **[DÚVIDA]** equivalente
  em TEMA V2 — tratar como mesmo comportamento, único bloco de notas por usuário,
  já que a tabela `usuario` é compartilhada pelos dois temas no legado).
- `app/Http/Controllers/Identidade/UsuarioController.php` — `index` (lista, aplica
  `Papel::ocultoDaListagemDeUsuarios()` — método que já existia no enum da Fase 1, mas
  não tinha nenhum caller planejado até este ajuste), `edit`/`update` (troca de papel,
  usa `UserPolicy::gerenciar`), mais as duas ações de senha acima.
- `app/Http/Controllers/Identidade/AnotacaoPessoalController.php` — `update` (usa
  `AtualizarAnotacaoPessoal`).
- `app/Policies/UserPolicy.php` — a descrição original ("papel ≥ Supervisor") era só
  prosa; a implementação real **chama o método nomeado do enum**
  (`$ator->papel->podeGerenciarUsuarios()`), nunca compara por ordinal — reforçado aqui
  para não deixar ambiguidade de leitura.
- `resources/views/identidade/usuarios/index.blade.php`,
  `resources/views/identidade/perfil/senha.blade.php` — sem fidelidade visual ainda.
- `tests/Feature/Identidade/TrocarPropriaSenhaTest.php` — senha correta troca; senha
  errada nega; **prova de regressão corrigida**: o teste teria falhado se a V3
  reproduzisse o SQL quebrado de TEMA V2.
- `tests/Feature/Identidade/ResetarSenhaDeUsuarioTest.php`,
  `GerenciarUsuariosTest.php` (lista oculta SuperAdministrador do papel Supervisor),
  `AnotacaoPessoalTest.php`.

**Pendência registrada, não decidida aqui (não inventar):** `LEG-RMA-002` (autocadastro
público com convite, hoje um segredo hardcoded comparado em `inc/signup.php`, confirmado
só em TEMA V1, `[DÚVIDA]` em TEMA V2) — decidir com o usuário se a V3 reconstrói
autocadastro público (com segredo em `.env`, não hardcoded) ou se usuário passa a ser
sempre criado por um Supervisor/SuperAdministrador via `UsuarioController`. Nenhuma das
duas opções tem evidência suficiente de qual é "o comportamento pretendido" — é decisão
de produto, não de arqueologia.

### OpenSpec desta fase

`openspec/changes/autenticacao-usuarios/{proposal.md,design.md,tasks.md}` — escrita
nesta mesma sessão, ver arquivos correspondentes.

## 7. Fase 2 em detalhe — Parceiros

### Decisão de modelagem (resolve a pendência da Parte 2 do checklist)

**FK real desde a baseline**, não relação por string. Isso é uma correção estrutural
que **não muda comportamento percebido** (mesma tela, mesmo CRUD, mesmo resultado para
o usuário) — só corrige um problema real e documentado do legado (auto-criação de
`cliente` sem deduplicação real, ver `modelo-dominio-rma-legado.md`). Registro
ANTES/PROBLEMA/DEPOIS/MIGRAÇÃO/COMPATIBILIDADE/TESTE (exigido pela regra de evolução do
banco):

- **ANTES:** `bd.cliente`/`bd.fabricante`/`bd.fornecedor`/`bd.destinatario` = nome em
  texto livre, sem FK.
- **PROBLEMA:** duplicidade por variação de digitação (`adicionar_cli()` só compara
  `WHERE nome = ?` exato); RMA "órfão" se o nome não bater exatamente numa listagem.
- **DEPOIS:** `Rma` referencia `Cliente`/`Fabricante`/`Fornecedor` por `foreignId` real;
  `destinatario` (que no legado é polimórfico — pode ser assistência, fornecedor ou
  fabricante) vira relação polimórfica nativa do Eloquent (`morphTo`).
- **MIGRAÇÃO:** o migrador (`MIG-V3`, Fase 9) resolve nomes existentes por
  correspondência aproximada, cria os registros que faltarem, relaciona por ID, reporta
  ambiguidade — não é problema desta fase, só precisa que o schema já esteja pronto pra
  receber isso.
- **COMPATIBILIDADE:** nenhuma — é mudança interna, invisível ao usuário.
- **TESTE:** a decidir na OpenSpec da Fase 9 (contagem de parceiros após migração bate
  com estimativa do legado).

**Não unificar `Cliente`/`Fabricante`/`Fornecedor`/`AssistenciaTecnica` num único
`Parceiro` polimórfico na baseline** — é exatamente a tentativa que o próprio legado
abandonou pela metade (tabela órfã `assistencias`, ver `regras-negocio-rma-legado.md`
RN-19). A ideia fica registrada como `EVO-DOM-001` no backlog evolutivo, não implementada
agora — os 4 tipos nascem como 4 tabelas/models separados, exatamente como
`LEG-RMA-030` a `033` já os cataloga.

### Arquitetura do módulo (mais simples que `Identidade`, com justificativa)

Sem `Dominio/`/`Infraestrutura/` — é CRUD com uma única regra de negócio real
(deduplicação na auto-criação de cliente), que não justifica a fronteira de repositório
completa. Eloquent direto em `app/Models/`, com **um** caso de uso em `Aplicacao/` só
para a regra que existe de fato.

### Arquivos

- `database/migrations/2026_08_26_000000_create_clientes_table.php`
- `database/migrations/2026_08_26_000001_create_fabricantes_table.php`
- `database/migrations/2026_08_26_000002_create_fornecedores_table.php`
- `database/migrations/2026_08_26_000003_create_assistencias_tecnicas_table.php`
- `app/Models/Cliente.php`, `Fabricante.php`, `Fornecedor.php`, `AssistenciaTecnica.php`
  — Eloquent puro; os 3 últimos compartilham o mesmo shape (endereço, contato,
  `politicadegarantia` — texto livre, sem parsing, igual ao legado) via um `trait`
  `app/Parceiros/Concerns/TemEnderecoEContato.php` (evita repetir os mesmos `$fillable`/
  `casts()` 3 vezes; `Cliente` não usa o trait porque não tem `politicadegarantia`/
  `email2`/`www` — schema genuinely diferente, não forçar uniformidade que o legado não
  tem)
- `app/Parceiros/Aplicacao/EncontrarOuCriarCliente.php` — único caso de uso real: busca
  por nome normalizado (trim + case-insensitive — correção sobre o bug do legado),
  cria se não existir, nunca duplica
- `app/Compartilhado/Uf.php` — **ajuste da revisão** (ver
  `docs/arquitetura/revisao-fases-1-2-3.md`): o próprio `§3` deste documento já prometia
  um "enum de UF" em `Compartilhado`, mas o schema desta fase usava `uf string(2)
  nullable` solto — exatamente o tipo de primitiva substituível por enum que o princípio
  1.1 proíbe (conjunto fechado de 27 valores). `enum Uf: string { case SP = 'SP'; case
  RJ = 'RJ'; ... }` (as 27 UFs do Brasil), usado via cast Eloquent nativo nos 4 models
  desta fase
- `app/Policies/ClientePolicy.php`, `FabricantePolicy.php`, `FornecedorPolicy.php`,
  `AssistenciaTecnicaPolicy.php` — todas delegam a `$user->papel->podeGravar()`
  (`Identidade\Dominio\Papel`, já existe da Fase 1); repetição de 1 linha em 4 arquivos
  é aceitável (não vale criar abstração para isso)
- `app/Http/Controllers/Parceiros/ClienteController.php`,
  `FabricanteController.php`, `FornecedorController.php`,
  `AssistenciaTecnicaController.php` — `index/create/store/edit/update/destroy`
  padrão de resource controller do Laravel
- `resources/views/parceiros/_form.blade.php` — **parcial compartilhada**
  parametrizada (não fidelidade visual ainda — isso é Fase 8, então não há razão para
  4 formulários quase idênticos agora)
- `resources/views/parceiros/index.blade.php` — genérica, recebe título/rota/coleção
- `routes/web.php` — grupo `parceiros.*` (resource routes ×4)
- `database/factories/ClienteFactory.php` (+ Fabricante/Fornecedor/AssistenciaTecnica)
- `tests/Feature/Parceiros/ClienteCrudTest.php` (+ 3 análogos)
- `tests/Feature/Parceiros/EncontrarOuCriarClienteTest.php` — caso feliz, caso de
  duplicata evitada, caso de variação de digitação/espaço

## 8. Fase 3 em detalhe — Rma núcleo

### Decisão de modelagem — aqui SIM se justifica a fronteira completa

Ao contrário de `Parceiros`, o módulo `Rma` **usa a fronteira completa do padrão
CONAHOM**: `Dominio/` com objeto de domínio **puro** (não Eloquent — achado confirmado
nesta sessão lendo `app/Filiacao/Dominio/SolicitacaoDeFiliacao.php` do CONAHOM: não
estende `Model`) + interface de repositório; `Infraestrutura/` implementa a interface.
Diferença deliberada do CONAHOM: a implementação usa **Eloquent**, não `DB::table()` cru
— o rigor que vale a pena replicar aqui é a **fronteira** (a interface, que isola quem
usa `Rma` de como ele é persistido), não o estilo de acesso a dado dentro da
implementação. `DB::table()` puro no CONAHOM se justifica pela escala e pelas consultas
muito específicas de reconciliação; o RMA não tem esse mesmo motivo.

**Por que a fronteira se paga aqui e não em `Parceiros`:** a Fase 9 (migração) precisa
ler o banco `rma_legacy` (schema `bd`, ~56 colunas, sem FK, valores legados fora do
domínio moderno) e gravar como `Rma` da V3. Se o resto da aplicação (casos de uso,
Controllers, regras de alerta da Fase 5) depender da interface de `Dominio/`, o
migrador é só mais um `Infraestrutura/` que sabe ler o formato velho — sem essa
fronteira, o código de migração vazaria pra dentro da aplicação toda.

### Arquivos

- `database/migrations/2026_08_27_000000_create_rmas_table.php` — schema novo,
  **não** espelha as ~56 colunas de `bd` 1:1 (ver mapa campo-a-campo em `INV-RMA-06`,
  Fase 9); nesta fase só os campos que o núcleo (criar/buscar/ver/editar) precisa:
  identificador, `descricao`, `fabricante_id`, `fornecedor_id`, `modelo`, `sn`, `os`,
  `origem`, `empresa`, `cliente_id`, `defeito`, `observacao`, timestamps. **Ajuste da
  revisão** (`docs/arquitetura/revisao-fases-1-2-3.md`): `fornecedor_id` estava ausente
  do desenho original — `bd.fornecedor` é campo de "Partes" do mesmo grupo de
  `fabricante`/`cliente` em `modelo-dominio-rma-legado.md`, preenchido na mesma tela de
  criação (`LEG-RMA-007`); não há motivo para incluir `fabricante_id`/`cliente_id` como
  FK real e deixar `fornecedor_id` como string solta. Campos de status/solução/NF/
  crédito entram nas Fases 4/5/6 (migration incremental, não monolítica — evita uma
  tabela gigante nascendo de uma vez sem uso real ainda)
- [DECISÃO A REGISTRAR NO `design.md` DA OPENSPEC, NÃO AQUI] identificador: `id`
  incremental do Eloquent é suficiente para a baseline — não há caso de uso de
  identificador público exposto externamente ainda (RMA não tem API pública, portal do
  cliente não existe). UUID/ULID fica registrado como `EVO` se/quando isso mudar.
- `app/Rma/Dominio/Rma.php` — objeto de domínio puro (não Eloquent), representa o
  agregado com os campos desta fase
- `app/Rma/Dominio/RepositorioDeRmas.php` — interface: `criar(...)`,
  `buscarPorId(int)`, `buscar(CriterioDeBusca)` (ver próximo item)
- `app/Rma/Dominio/CriterioDeBusca.php` — value object da busca/localização
  (`LEG-RMA-008`) — em vez de replicar os `campo=TUDO/NF/SNPNSNID` do legado como string
  mágica, vira um objeto com métodos nomeados (`porTexto(string)`,
  `porNotaFiscal(string)`, `porSerial(string)`) — mesmo princípio de "sem número/string
  mágica" já fixado para `Papel`
- `app/Rma/Aplicacao/CriarRma.php` — caso de uso: recebe dados do formulário, usa
  `EncontrarOuCriarCliente` (módulo `Parceiros`) se o cliente for novo, grava via
  `RepositorioDeRmas`; aplica as normalizações de gravação abaixo
- `app/Rma/Aplicacao/EditarRma.php` — **ajuste da revisão**
  (`docs/arquitetura/revisao-fases-1-2-3.md`): `LEG-RMA-010` ("editar/salvar RMA") não
  tinha fase dona — não é uma transição de ciclo de vida (Fase 4), é a mesma família de
  `CriarRma`/`VerDetalheDoRma` (escrever/ler o núcleo do agregado antes de qualquer
  status/solução existir); aplica as mesmas normalizações de `CriarRma`
- `app/Rma/Aplicacao/BuscarRmas.php` — caso de uso de leitura, usa `CriterioDeBusca`
- `app/Rma/Aplicacao/VerDetalheDoRma.php` — caso de uso de leitura simples

### Normalizações de gravação — RN-13/RN-14/RN-17 (ajuste da revisão)

`CriarRma` e `EditarRma` compartilham (via método privado ou objeto de domínio) três
regras confirmadas **em ambos os temas**, que no legado disparam tanto na criação quanto
na edição (`pp/novo_rma.php`/`pp/salvar_rma.php`, `post/novo.php`/
`post/processa_detalhes.php`) — nenhuma das Fases 4-8 as reivindicava, e adiar para
Fase 4/5 (que dependem de `status`/`solucao`, ainda não existentes) faria `CriarRma`
gravar dado não normalizado, quebrando fidelidade desde o primeiro RMA criado:

- **RN-13 (`LEG-RMA-046`, HGST→Hitachi):** se `fabricante`/`destinatario` informado for
  "HGST", grava como "Hitachi" — comparação simples, sem enum (é substituição de string
  de exibição, não um conjunto fechado de domínio).
- **RN-14 (`LEG-RMA-046`, cascata de `origem`):** sequência de normalização
  confirmada idêntica nos dois temas — implementada como método puro em
  `Dominio/Rma` (não `str_replace` solto como o legado, que tinha bug de variável não
  inicializada); domínio fechado de saída (`Unknown`/`Cliente`/`Loja`/`Leilão`/valor
  original) — candidato a enum `Origem` quando a Fase 4/5 (que também leem `origem` nas
  regras de alerta) fixarem o domínio completo; nesta fase o resultado da normalização é
  gravado como string, sem introduzir o enum ainda (evita fixar o enum com informação
  parcial).
- **RN-17 (`marcarestoque`, dívida técnica, não bug):** o legado **calcula** um valor por
  `origem` e imediatamente **descarta**, usando só o checkbox do formulário — achado já
  reclassificado em `regras-negocio-rma-legado.md` como dívida técnica, não regressão. A
  V3 **não reproduz o cálculo morto**: `marcarestoque` é gravado só a partir do valor
  informado no formulário, produzindo exatamente o mesmo resultado observável, sem o
  código morto. Campo `marcarestoque` só é adicionado ao schema quando a Fase 5 (que o
  usa nas regras de alerta) precisar dele — nesta fase, se o formulário mínimo (sem
  fidelidade visual) já expuser o checkbox, o valor é persistido, mas a coluna nasce
  junto com a Fase 5, não antes.

RN-15 (`snretorno` auto-preenchido, `LEG-RMA-047`) **fica fora desta fase** — depende de
`solucao`, que só existe a partir da Fase 4; entra lá.
- `app/Rma/Infraestrutura/RmasEmBanco.php` — implementa `RepositorioDeRmas` via
  Eloquent (usa um `app/Models/Rma.php` interno, não exposto fora da infra)
- `app/Models/Rma.php` — Eloquent, **uso interno da Infraestrutura só** — o resto da
  aplicação nunca importa este model diretamente, só o objeto de `Dominio/Rma.php`
- `app/Http/Controllers/Rma/RmaController.php` — `index` (busca), `create`/`store`
  (novo), `show` (detalhe) — usa os casos de uso de `Aplicacao/`, nunca o Eloquent
  model direto
- `resources/views/rma/index.blade.php`, `create.blade.php`, `show.blade.php` — sem
  fidelidade visual ainda
- `routes/web.php` — grupo `rma.*`
- `database/factories/RmaFactory.php`
- `tests/Feature/Rma/CriarRmaTest.php`, `BuscarRmasTest.php`, `VerDetalheDoRmaTest.php`
- `tests/Unit/Rma/CriterioDeBuscaTest.php`

### O que NÃO entra na Fase 3 (fica pras fases seguintes, mesmo já estando no schema de `bd`)

`status`, `solucao`, `prioridade`, campos de NF, crédito, destinatário, `snretorno`
(RN-15/`LEG-RMA-047`, depende de `solucao`), `marcarestoque` como coluna (RN-17, entra
com a Fase 5) — tudo isso é Fase 4/5/6. Criar a coluna antes de ter a regra que a usa é
exatamente o tipo de "planejar no escuro" que este documento evita.

## 9. Fase 4 em detalhe — Ciclo de vida

### Decisão de modelagem 1 — `status` como enum sem número mágico, com data própria por transição

O legado grava `status` como string comparada por igualdade em dezenas de arquivos, e
**cada transição tem sua própria coluna de data** (`bd.entrada`, `bd.recebido`,
`bd.encaminhado`, `bd.concluido` — confirmado em `modelo-dominio-rma-legado.md` §Status)
— não é só um `status` + `updated_at` genérico, porque as 10 regras de alerta (Fase 5)
calculam `Diferenca_de_dias()` contra a data de uma transição específica (ex.: RN-01 usa
`recebido`, RN-07 usa `encaminhado`). Um `updated_at` único seria sobrescrito pela
transição seguinte e perderia a informação. A V3 preserva essa forma (datas por
transição), só troca a representação do estado em si:

```php
enum Status
{
    case Entrada;
    case Recebido;
    case Encaminhado;
    case Concluido;
    case Arquivado;
    // Sem case Retornou — LEG-RMA-016, código morto em ambos os temas
    // (regra existe no roteamento, nenhuma transição jamais grava esse valor),
    // paridade-v2-v3.md já registra como NÃO RECONSTRUIR.

    public function podeReceber(): bool { return $this === self::Entrada; }
    public function podeEncaminhar(): bool { return $this === self::Recebido; }
    public function podeConcluir(): bool { return $this === self::Encaminhado; }

    /** [INFERIDO] — legado não documenta explicitamente de quais status se
     * pode arquivar; "pausa reabrível" (parecer §6) sugere pausar algo em
     * andamento, não algo já concluído. Sem evidência de exceção — registrar
     * dúvida se aparecer caso real de arquivar um Concluido. */
    public function podeArquivar(): bool
    {
        return match ($this) {
            self::Entrada, self::Recebido, self::Encaminhado => true,
            default => false,
        };
    }

    /** LEG-RMA-015: "retornar para entrada" — só de Recebido/Encaminhado. */
    public function podeReverterParaEntrada(): bool
    {
        return match ($this) {
            self::Recebido, self::Encaminhado => true,
            default => false,
        };
    }
}
```

`entrada` (data de criação) **não vira coluna nova** — é `created_at`, já existente
desde a Fase 3 (o RMA nasce em `status=Entrada`, não há transição própria para isso).

### Decisão de modelagem 2 — `Arquivar`: TEMA V2 é a especificação (achado confirmado nesta revisão)

`LEG-RMA-014` já registrava TEMA V1 como **quebrado** e TEMA V2 como **funcional** — a
revisão desta fase confirmou o porquê, lendo o código-fonte diretamente:
`14.6.1/post/arquivar.php` instancia `new controle()` e chama `->arquivar($conexao,
$numero)`; `14.6.1/banco.oo.php` só declara **uma** classe (`autenticacao`, linha 24) —
`controle` **não existe em lugar nenhum do arquivo**, então a chamada é `Fatal Error:
Uncaught Error: Class "controle" not found` sempre que acionada, sem exceção condicional.
Não é ambiguidade de leitura — é erro fatal incondicional. **Decisão: `ArquivarRma` usa
TEMA V2 (`15.8.1/banco.php::arquivar()`) como especificação de comportamento**, o mesmo
padrão já estabelecido para RN-21 (só que invertido — lá TEMA V1 era a referência).

### Decisão de modelagem 3 — `solucao` como enum backed string (lista real, não inventada)

Os documentos anteriores citavam "17 valores" sem listá-los todos. Esta revisão leu
diretamente o `<select name="solucao">` de `15.8.1/page/rma.php:578-595` (arquivo
ISO-8859-1, decodificado para conferir) e confirmou **16 valores nomeados** (a differença
para "17" nos documentos anteriores é, com razoável confiança, a opção vazia inicial
`<option value="" selected>` sendo contada como um 17º "estado" nulo — não um valor de
negócio adicional; se surgir evidência de um 17º valor nomeado real, corrigir aqui, não
inventar agora):

```php
enum Solucao: string
{
    case Reparo = 'REPARO';
    case TrocaDoProduto = 'TROCA DO PRODUTO';
    case TrocaDePecaInterna = 'TROCA DE PECA INTERNA';
    case PendenteCredito = 'PENDENTE CREDITO';
    case GeradoCredito = 'GERADO CREDITO';
    case DevolucaoDoProduto = 'DEVOLUCAO DO PRODUTO';
    case ReembolsoDoDinheiro = 'REEMBOLSO DO DINHEIRO';
    case OrcamentoPago = 'ORCAMENTO PAGO';
    case OrcamentoPendente = 'ORCAMENTO PENDENTE';
    case OrcamentoNegado = 'ORCAMENTO NEGADO';
    case ReparoPeloRma = 'REPARO PELO RMA';
    case CasoSolucionado = 'CASO SOLUCIONADO';
    case TestadoTudoOk = 'TESTADO TUDO OK';
    case Procon = 'PROCON';
    case DescritoNaObservacao = 'DESCRITO NA OBSERVACAO';
    case SemGarantia = 'SEM GARANTIA';

    /** RN-15 (LEG-RMA-047): classe "reparo" — o aparelho que volta é o mesmo,
     * snretorno é auto-preenchido com o sn original. */
    public function implicaMesmoAparelhoDeRetorno(): bool
    {
        return match ($this) {
            self::TrocaDePecaInterna, self::Reparo, self::OrcamentoPago,
            self::OrcamentoNegado, self::ReparoPeloRma, self::TestadoTudoOk => true,
            default => false,
        };
    }
}
```

Backed `string` pelo mesmo motivo de `TemaPreferido` (Fase 1): os valores precisam
aparecer literalmente em filtro/URL (TEMA V1 tem filtro rápido por `solucao` no menu,
`inventario-visual-tema-v1.md`), sem ordem/precedência a esconder.

### Arquivos

- `database/migrations/2026_08_28_000000_add_ciclo_de_vida_fields_to_rmas_table.php`
  — adiciona `status` (string, cast `Status`), `recebido_em`, `encaminhado_em`,
  `concluido_em`, `arquivado_em` (datetime, nullable — uma coluna por transição, não um
  `updated_at` genérico, ver decisão 1), `protocolo` (string, nullable — pertence ao
  ciclo de vida: aberto entre `Recebido` e `Encaminhado`, RN-04 só lê o que esta fase
  grava), `solucao` (string, cast `Solucao`, nullable), `snretorno` (string, nullable),
  `destinatario_type`/`destinatario_id` (colunas de relação polimórfica Eloquent —
  substitui a cascata de resolução por nome do legado `assistencia_tecnica →
  fornecedor → fabricante`; FK real desde a baseline, mesmo princípio já aplicado nas
  Fases 2/3)
- `app/Rma/Dominio/Status.php` — enum (ver decisão 1)
- `app/Rma/Dominio/Solucao.php` — enum (ver decisão 3)
- `app/Rma/Dominio/Rma.php` — **estendido** (não recriado): ganha `status`,
  `recebidoEm`, `encaminhadoEm`, `concluidoEm`, `arquivadoEm`, `protocolo`, `solucao`,
  `snretorno`, `destinatario` (propriedades readonly novas) + método
  `comSnretornoAutoPreenchido(): self` (aplica RN-15 quando `solucao` pertence à classe
  `implicaMesmoAparelhoDeRetorno()` e `snretorno` está vazio — chamado por
  `RegistrarSolucao` e por `ConcluirRma`)
- `app/Rma/Aplicacao/ReceberRma.php` — `LEG-RMA-011`; exige `Papel::podeGravar()`,
  `Status::podeReceber()`, grava `recebido_em = now()`
- `app/Rma/Aplicacao/EncaminharRma.php` — `LEG-RMA-012`; exige `destinatario`
  preenchido (regra confirmada — validação que no legado é só JS vira validação de
  domínio real aqui), grava `encaminhado_em = now()`
- `app/Rma/Aplicacao/ConcluirRma.php` — `LEG-RMA-013`; exige `solucao` preenchida,
  grava `concluido_em = now()`, aplica `comSnretornoAutoPreenchido()`, dispara evento
  `RmaConcluido` (Fase 7 assina para notificação por e-mail — `ezequiel()` do legado)
- `app/Rma/Aplicacao/ArquivarRma.php` — `LEG-RMA-014`; **TEMA V2 como especificação**
  (decisão 2); exige `Papel::podeGerenciarUsuarios()` (permissão ≥3, `[INFERIDO]`,
  mesma incerteza já registrada em `inventario-funcional-rma-v2.md` — não resolvida
  nesta revisão por falta de evidência adicional, mantida como inferência documentada,
  não promovida a certeza)
- `app/Rma/Aplicacao/ReverterRmaParaEntrada.php` — `LEG-RMA-015`; permitido se
  `encaminhado_em` é hoje **ou** `Papel::podeReverterAlemDoMesmoDia()` (novo método do
  enum `Papel`, Fase 1 — só `SuperAdministrador`); reseta `status = Entrada`,
  `recebido_em`/`encaminhado_em` para `null`
- `app/Rma/Aplicacao/RegistrarSolucao.php` — `LEG-RMA-017`; atualiza `solucao`
  independente de transição de status (o legado permite editar `solucao` a qualquer
  momento via `salvar_rma.php`, não só ao concluir), aplica
  `comSnretornoAutoPreenchido()`
- `app/Identidade/Dominio/Papel.php` — **estendido**: novo método
  `podeReverterAlemDoMesmoDia(): bool` (true só para `SuperAdministrador`) — mesmo
  arquivo da Fase 1, adicionado aqui porque é a primeira regra que precisa dele
- `app/Http/Controllers/Rma/CicloDeVidaController.php` — uma ação por verbo
  (`receber`/`encaminhar`/`concluir`/`arquivar`/`reverter`), delega aos casos de uso
  acima; não reaproveita `RmaController` da Fase 3 (que é só CRUD do núcleo) —
  transições são uma responsabilidade HTTP diferente, mesmo agregado
- `resources/views/rma/_acoes_de_transicao.blade.php` — parcial mínima (botões de
  ação), sem fidelidade visual ainda
- `tests/Feature/Rma/ReceberRmaTest.php`, `EncaminharRmaTest.php` (com/sem
  destinatário), `ConcluirRmaTest.php` (com/sem solução, snretorno auto-preenchido nos
  6 casos de `implicaMesmoAparelhoDeRetorno()`, em branco nos demais),
  `ArquivarRmaTest.php` (prova de que segue TEMA V2, não TEMA V1 — teste que falharia
  se a V3 replicasse o `Fatal Error`), `ReverterRmaParaEntradaTest.php` (mesmo dia
  permite, dia seguinte nega exceto `SuperAdministrador`), `RegistrarSolucaoTest.php`
- `tests/Unit/Rma/StatusTest.php`, `SolucaoTest.php` (`implicaMesmoAparelhoDeRetorno()`
  nos 16 valores)

### O que NÃO entra na Fase 4

As 10 regras de alerta e a classificação visual (Fase 5, usam os campos aqui criados
mas são consultas de leitura, não transições); NF/crédito/`lancadoretorno` (Fase 5/6,
nenhuma transição desta fase os lê); e-mail de conclusão de fato enviado (Fase 7 — aqui
só o evento de domínio é disparado); `marcarestoque`/`prioridade` (Fase 5, primeira
regra que de fato os usa).

### OpenSpec desta fase

`openspec/changes/rma-ciclo-de-vida/{proposal.md,design.md,tasks.md}`.

## 10. Fase 5 em detalhe — Alertas e regras

### Decisão de modelagem — onde mora o cálculo de data (a pergunta central desta fase)

Achado do parecer (`docs/pareceres/2026-08-24-parecer-arqueologia-rma.md` §13, e
`regras-negocio-rma-legado.md` RN-01 a RN-10): o legado traz o `SELECT` **sem** filtro
de data (`status='recebido'`, por exemplo) e filtra por `Diferenca_de_dias()` **linha a
linha em PHP**, na camada de apresentação — o mesmo bug (`num_rows` mentiroso, contagem
do `SELECT` bruto exibida antes do filtro PHP reduzir a lista) se repete em 6 das 10
regras porque a lógica de filtro está espalhada em cada view, não centralizada.

**Decisão: o cálculo de data mora inteiramente no SQL (query builder do Eloquent),
nunca em PHP pós-`SELECT`.** Cada uma das 10 regras vira um **Scope** nomeado (ou
método de query) em `app/Rma/Infraestrutura/RmasEmBanco.php`/`app/Models/Rma.php`, que
filtra por data via `whereDate`/`whereRaw(DATEDIFF(...))` diretamente no banco — o
`SELECT` já devolve só as linhas corretas, então não existe a classe de bug do legado
(contagem mentirosa antes do filtro) por construção, não por disciplina de código. Cada
regra é exposta como um caso de uso de leitura em `Aplicacao/`
(`app/Rma/Aplicacao/Alertas/`), nomeado pelo verbo/substantivo da regra, nunca uma
query solta no Controller.

**Por que não um "serviço de domínio" genérico único:** um único `AlertaService` com 10
métodos privados reproduziria a mesma bagunça do `metodo.php` do legado (10 funções
soltas no mesmo arquivo, sem nome de negócio isolável, sem teste individual). Cada
regra vira sua própria classe pequena — mais arquivos, mas cada um testável e nomeável
por si, coerente com o padrão já usado em `Aplicacao/` desde a Fase 1.

### Enums novos desta fase

- `app/Rma/Dominio/Origem.php` — enum backed string, domínio confirmado em
  `inventario-banco-rma-v2.md`: `Unknown`, `Loja`, `Casa`, `Cliente`, `Licitação`,
  `Leilão`, `Mercado Livre`, `Credito`, `AC`, `Rolo` (nem todos selecionáveis no
  formulário original — preservados porque já existem em dados reais, conferir na
  migração/Fase 9 se algum nunca ocorre e pode ser removido do enum então, não agora).
  O método de normalização RN-14 (Fase 3) passa a devolver este enum em vez de string
  solta, agora que o domínio completo está fixado (era `string` "provisória" na Fase 3
  exatamente para não fixar o enum com informação parcial, ver `INV-RMA-05` §8).
- `app/Rma/Dominio/Prioridade.php` — enum sem backing (mesmo padrão de `Papel`):
  `Baixa`, `Media`, `Alta`. **Não inclui `Urgente`** — achado RN-08: esse valor aparece
  em código de destaque em ~14 arquivos mas **não existe no `<select>` real** (resíduo
  de domínio anterior de 4 níveis) — reproduzir um case morto que nunca é gravado por
  nenhum formulário violaria "sem número/string mágica sem significado" tanto quanto
  reproduzir um bug. Método `alta(): bool`.
- Coluna nova `marcarestoque` (boolean) e `prioridade` (string, cast `Prioridade`) —
  nascem nesta fase (primeira que os usa de fato), gravadas por `EditarRma`/`CriarRma`
  (Fase 3, estendidos aqui, mesmo padrão de "Rma estendido, não recriado" da Fase 4).

### As 10 regras (`app/Rma/Aplicacao/Alertas/`, uma classe por regra)

| Classe | RN | `LEG-RMA` | Filtro (SQL, não PHP) |
|---|---|---|---|
| `RecebidosSemEncaminhar30Dias.php` | RN-01 | 018 | `status=Recebido AND recebido_em < hoje-30d` |
| `NaoVaiDarGarantia.php` | RN-02 | 019 | `status IN (Entrada,Recebido) AND ((nfvenda_emissao preenchida AND >365d) OR (fabricante=MARKVISION AND (fornecedor=Receita OR (nfcompra_emissao preenchida AND >365d))))` |
| `NfRetornoPendenteDeLancar.php` | RN-03 | 020 | `status=Concluido AND lancadoretorno=Pendente` |
| `ProtocoloAbertoNaoEncaminhado.php` | RN-04 | 021 | `status=Recebido AND protocolo IS NOT NULL AND protocolo != ''` |
| `GarantiaFornecedorExpirada.php` | RN-05 | 022 | `status IN (Entrada,Recebido) AND nfcompra_emissao < hoje-365d` |
| `GarantiaFornecedorExpirandoEm30Dias.php` | RN-06 | 023 | mesma base, janela `[336,365]` dias |
| `PrazoDestinatarioEstourado.php` | RN-07 | 024 | `status=Encaminhado AND encaminhado_em < hoje-30d` |
| `PrioridadeAltaSemEncaminhar.php` | RN-08 | 025 | `status IN (Entrada,Recebido) AND prioridade=Alta` |
| `SemNotaFiscal.php` | RN-09 | 026 | `status=Recebido AND nfcompra vazia AND nfvenda vazia` |
| `SemNumeroDeSerie.php` | RN-10 | 027 | `status=Recebido AND sn vazio` |

Cada classe expõe um único método público (`aplicavelA(Rma): bool` ou `listar():
Collection`, decidir na `design.md` da OpenSpec conforme uso real no Controller/Blade
da Fase 8) — não reinventar 10 assinaturas diferentes.

### Classificação visual e threshold (RN-11, RN-12)

- `app/Rma/Dominio/ClasseDeAlerta.php` — enum sem backing:
  `Inconformidade`, `Urgente`, `SemGarantia`, `Neutro` (equivalente a `TrZebrada`, sem
  significado de alerta). Método `Rma::classeDeAlerta(): ClasseDeAlerta` no objeto de
  domínio, implementando a ordem de avaliação confirmada em RN-11 (composta, ordem
  importa) — **sem o valor morto `prioridade=='urgente'`** (não existe mais, ver
  `Prioridade` acima) e **sem reproduzir `marcarestoque` sobrescrito** (RN-17 já
  resolvido na Fase 3 — o valor gravado já é só o do formulário).
- **Achado a resolver nesta fase, não decidido aqui:** `regras-negocio-rma-legado.md`
  RN-11 registra `[DÚVIDA]` se existe equivalente exato em TEMA V1 (paleta CSS menor,
  207 linhas vs. 905). Isso é responsabilidade da Fase 8 (Apresentação), que já herda
  esta dúvida — `ClasseDeAlerta` é o mesmo enum de domínio para os dois temas; o que
  pode variar por tema é só a classe CSS/Blade que o exibe, não a regra em si (mesmo
  princípio da Fase 4: domínio único, apresentação por tema).
- `app/Rma/Aplicacao/Alertas/UrgenciaPorThreshold.php` — RN-12 (`LEG-RMA-029`, R$75).
  **Confirmado nesta revisão como VIGENTE só em TEMA V2** — a busca textual em
  `14.6.1/menujs-right/` e `14.6.1/page/` continua sem achar `right_urgente`/`valor>75`
  (mesma conclusão do achado ARQ-06b, não refeita linha a linha 100% nesta sessão por
  não ser o foco desta revisão). **Decisão adotada, coerente com o princípio "fidelidade
  é do resultado percebido, não implementação por tema"**: a regra é implementada uma
  vez no domínio compartilhado (`Rma`), como as outras 9 — TEMA V1 e TEMA V2 sempre
  compartilharam a mesma camada de regras (`metodo.php`, incluído por ambos); RN-12 é a
  única das 12 que vive fora dessa camada (`15.8.1/banco.php`, não `metodo.php`), o que
  é mais provável de ser um **acidente de organização de arquivo** (a função podia ter
  sido colocada em `metodo.php` e teria sido herdada automaticamente, como as outras 9)
  do que uma decisão deliberada de excluir TEMA V1. Ainda assim, **isto é uma inferência,
  não evidência direta — registrado como tal**; se o usuário souber que a exclusão foi
  deliberada, a decisão deve ser revertida (regra fica condicionada ao tema ativo).
  `prazo` **não é coluna persistida** (diferente do legado): calculado por
  `Rma::prazoLegal(): CarbonImmutable` = `created_at->addDays(30)` — resultado idêntico
  ao legado (`entrada + 30 dias, gravado na criação`), sem denormalização.

### Arquivos

- `database/migrations/2026_08_29_000000_add_alertas_fields_to_rmas_table.php` —
  `prioridade` (string, cast `Prioridade`), `marcarestoque` (boolean, default `true` —
  mesmo padrão do checkbox do legado, "vem marcado por padrão"), `origem` passa a ter
  cast `Origem` (coluna já existe da Fase 3, só o cast muda), NF (`nfcompra`,
  `nfcompra_emissao`, `nfcompra_chave`, `nfvenda`, `nfvenda_emissao`, `nfvenda_chave` —
  só os 2 blocos usados pelas 10 regras; `nfremessa`/`nfretorno` ficam para Fase 6/7 se
  alguma regra vier a precisar, não copiados "por completude"), `lancadoretorno`
  (string, cast enum `StatusDeLancamento: Pendente|NfDevolucao|SemMovimentacao|Nao|Sim`
  — domínio confirmado em `inventario-banco-rma-v2.md`)
- `app/Rma/Dominio/Origem.php`, `Prioridade.php`, `ClasseDeAlerta.php`,
  `StatusDeLancamento.php` (enums)
- `app/Rma/Aplicacao/Alertas/*.php` — as 10 classes da tabela acima +
  `UrgenciaPorThreshold.php` (RN-12)
- `app/Rma/Dominio/Rma.php` — **estendido**: `classeDeAlerta(): ClasseDeAlerta`,
  `prazoLegal(): CarbonImmutable`
- `app/Http/Controllers/Rma/PainelDeAlertasController.php` — `index`, agrega as 11
  classes para a home (equivalente a `page/inicio.php`) — Controller só orquestra, cada
  regra continua isolada e testável
- `resources/views/rma/_painel_de_alertas.blade.php` — sem fidelidade visual ainda
- `tests/Unit/Rma/Alertas/*Test.php` — uma classe de teste por regra (11 arquivos),
  cada um com fixture cobrindo: caso que dispara, caso que não dispara, caso limite
  (ex.: exatamente 30 dias — decidir inclusive/exclusive lendo o operador `>` real do
  legado, que é estritamente maior, não `>=`)
- `tests/Unit/Rma/ClasseDeAlertaTest.php` — a ordem de avaliação composta de RN-11,
  os 4 critérios, garantindo que a ordem de `match`/`if` reproduz a precedência
  confirmada (primeiro critério que bate vence)

### O que NÃO entra na Fase 5

Fidelidade visual das cores/classes CSS por tema (Fase 8 — aqui só o enum de domínio
`ClasseDeAlerta` existe); crédito (`GERADO CREDITO`/`PENDENTE CREDITO` já existem como
valores de `Solucao` desde a Fase 4, mas o fluxo de controle de crédito em si é Fase 6);
consolidação de frete Porto Alegre (Fase 6); notificação por e-mail de fato enviada
(Fase 7, o e-mail de "tentativa negada" citado em `LEG-RMA-045` correlaciona com
`Papel::podeGravar()===false`, já existe desde a Fase 1 — o evento de domínio nasce
aqui/Fase 1, o envio real é Fase 7).

### OpenSpec desta fase

`openspec/changes/rma-alertas-e-prioridade/{proposal.md,design.md,tasks.md}`.

## 11. Fase 6 em detalhe — Créditos e relatórios

### Decisão de modelagem — crédito não é módulo próprio (reafirma `INV-RMA-05` §3)

Confirmado pela leitura desta revisão: o "fluxo de crédito" do legado
(`solucao='PENDENTE CREDITO' → 'GERADO CREDITO' → creditodisponivel`) já é só uma
combinação dos campos `solucao` (enum `Solucao`, existe desde a Fase 4) e
`creditodisponivel` (novo, boolean) no mesmo agregado `Rma` — não introduz entidade
nova. `LEG-RMA-048` (módulo "Créditos pendentes/usados/disponíveis") está **quebrado em
TEMA V2** (rotas sem arquivo de destino, RN-18) e **nunca existiu em TEMA V1** — a V3
reconstrói só a intenção única (um fluxo de crédito, não 3 sub-rotas), coerente com
`paridade-v2-v3.md` ("reconstruir só a intenção: fluxo único de crédito").

### Arquivos

- `database/migrations/2026_08_30_000000_add_credito_fields_to_rmas_table.php` —
  `credito_disponivel` (boolean, default `false`)
- `app/Rma/Aplicacao/MarcarCreditoDisponivel.php` — `LEG-RMA-036`; exige
  `solucao === Solucao::GeradoCredito`, grava `credito_disponivel = true`; sem
  transição automática entre `PendenteCredito`→`GeradoCredito` (o legado também não
  automatiza isso, `EVO-AUT-002` no backlog evolutivo já registra a automação como
  melhoria futura — não implementar agora)
- `app/Rma/Aplicacao/Alertas/AguardandoCredito.php` — lista `solucao=PendenteCredito`
  (mesma família de `Aplicacao/Alertas/` da Fase 5 — é uma consulta de leitura, não um
  módulo próprio, reforçando a decisão de `INV-RMA-05` §3)
- `app/Rma/Aplicacao/Relatorios/RelatorioCreditosDisponiveis.php` — RCD, `LEG-RMA-037`
- `app/Rma/Aplicacao/Relatorios/RelatorioProdutosEmEstoqueParaContagem.php` — RPEC,
  `LEG-RMA-038` (usa `marcarestoque`, Fase 5)
- `app/Rma/Aplicacao/Relatorios/RelatorioProdutosEncaminhados.php` — RMPE,
  `LEG-RMA-039`. **Achado a preservar como correção, não bug:** o legado tem um
  relatório com intervalo hardcoded para 2014 (achado já registrado em
  `inventario-funcional-rma-v2.md`) — a V3 usa intervalo de datas real informado pelo
  usuário (Form Request), não hardcoded; é bug óbvio de manutenção, não comportamento
  intencional (não há RN documentando "2014" como valor de negócio)
- `app/Http/Controllers/Rma/RelatorioController.php`,
  `app/Http/Controllers/Rma/CreditoController.php`
- `resources/views/rma/relatorios/{rcd,rpec,rmpe}.blade.php`,
  `resources/views/rma/credito/index.blade.php` — sem fidelidade visual (impressão via
  `Ctrl+P`, igual ao legado — PDF real fica em `EVO-REL-001`, backlog evolutivo, não
  entra na baseline)
- `tests/Feature/Rma/MarcarCreditoDisponivelTest.php`,
  `tests/Unit/Rma/Relatorios/{Rcd,Rpec,Rmpe}Test.php`

**Nota de correção desta revisão:** a primeira redação desta seção também incluía
`ConsolidarFretePorCidade` (RN-16, `LEG-RMA-040`) e `BoletinsRelacionados`
(`LEG-RMA-041`) aqui — mas `checklist-master-v3.md` já cataloga essas duas
funcionalidades na Fase 7 (`rma-logistica-e-historico`), decisão anterior a este
planejamento. Movidas para `§12` para não contradizer o catálogo já existente.

### O que NÃO entra na Fase 6

Automação de transição de crédito (`EVO-AUT-002`, backlog); PDF real de relatório
(`EVO-REL-001`, backlog); dashboard de recorrência de defeito (`EVO-REL-002`, backlog);
`LEG-RMA-040`/`041` (Fase 7, ver nota acima).

### OpenSpec desta fase

`openspec/changes/rma-creditos-e-relatorios/{proposal.md,design.md,tasks.md}`.

## 12. Fase 7 em detalhe — Auditoria

### Decisão de modelagem — snapshot estruturado, não diff (fica para Trilha B)

`modelo-dominio-rma-legado.md` §Auditoria: a tabela `modificacao` do legado grava um
snapshot desnormalizado dos campos-chave após a edição, sem diff nem ação específica.
`backlog-evolutivo.md` `EVO-AUD-001` já decide que o diff estruturado é melhoria
("candidato à Trilha A por ser invisível ao usuário final", mas ainda não promovido).
**Esta fase não promove `EVO-AUD-001` à Trilha A** (não há evidência de que o usuário já
tomou essa decisão — só um "candidato" registrado) — implementa o equivalente funcional
do legado (log de que uma modificação aconteceu, quem, quando, com os campos-chave),
mas **estruturado desde já** (uma linha por modificação com nome da ação, não só um
retrato do estado final) porque isso é possível sem custo extra usando Eloquent events —
não é o mesmo custo de reescrever um sistema de diff, é só usar a granularidade que o
Laravel já oferece de graça. Registrar em `design.md` da OpenSpec como pergunta aberta
para o usuário: "isso já conta como ter adotado `EVO-AUD-001`, ou ainda falta o diff
campo-a-campo de verdade?" — não decidir sozinho que a Trilha B já foi antecipada.

### Arquivos

- `database/migrations/2026_08_31_000000_create_modificacoes_de_rma_table.php` —
  `rma_id` (FK real — o legado tinha `numero` sem constraint, achado de
  `inventario-banco-rma-v2.md`), `user_id` (FK real — o legado tinha `email` sem
  constraint), `acao` (string, cast enum `AcaoDeModificacao: Criacao|Edicao|Receber|
  Encaminhar|Concluir|Arquivar|Reverter|RegistrarSolucao` — nomeada, não um snapshot
  cego), `ip`, `user_agent`, `estado_apos` (json — os campos-chave, equivalente ao
  snapshot do legado), timestamps
- `app/Rma/Dominio/AcaoDeModificacao.php` — enum
- `app/Rma/Aplicacao/RegistrarModificacaoDeRma.php` — assina os eventos de domínio
  disparados por `CriarRma`/`EditarRma` (Fase 3) e pelos 5 verbos de transição (Fase 4)
  — não é chamado diretamente pelos Controllers, é um listener (equivalente a
  `registra_modificacao()` do legado, mas centralizado em vez de chamado manualmente em
  cada arquivo)
- `app/Rma/Aplicacao/EnviarNotificacaoDeConclusao.php` — `LEG-RMA-045`; assina o evento
  `RmaConcluido` (disparado na Fase 4), usa `Mailable` do Laravel, **destinatário
  configurável via `.env`** (não hardcoded como `ezequiel()`/`naopermitido()` do
  legado — é correção de segurança/manutenibilidade invisível ao comportamento
  percebido, entra na baseline por princípio 4/5 já fixados)
- `app/Rma/Aplicacao/EnviarNotificacaoDeTentativaNaoPermitida.php` — dispara quando
  `Papel::podeGravar() === false` tenta editar (equivalente a `naopermitido()`)
- `app/Http/Controllers/Rma/HistoricoDeModificacaoController.php` — `LEG-RMA-044`;
  `index` (exige `Papel::podeGerenciarUsuarios()`, mesma regra confirmada de
  `subp/logs_de_modificacao.php`)
- Nenhum Controller novo para `LEG-RMA-043` (log de autenticação) — já existe desde a
  Fase 1 (`TentativaDeAcesso`); esta fase só adiciona a **tela** de consulta se ainda
  não existir (`app/Http/Controllers/Identidade/HistoricoDeAcessoController.php`,
  registrar aqui por ser o mesmo tipo de tela administrativa desta fase, mesmo sendo
  dado de outro módulo)
- `app/Rma/Aplicacao/ConsolidarFretePorCidade.php` — RN-16, `LEG-RMA-040` (nome desta
  change, `rma-logistica-e-historico`, já catalogava esta funcionalidade em
  `checklist-master-v3.md` antes deste planejamento — mantida aqui, não em §11).
  **Confirmado VIGENTE só em TEMA V2** (código idêntico, comentado/desativado em TEMA
  V1, `14.6.1/inc/startpage.php:139-165` — existiu, foi desativado). **TEMA V2 como
  especificação.** Implementada uma vez no domínio compartilhado (mesmo raciocínio de
  RN-12 na Fase 5); cidade "PORTO ALEGRE" mantida hardcoded (é o comportamento
  documentado, não há política configurável no legado — não inventar generalização
  não pedida)
- `app/Rma/Aplicacao/BoletinsRelacionados.php` — `LEG-RMA-041`; lista RMAs do mesmo
  destinatário/fabricante/fornecedor. **Correção de performance sem mudar
  comportamento percebido:** o legado não tem `LIMIT` (achado de risco de performance
  já registrado); a V3 pagina (`Paginator` do Laravel) — resultado percebido pelo
  usuário é o mesmo conjunto de dados, só a forma de consumir muda
- `resources/views/rma/historico/index.blade.php`,
  `resources/views/identidade/historico-de-acesso/index.blade.php`,
  `resources/views/rma/logistica/{frete-porto-alegre,boletins-relacionados}.blade.php`
- `tests/Feature/Rma/RegistrarModificacaoDeRmaTest.php` (dispara em cada uma das ações
  listadas no enum), `EnviarNotificacaoDeConclusaoTest.php` (`Mail::fake()`),
  `HistoricoDeModificacaoTest.php` (permissão),
  `tests/Unit/Rma/ConsolidarFretePorCidadeTest.php` (JOINs corretos, sem os aliases
  mortos `FOD`/`FAD` do legado; prova de TEMA V2 como especificação),
  `tests/Feature/Rma/BoletinsRelacionadosTest.php` (paginação)

### O que NÃO entra na Fase 7

Diff campo-a-campo de verdade (`EVO-AUD-001`, pendência registrada acima, não decidida);
qualquer fidelidade visual (Fase 8).

### OpenSpec desta fase

`openspec/changes/rma-logistica-e-historico/{proposal.md,design.md,tasks.md}` (nome já
catalogado em `checklist-master-v3.md`).

## 13. Fase 8 em detalhe — Apresentação (Temas V1/V2)

### Investigação pendente resolvida (parcialmente) — granularidade de compartilhamento V1/V2

`checklist-master-v3.md` Parte 2 registrava como pendente: "comparar novo RMA/detalhe
lado a lado entre os dois temas, decidir se a diferença é só view ou também
Controller/rota". Evidência já reunida por esta revisão e pelas fases anteriores:

- **É só apresentação, não Controller/rota, para as 21 regras de negócio.** Todas as
  RN-01 a RN-21 já foram confirmadas como implementadas na camada compartilhada
  (`metodo.php`) ou duplicadas **identicamente** entre os dois temas (RN-13/RN-14) —
  nenhuma regra de negócio real diverge por tema, exceto as duas exceções já
  documentadas (RN-15 ausente em V1, RN-21 quebrada em V2) — e ambas são "presença/
  ausência de funcionalidade", não "mesma funcionalidade com regra diferente por tema".
  Isso já favorece fortemente Controllers/casos de uso **únicos** (Fases 1-7, já
  escritos assim) com **views** variando por tema.
- **A navegação diverge de verdade** (achado confirmado em
  `inventario-visual-tema-v2.md`): TEMA V1 usa páginas completas
  (`index.php?page=entrada`, recarrega), TEMA V2 usa âncoras de aba (`#entrada`, sem
  reload — mecanismo exato ainda `[DÚVIDA]`, ver pendência abaixo). Isso é diferença de
  **rota/URL e de comportamento de front-end (JS)**, não de Controller server-side — a
  V3 pode servir a mesma resposta do Controller (ex.: `RmaController@index` filtrado
  por status) para as duas rotas de URL diferentes por tema, com o JS de cada tema
  decidindo se recarrega a página ou troca de painel via fetch/AJAX.
- **Decisão adotada por esta revisão, coerente com a evidência acima:** Controllers e
  casos de uso continuam **únicos** (já escritos assim desde a Fase 1) — Fase 8 não
  duplica nenhum Controller. Cada tema ganha sua própria árvore de rotas
  (`routes/tema-v1.php`, `routes/tema-v2.php`) que aponta para os **mesmos**
  Controllers/casos de uso das Fases 1-7, e sua própria árvore de Blade
  (`resources/views/temas/{v1,v2}/`). Isso é a aplicação direta do princípio "fidelidade
  é do resultado percebido, não da implementação" ao caso mais delicado do projeto.
- **Pendência RESOLVIDA em 2026-08-24 por inspeção direta do LEGACY-RUNTIME** (sessão
  autenticada real via `curl`, não só leitura estática): o mecanismo das âncoras de
  TEMA V2 é o plugin de abas NATIVO do Bootstrap 3.3.5 (`data-toggle="tab"` +
  `.tab-pane`) — os 7 painéis principais (início/pesquisar/novo_rma/entrada/recebido/
  encaminhado/concluído) vêm todos pré-renderizados no MESMO HTML da página inicial;
  a troca é JS client-side puro, sem fetch/AJAX, sem reload. Só páginas de detalhe/CRUD
  (`/info/{id}` etc.) são reload completo, via URLs limpas já mapeadas em
  `15.8.1/.htaccess`. Detalhe completo em `openspec/changes/temas-v1-v2/design.md`.

### Fidelidade visual (fonte: `inventario-visual-tema-{v1,v2}.md`, ATUALIZADA 2026-08-24)

- TEMA V1: fundo `#262626`, acento `#C3FF00`, tipografia "Open Sans"/"Fira Sans" 12px,
  branco sobre escuro; **layout fixo, confirmado sem NENHUM `@media` query**
  (`#BASE{width:984px}`) — não-responsivo de verdade, fidelidade correta é reproduzir
  isso, não "consertar". Estados de linha (`TrInconformidade`/`TrUrgente`/`TrZebrada1/2`)
  **CONFIRMADOS presentes** — pendência anterior **RESOLVIDA**: TEMA V1 carrega
  `pattern/15.9.7.css` (CSS compartilhado com TEMA V2) ALÉM do seu próprio
  `14.6.1.css`, e usa essas classes de fato em `page/entrada.php`/`encaminhados.php`/
  `localizar.php`. Única diferença real: não usa `TrSemGarantia1/2` como classe própria
  (mapeia "SEM GARANTIA" para `TrInconformidade`).
- TEMA V2: azul petróleo `#224A5D`/`#18354B`, vermelho de alerta `#904141` (mesmo tom
  do TEMA V1). **Fundo real da página é `#262626` (escuro), CORRIGIDO** — achado
  anterior ("fundo branco") estava errado; `#FFF`/`#EEE` são os painéis de conteúdo
  (`.box-content` etc.) sobre o fundo escuro, confirmado em `pattern/15.8.1.css:12-18`.
  Estados de linha completos, confirmados em CSS — mas o CSS-fonte real é o
  COMPARTILHADO `pattern/15.9.7.css`, não `pattern/15.8.1.css` como catalogado antes.
  **Breakpoints responsivos reais existem** (achado novo): 568/800/992/1080/1280/1366px
  via `15.8.1/css/media.php`, um `<style>` injetado inline, escalando um `.container`
  de largura fixa por faixa (não fluido) — não batem exatamente com os 3 breakpoints de
  QA do projeto (390/768/1440); a comparação visual precisa mapear pra regra mais
  próxima abaixo.
- Paleta comum aos dois: mesmo vermelho de alerta (`#904141`/`#9B3949`/`#cd5c5c`
  variações) — confirma que `ClasseDeAlerta::Inconformidade`/`Urgente`/`SemGarantia`
  convergem para uma variável Sass compartilhada (`$cor-alerta`) entre os dois temas.
  **Achado adicional:** o compartilhamento vai muito além dessa variável — os dois
  temas literalmente carregam o MESMO arquivo `pattern/15.9.7.css`/`.js` em produção
  (296/181 linhas), com `.breadcrumb`, `.centrodeavisos`, `.formSelect`, `.designedby`
  (rodapé), as 6 classes `Tr*` de alerta. O `_compartilhado.scss` da Fase 8 precisa
  portar esse arquivo real, não só uma variável de cor.
- **Divergência estrutural real (não só cor) entre temas:** TEMA V1 usa formulários
  HTML `<table>` autoral, sem framework CSS nenhum; TEMA V2 usa grid genuíno do
  Bootstrap 3.3.5 (`col-md-4`, `.form-group`, `.form-control`). Os bundles Vite por tema
  precisam de dependências NPM diferentes (Bootstrap só no entry point de V2), não só
  paleta diferente.
- **2 pendências novas, registradas nesta inspeção, não decididas:** (1) a fonte "Open
  Sans" do TEMA V2 nunca carrega de fato — `@font-face` aponta pra URL de produção
  morta (`cellsystem.com.br`) — decisão de produto: reproduzir o fallback quebrado ou
  self-hostar corretamente (arquivos já existem no repo legado); (2) comportamento
  pós-login assimétrico — existe um login-gateway COMPARTILHADO (porta de entrada
  padrão, `pattern/15.9.7.css` isolado, nem V1 nem V2) que respeita `tema_preferido`, e
  um login PRÓPRIO de TEMA V1 (embutido, tabela HTML) que sempre mantém o usuário em V1
  independente da preferência salva — decidir se a V3 reproduz essa assimetria.

### Arquivos

- `resources/sass/temas/v1.scss`, `v2.scss`, `_compartilhado.scss` (porta de verdade
  `pattern/15.9.7.css` — classes de alerta, `.breadcrumb`, `.centrodeavisos`,
  `.designedby`; `v2.scss` importa Bootstrap 3 SCSS, `v1.scss` não importa framework
  nenhum, achado desta revisão)
- `resources/js/temas/v1.js`, `v2.js` (`v2.js` importa o plugin de abas do Bootstrap 3
  para reproduzir a troca client-side dos 7 painéis do dashboard sem reload)
- `resources/views/identidade/login.blade.php` — login-gateway COMPARTILHADO (achado:
  não pertence a nenhum tema, é a porta de entrada padrão do legado)
- `resources/views/layouts/tema-v1.blade.php`, `tema-v2.blade.php`
- `resources/views/temas/v1/{rma,parceiros,identidade}/*.blade.php` — reimplementa as
  views mínimas das Fases 1-7 com fidelidade real ao TEMA V1; `identidade/login.blade.php`
  PRÓPRIO de V1 também entra aqui (achado: V1 tem login embutido redundante ao gateway)
- `resources/views/temas/v2/{rma,parceiros,identidade}/*.blade.php` — idem, TEMA V2, SEM
  `identidade/login.blade.php` próprio (usa o gateway)
- `app/Http/Middleware/ResolverTemaAtivo.php` — lê `tema_preferido` do usuário (Fase 1),
  decide qual árvore de views/rotas usar; equivalente a `trocarapp.php` já persistir a
  escolha, aqui só aplica a escolha já persistida a cada request
- `routes/tema-v1.php`, `routes/tema-v2.php` — apontam para os Controllers já existentes
- `tests/Feature/Temas/RenderizaTemaV1Test.php`, `RenderizaTemaV2Test.php` (smoke: cada
  tela principal renderiza sem erro, no tema certo)
- `tests/Browser/` (Playwright, conforme `INV-RMA-05` §4) — comparação visual lado a
  lado com o LEGACY-RUNTIME (`:8094`) nos 3 breakpoints já definidos em
  `checklist-master-v3.md` Parte 3/Fase 10 (390/768/1440); TEMA V1 deve confirmar
  layout FIXO/não-responsivo nos 3 (é o comportamento real, sem `@media` no legado);
  TEMA V2 tem breakpoints próprios (568/800/992/1080/1280/1366px via `css/media.php`) —
  mapear cada breakpoint de QA para a regra `min-width` mais próxima abaixo

### O que NÃO entra na Fase 8

Qualquer regra de negócio nova (todas já existem desde as Fases 1-7); Bootstrap 5/
AdminLTE modernos além do necessário para reproduzir a aparência (não uma reescrita de
design system, `INV-RMA-05` §4 já decide isso).

### OpenSpec desta fase

`openspec/changes/temas-v1-v2/{proposal.md,design.md,tasks.md}` (nome já catalogado em
`checklist-master-v3.md`).

## 14. Fase 9 em detalhe — Migração V2→V3

### Pré-requisito que destravou o detalhamento desta fase

A nota de §5 dizia que Fases 9/10 dependiam de "schema final, enums finais" só
disponíveis depois das Fases 1-8 implementadas. Isso segue parcialmente verdade em
termos de **código** (só as Fases 1-2 estão implementadas até agora), mas os enums de
domínio fechado que a migração precisa (`Status`, `Solucao`, `Origem`, `Prioridade`,
`StatusDeLancamento`, `Papel`, `TemaPreferido`) já têm **desenho fechado e código exato
escrito** nas Fases 4/5 (§9/§10 acima) — a incerteza que restava era só "qual é o mapa
campo-a-campo real", que dependia de ler o schema legado real (`bd` e as outras 8
tabelas), não de esperar a implementação. Essa leitura foi feita
(`~/github/08.24.4-legacy-gerenciador-de-rma/db/schema-only.sql`, `CREATE TABLE`
completo das 9 tabelas) e o mapa está em
`docs/arquitetura/INV-RMA-06-estrategia-reconstrucao.md` — este `§14` referencia esse
mapa em vez de repeti-lo.

### Arquitetura do migrador

- Módulo `Rma/Infraestrutura` ganha uma sub-árvore `Migracao/`: o migrador é
  infraestrutura (lê um formato de dado externo, o schema `rma_legacy`), não domínio
  novo — coerente com a fronteira já justificada em §8 ("a Fase 9 precisa ler
  `rma_legacy` sem vazar esse conhecimento pro resto da aplicação").
- Conexão secundária **só-leitura**: `config/database.php` ganha uma entrada
  `rma_legacy` (mesmo padrão de conexão nomeada do Laravel), apontando pro banco
  histórico (`rma_legacy` do ambiente Docker, ver `docs/desenvolvimento/ambiente-v2-v3.md`).
  A conexão é configurada com um usuário MySQL **sem permissão de escrita** (`GRANT
  SELECT`), reforçando em nível de banco — não só de disciplina de código — que o
  migrador nunca grava no banco legado, só lê.
- Comando Artisan: `php artisan rma:migrar-legado` (nome final, substitui o placeholder
  `rma:migrate-legacy` citado antes de decidir a convenção de nomenclatura em português
  já usada em todo o resto do código desta V3 — `AutenticarUsuario`, `CriarRma`, etc.,
  nunca em inglês).
  - Opções: `--somente=<tabela>` (roda só um importador, para depuração incremental),
    `--dry-run` (roda toda a lógica de tradução e reconciliação sem gravar nada — só
    imprime o relatório), `--forcar` (permite reprocessar linhas já marcadas como
    migradas — não usado no fluxo normal, só para corrigir um importador com bug depois
    de já ter rodado uma vez).

### Ordem de execução (por dependência de FK)

```
1. Usuarios         (users, dedup por email — sem dependência)
2. Clientes         (clientes, dedup por nome — sem dependência)
3. Fabricantes      (fabricantes, dedup por nome — sem dependência)
4. Fornecedores     (fornecedores, dedup por nome — sem dependência)
5. AssistenciasTecnicas (assistencias_tecnicas, dedup por nome — sem dependência)
6. Rmas             (rmas — depende de 2/3/4/5 para resolver fabricante_id/
                      fornecedor_id/cliente_id/destinatario_type+id)
7. LogsDeAcesso     (tentativas_de_acesso — depende de 1, soft match por email)
8. ModificacoesDeRma (modificacoes_de_rma — depende de 1 e 6, resolve rma_id via
                      numero_legado e user_id via soft match; só roda se a Fase 7
                      já estiver implementada, senão o comando pula esse passo e avisa)
```

### Arquivos

- `app/Rma/Infraestrutura/Migracao/TabelaDeTraducao.php` — um método estático por enum
  (`status()`, `solucao()`, `origem()`, `prioridade()`, `statusDeLancamento()`,
  `papel()`, `temaPreferido()`), implementando as tabelas de `INV-RMA-06` §2-§5, §9,
  §11 — **único lugar do código onde um valor cru do legado (`'entrada'`, `-1`,
  `'14.6.1'`...) é comparado por igualdade**; todo o resto do migrador chama estes
  métodos, nunca compara string/int diretamente
- `app/Rma/Infraestrutura/Migracao/ConexaoLegado.php` — thin wrapper sobre
  `DB::connection('rma_legacy')`, um método por tabela (`bd()`, `cliente()`,
  `fabricante()`, `fornecedor()`, `assistenciaTecnica()`, `usuario()`, `log()`,
  `modificacao()`) devolvendo `LazyCollection` (evita carregar as ~56 colunas × N
  linhas de `bd` inteiras na memória de uma vez)
- `app/Rma/Infraestrutura/Migracao/RelatorioDeReconciliacao.php` — objeto acumulador:
  `contarOrigem(string $tabela, int $n)`, `contarDestino(string $tabela, int $n)`,
  `registrarAnomalia(string $tabela, mixed $chaveOrigem, string $motivo)`,
  `registrarConversaoAssistida(string $tabela, mixed $chaveOrigem, string $detalhe)`;
  ao fim do comando, `resumo(): string` monta o relatório final (contagem origem×destino
  por tabela + lista de anomalias + lista de conversões assistidas), impresso no
  console e salvo em `storage/app/migracao/relatorio-{timestamp}.txt`
- `app/Rma/Infraestrutura/Migracao/Importadores/ImportarUsuarios.php`,
  `ImportarClientes.php`, `ImportarFabricantes.php`, `ImportarFornecedores.php`,
  `ImportarAssistenciasTecnicas.php`, `ImportarRmas.php`, `ImportarLogsDeAcesso.php`,
  `ImportarModificacoesDeRma.php` — uma classe por tabela legada, método público único
  `executar(RelatorioDeReconciliacao $relatorio): void`, implementando exatamente o
  mapeamento de `INV-RMA-06` para aquela tabela (idempotência via `numero_legado`/
  `updateOrCreate` por email/nome conforme §1, §11, §16 de `INV-RMA-06`)
- `app/Parceiros/Aplicacao/EncontrarOuCriarFabricante.php`,
  `EncontrarOuCriarFornecedor.php`, `EncontrarOuCriarAssistenciaTecnica.php` —
  generalização de `EncontrarOuCriarCliente` (Fase 2), usadas **só pelos importadores**
  (`INV-RMA-06` §17 explica por que o runtime de criação de RMA, Fase 3, não passa a
  usá-las — comportamento de tela não muda)
- `app/Rma/Infraestrutura/Migracao/ResolverDestinatario.php` — cascata
  `assistencia_tecnica → fornecedor → fabricante` (`INV-RMA-06` §7), sem auto-criação
- `database/migrations/2026_09_01_000000_add_numero_legado_to_rmas_table.php` —
  `numero_legado` (int, nullable, `unique`)
- `database/migrations/2026_09_01_000001_add_campos_historicos_de_migracao_to_rmas_table.php`
  — todas as colunas de `INV-RMA-06` §1.2 (preservação sem regra de negócio dona),
  `solucao_legado_bruto`, `destinatario_nome_legado`, `operador_email_legado`,
  `operador_id` (FK nullable)
- `app/Console/Commands/MigrarLegado.php` — orquestra os 8 importadores na ordem de
  dependência acima, dentro de uma transação por importador (não uma transação única
  gigante — uma tabela com erro não deve impedir as outras de migrarem), imprime o
  relatório de reconciliação ao final
- `tests/Feature/Migracao/ImportarUsuariosTest.php` (+ 7 análogos, um por importador) —
  banco `rma_legacy` de teste com fixture pequena e conhecida (não o dump de produção
  inteiro): caso feliz, caso de anomalia (valor fora do domínio), caso de idempotência
  (rodar o importador duas vezes não duplica), caso de soft-match (email bate/não bate)
- `tests/Feature/Migracao/MigrarLegadoComandoTest.php` — roda o comando completo contra
  a fixture pequena, valida ordem de execução e relatório final

### Pendências herdadas de `INV-RMA-06`, não decididas aqui

As 4 pendências numeradas no fim de `INV-RMA-06` (formato de data ambíguo em campos
`varchar`, ocorrência real de `status='retornou'`, destino de `relatorio.
informacaoadicional`, coordenação da coluna `rmas.valor` com a Fase 5) continuam sem
decisão — são carregadas para o `proposal.md` desta change, não resolvidas por
inferência.

### OpenSpec desta fase

`openspec/changes/migracao-v2-v3/{proposal.md,design.md,tasks.md}`.

## 15. Fase 10 em detalhe — QA de paridade

### Por que esta fase não depende de nenhuma implementação nova

QA de paridade não escreve código de produto — verifica o que as Fases 1-9 já
produziram contra 3 fontes de verdade já existentes: `paridade-v2-v3.md` (funcional),
os dois temas do LEGACY-RUNTIME (visual) e o relatório de reconciliação da Fase 9
(dados). O "esqueleto" anterior já cobria os 3 eixos corretamente; o que faltava era o
critério objetivo de **quando cada eixo está fechado** e o **critério de conclusão do
projeto** como um todo.

### Eixo 1 — Paridade funcional

Critério objetivo, por `LEG-RMA-NNN`: a linha em `paridade-v2-v3.md` está em
`PARIDADE` **e** tem pelo menos uma das duas evidências ligadas a ela:

- um teste automatizado (`tests/Feature/...`/`tests/Unit/...`) que passaria com o
  comportamento do legado e falharia se a V3 divergisse sem justificativa — os testes
  de regressão já escritos nas Fases 1-8 (`TrocarPropriaSenhaTest`, `ArquivarRmaTest`,
  etc.) já seguem esse padrão; ou
- para os itens sem teste automatizável direto (ex.: comparação visual pura), um passo
  de QA manual documentado em `docs/qa/roteiro-paridade-funcional.md` (arquivo novo
  desta fase), com o resultado esperado e o resultado observado registrados.

Itens `NÃO RECONSTRUIR` (`LEG-RMA-016`, `034`) e `RETOMAR IDEIA` (`LEG-RMA-035`) contam
como "fechados" sem teste — a evidência exigida ali é só a entrada na matriz com a
justificativa já registrada (código morto confirmado), não um teste provando ausência.

### Eixo 2 — Paridade visual

Retoma os 3 breakpoints já usados como referência desde a Fase 8 (390/768/1440,
`tests/Browser/` Playwright) e os mapeia para os breakpoints reais de cada tema
(`INV-RMA-05` §13):

| Breakpoint de QA | TEMA V1 (sem `@media`, layout fixo 984px) | TEMA V2 (`css/media.php`, 568/800/992/1080/1280/1366px) |
|---|---|---|
| 390px | layout fixo — mesma renderização de 984px com scroll horizontal (comportamento correto a preservar, **não** "consertar" para responsivo) | regra `min-width` mais próxima abaixo de 390 → nenhuma (abaixo do menor breakpoint, 568px) — comparar contra o estado "sem nenhum breakpoint aplicado" |
| 768px | idem (fixo) | entre 568 e 800 → aplica a regra de 568px |
| 1440px | idem (fixo) | acima de 1366 → aplica a regra de 1366px (maior breakpoint, "modo desktop cheio") |

Critério objetivo: para cada combinação (tema × breakpoint × tela principal —
login/painel de alertas/novo RMA/detalhe/localizar), screenshot da V3 lado a lado com o
LEGACY-RUNTIME (`:8094`) sem diferença estrutural relevante (mesma disposição de
elementos, mesma paleta — pequenas diferenças de rasterização de fonte não contam como
divergência). Divergência real = pendência registrada com origem rastreável (achado já
feito na Fase 8, ex.: fonte Open Sans nunca carrega em produção) ou correção aprovada
pelo usuário, nunca "ficou diferente e não documentado por quê".

### Eixo 3 — Paridade de dados

O relatório de reconciliação da Fase 9 (`RelatorioDeReconciliacao`, §14 acima) **é** a
evidência deste eixo, não uma verificação isolada e repetida — QA de paridade só precisa
confirmar que:

- a contagem "origem × destino" de cada tabela bate (ou a diferença é exatamente igual
  ao número de anomalias + conversões assistidas + registros intencionalmente não
  migrados — ex.: `assistencias`, sempre 0 migrado, por decisão já registrada em
  `INV-RMA-06` §15);
- nenhuma anomalia ficou sem uma linha correspondente no relatório final (toda anomalia
  visível é rastreável até a chave de origem exata, não um contador solto).

### Critério de conclusão do projeto (Trilha A → habilita Trilha B)

Objetivo, sem inventar critério novo — consolida o que já estava disperso em
`checklist-master-v3.md`/`paridade-v2-v3.md`/diretrizes do usuário ao longo do projeto:

1. `docs/produto/paridade-v2-v3.md`: **100% das linhas não-`NÃO RECONSTRUIR`/
   `RETOMAR IDEIA` em `PARIDADE`** (nenhuma `PENDENTE`/`EM ESPECIFICAÇÃO`/
   `EM IMPLEMENTAÇÃO` restante).
2. `sail test` verde na suíte inteira (não só a da última fase) — full-regression, não
   incremental.
3. Eixo 2 (paridade visual) fechado nos 3 breakpoints × 2 temas × telas principais, com
   evidência em `tests/Browser/` (Playwright) e screenshots arquivados.
4. Relatório de reconciliação da Fase 9 sem divergência não explicada (Eixo 3).
5. Todas as pendências reais registradas ao longo das 9 fases anteriores (`LEG-RMA-002`
   autocadastro, `EVO-AUD-001` diff estruturado, fonte Open Sans, assimetria de
   pós-login, as 4 de `INV-RMA-06`) estão **decididas pelo usuário ou explicitamente
   adiadas para o backlog evolutivo** — nenhuma pode ficar "esquecida", mas nem toda
   pendência precisa virar código antes da Trilha B começar; precisa só ter uma decisão
   registrada (implementar agora / adiar para `EVO-*` / não fazer).

Só quando os 5 itens acima são verdade a V3 é declarada em paridade com a V2, e a
Trilha B (`docs/produto/backlog-evolutivo.md`) pode começar.

### Arquivos

- `docs/qa/roteiro-paridade-funcional.md` — passos manuais para os itens sem teste
  automatizável direto (criado nesta fase)
- `docs/qa/relatorio-paridade-final.md` — o relatório final consolidando os 3 eixos +
  o checklist de conclusão de projeto acima, gerado ao fim da execução desta fase
- `tests/Browser/ParidadeVisualTest.php` (Playwright) — os 3 breakpoints × 2 temas ×
  telas principais, screenshot diff contra o LEGACY-RUNTIME

### OpenSpec desta fase

`openspec/changes/qa-paridade/{proposal.md,design.md,tasks.md}`.
