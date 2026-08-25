# INV-RMA-07 — Evolução do RMA V3 para SaaS multiempresa

Data: 2026-08-25. Investigação formal, aberta e concluída nesta sessão (mesmo padrão de
`INV-RMA-05`/`INV-RMA-06`: investigação e parecer no mesmo documento). Numeração: `04`
foi reservado e nunca escrito (citado em `inventario-tecnico-15.9.7.md` e no `INV-RMA-00`
como investigação de interface/identidade visual — acabou absorvida por
`inventario-visual-tema-{v1,v2}.md` e pela revisão de UI/UX da Fase 8, nunca virou
documento próprio). `05`/`06` existem. Este documento usa `07` para não reabrir/reusar um
número já citado em referências cruzadas antigas.

**Pergunta que este documento responde:** como transformar o RMA V3, depois da baseline
de paridade, em um SaaS multiempresa sem destruir as fronteiras arquiteturais e a
rastreabilidade da reconstrução atual?

**Regra de ouro desta investigação:** nada aqui é implementação. Nenhuma migration,
`company_id`, model `Company`, middleware de tenancy, plano ou billing é criado nesta
sessão. O que existe é decisão registrada + pendência registrada, no mesmo padrão de
confiança usado no resto do projeto: evidência do legado > desenho já fechado > inferência
justificada > pendência explícita.

---

## 1. Estado atual do RMA V3 (Trilha A)

Confirmado diretamente no Git nesta sessão (`git log --oneline -40`, `git status`,
execução da suíte de testes):

- **Fase 1 (Identidade)** — implementada, testada, commitada (`586513f`). `User`,
  `Papel` (enum sem backing, 5 casos), `TemaPreferido`, `TentativaDeAcesso`, gestão de
  usuários, troca de senha (TEMA V1 como especificação, corrige RN-21).
- **Fase 2 (Parceiros)** — implementada, testada, commitada (`628475d`). `Cliente`,
  `Fabricante`, `Fornecedor`, `AssistenciaTecnica`, `App\Compartilhado\Uf`,
  `EncontrarOuCriarCliente`.
- **Fase 3 (Rma núcleo)** — implementada, testada, commitada (`b2b3e74`).
  `App\Rma\{Dominio,Aplicacao,Infraestrutura}`, única fronteira completa com interface
  de repositório (`RepositorioDeRmas`) do projeto até agora.
- **Suíte de testes** — verificada pessoalmente por mim (não só relato de agente)
  imediatamente após o commit `b2b3e74`: **85/85 verdes, 189 assertions**. Não repeti a
  execução nesta sessão porque a Fase 4 está em implementação ativa em background no
  momento desta investigação (working tree com alterações não commitadas em
  `app/Identidade/Dominio/Papel.php`, `app/Rma/Dominio/Rma.php`,
  `app/Models/Rma.php`, `tests/Unit/Identidade/PapelTest.php`, mais os arquivos novos
  `Status.php`/`Solucao.php`/a migration do ciclo de vida) — rodar `sail test` agora
  correria o banco de teste compartilhado (`DB_DATABASE=testing` no `phpunit.xml`, mesmo
  servidor MySQL, não SQLite isolado) contra dois processos concorrentes, arriscando
  falso-negativo em ambos. O número 85/85 continua sendo o estado real e verificado da
  Trilha A até a Fase 4 fechar.
- **Fases 4-8** — especificadas a nível arquivo-por-arquivo em `INV-RMA-05` §9-§13, com
  OpenSpec completo. Fase 4 em implementação ativa (não commitada ainda).
- **Fases 9-10** — especificadas em `INV-RMA-05` §14-§15 + `INV-RMA-06` (mapa
  campo-a-campo) + OpenSpec (`migracao-v2-v3`, `qa-paridade`), ambas ainda não
  codificadas — Fase 9 formalmente bloqueada até as Fases 4/5 existirem em código (os
  enums `Status`/`Solucao`/`Origem`/`Prioridade`/`StatusDeLancamento` que o migrador
  traduz).
