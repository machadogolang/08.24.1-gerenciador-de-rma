# INV-RMA-08 — Terceiro tema visual, mobile-first (Trilha B)

Data: 2026-08-25. Investigação formal, aberta e concluída nesta sessão, mesmo padrão de
`INV-RMA-05`/`06`/`07`: investigação e parecer no mesmo documento. Numeração: `07` já
existe (`INV-RMA-07-evolucao-saas-multiempresa.md`); `05`/`06` também. `08` confirmado
como o próximo número livre (`ls docs/arquitetura/` nesta sessão não mostra `08` em uso).

**Pergunta que este documento responde:** como um TERCEIRO tema visual do RMA V3 —
mobile-first, inspirado na abordagem real do CONAHOM (`~/github/online-conahom-laravel`),
sem paridade com o legado — se encaixa na arquitetura de temas já fixada nas Fases 1 e 8
(`TemaPreferido`, `ResolverTemaAtivo`, `resources/views/temas/{v1,v2}/`), sem quebrar
nada já implementado?

**Regra de ouro desta investigação:** nada aqui é implementação. Nenhuma view, Sass,
rota ou alteração real do enum `TemaPreferido` é criada nesta sessão. O que existe é
decisão registrada + pendência registrada, mesmo padrão de confiança do resto do
projeto: evidência de código real (CONAHOM, RMA V3 atual) > desenho já fechado
(`temas-v1-v2/design.md`, `INV-RMA-05`) > inferência justificada > pendência explícita.

## 0. Por que isto é Trilha B, não Trilha A

TEMA V1 e TEMA V2 existem para **preservar** — reproduzir fielmente duas superfícies
visuais reais do sistema legado (`14.6.1` e `15.8.1`), pixel a pixel onde possível,
como especificado em `openspec/changes/temas-v1-v2/design.md` e implementado na Fase 8
(commits `92cce93`, `acd8271`, `ce14a40`, este último ainda em correção visual ativa por
outro agente no momento desta investigação). Um "TEMA V3 mobile-first" não tem
equivalente no legado — nenhuma das duas superfícies legadas (`14.6.1` layout fixo
984px sem nenhum `@media`; `15.8.1` com breakpoints próprios não-fluidos, larguras fixas
por faixa) é mobile-first em nenhum sentido real. Propor um terceiro tema mobile-first é
**evolução de produto pura**, exatamente a mesma categoria de `INV-RMA-07` (SaaS
multiempresa): investigado e registrado agora, implementado só depois da baseline de
paridade da Trilha A estar validada (Fases 1-10 + QA, `INV-RMA-05` §15). Ver §8 para a
recomendação explícita de momento.

## 1. O que o CONAHOM realmente faz (evidência de código, não suposição)

Lido diretamente nesta sessão em `~/github/online-conahom-laravel/resources/scss/`:

- **Mobile-first real, declarado e seguido.** `_base.scss:1-6`: *"Base do sistema -
  escrita primeiro para o telefone. Toda consulta de midia aqui usa `min-width`: a
  folha vale para a tela pequena e os pontos de quebra apenas ampliam. Nenhuma regra
  reduz."* Mesmo princípio repetido em `_tokens.scss:498-503`: *"Mobile-first: a folha
  base vale para o telefone e estes pontos so ampliam. Nunca escrever uma consulta de
  midia que reduza."* Confirmado no uso real — `components/_navegacao.scss` só usa
  `@media (min-width: $quebra-md)` / `@media (min-width: $quebra-lg)` (linhas 109, 121,
  138, 212, 273, 370), nunca `max-width`.
- **Breakpoints nomeados, sem número mágico** (`_tokens.scss:498-509`):
  `$quebra-xs: 480px`, `$quebra-sm: 576px`, `$quebra-md: 768px`, `$quebra-lg: 992px`,
  `$quebra-xl: 1200px`. Todo consumidor referencia o nome, nunca o literal — mesma
  disciplina que o RMA V3 já aplica a `$breakpoints-tema-v2` (`temas-v1-v2/design.md`,
  linhas 61-68) e a enums de domínio sem valor mágico em todo o projeto.
