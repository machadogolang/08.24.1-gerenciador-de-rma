# Roadmap — Painel de configuração de admin + sistema de anexos (Trilha B)

Data: 2026-08-25. Documento executivo que amarra as duas evoluções já investigadas em
`docs/arquitetura/INV-RMA-09-arquivos-e-configuracao-admin.md` e registradas como
`EVO-CONF-001`/`EVO-ARQ-001` em `docs/produto/backlog-evolutivo.md`, no mesmo formato de
detalhamento arquivo-por-arquivo já usado em `INV-RMA-05` §6-§15 para as Fases 1-10 da
Trilha A. **Este documento não implementa nada** — é o roteiro para quando a
implementação começar.

## Regra de ouro — não implementar antes da hora

Nenhuma linha de código de produto (migration, model, view, Controller, Service
Provider, config publicável) das duas fases abaixo é escrita antes da baseline de
paridade da Trilha A estar validada — **Fases 1-10 completas, Fase 10/QA de paridade
fechada** (`INV-RMA-05` §15). Mesma regra já aplicada a `EVO-SAAS-001` (SaaS,
`INV-RMA-07` §13) e `EVO-UX-001` (Tema V3, `INV-RMA-08` §8). No momento em que este
documento foi escrito, a Trilha A está com Fases 1-9 prontas e a Fase 10 pode estar em
andamento em paralelo — **não editar/consultar como concluída até confirmação
explícita** (checar `docs/produto/checklist-master-v3.md`, que este documento não
edita).

## As duas fases, em ordem (pedido explícito do usuário)

```
Fase A — Configuração de admin     openspec/changes/configuracao-admin/
Fase B — Anexos de arquivo no RMA  openspec/changes/anexos-de-rma/
```

### Fase A — Painel de configuração de admin

Módulo novo `App\Configuracao`, fronteira `Dominio/Aplicacao/Infraestrutura` própria.
Resolve 3 parâmetros de negócio hoje hardcoded/só-`.env` (destinatário de notificação,
threshold de urgência R$75, cidade de consolidação de frete Porto Alegre), com o padrão
"publicar/efetivo" do CONAHOM aplicado proporcionalmente (tela única, sem hub de 6
seções, sem segredo separado). Detalhamento completo:
`openspec/changes/configuracao-admin/{proposal,design,tasks}.md`.

### Fase B — Sistema de anexos de arquivo no RMA

Entidade `AnexoDoRma` **dentro** do módulo `app/Rma/` já existente (não um módulo
central `Arquivos`/`Armazenamento`), interface mínima de storage com adaptador local,
seção de anexos aditiva na tela de detalhe do RMA. Detalhamento completo:
`openspec/changes/anexos-de-rma/{proposal,design,tasks}.md`.

## Por que esta ordem, e a dependência real entre as duas

**Pedido explícito do usuário: painel admin primeiro.** Tecnicamente, as duas fases são
**independentes uma da outra** — nenhuma classe/tabela de `Configuracao` é lida por
`AnexoDoRma`, e nenhuma classe/tabela de `AnexoDoRma` é lida por `Configuracao`. Poderiam
ser implementadas em qualquer ordem, ou em paralelo por dois agentes/desenvolvedores
diferentes, sem conflito de arquivo (`app/Configuracao/` vs. `app/Rma/Aplicacao/`,
`app/Rma/Dominio/`, `app/Rma/Infraestrutura/` — únicos pontos de toque comuns são
`routes/web.php` e `bootstrap/providers.php`, ambos aditivos, sem risco real de merge
conflict destrutivo).

A única dependência real registrada é a ordem de recomendação do próprio texto acima:
"evita as duas fases de Trilha B em paralelo sem necessidade" — não é uma dependência de
dado/schema, é uma preferência de sequenciamento de trabalho (uma pessoa/agente termina
uma antes de começar a outra, para revisão e QA mais simples). Se o usuário decidir
paralelizar depois, nada no desenho técnico impede.

## O que "módulo desacoplável" significa aqui, e como cada fase garante isso

Cada módulo deve: (1) não ser dependência obrigatória de nenhum módulo das Fases 1-9;
(2) o RMA V3 continuar funcionando perfeitamente se o módulo nunca for ativado; (3) ter
fronteira de domínio proporcional (não Dominio/Aplicacao/Infraestrutura por estética);
(4) poder ser removido/desligado sem quebrar nada existente, por mecanismo técnico
explícito, não por promessa.