- **Documentação reconciliada nesta sessão:** `PLAN.md` e `PLANO-ATAQUE.md` estavam
  desatualizados desde o commit `081cf35` (congelados em "reconstrução ainda não
  iniciada") — corrigidos separadamente desta investigação (ver commit de reconciliação).
  `docs/produto/checklist-master-v3.md` já estava corrente (mantido a cada fase) e serviu
  de fonte de verdade operacional durante esta sessão, conforme a ordem de confiança
  código+testes → OpenSpec → checklist → PLAN/PLANO-ATAQUE.
- **Divergência de código real encontrada e corrigida:** `RepositorioDeRmas` (Fase 3)
  ganhou um método `atualizar()` durante a implementação real, ausente do snippet
  original de `openspec/changes/rma-cadastro-e-localizacao/design.md` — o próprio código
  documentava isso em comentário, mas o `design.md` não refletia. Corrigido nesta sessão
  (edição pontual, não reescrita).

## 2. Baseline arquitetural vigente (não reaberta sem motivo)

Confirmada em `INV-RMA-05` e no código real das Fases 1-3: Laravel 13/PHP 8.3/MySQL 8.4
via Sail; monólito modular (`app/{Modulo}/{Dominio,Aplicacao,Infraestrutura}` +
`app/Compartilhado/`) aplicado proporcionalmente — só o módulo `Rma` tem fronteira
completa, `Identidade`/`Parceiros` usam Eloquent direto; autenticação Laravel nativa
(`Auth`/`Hash`, nunca sessão manual); Blade/Vite/Sass/Bootstrap para fidelidade visual
(Fase 8); enums sem número mágico para todo conceito de domínio fechado (`Papel`,
`TemaPreferido`, `Uf`, e os que a Fase 4 está introduzindo agora — `Status`, `Solucao`);
legado preservado em repositório próprio, nunca modernizado; migração V2→V3 é requisito
de produto explícito, não script descartável; Trilha A (reconstrução) e Trilha B
(evolução) mantidas separadas desde o início do projeto. Nenhuma dessas decisões é
reaberta por esta investigação — todas seguem vigentes e são o ponto de partida do
raciocínio abaixo.

## 3. O que significa "SaaS multiempresa" aqui

Não é reescrever o produto. É acrescentar uma fronteira de isolamento (tenant) sobre um
produto cujo domínio (RMA, ciclo de vida, parceiros, alertas) já está sendo reconstruído
fielmente. O RMA continua sendo RMA — SaaS não é ERP (regra do usuário, §22 do prompt que
originou esta investigação). A pergunta técnica central é **onde mora a fronteira de
tenant** e **como garantir por construção que ninguém a esquece**, não "quais features
novas o produto ganha".

## 4. Fronteira do tenant — classificação entidade por entidade

| Entidade | Classificação | Justificativa |
|---|---|---|
| `User` | **Pertence ao tenant, mas via vínculo, não posse exclusiva** | Ver §7 — um usuário pode legitimamente participar de mais de uma empresa (evidência real: `EVO-SAAS-001` já registra que o legado opera múltiplas empresas do mesmo grupo — Cellsystem/Expert/Registros Ativos/Informática — sob um único banco; é razoável que a mesma pessoa administre mais de uma). Recomendação em §7. |
| `Papel` | **Atributo do vínculo User×Company, não do User global** | Consequência direta de `User` ser multi-tenant: uma pessoa pode ser `Supervisor` na Empresa A e `Operador` na B. Ver §8. |
| `Cliente` | **Pertence ao tenant** | Cada empresa tem sua própria carteira de clientes — nenhuma evidência de compartilhamento no legado (`bd.empresa` nunca cruza com `cliente`). |
| `Fabricante` | **Pertence ao tenant, com padrão de importação de catálogo de referência decidido (ver §4.1)** | O legado trata fabricante como cadastro livre por empresa (sem catálogo global). A cópia local continua pertencendo ao tenant — o que muda é a origem: em vez de o usuário digitar do zero, pode importar de um catálogo de referência da plataforma. |
| `Fornecedor` | **Pertence ao tenant** | Mesma lógica de `Fabricante`, sem a mesma pressão de catálogo global (fornecedor tende a ser relação comercial própria de cada empresa, não uma entidade "universal"). |
| `AssistenciaTecnica` | **Pertence ao tenant** | Idem `Fornecedor`. |
| `Rma` | **Pertence ao tenant** | Sem ambiguidade — é o registro central do negócio de cada empresa. |
| Histórico/modificações (`ModificacaoDeRma`, Fase 7) | **Pertence ao tenant** | Segue o `Rma` a que se refere. |
| Relatórios | **Pertence ao tenant** | Computados a partir de dado do tenant — um relatório nunca deve agregar dado de outro tenant (Eixo de teste arquitetural, §14). |
| Configurações | **Duas categorias** | Configuração de plataforma (feature flags globais, parâmetros de sistema) é **global da plataforma**; configuração de aparência/operação por empresa (ex.: preferência de tema padrão, política de garantia — `EVO-DOM-003`) é **do tenant**. Nenhuma configuração hoje é compartilhável entre tenants por design — se aparecer um caso real, tratar como exceção documentada, não regra. |
| Anotação pessoal (`users.anotacao`, Fase 1) | **Pertence ao User, que pertence ao tenant via vínculo** | Escopo transitivo — a anotação em si não precisa de coluna de tenant própria se o `User` já resolve isso via `company_user` (ver §7). |
| Tentativas de acesso (`TentativaDeAcesso`, Fase 1) | **Pertence ao tenant (via `User`), com valor agregado à plataforma** | O registro individual é do tenant (auditoria de acesso da empresa); a plataforma pode querer agregações de segurança cross-tenant (ex.: detectar padrão de ataque de força bruta) — isso é uma capacidade de observabilidade da plataforma sobre dado do tenant, não uma entidade global própria. Não implementar essa agregação agora — registrar como possível `EVO-SEG` futuro. |

### 4.1. Padrão "catálogo de referência + importação seletiva" (direção dada pelo usuário nesta sessão)

Resolve a pendência original sobre `Fabricante`/`Fornecedor`/`AssistenciaTecnica`
virarem "compartilháveis entre tenants" — a resposta não é dado compartilhado em tempo
real (nenhum tenant nunca lê o cadastro de outro, isso violaria o próprio §14 desta
investigação), é um **catálogo de referência da plataforma** (chamado aqui de "wiki" —
um repositório de registros de referência, mantido em nível de plataforma, fora do
escopo de qualquer tenant específico) do qual cada tenant **importa sob demanda,
seleção explícita, com um clique**:

- A wiki é dado **global da plataforma** — não pertence a nenhum tenant, não é
  criada/editada no fluxo normal de uso do produto por um usuário comum.
- Um usuário do tenant abre uma tela de importação (ex.: "Importar fabricantes da
  wiki"), vê a lista de registros de referência disponíveis, **seleciona quais quer**
  (não é um "importar tudo" automático) e confirma.
- A importação **copia** o registro selecionado para dentro do tenant, criando um
  `Fabricante` (ou `Fornecedor`/`AssistenciaTecnica`) normal, pertencente ao tenant,
  igual a qualquer outro criado manualmente. **Não é uma referência viva** — depois de
  importado, o registro do tenant é independente; uma edição posterior na wiki não
  propaga automaticamente para os tenants que já importaram (evita a classe de bug
  "um tenant edita e quebra o cadastro de todos os outros" e mantém o isolamento de
  dado do tenant como propriedade absoluta, não relativizada por uma FK cross-tenant).
- Consequência arquitetural direta: a entidade de catálogo (`Fabricante` etc.) continua
  **pertencendo ao tenant** exatamente como já classificado na tabela acima — o padrão
  novo é só a ORIGEM do dado na criação (digitado manualmente vs. importado da wiki via
  clique), não uma mudança na fronteira de tenant já decidida. A wiki em si é uma nova
  entidade de plataforma (`WikiDeFabricantes` ou nome melhor a decidir na
  implementação), fora da fronteira de qualquer tenant, com seu próprio fluxo de
  curadoria (quem alimenta a wiki é uma decisão de produto adiada, não de arquitetura —
  ver pendência abaixo).
- Não implementar agora — mesmo raciocínio de momento do §13: é Trilha B, só depois da
  baseline de paridade.
| Arquivos/anexos futuros | **Pertence ao tenant** | Isolamento de storage por tenant é parte da mesma fronteira — ver §9. |

## 5. Estratégia de banco de dados

Comparação objetiva dos 3 modelos, nos critérios pedidos:

| Critério | A — 1 banco, N tenants (`tenant_id`) | B — 1 banco por empresa | C — híbrido |
|---|---|---|---|
| Complexidade de código | Baixa (uma conexão, scoping por query) | Alta (roteamento de conexão dinâmico em todo lugar) | Alta (dois caminhos de código a manter) |
| Migrations | Uma rodada, todo tenant migra junto | N rodadas — drift entre bancos é risco real | Duas rotinas de migration |
| Backup/restore | Simples, granularidade por linha exige filtro | Simples por empresa, mas N backups a orquestrar | Duas rotinas |
| Deploy | Um deploy afeta todos, simples | Deploy por banco é mais controlável, mas operacionalmente caro em escala | Complexidade combinada |
| Custo de infraestrutura | Baixo, cresce devagar com N tenants | Alto, cresce linear com N tenants (N conexões, N bancos ativos) | Médio |
| Relatórios cross-tenant (uso da plataforma, nunca do tenant) | Trivial (uma query com `GROUP BY tenant_id`) | Exige agregação distribuída | Depende de qual lado o tenant está |
| Suporte (debugar problema de 1 cliente) | Fácil (filtrar por `tenant_id`) | Fácil (já isolado fisicamente) | Depende |
| Segurança/isolamento | Depende inteiramente de disciplina de aplicação (risco real, ver §6) | Isolamento físico forte por padrão | Isolamento forte só para quem está no modelo B |
| Crescimento (dezenas/centenas de tenants pequenos, perfil deste produto) | Ótimo | Ruim (custo/operação não escala) | Médio |
| Onboarding de novo cliente | Instantâneo (uma linha nova) | Lento (provisionar banco novo) | Depende |
| Migração futura (se precisar isolar fisicamente um tenant grande depois) | Possível migrar um tenant específico para banco próprio depois, sob demanda | N/A, já é o modelo | N/A |

**Recomendação: Modelo A (banco compartilhado, `tenant_id`)**, com a ressalva do §6
(isolamento não pode depender de disciplina humana). O perfil esperado do produto —
oficinas/assistências técnicas de porte pequeno/médio, muitos tenants pequenos, não
poucos tenants gigantes — favorece fortemente A sobre B em custo operacional e
velocidade de onboarding. B só se justificaria se surgisse um requisito real de
isolamento físico regulatório por cliente (não há evidência disso). C combina as
desvantagens dos dois sem benefício claro nesta fase — não recomendado. Se um tenant
específico crescer a ponto de justificar isolamento físico, o modelo A não impede migrar
esse tenant para um banco próprio depois — é uma porta que continua aberta, não fechada
por essa escolha.

## 6. Isolamento por construção — mecanismos a investigar (não implementar)

Não é aceitável depender de `Rma::where('company_id', $companyId)` espalhado pelo
código — um único `WHERE` esquecido é um vazamento de dado entre empresas. Mecanismos
avaliados:

- **Global Scope automático** (`BelongsToTenant` trait aplicado ao model Eloquent,
  registrando um `Illuminate\Database\Eloquent\Scope` que injeta `WHERE tenant_id = ?`
  em toda query, incluindo `find()`) — mecanismo central recomendado. Cobre o caso comum
  sem exigir que cada Controller/caso de uso lembre de filtrar.
- **`TenantContext`** (objeto resolvido uma vez por request, bound no container como
  singleton, populado por middleware logo após autenticação, a partir do vínculo
  `company_user` ativo do usuário) — é a fonte única de verdade de "qual é o tenant
  corrente"; o Global Scope acima lê dele, não de `session()`/`request()` direto
  espalhado.
- **Middleware de resolução de tenant** — roda antes de qualquer Controller, falha
  explicitamente (403/redirect) se o usuário não tiver vínculo ativo com nenhuma
  empresa, ou se a rota exigir uma empresa que o usuário não pertence.
- **Route model binding customizado** (`resolveRouteBinding` sobrescrito nos models
  tenant-scoped) — fecha o buraco de "usuário troca o ID na URL manualmente": mesmo que
  o Global Scope falhe por algum motivo, o binding de rota já rejeita um ID de outro
  tenant como 404, nunca 200 com dado errado.
- **Policies** — continuam sendo a camada de autorização por ação (já é o padrão do
  projeto desde a Fase 1/`UserPolicy`), mas **não** devem ser a única linha de defesa
  contra vazamento de tenant — policy decide "pode fazer X", Global Scope + route
  binding decidem "esse registro existe pra este usuário". Redundância deliberada
  (defesa em profundidade), não desperdício.
- **Model Observers** — usados para preencher `tenant_id` automaticamente no `creating`
  (a partir do `TenantContext`), nunca aceito como input do formulário/request —
  elimina a classe de bug "usuário mandou `tenant_id` errado no payload".
- **Não adotar** uma biblioteca de tenancy de terceiros no automático (`stancl/tenancy`
  e afins) sem avaliação própria — decisão já registrada em `EVO-SAAS-001` antes desta
  investigação, mantida: o modelo A (shared database) é simples o bastante para não
  precisar de uma dependência pesada; reavaliar só se a complexidade real superar o
  esperado.

**Atenção especial ao módulo `Rma`:** por já ter fronteira `Dominio/Aplicacao/
Infraestrutura`, o ponto de inserção natural do tenant scoping é dentro de
`RmasEmBanco` (a única classe que toca Eloquent) — o objeto de domínio `Dominio\Rma`
continua puro e nunca precisa saber que tenant existe; é a infraestrutura que aplica o
filtro. Para `Identidade`/`Parceiros` (sem fronteira própria), o Global Scope no
Eloquent model é o mecanismo direto, já que eles usam Eloquent nos Controllers/casos de
uso sem uma camada de infra dedicada — coerente com a proporcionalidade já decidida em
`INV-RMA-05` (não forçar toda a aplicação a ganhar uma fronteira `Dominio/Infraestrutura`
só por causa de tenancy).

## 7. `User` × `Company`

| Alternativa | Descrição | Avaliação |
|---|---|---|
| A — `users.company_id` | Um usuário pertence a exatamente uma empresa | Mais simples de implementar, mas contradiz evidência real do próprio legado (grupo econômico com múltiplas empresas sob um usuário administrador comum) |
| B — `users`/`companies`/`company_user` | Um usuário pode participar de várias empresas, com papel por vínculo | Mais flexível, custo de implementação moderado (uma tabela pivot a mais, resolução de "empresa ativa" na sessão) |
| C — outra solução | Não há evidência de domínio que justifique algo além de A/B | Descartada por falta de evidência, não por preguiça de investigar |

**Recomendação: Alternativa B**, mas com uma simplificação para a primeira versão: a
UI/UX da primeira versão do SaaS pode tratar o caso comum (um usuário, uma empresa) sem
exigir seletor de empresa visível — o vínculo múltiplo existe no schema desde o início
(evita migração de schema dolorosa depois), mas o fluxo de "trocar de empresa ativa" só
precisa aparecer na interface quando o primeiro usuário real com múltiplos vínculos
existir. Isso evita o erro comum de escolher A por parecer mais simples agora e precisar
de uma migração de dado + reescrita de autorização inteira quando o primeiro caso B
aparecer de verdade — que, pela própria evidência do legado, é bem provável de
acontecer cedo (grupo econômico já existia na V2).

## 8. Impacto sobre `Papel`

Consequência direta de §7: se `User` pode pertencer a mais de uma empresa, `Papel` **não
pode continuar sendo uma coluna de `users`** — precisa migrar para a tabela pivot
(`company_user.papel`). Isso não é uma correção nem uma crítica à Fase 1 — na época da
implementação (baseline single-tenant), `users.papel` era exatamente correto; é o tipo
de decisão que só faz sentido reavaliar quando a fronteira de tenant é introduzida de
verdade, não antes (coerente com a regra "não reabrir decisão madura sem evidência" —
aqui a evidência é a introdução formal de multiempresa, que ainda não aconteceu). O
enum `Papel` em si (5 casos, métodos nomeados, sem número mágico) não muda — só a
tabela onde o valor é gravado.

## 9. Superadmin — plataforma × tenant

Dois conceitos de autorização deliberadamente separados, não um só `Papel` esticado:

- **Platform authorization** — "pode criar empresa, bloquear empresa, ver status de
  assinatura, dar suporte, mexer em configuração global". Não implica acesso a nenhum
  `Rma`/`Cliente` de nenhuma empresa — é autorização sobre a tabela `companies` e
  metadados de plataforma, nunca sobre dado de tenant.
- **Tenant authorization** — o `Papel` já existente (Fase 1), agora escopado por
  `company_user` (§8) — decide o que um usuário pode fazer **dentro** da empresa a que
  está vinculado.

Recomendação: **não sobrecarregar `Papel::SuperAdministrador`** com poder de
plataforma — criar um mecanismo ortogonal (um guard/flag de plataforma separado, ex.
`users.eh_administrador_de_plataforma` ou uma tabela própria) que não interfere no
`Papel` de tenant. Um administrador de plataforma que também usa o produto operacionalmente
dentro de uma empresa específica ainda precisa de um `Papel` normal nessa empresa — os
dois papéis são independentes.

## 10. Numeração do RMA

Recomendação: **numeração por empresa**, não global — coerente com a expectativa de
produto (cada empresa quer ver "RMA 000001, 000002..." começando do seu próprio zero,
não um número que carrega histórico de outras empresas visível na UI). Implementação
recomendada (não feita agora): uma tabela de contador dedicada por empresa
(`contadores_de_rma`, `company_id` + `proximo_numero`), incrementada dentro de uma
transação com lock (`SELECT ... FOR UPDATE` ou equivalente Eloquent), nunca
`MAX(numero) + 1` (race condition sob concorrência real). O `id` interno (chave primária
técnica) continua global e nunca exposto ao usuário — separa completamente identidade
técnica de numeração de negócio, o que também simplifica a migração do legado (RMAs
importados da V2 recebem `numero_legado` — já especificado em `INV-RMA-06` — e podem ser
recontados como "numeração do tenant CellSystem" nesse momento, sem conflito).

## 11. Migração do cliente original (CellSystem)

A V3 nasce single-company. Quando a fronteira de tenant for introduzida, os dados
migrados da V2 (Fase 9, já especificada) precisam pertencer a um tenant inicial.
Estratégia recomendada: criar a empresa `CellSystem` como o primeiro tenant (seed
determinístico, não digitado à mão) no momento em que a Fase de tenancy for
implementada, e todo o processo de migração V2→V3 (`migracao-v2-v3`, já especificado em
`INV-RMA-06`) recebe um passo adicional trivial — carimbar `company_id` do tenant
CellSystem em cada linha importada. Isso **não** exige redesenhar o migrador já
especificado; é uma extensão pequena e localizada (mesma classe `TabelaDeTraducao`
citada em `INV-RMA-06`/`INV-RMA-05` §14 ganha mais uma tradução: "toda linha migrada
pertence ao tenant CellSystem"). Registrado aqui como nota de compatibilidade futura no
próprio OpenSpec de migração (`openspec/changes/migracao-v2-v3/proposal.md`), sem
implementar nada agora.

## 12. Pendências reais — não decididas por inferência

1. **`Fabricante`/`Fornecedor`/`AssistenciaTecnica` como catálogo compartilhável entre
   tenants — padrão de arquitetura resolvido em 2026-08-25 (§4.1): catálogo de
   referência da plataforma ("wiki") + importação seletiva por clique, cópia
   independente por tenant, nunca referência viva cross-tenant.** O que continua
   pendente (decisão de produto, não de arquitetura): quem/como a wiki é alimentada
   (curadoria manual da plataforma? agregação anônima do que os tenants já cadastraram?
   catálogo de terceiros?) — sem evidência para decidir agora, e não bloqueia o resto do
   desenho de tenancy.
2. **Agregação de segurança cross-tenant sobre `TentativaDeAcesso`** (detecção de padrão
   de ataque na plataforma) — capacidade de plataforma, não de tenant; sem evidência de
   necessidade imediata, registrada como possível `EVO-SEG` futuro.
3. **Formato exato do flag/mecanismo de "administrador de plataforma"** (coluna vs.
   tabela própria vs. guard dedicado do Laravel) — decisão de implementação, não de
   arquitetura; só deve ser fechada quando a Fase de tenancy realmente começar a ser
   codificada, com o schema de `companies`/`company_user` já concreto na frente.
4. **Se/quando um tenant específico crescer a ponto de justificar banco próprio** (saída
   do modelo A para um caso isolado) — sem evidência de que isso vai acontecer; a
   arquitetura recomendada em §5 não fecha essa porta, mas não há decisão a tomar agora.

## 13. Momento de introduzir multitenancy

| Estratégia | Descrição | Avaliação |
|---|---|---|
| A — Terminar Fases 1-10 + QA de paridade primeiro, depois SaaS | Tenancy só começa depois da baseline validada | Menor risco de contaminar a reconstrução; todo o trabalho de paridade (testes, OpenSpec, `paridade-v2-v3.md`) continua válido sem retrabalho |
| B — Introduzir fundação multiempresa antes de concluir todas as fases | `company_id` entra durante a Trilha A | Risco alto de contaminação — cada fase ainda não implementada (4-10) teria que nascer já tenant-aware, mas a baseline de paridade compara contra um legado que é fundamentalmente single-tenant; mistura os dois problemas exatamente como o usuário pediu para evitar |
| C — Preparar fronteiras agora (esta investigação), implementar persistência só depois da baseline | Investigação/decisão de arquitetura adiantada, código de tenancy não | É exatamente o que este documento faz |

**Recomendação: Estratégia C, convergindo para A na prática de código.** Esta
investigação (o "preparar fronteiras agora" de C) já está feita — a classificação de
entidades, a estratégia de banco, os mecanismos de isolamento e as decisões de
`User`×`Company`/`Papel`/superadmin estão registradas e não precisam ser
re-investigadas quando a Trilha B começar de verdade. Mas nenhuma linha de código de
tenancy deve ser escrita antes da baseline de paridade (Fases 1-10 + QA, `INV-RMA-05`
§15) estar validada — o custo de retrabalho de introduzir tenancy depois é baixo
justamente porque a arquitetura modular já existente (fronteira própria do módulo `Rma`,
Policies centralizadas desde a Fase 1, ausência de `Rma::where()` solto espalhado pelo
código porque tudo passa por `RepositorioDeRmas`) foi desenhada de um jeito que absorve
essa mudança sem reescrita ampla. O risco de misturar os dois problemas agora (rodar
atrás de paridade E de isolamento multiempresa ao mesmo tempo) é maior que o custo de
esperar.

## 14. Testes arquiteturais de isolamento (a garantir quando a Fase de tenancy existir)

Os cenários pedidos (Empresa A cria Cliente A; Empresa B não acessa Cliente A; RMA de
outra empresa não é encontrado nem por busca nem por URL manual; anexo de outra empresa
negado; busca/relatório nunca cruza tenant) devem virar uma suíte própria e obrigatória
— não testes esparsos por feature. Recomendação de formato: uma classe base de teste
(`TestesDeIsolamentoDeTenant`) parametrizada por model tenant-scoped, rodando
automaticamente os mesmos 3 asserts (criar em A, tentar ler/editar/listar como B, ID
direto na URL como B) para cada model marcado com o trait `BelongsToTenant` — garante
que todo model novo que ganha a trait automaticamente ganha a cobertura, sem depender de
alguém lembrar de escrever o teste manualmente. Este é o eixo de teste que a Fase 10
(QA de paridade) **não** cobre — QA de paridade é sobre fidelidade ao legado
single-tenant; isolamento de tenant é uma garantia nova, exclusiva da Trilha B, e deve
ganhar seu próprio gate de conclusão quando chegar a hora (não misturar com o gate de
`INV-RMA-05` §15).

## 15. Riscos

- **Técnico:** esquecer o Global Scope num model novo é o risco mais provável e mais
  grave — mitigado pelo pacote de mecanismos do §6 (scope + route binding + observer),
  nunca um só.
- **Técnico:** `Papel` migrar de `users` para `company_user` é uma migração de dado real
  quando acontecer — pequena agora (poucos usuários de teste), mas deve ser planejada
  como migration de produção cuidadosa quando a Trilha B começar de fato.
- **Produto:** overengineering — construir a fundação multiempresa antes de ter um
  segundo cliente real validando a necessidade. Mitigado pela recomendação do §13
  (esperar a baseline, não adiantar código).
- **Produto:** o risco oposto também existe — atrasar demais a decisão de arquitetura de
  tenant a ponto de a Trilha A terminar com padrões (ex.: `users.company_id` improvisado
  por pressão de prazo) que contradizem o que esta investigação já recomenda. Mitigado
  por este documento existir agora, mesmo sem código.

## 16. Decisões recomendadas (podem ser tratadas como vigentes a partir de agora)

- Modelo de banco: shared database + `tenant_id` (Modelo A, §5).
- Isolamento: Global Scope + `TenantContext` + route model binding customizado + Model
  Observer, nunca só disciplina de código (§6).
- `User`×`Company`: many-to-many via `company_user`, com `Papel` migrando para o vínculo
  (§7/§8).
- Superadmin: autorização de plataforma separada de `Papel` de tenant (§9).
- Numeração de RMA: por empresa, via contador transacional dedicado, nunca `MAX+1` (§10).
- Momento de implementação: depois da baseline de paridade (Fases 1-10 + QA), nunca
  durante (§13).
- Migração CellSystem: primeiro tenant seedado, migrador ganha um passo trivial de
  carimbo de `company_id` (§11) — sem redesenhar `INV-RMA-06`.
- Catálogo de referência compartilhável (`Fabricante`/`Fornecedor`/
  `AssistenciaTecnica`): padrão "wiki de plataforma + importação seletiva por clique,
  cópia independente por tenant" (§4.1/§12.1) — não altera a fronteira de tenant já
  decidida (a entidade continua pertencendo ao tenant), só a origem do dado na criação.

## 17. Decisões adiadas (sem evidência suficiente agora)

- Curadoria da wiki de catálogo (quem/como alimenta os registros de referência) —
  arquitetura do mecanismo de importação já decidida (§4.1), só a operação de conteúdo
  fica pendente (§12.1).
- Agregação de segurança cross-tenant sobre tentativas de acesso (§12.2).
- Formato exato do mecanismo de administrador de plataforma (§12.3).
- Gatilho para eventualmente isolar fisicamente um tenant específico (§12.4).

## 18. Impacto sobre F1-F10

- **Fase 1 (Identidade)** — a mais afetada quando a Trilha B começar: `Papel` migra de
  `users` para `company_user`; nenhuma mudança necessária agora.
- **Fases 2/3 (Parceiros/Rma núcleo)** — ganham o trait `BelongsToTenant`/coluna
  `tenant_id` quando a Trilha B começar; nenhuma mudança de comportamento percebido.
- **Fases 4-7** — não afetadas na lógica de negócio (ciclo de vida, alertas, créditos,
  auditoria continuam iguais); ganham isolamento de tenant "de graça" assim que o módulo
  `Rma` (do qual dependem) tiver a fronteira de tenant aplicada na infraestrutura.
- **Fase 8 (Temas)** — sem impacto imediato; personalização visual por empresa (ex.
  logo/cores próprias) é um `EVO` futuro plausível, não parte desta investigação.
- **Fase 9 (Migração)** — ganha um passo adicional pequeno (carimbo de tenant), sem
  redesenho (§11).
- **Fase 10 (QA de paridade)** — gate de conclusão continua sendo só sobre paridade
  single-tenant; isolamento de tenant ganha seu próprio gate depois, na Trilha B (§14).

## 19. Backlog evolutivo — itens complementados/criados

- **`EVO-SAAS-001`** (já existente) — complementado com as decisões concretas desta
  investigação (modelo de banco, mecanismo de isolamento, `User`×`Company`, superadmin,
  numeração) — ver edição em `docs/produto/backlog-evolutivo.md`. Não duplicado.
- **`EVO-SAAS-002`** (novo, criado nesta sessão) — "Catálogo de referência da
  plataforma com importação seletiva" (§4.1). Capacidade genuinamente diferente de
  `EVO-SAAS-001`: não é infraestrutura de isolamento de tenant, é um produto de
  conteúdo (catálogo compartilhado + fluxo de importação por clique) que só faz sentido
  depois que a fronteira de tenant existir. Depende de `EVO-SAAS-001`.
- **`EVO-SAAS-003`** (novo, criado nesta sessão) — "Comunidade/fórum inter-tenant"
  (usuários de empresas diferentes conversando entre si, ideia registrada pelo usuário
  explicitamente como evolução distante, sem detalhe de especificação pedido agora). É
  o único caso identificado até aqui onde cruzar a fronteira de tenant é a intenção do
  produto, não uma falha de isolamento — precisa de um modelo de identidade de
  comunidade separado do dado operacional quando for especificado de verdade.

## 20. Referências

`docs/produto/backlog-evolutivo.md` (`EVO-SAAS-001`), `docs/legado/
modelo-dominio-rma-legado.md` (§Empresa, origem do achado de grupo econômico),
`docs/arquitetura/INV-RMA-05-arquitetura-proposta.md` (arquitetura vigente),
`docs/arquitetura/INV-RMA-06-estrategia-reconstrucao.md` (migração, ponto de integração
do §11), `docs/produto/checklist-master-v3.md` (estado operacional das fases).
