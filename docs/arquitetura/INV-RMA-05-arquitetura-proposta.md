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
  `v1`/`v2`) à tabela `users` padrão do Laravel.
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

### OpenSpec desta fase

`openspec/changes/autenticacao-usuarios/{proposal.md,design.md,tasks.md}` — escrita
nesta mesma sessão, ver arquivos correspondentes.

## 7. O que fica para quando a Fase 1 estiver implementada

Fases 2 a 10 recebem o mesmo nível de detalhe (arquivo por arquivo) **só quando forem a
fase corrente** — detalhar todas agora, antes de aprender com a implementação da Fase 1,
seria planejar no escuro. O esqueleto de dependência (seção 5) já é suficiente para
saber a ordem.