- **Tokens de design centralizados em um único arquivo** (`_tokens.scss`, 527 linhas):
  cor (institucional, destaque, neutra, situação, painel claro/escuro, ação,
  confirmação/recusa, botão por variante×tema), tipografia (escala `$texto-xs`…
  `$texto-3xl`, pesos nomeados, alturas de linha nomeadas), espaçamento em escala de 4px
  (`$espaco-1`…`$espaco-8`), forma (raio, borda), elevação (sombra nomeada), alvo de
  toque (`$alvo-de-toque: 2.75rem` / 44px — comentário explícito: *"Nenhum elemento
  clicavel abaixo disso: e a medida confortavel para o dedo, e o telefone e o uso
  principal do associado"*, `_tokens.scss:412-421`), largura de leitura/conteúdo, e
  camadas de empilhamento nomeadas (`$camada-base`…`$camada-aviso`). Regra do próprio
  arquivo (linha 4-10): *"UNICO arquivo do projeto onde pode existir valor literal de
  cor, medida, raio ou sombra."*
- **Alvo de toque aplicado globalmente, não só documentado**: `_base.scss:61-68` — todo
  `button`, `a.btn`, `[role='button']`, `.form-control`, `.form-select` recebe
  `min-height: $alvo-de-toque` incondicionalmente, telefone ou desktop.
- **Estrutura de arquivos**: `components/` (peças reutilizáveis — botão, cartão, tabela,
  modal, navegação, formulário, ~19 arquivos), `contextos/` (3 arquivos —
  `_admin.scss`, `_associado.scss`, `_publico.scss`, cada um compondo os components para
  uma área do produto), `superficies/` (4 arquivos — variações de fundo/tema dentro de
  cada contexto, incluindo tema claro/escuro do admin), mais `_base.scss`/`_tokens.scss`
  na raiz e `app.scss` como entry point único. Não há um diretório por tema visual (não
  é o mesmo problema que V1/V2 do RMA, que são *dois sistemas visuais distintos*
  simultâneos) — é uma única linguagem visual, responsiva por natureza, com variação de
  tema claro/escuro (`superficies/_admin.scss`) resolvida por classe de contexto
  (`.contexto-admin`) e não por bundle Vite separado.
- **Layout responsivo real**: `resources/views/components/layouts/admin.blade.php` usa
  um componente Blade parametrizado (`x-layouts.admin largura="formulario|composicao|
  dados"`) que decide a largura do corpo por natureza do conteúdo (não por dispositivo),
  documentado com racional numérico medido (`_tokens.scss:459-489`) — sinal de que o
  mobile-first do CONAHOM não é só "CSS que encolhe", é uma decisão de composição de
  layout ponta a ponta (Blade + Sass).
- **Não usa Tailwind** — o CONAHOM é Sass autoral (BEM-like, nomes em português,
  metodologia `tokens → base → components → contextos/superfícies`), sem framework de
  utilitário. Isso é uma diferença relevante para a decisão do §4 abaixo.

## 2. Arquitetura de temas já fixada no RMA V3 (não reaberta sem motivo)

Confirmada em `openspec/changes/temas-v1-v2/design.md` e no código real das Fases 1/8:

- **`TemaPreferido`** (`app/Identidade/Dominio/TemaPreferido.php`) — enum backed string,
  hoje **exatamente 2 casos**:
  ```php
  enum TemaPreferido: string
  {
      case V1 = 'v1';
      case V2 = 'v2';

      public function alternar(): self
      {
          return match ($this) {
              self::V1 => self::V2,
              self::V2 => self::V1,
          };
      }
  }
  ```
  O método `alternar()` é um **toggle binário literal** — `match` exaustivo sobre
  exatamente 2 casos, sem `default`. É o único lugar do código hoje que assume
  estruturalmente "só existem 2 temas".
- **`AlternarTemaPreferido`** (`app/Identidade/Aplicacao/AlternarTemaPreferido.php`) —
  caso de uso que chama `$usuario->tema_preferido->alternar()` e persiste. Depende
  inteiramente do comportamento binário do enum — não filtra nem escolhe, só inverte.
- **`TemaPreferidoController::update`** (`app/Http/Controllers/Identidade/
  TemaPreferidoController.php`) — endpoint único, sem parâmetro de tema no request; o
  "qual tema escolher" nunca é uma entrada do usuário, é sempre "o oposto do atual".
  Isso é o segundo lugar que assume 2 valores: a UI (fora do escopo desta leitura, mas
  coerente com o Controller) provavelmente é um botão só ("alternar tema"), não um
  seletor — um seletor de 3 opções não pode reaproveitar esse endpoint como está.
- **`ResolverTemaAtivo`** (middleware, `temas-v1-v2/design.md` linhas 88-103) — já
  resolve por valor do enum (`$request->user()?->tema_preferido ?? TemaPreferido::V2`) e
  compartilha `temaAtivo` via `View::share`; a resolução de view por tema acontece por
  fora, no helper `view_do_tema('rma.index')` que resolve para `resources/views/temas/
  {v1,v2}/rma/index.blade.php`. **Este mecanismo já é estruturalmente N-ário** — nada
  aqui assume 2 valores, ele só faz `match`/lookup pelo valor do enum. Não é o mecanismo
  binário; é `alternar()` que é.
- **Vite/Sass**: 2 bundles separados hoje (`resources/js/temas/{v1,v2}.js`,
  `resources/sass/temas/{v1,v2}.scss` + `_compartilhado.scss` para as classes de alerta
  RN-11), registrados como 2 entradas distintas em `vite.config.js` `input`, para que
  CSS/JS de um tema nunca vaze para o outro.
- **`resources/views/identidade/login.blade.php`** — único, fora de `temas/`, sempre
  redireciona respeitando `tema_preferido` no pós-login (decisão já resolvida na Fase 8,
  não reaberta aqui).
- **Escopo real coberto pela Fase 8** — confirmado em `resources/views/temas/{v1,v2}/`:
  `rma/{index,create,edit,show}`, `parceiros/{index,_form}`, `identidade/{usuarios,
  perfil}`. Alertas (painel dedicado), crédito, relatórios e auditoria **não** ganharam
  view própria por tema na Fase 8 original — ficaram fora do escopo visual inicial
  (coerente com o prompt do usuário, que já cita isso como fato conhecido).

## 3. `TemaPreferido` crescendo de 2 para 3 casos

**Não é só adicionar `case V3` e um valor no enum.** O enum em si (adicionar o caso) é
trivial — o problema real é `alternar()`, que deixa de fazer sentido como conceito assim
que existem 3 valores: "alternar" pressupõe exatamente 2 estados formando um ciclo de
tamanho 2. Com 3 valores, "o oposto do atual" não é uma pergunta bem-formada.

**Decisão recomendada:** `alternar()` é substituído por um mecanismo de **seleção
explícita** — o usuário escolhe um `TemaPreferido` específico (`V1`/`V2`/`V3`), não
inverte o atual. Concretamente:

- `TemaPreferidoController::update` passa a receber o tema desejado como parâmetro do
  request (validado contra os casos do enum via `Rule::enum(TemaPreferido::class)` ou
  equivalente), em vez de não receber nenhum parâmetro.
- `AlternarTemaPreferido` (caso de uso) é renomeado/substituído por algo como
  `DefinirTemaPreferido` — recebe o `User` e o `TemaPreferido` alvo, persiste
  diretamente. Nome atual (`Alternar...`) fica semanticamente errado para uma escolha de
  3+ opções; renomear é parte da mudança, não um detalhe cosmético.
- O método `alternar(): self` do enum é removido (não adaptado para um `match` de 3
  ramos sem sentido de ciclo) — nenhuma outra parte do código hoje depende dele além dos
  dois pontos acima (confirmado por leitura: `AlternarTemaPreferido` é o único
  consumidor).
- **Nenhum outro ponto do código** (`ResolverTemaAtivo`, `view_do_tema()`, os bundles
  Vite, o middleware) precisa mudar de forma — todos já operam por valor do enum via
  lookup/match exaustivo sobre "qual view/bundle corresponde a este valor", que é
  naturalmente N-ário. A adição de `V3` é, para eles, só mais um ramo do `match`/mais uma
  entrada no diretório `temas/`.
- **Testes que dependem hoje de assumir 2 valores:** o teste mais provável de quebrar é
  qualquer teste unitário de `TemaPreferido::alternar()` (ex.: "V1 alterna para V2 e
  vice-versa") — esse teste inteiro deixa de fazer sentido e deve ser removido/
  substituído por um teste do novo `DefinirTemaPreferido`, não adaptado. Testes de
  `ResolverTemaAtivo`/`RenderizaTemaV1Test`/`RenderizaTemaV2Test` (citados em
  `temas-v1-v2/design.md`) não precisam mudar — ganham um `RenderizaTemaV3Test` irmão,
  sem alterar os existentes.

## 4. Onde o V3 se encaixa na estrutura de arquivos existente

**Decisão recomendada: um terceiro diretório `v3/`, seguindo o mesmo padrão dos outros
dois, mas com stack interna diferente (Tailwind em vez de Sass autoral/Bootstrap).**

```
resources/views/temas/
├── v1/   (inalterado)
├── v2/   (inalterado)
└── v3/
    ├── layout.blade.php
    ├── rma/{index,create,edit,show}.blade.php
    ├── parceiros/{index,_form}.blade.php
    └── identidade/{usuarios,perfil}.blade.php

resources/js/temas/
├── v1.js  (inalterado)
├── v2.js  (inalterado)
└── v3.js  (novo entry point Vite)

resources/sass/temas/            → v1/v2 continuam aqui, inalterados
resources/css/temas/v3.css       → NOVO: entry point Tailwind do V3, não Sass
```

Justificativa de cada parte:

- **Diretório irmão (`v3/`), não algo estruturalmente diferente na árvore de views** —
  o mecanismo `view_do_tema('rma.index')` já é N-ário (§2); um terceiro diretório
  seguindo a mesma convenção de nomes de arquivo (`rma/index.blade.php`,
  `parceiros/_form.blade.php` etc.) é o caminho de menor atrito e mantém
  `ResolverTemaAtivo`/`view_do_tema()` sem qualquer mudança de forma, só mais uma
  entrada de lookup. Não há motivo arquitetural para o V3 fugir dessa convenção — "ser
  mobile-first" é uma propriedade do CSS/composição de layout, não da forma como Blade
  resolve views por tema.
- **Tailwind, não um terceiro par Sass autoral/Bootstrap.** Confirmado por leitura de
  `package.json` e `vite.config.js` do RMA V3 nesta sessão: `tailwindcss@^4.0.0` e
  `@tailwindcss/vite@^4.0.0` já estão no scaffold padrão do Laravel 13 deste projeto
  (`devDependencies`), o plugin já está registrado em `vite.config.js`
  (`tailwindcss()` na lista de `plugins`), e `resources/css/app.css` já é
  `@import 'tailwindcss';` com um bloco `@theme` mínimo (só `--font-sans`) — mas
  **nenhuma view do projeto usa classe utilitária Tailwind hoje** (busca por padrão
  `classe:variante` nas views não retornou nenhum resultado). Ou seja: Tailwind é
  scaffold morto, não uma decisão de arquitetura já tomada e ativa. Usar Tailwind para
  V3 aproveita uma dependência que já existe e já está configurada (sem custo de
  introdução), e é coerente com "design novo, não fidelidade ao legado" (§5) — o V1/V2
  têm boas razões para serem Sass autoral (reproduzir CSS legado linha a linha
  literalmente exige controle fino que um framework utilitário atrapalha); um tema novo,
  sem alvo de fidelidade, não tem essa restrição e ganha velocidade de desenvolvimento
  com utilitários responsivos nativos do Tailwind (`sm:`/`md:`/`lg:` já são mobile-first
  por padrão no próprio Tailwind, o que casa diretamente com o requisito do tema).
  **Decisão adiada, não fechada aqui:** se os tokens (§7) devem ser expressos via
  `@theme` do Tailwind 4 (CSS nativo) ou se ainda cabe um `_tokens.scss` próprio
  co-existindo — ambos são tecnicamente possíveis em Tailwind 4; a escolha fina fica
  para quando a implementação real começar, não é uma decisão de fronteira.
- **`resources/css/temas/v3.css` em vez de `resources/sass/temas/v3.scss`** — nome de
  diretório reflete a tecnologia real (paralelo a `resources/css/app.css`, que já é
  Tailwind), evitando o falso sinal de "é mais um tema Sass" para quem só olhar a árvore
  de diretórios.
- **`vite.config.js` ganha uma terceira entrada** (`resources/js/temas/v3.js` +
  `resources/css/temas/v3.css`) na mesma lista `input`, exatamente como `v1`/`v2` já
  estão — nenhuma mudança na forma da configuração, só mais um item.

## 5. Fidelidade visual: V3 é design novo, não reprodução do legado

**Decisão explícita: TEMA V3 é deliberadamente um design novo e moderno — não busca
nenhuma fidelidade ao legado.** Esta não é a resposta óbvia sem consequência — é uma
mudança de natureza que precisa ficar registrada com todas as letras:

- **TEMA V1 e TEMA V2 existem para PRESERVAR.** São a reconstrução fiel de duas
  superfícies reais do sistema legado, com processo de QA de paridade dedicado (Fase
  10), captura de screenshots de referência (`docs/produto/screenshots-fase8-legacy-ref/`),
  e regra de princípio já registrada em `temas-v1-v2/design.md` ("fidelidade visual é do
  resultado real observado no LEGACY-RUNTIME... não da intenção original nunca
  renderizada" — caso da fonte Open Sans). O usuário legado precisa reconhecer a tela.
- **TEMA V3 existe para EVOLUIR.** Não há usuário legado a reconhecer — é uma superfície
  nova, para quem hoje usa (ou usaria) o produto primariamente em telefone, algo que
  nenhum dos dois temas legados nunca foi projetado para fazer bem (V1 é literalmente
  fixo em 984px sem nenhum `@media`; V2 tem breakpoints, mas não fluidos, largura fixa
  por faixa — nenhum dos dois é "mobile-first" em nenhuma leitura razoável do termo).
  Não existe requisito de "parecer com o legado" para V3 — pelo contrário, um V3 que
  imitasse a estética do legado (tabelas HTML autorais, AdminLTE 2.2.0, Bootstrap 3)
  contradiria o próprio motivo de ele existir.
- **Consequência prática:** o processo de QA de paridade da Fase 10 (`INV-RMA-05` §15)
  **não se aplica** a V3 — não há screenshot do legado para comparar lado a lado, porque
  não há equivalente no legado. V3 precisa do próprio critério de qualidade (usabilidade
  mobile, não fidelidade pixel a pixel) — a decidir quando a implementação real
  começar, fora do escopo desta investigação.
- **Risco de confusão a evitar deliberadamente:** nomear como "V3" ao lado de "V1"/"V2"
  pode sugerir aos leitores futuros do código que é "mais uma variação fiel do legado,
  numerada em sequência" — não é. Recomenda-se que a documentação de produto sempre
  qualifique como "TEMA V3 (mobile-first, design novo — não reproduz o legado)" na
  primeira menção de cada documento novo que o cite, para não deixar essa diferença de
  natureza se perder silenciosamente com o tempo.

## 6. Escopo funcional do V3 — mesmo escopo inicial da Fase 8, não tudo de uma vez

**Decisão recomendada: V3 cobre inicialmente o mesmo escopo que a Fase 8 original
cobriu para V1/V2** — RMA (`index/create/edit/show`), parceiros (`index/_form`),
identidade (`usuarios/perfil`) — **não** todas as rotas/Controllers das Fases 1-9 de
uma vez. Justificativa:

- É o padrão de entrega incremental que a própria Fase 8 já estabeleceu como aceitável
  (alertas/crédito/relatórios/auditoria ficaram fora do escopo visual inicial de V1/V2
  também, sem que isso tenha sido tratado como falha — são telas de uso menos frequente
  ou mais especializado).
- Cobrir "tudo de uma vez" antes de validar que a abordagem mobile-first do V3 funciona
  bem nas telas de maior tráfego (RMA, parceiros) seria investir esforço de design antes
  de ter evidência de que o padrão escolhido (tokens, breakpoints, composição de layout)
  está certo — o mesmo raciocínio de risco que orienta o momento de implementação (§8).
- Alertas/crédito/relatórios/auditoria (mesma lista deixada de fora na Fase 8) ganham
  view V3 em uma expansão posterior, quando for priorizada — não é uma decisão de
  arquitetura fechá-las fora para sempre, é sequenciamento.

## 7. Breakpoints e tokens nomeados propostos para V3

Sem número mágico, mesmo princípio do resto do projeto (`$breakpoints-tema-v2` já é o
precedente direto dentro do próprio RMA V3). Dado que a stack recomendada é Tailwind
(§4), a forma concreta é `@theme` no CSS (Tailwind 4) em vez de variável Sass, mas o
**conjunto de nomes e a lógica de quebra** são o que importa registrar aqui — a sintaxe
exata é detalhe de implementação, não de arquitetura.

- **Reaproveitar os breakpoints nomeados do CONAHOM como ponto de partida, não
  reinventar um conjunto novo sem motivo**: `quebra-xs` (480px), `quebra-sm` (576px),
  `quebra-md` (768px), `quebra-lg` (992px), `quebra-xl` (1200px) — evidência real de um
  projeto irmão do mesmo autor/organização que já validou esses valores em produção
  (`_tokens.scss:505-509`). Não há motivo de negócio específico do RMA para esses
  valores serem diferentes — o público (operador de assistência técnica em
  telefone/tablet/desktop) é comparável ao do CONAHOM (associado/administrador
  acessando de qualquer dispositivo). Se a implementação real do V3 encontrar um
  dispositivo/breakpoint que o conjunto do CONAHOM não cobre bem, ajustar então, com
  evidência — não é uma decisão a reabrir por especulação agora.
- **Regra dura herdada do CONAHOM, a repetir explicitamente na implementação real**:
  toda consulta de mídia usa `min-width`, nunca `max-width` — a folha base é escrita
  para a tela pequena primeiro, breakpoints só ampliam complexidade/densidade, nunca
  reduzem. Esta é a definição operacional de "mobile-first" que este documento adota —
  não é só "responsivo", é a ordem de escrita do CSS que importa.
- **Alvo de toque mínimo, herdado do CONAHOM**: 44px (`2.75rem`) em todo elemento
  acionável, sempre — mesmo em desktop (o CONAHOM não relaxa essa regra fora do
  telefone, e a justificativa dele — dedo humano, não dispositivo — se aplica igualmente
  aqui).
- **Tokens de espaçamento em escala fixa** (proposta: reaproveitar a escala de 4px do
  CONAHOM — `espaco-1` a `espaco-8`) em vez de valores soltos por tela, mesma
  disciplina.
- Cor/tipografia própria do RMA V3 **não** são herdadas do CONAHOM — são identidade
  visual de produto diferente; só a metodologia (tokens nomeados, nunca literal fora de
  um único arquivo/bloco `@theme`) é reaproveitada, não a paleta.

## 8. Momento de implementação

Mesmo raciocínio de `INV-RMA-07` §13.

| Estratégia | Descrição | Avaliação |
|---|---|---|
| A — Terminar Fases 1-10 + QA de paridade primeiro, depois V3 | V3 só começa depois da baseline validada | Menor risco de contaminar a reconstrução fiel (V1/V2 ainda não passaram por Fase 10/QA); nenhum recurso de implementação desviado da Trilha A antes dela fechar |
| B — Implementar V3 em paralelo às Fases 9-10 | V3 nasce antes da baseline de paridade terminar | Risco de diluir foco exatamente quando a Fase 8 está sendo corrigida visualmente e a Fase 10 (QA de paridade) ainda nem começou — dois problemas de natureza diferente (fidelidade vs. evolução) competindo pela mesma janela de trabalho |
| C — Preparar decisão de arquitetura agora (esta investigação), implementar código só depois da baseline | Investigação adiantada, código não | É exatamente o que este documento faz |

**Recomendação: Estratégia C, convergindo para A na prática de código — mesma
recomendação de `INV-RMA-07` §13.** A investigação (classificação de onde V3 se encaixa
na estrutura de arquivos, decisão de `TemaPreferido`, decisão de fidelidade vs. design
novo, escopo inicial, tokens/breakpoints) está feita e não precisa ser reaberta quando a
Trilha B começar de verdade. Mas nenhuma linha de código de V3 — nenhuma view, nenhum
`v3.css`/`v3.js`, nenhuma alteração real do enum `TemaPreferido` — deve ser escrita
antes de: (1) a Fase 8 (V1/V2) estar visualmente corrigida e commitada (em andamento
agora, fora do escopo desta investigação); (2) a Fase 10 (QA de paridade V1/V2 contra o
legado) estar concluída; (3) a baseline de paridade completa (Fases 1-10) estar
validada. Motivo adicional específico deste caso (além do já registrado em
`INV-RMA-07` §13): introduzir um terceiro tema — e a mudança de `TemaPreferido::
alternar()` para seleção explícita (§3) — **antes** da Fase 10 fechar arriscaria a
suíte de teste/QA de paridade ter que lidar com uma UI de troca de tema (botão de
alternância binária) que já mudou de forma no meio do processo, sem necessidade.

## 9. Pendências reais — não decididas por inferência

1. **Forma exata de expressão dos tokens em Tailwind 4** (`@theme` puro vs. `@theme`
   combinado com um arquivo `_tokens.css` próprio do V3) — decisão de implementação, não
   de arquitetura; fica para quando a Fase de V3 realmente começar a ser codificada.
2. **Curadoria/paleta de cor própria do RMA V3** — este documento decide *metodologia*
   (tokens nomeados, mobile-first, alvo de toque), não a identidade visual em si (cores,
   tipografia, tom) — é decisão de design de produto, fora do escopo de uma investigação
   de arquitetura.
3. **Se o botão de troca de tema na UI vira um seletor explícito de 3 opções ou
   permanece um controle mais simples com "V3" como opção adicional** — consequência
   direta de `DefinirTemaPreferido` (§3) precisar de um alvo explícito, mas o desenho de
   UI do seletor em si não foi pedido nesta investigação.
4. **Critério de qualidade/aceite do V3** (o que substitui "QA de paridade" quando não
   há legado para comparar) — precisa existir antes da implementação real começar, mas
   não foi especificado aqui (fora do escopo "decisão de arquitetura", é processo de
   produto).
5. **Se/quando alertas/crédito/relatórios/auditoria ganham view V3** — sequenciamento
   de produto, não bloqueia a decisão de arquitetura registrada em §4/§6.

## 10. Impacto sobre código/estrutura já existente

- **`app/Identidade/Dominio/TemaPreferido.php`** — ganha `case V3 = 'v3'`; perde o
  método `alternar()` (§3). Nenhuma outra alteração de forma.
- **`app/Identidade/Aplicacao/AlternarTemaPreferido.php`** — renomeado/substituído por
  `DefinirTemaPreferido`, assinatura muda de `alternar(User $usuario)` para
  `definir(User $usuario, TemaPreferido $tema)` (nome exato a decidir na implementação).
- **`app/Http/Controllers/Identidade/TemaPreferidoController.php`** — `update()` passa a
  validar e receber o tema alvo do request, em vez de nenhum parâmetro.
- **`app/Http/Middleware/ResolverTemaAtivo`** — sem alteração de forma (já é N-ário,
  §2/§3).
- **`resources/views/temas/`** — ganha diretório irmão `v3/` (§4). `v1/`/`v2/`
  inalterados.
- **`resources/js/temas/`** — ganha `v3.js`. `v1.js`/`v2.js` inalterados.
- **`resources/sass/temas/`** — inalterado (V3 não usa Sass, ver `resources/css/temas/`
  em §4).
- **`vite.config.js`** — ganha uma terceira entrada em `input` (mesma forma das duas
  já existentes).
- **Testes** — teste de `alternar()` do enum é removido/substituído (§3); ganha
  `RenderizaTemaV3Test` irmão dos já existentes de V1/V2; ganha suíte própria de
  usabilidade mobile do V3 (critério a decidir, pendência §9.4) — **não** ganha suíte de
  paridade com legado (§5).
- **Fase 10 (QA de paridade)** — escopo continua sendo só V1/V2 contra o legado; V3 não
  entra nesse gate (§5/§8).

## 11. Backlog evolutivo — item complementado/criado

- **Categoria nova `EVO-UX`** (não `EVO-SAAS`) — criada nesta sessão. Justificativa: um
  tema mobile-first não é sobre multiempresa/isolamento de tenant (o domínio de
  `EVO-SAAS-*`), é sobre usabilidade/experiência de uso em dispositivo — categoria
  genuinamente diferente, que merece namespace próprio no backlog em vez de forçar
  `EVO-SAAS-004` sobre um tema que nada tem a ver com tenancy. Consistente com o padrão
  já usado no documento (`EVO-DOMINIO`, `EVO-AUTOMACAO`, `EVO-RELATORIOS`,
  `EVO-SEGURANCA`, `EVO-AUDITORIA`, `EVO-PERFORMANCE`, `EVO-IA` já são categorias
  próprias por natureza do item, não um contador único).
- **`EVO-UX-001` — Tema V3 mobile-first** (novo, criado nesta sessão) — ver
  `docs/produto/backlog-evolutivo.md`, cross-referenciando este documento
  (`INV-RMA-08`).

## 12. Referências

`docs/produto/backlog-evolutivo.md` (`EVO-UX-001`, novo), `openspec/changes/
temas-v1-v2/design.md` (arquitetura de temas V1/V2 vigente, mecanismo `ResolverTemaAtivo`/
`view_do_tema()`), `docs/arquitetura/INV-RMA-05-arquitetura-proposta.md` (arquitetura
proposta geral, §15 gate de paridade), `docs/arquitetura/INV-RMA-07-evolucao-saas-
multiempresa.md` (mesmo padrão de investigação/decisão sem implementação, §13 modelo de
raciocínio de momento reaproveitado em §8 aqui), `~/github/online-conahom-laravel/
resources/scss/_tokens.scss` e `_base.scss` (evidência real de mobile-first, breakpoints
nomeados, alvo de toque), `app/Identidade/Dominio/TemaPreferido.php`,
`app/Identidade/Aplicacao/AlternarTemaPreferido.php`,
`app/Http/Controllers/Identidade/TemaPreferidoController.php` (estado real do mecanismo
de tema antes desta investigação), `package.json`/`vite.config.js`/`resources/css/
app.css` (confirmação de que Tailwind já está no scaffold, não usado por nenhuma view).
