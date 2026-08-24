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

## 9. O que fica para quando a Fase 2/3 estiverem implementadas

Fases 4 a 10 recebem o mesmo nível de detalhe **quando forem a fase corrente** — depois
de aprender o que funcionou (ou não) nas Fases 1 a 3. O esqueleto de dependência
(seção 5) já é suficiente para saber a ordem até lá.