| | Fase A (`Configuracao`) | Fase B (`AnexoDoRma`) |
|---|---|---|
| Mecanismo de desacoplamento | Binding **opcional** no service container (`ConfiguracaoServiceProvider`) que resolve o valor efetivo e o injeta como parâmetro **opcional com default** nos 3 consumidores (`UrgenciaPorThreshold`, `EnviarNotificacaoDeConclusao`, `ConsolidarFretePorCidade`) | Isolamento por **composição aditiva** — tabela nova, Controller novo, rota nova, `@include` defensivo (`@if(Route::has(...))`) na view de detalhe |
| Por que esse mecanismo e não outro | O valor de configuração precisa mudar em **runtime** sem redeploy — feature flag simples não bastaria, o binding é a própria "chave" (Service Provider registrado ou não) | Não há necessidade de resolução em runtime — anexo é uma funcionalidade adicional inerte para o resto do domínio, remover os arquivos concretos já desliga com segurança |
| Prova exigida nas tasks | Comentar `ConfiguracaoServiceProvider` em `bootstrap/providers.php` e rodar `sail test` — deve continuar 100% verde | Comentar as rotas `rmas.anexos.*` e confirmar que `show.blade.php` renderiza sem erro |
| Nenhuma classe de `Rma` importa | `App\Configuracao\...` (acoplamento é unidirecional: `Configuracao` pode consultar `Rma`/`User` para autoria, nunca o contrário) | Nada aplicável — `AnexoDoRma` vive dentro de `Rma`, mas nenhum caso de uso pré-existente (`CriarRma`, `ReceberRma`, as 10 regras de alerta, etc.) lê/escreve `AnexoDoRma` |
| Se o módulo for apagado do disco | `UrgenciaPorThreshold::__construct(?float $threshold = null)` cai no fallback `?? 75.00` — comportamento idêntico ao pré-Fase A | `storage/app/rma/` deixa de ser escrito; nenhuma outra funcionalidade referencia esse prefixo |

**Diferença deliberada entre os dois mecanismos:** Fase A precisa de resolução em
runtime (admin pode publicar um novo valor a qualquer momento, sem redeploy) — daí o
binding de container. Fase B não tem essa necessidade (anexo é presença/ausência de
funcionalidade, não um valor que muda) — daí a composição aditiva ser suficiente e mais
simples. Escolher o mecanismo mais simples que resolve cada problema é a mesma
proporcionalidade já aplicada em toda a arquitetura (`INV-RMA-05` §2).

## Critério de "pronto para começar a codificar"

Mesmo espírito do gate de conclusão da Trilha A (`INV-RMA-05` §15/Fase 10):

1. `docs/produto/checklist-master-v3.md` confirma Fase 10 (QA de paridade) concluída e
   commitada.
2. `sail test` roda 100% verde na baseline atual, sem teste pendente/skipped não
   justificado.
3. Nenhuma pendência aberta em `docs/produto/paridade-v2-v3.md` sem decisão registrada
   (implementar / `EVO-*` / não fazer).
4. Para a Fase A especificamente: os 3 arquivos-alvo
   (`EnviarNotificacaoDeConclusao.php`, `UrgenciaPorThreshold.php`,
   `ConsolidarFretePorCidade.php`) não foram alterados por nenhuma correção de última
   hora da Fase 10 de um jeito que invalide as assinaturas assumidas em
   `openspec/changes/configuracao-admin/design.md` — se algo mudou, revisar o `design.md`
   antes de codificar, não assumir que continua válido sem checar.
5. Para a Fase B especificamente: `resources/views/rma/show.blade.php` e
   `VerDetalheDoRma.php` não sofreram redesenho estrutural na Fase 10 que invalide o
   ponto de inserção (`@include`) assumido em
   `openspec/changes/anexos-de-rma/design.md` — mesma checagem de revisão antes de
   codificar.
6. Implementação segue a ordem: Fase A completa (tasks + testes + regressão + commit)
   antes de iniciar Fase B, salvo decisão explícita do usuário de paralelizar.

## Rastreabilidade com o backlog evolutivo

`docs/produto/backlog-evolutivo.md` — `EVO-CONF-001` (categoria `EVO-DOMINIO`) e
`EVO-ARQ-001` (categoria `EVO-ARQUIVOS`) apontam para este roadmap e para os respectivos
OpenSpec. Este documento não duplica o conteúdo do backlog (origem, problema,
benefício, impacto, complexidade, risco já registrados lá) — só adiciona a visão de
sequenciamento/dependência/critério de pronto que o formato do backlog não cobre.

## Referências

`docs/arquitetura/INV-RMA-09-arquivos-e-configuracao-admin.md` (investigação e decisões
de proporcionalidade), `docs/arquitetura/INV-RMA-05-arquitetura-proposta.md` §2 (critério
de proporcionalidade de fronteira de módulo, reaplicado aqui), `docs/produto/
backlog-evolutivo.md` (`EVO-CONF-001`, `EVO-ARQ-001`),
`openspec/changes/configuracao-admin/{proposal,design,tasks}.md`,
`openspec/changes/anexos-de-rma/{proposal,design,tasks}.md`.
