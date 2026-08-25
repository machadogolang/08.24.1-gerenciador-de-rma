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

## Fase 4 — Ciclo de vida

**Data:** 2026-08-25.

**Implementado:** migration incremental `add_ciclo_de_vida_fields_to_rmas_table`
(`status`, `recebido_em`/`encaminhado_em`/`concluido_em`/`arquivado_em`, `protocolo`,
`solucao`, `snretorno`, `destinatario_type`/`destinatario_id` polimórficos);
`App\Rma\Dominio\{Status,Solucao}` (enums sem número mágico — `Status` sem backing e
sem case `Retornou`, `Solucao` backed string com os 16 valores confirmados de
`15.8.1/page/rma.php:578-595`); `App\Rma\Dominio\Rma` estendido (Fase 3 → aqui) com as
novas propriedades readonly e `comSnretornoAutoPreenchido()` (RN-15); `Papel`
estendido com `podeReverterAlemDoMesmoDia()`; `RmasEmBanco`/`Models\Rma` atualizados
para persistir/ler os novos campos (casts de `Status`/`Solucao`, `morphTo`); os 6 casos
de uso `App\Rma\Aplicacao\{ReceberRma,EncaminharRma,ConcluirRma,ArquivarRma,
ReverterRmaParaEntrada,RegistrarSolucao}` (todos usando `RepositorioDeRmas::atualizar()`
já existente da Fase 3, sem método novo por transição); evento
`App\Rma\Dominio\Eventos\RmaConcluido` (sem listener nesta fase — Fase 7 assina);
`CicloDeVidaController` + rotas + `_acoes_de_transicao.blade.php` (view mínima, sem
fidelidade visual); 8 arquivos de teste (6 feature + 2 unit) + 1 teste novo em
`PapelTest` (já existente da Fase 1).

**Decisão confirmada — `ArquivarRma` usa TEMA V2:** `LEG-RMA-014` já registrava TEMA
V1 como quebrado; esta fase confirmou a causa por leitura de código-fonte
(`14.6.1/post/arquivar.php` instancia `new controle()`, classe inexistente em
`14.6.1/banco.oo.php`, `Fatal Error` incondicional). `ArquivarRmaTest` prova a decisão:
arquiva um RMA em `Recebido` (o cenário que quebraria em TEMA V1) com sucesso, para os
três status permitidos por `Status::podeArquivar()` (`Entrada`/`Recebido`/
`Encaminhado`).

**Desvios do OpenSpec (documentados no código):**
- `Dominio\Rma::destinatario` (design.md descreve como "objeto polimórfico" único) foi
  implementado como duas propriedades readonly, `destinatarioType`/`destinatarioId`
  (string/int), em vez de um objeto Eloquent embutido — mantém o objeto de domínio
  puro (mesmo padrão já usado em `fabricanteId`/`fornecedorId`/`clienteId`: ids
  resolvidos para exibição fora do objeto de domínio). A relação Eloquent
  `morphTo('destinatario')` real vive só em `App\Models\Rma` (infraestrutura), que
  segue sendo interna a `RmasEmBanco`.
- `CicloDeVidaController::TIPOS_DE_DESTINATARIO` mapeia um rótulo curto do formulário
  (`assistencia_tecnica`/`fornecedor`/`fabricante`) para o FQCN do model Eloquent —
  não estava no `design.md` (que não detalhava o controller), necessário para não expor
  nomes de classe PHP no HTML.

**Testes:** 131/131 verdes, 289 assertions (`sail test`), mantendo os 85 das Fases 1-3.
`ConcluirRmaTest` cobre os 16 valores de `Solucao` via `#[DataProvider]` (6 que
implicam mesmo aparelho de retorno auto-preenchem `snretorno`, 10 que não implicam
ficam em branco). `ArquivarRmaTest` cobre os 3 status permitidos também via
`#[DataProvider]`. `ReverterRmaParaEntradaTest` cobre mesmo-dia (qualquer papel com
`podeGravar()`) e dia-anterior (só `SuperAdministrador`). Nota técnica: PHPUnit 12
exige o atributo `#[DataProvider]` (`PHPUnit\Framework\Attributes\DataProvider`) — a
anotação `@dataProvider` em docblock, usada no rascunho inicial destes dois arquivos,
falha silenciosamente com "too few arguments"; corrigido antes do commit.

Confirmado manualmente via `tinker`: RMA criado em `Entrada` → `ReceberRma` (status
`Recebido`, `recebido_em` preenchido) → `EncaminharRma` para uma `AssistenciaTecnica`
real (status `Encaminhado`, `destinatarioType`/`destinatarioId` preenchidos) →
`ConcluirRma` com `Solucao::Reparo` (`implicaMesmoAparelhoDeRetorno() === true`):
`snretorno` auto-preenchido com o valor de `sn` (`SN-MANUAL-123` nos dois campos),
confirmando RN-15 de ponta a ponta.

**Pendências que ficaram de fora:** nenhuma da Fase 4 propriamente dita. As 10 regras
de alerta e a classificação visual (Fase 5), NF/`lancadoretorno`/`marcarestoque`/
`prioridade` (Fase 5/6) e o envio real do e-mail de conclusão (Fase 7, que assina
`RmaConcluido`) permanecem fora de escopo por decisão já registrada no `proposal.md`.
Fidelidade visual das views fica para a Fase 8.

**Commit:** `#F4 - Ciclo de vida (receber/encaminhar/concluir/arquivar/reverter)` (ver
hash abaixo, aplicado junto com este log).

---

## Fase 5 — Alertas e regras

**Data:** 2026-08-25.

**Implementado:** migration incremental `add_alertas_fields_to_rmas_table`
(`prioridade`, `marcarestoque`, blocos de NF `nfcompra`/`nfvenda` — só compra/venda,
usados por RN-02/05/06/09 —, `lancadoretorno`, `valor` decimal(10,2) nullable);
`App\Rma\Dominio\{Origem,Prioridade,StatusDeLancamento,ClasseDeAlerta}` (enums —
`Prioridade` sem backing e sem case `Urgente` morto, RN-08; os demais backed string);
`App\Rma\Dominio\Rma` estendido com `classeDeAlerta()` (RN-11), `prazoLegal()` (RN-12) e
as novas propriedades readonly (`prioridade`, `marcarestoque`, NF compra/venda,
`lancadoretorno`, `valor`, `createdAt`); as 10 classes de regra +
`UrgenciaPorThreshold` em `app/Rma/Aplicacao/Alertas/`, cada uma lendo
`App\Models\Rma` diretamente (não passa pelo repositório — são consultas de leitura
para um painel, não casos de uso de escrita) com o filtro **inteiro no SQL** (decisão
central da fase); `Models\Rma`/`RmasEmBanco` atualizados (novos casts, novos campos
persistidos/lidos, relações `fabricante()`/`fornecedor()` para o join real de
`NaoVaiDarGarantia`); `PainelDeAlertasController` + `_painel_de_alertas.blade.php` +
rota `GET /rmas-alertas` (view mínima, sem fidelidade visual — Fase 8); 13 arquivos de
teste (10 regras + `ClasseDeAlertaTest` + `UrgenciaPorThresholdTest`, todos em
`tests/Unit/Rma/`, já que operam sobre o Eloquent model diretamente sem HTTP).

**Correção ao `design.md` feita nesta sessão (antes de codificar) — coluna `valor`
ausente:** `UrgenciaPorThreshold` (RN-12) usa `->where('valor', '>', 75.00)`, mas o
schema original desta fase não listava a coluna — divergência real, já registrada em
`INV-RMA-06` ("coordenação da coluna `rmas.valor` com a Fase 5"). Origem confirmada em
`regras-negocio-rma-legado.md` RN-12: `15.8.1/banco.php:777` (`right_urgente()`), campo
monetário real do RMA (não calculado). Adicionada ao `design.md` e à migration como
`decimal(10,2) nullable`.

**Decisão registrada — `Origem` sem cast Eloquent em `Models\Rma`:** o `design.md`
sugere `casts(): ['origem' => Origem::class]`. Investigação: `App\Rma\Dominio\Rma::
comNormalizacaoDeGravacao()` (Fase 3) tem um ramo `default` que devolve o valor de
origem original **sem alterar** quando não bate com nenhum padrão conhecido — ou seja,
texto livre fora do domínio fechado de 10 valores do enum pode legitimamente ser
persistido. Um cast Eloquent `BackedEnum` lança `ValueError` na hidratação de qualquer
registro cujo `origem` não seja um dos 10 valores — isto quebraria a leitura de RMAs
legítimos assim que o domínio real (dados migrados do legado, ou texto livre digitado
por um operador) contivesse um valor fora do enum. **Decisão adotada:** `origem`
permanece coluna string simples em `Models\Rma` (sem cast) e também em `Dominio\Rma`
(sem retipar a propriedade do construtor, para não quebrar `RmaTest`, que testa
`comNormalizacaoDeGravacao()` com valores de entrada arbitrários pré-normalização,
como `'Hitachi'`, `'CELLSYSTEM'`, `'Correios'` — nenhum deles é case do enum). As 10
regras de alerta continuam usando `Origem::Cliente`/`Origem::Licitacao` literalmente
nas queries (`whereIn('origem', [Origem::Cliente, Origem::Licitacao])`) — funciona sem
cast porque o query builder do Laravel converte `BackedEnum` para `->value` na
construção do SQL via `Illuminate\Support\enum_value()`, independente de qualquer cast
no model (confirmado lendo `vendor/laravel/framework/.../Query/Builder.php`). Em
`Rma::classeDeAlerta()` (domínio puro, sem Eloquent), a comparação usa
`Origem::Cliente->value` (string) em vez do enum diretamente, pela mesma razão. Isto é
uma correção da frase do `design.md` ("`Origem::normalizar()` passa a devolver este
enum em vez de string solta") — não existe `Origem::normalizar()` nem no `design.md`
nem implementado; a normalização real (`comNormalizacaoDeGravacao()`) continua
devolvendo string, e o enum `Origem` participa só nas bordas de leitura (queries de
alerta), não na gravação.

**Correção ao `design.md` feita durante a implementação — `UrgenciaPorThreshold`
(RN-12), condição de "prazo":** o snippet literal usa
`$q3->whereColumn('created_at', '<', now())` — sintaxe inválida (`whereColumn()`
compara duas colunas entre si, não aceita um valor `Carbon` como segundo argumento).
Além do erro de sintaxe, o sentido "`<` now()" seria um guard quase sempre verdadeiro e
comprovadamente frágil sob teste: `created_at` truncado ao segundo pode empatar com
`now()` em execuções rápidas (RefreshDatabase + factory + query no mesmo milissegundo),
produzindo falso negativo por corrida — reproduzido de fato ao rodar a suíte completa.
A leitura que faz sentido de negócio e bate com a prosa do próprio `design.md`
("`prazo` calculado como `created_at->addDays(30)`") é: o alerta significa "ainda dá
tempo de agir, mas o valor alto exige prioridade" — ou seja, o prazo legal de 30 dias
**ainda não estourou**. Implementado como `where('created_at', '>', now()->subDays(30))`
(equivalente a `!Rma::prazoLegal()->isPast()`), o que também faz o cenário de
verificação manual do `tasks.md` funcionar (RMA recém-criado aparece na listagem).
Coberto por `UrgenciaPorThresholdTest::test_nao_dispara_quando_prazo_legal_ja_estourou`.

**Correção adicional descoberta durante os testes — colunas `date` vs `now()`:**
`nfcompra_emissao`/`nfvenda_emissao` são colunas `date` (sem hora), mas as 3 regras que
as filtram (`GarantiaFornecedorExpirada`, `GarantiaFornecedorExpirandoEm30Dias`,
`NaoVaiDarGarantia`) inicialmente usavam `now()->subDays(N)` (timestamp com hora) como
limite — comparar uma data pura (meia-noite) contra um timestamp com a hora atual
deslocava o limite exato em até 1 dia dependendo da hora em que a consulta roda,
quebrando os testes de caso-limite (RMA com `nfcompra_emissao` exatamente 365 dias
atrás disparava `GarantiaFornecedorExpirada`, quando não deveria). Corrigido para usar
`today()->subDays(N)` (meia-noite) nas 3 regras — `recebido_em`/`encaminhado_em`
(colunas `dateTime`) continuam usando `now()`, que é o tipo correto para elas.

**Testes:** 190/190 verdes, 348 assertions (`sail test`), mantendo os 131 das Fases
1-4. Cada uma das 10 regras de data tem teste de caso-que-dispara, caso-que-não-dispara
e caso-limite exato (prova do operador estrito `>`/`<`, não `>=`/`<=`); para as regras
sem data contínua (`NfRetornoPendenteDeLancar`, `ProtocoloAbertoNaoEncaminhado`,
`PrioridadeAltaSemEncaminhar`, `SemNotaFiscal`, `SemNumeroDeSerie`) o "caso limite" foi
adaptado para a borda de domínio equivalente (ex.: string vazia vs `null` vs valor
"quase vazio", ou um valor de enum adjacente que não deveria bater). Nota técnica
adicional: `SemNumeroDeSerieTest` originalmente testava `sn = ' '` (um espaço) como
"não deveria disparar", mas o MySQL trata `'' = ' '` como iguais por padrão (colações
não-binárias usam `PAD SPACE`, espaços à direita não importam na comparação `=`) —
teste corrigido para `sn = '0'` (falsy em PHP, mas um valor preenchido de verdade em
SQL), provando que a regra usa comparação SQL real, não uma checagem estilo PHP.

**Testes manuais confirmados via `tinker`:** RMA criado com `origem=Cliente`,
`marcarestoque=false`, `valor=100.00`, `status=Entrada` — `UrgenciaPorThreshold::
listar()` o retorna; RMA idêntico com `valor=75.00` (exato) — não é retornado,
confirmando o operador estrito `>` na fronteira R$75.

**Pendências que ficaram de fora:** fidelidade visual das cores/CSS do painel por tema
(Fase 8); crédito de fato (`PendenteCredito`→`GeradoCredito`, Fase 6); consolidação de
frete Porto Alegre (Fase 6); envio real do e-mail de alerta (Fase 7). Todas já
registradas como fora de escopo no `proposal.md`.

**Commit:** `#F5 - Alertas e prioridade (10 regras + MARKVISION + threshold R$75)` (ver
hash abaixo, aplicado junto com este log).

**Revisão pós-fase (sessão principal, 2026-08-25):** encontrados 2 gaps reais de
cobertura de teste, ambos corrigidos com testes comportamentais novos, nenhum ajuste de
código de produto necessário:
- `NaoVaiDarGarantiaTest` cobria "MARKVISION + fornecedor Receita" mas não o outro lado
  do `OR` interno da regra ("MARKVISION + NF de compra vencida sem fornecedor Receita")
  nem o caso negativo "fabricante diferente de MARKVISION nunca dispara pela regra
  MARKVISION mesmo com as outras condições satisfeitas" nem "status fora de
  Entrada/Recebido nunca dispara". 4 testes novos adicionados.
- Nenhum teste de Feature exercitava `PainelDeAlertasController` pela camada HTTP (só
  as 10 regras isoladamente, via Unit). Criado `tests/Feature/Rma/PainelDeAlertasTest.php`
  (3 testes): RMA que dispara alerta aparece na view; RMA "tranquilo" (sem nenhuma
  condição de alerta) não aparece; usuário não autenticado é redirecionado ao login.

`sail test`: 196/196 verdes, 357 assertions (190→196, 348→357).

---

## Fase 6 — Créditos e relatórios

OpenSpec: `openspec/changes/rma-creditos-e-relatorios/{proposal,design,tasks}.md` (tudo
`[x]`). Arquivo por arquivo detalhado em `INV-RMA-05` §11. Cobre `LEG-RMA-036` a `039` e
`048` — reconstrói só a intenção do módulo de créditos quebrado em TEMA V2 (um fluxo
único de crédito, não as 3 sub-rotas `pendentes/usados/disponíveis`, que estão
quebradas mesmo em TEMA V2 e nunca existiram em TEMA V1). Não introduz entidade nova:
consultas de leitura e um controle de flag sobre o agregado `Rma` já maduro depois da
Fase 5, coerente com `INV-RMA-05` §3 ("Créditos"/"Relatórios" não são módulo próprio).

**Migration e domínio:** `2026_08_30_000000_add_credito_fields_to_rmas_table.php`
adiciona `credito_disponivel boolean default false`. `Dominio\Rma` ganhou a propriedade
readonly `creditoDisponivel` (default `false`) como **último** parâmetro do
construtor — decisão deliberada para não quebrar nenhum `new Rma(...)` já existente nas
Fases 3-5 (`RegistrarSolucao`, `CriarRma`, `EditarRma` etc., todos com argumentos
nomeados, mas o default evita qualquer regressão de call site que não passe o novo
campo). Os dois métodos puros que reconstroem o objeto
(`comNormalizacaoDeGravacao()`, `comSnretornoAutoPreenchido()`) foram atualizados para
passar `creditoDisponivel: $this->creditoDisponivel` adiante explicitamente — sem essa
linha, editar ou concluir um RMA que já tivesse crédito marcado disponível apagaria o
flag silenciosamente (o default `false` do construtor venceria). `RmasEmBanco` e
`Models\Rma` foram estendidos em conjunto (`paraArray()`/`paraDominio()`, `$fillable`,
cast `boolean`), mesmo padrão das extensões incrementais das Fases 4-5.

**`MarcarCreditoDisponivel`:** implementado literalmente como no `design.md` —
`abort_unless($ator->papel->podeGravar(), 403)`,
`abort_unless($rma->solucao === Solucao::GeradoCredito, 422)`, grava
`credito_disponivel=true`. **Sem transição automática**
`PendenteCredito`→`GeradoCredito`→`credito_disponivel=true`: confirmado que o legado
também não automatiza (controle manual em duas camadas independentes, ver
`modelo-dominio-rma-legado.md`); `EVO-AUT-002` já registra a automação como melhoria
futura, deliberadamente não implementada nesta fase.

**`AguardandoCredito`:** colocada em `app/Rma/Aplicacao/Alertas/` (não em
`Relatorios/`), listando `solucao=PendenteCredito` — mesma família e disciplina de
filtro inteiro no SQL das 10 regras de alerta da Fase 5, reforçando por construção que
crédito não é um módulo próprio.

**3 relatórios** (`app/Rma/Aplicacao/Relatorios/`):
`RelatorioCreditosDisponiveis` (`credito_disponivel=true`, sem parâmetros);
`RelatorioProdutosEmEstoqueParaContagem` (`marcarestoque=true` + filtro de `Status`
opcional, configurável pelo usuário via query string — não hardcoded como no legado);
`RelatorioProdutosEncaminhados` (`status=Encaminhado` + intervalo de datas real via
dois parâmetros `\DateTimeInterface` obrigatórios). Este último **corrige** o intervalo
hardcoded para "2014" do legado (`LEG-RMA-039`) — confirmado como bug de manutenção
(nenhuma RN documenta "2014" como valor de negócio intencional), não uma regra a
preservar; coberto por `RelatorioProdutosEncaminhadosTest::
test_intervalo_nao_e_hardcoded_para_2014`, que prova o relatório funcionando fora do
ano 2014.

**Controllers, views e rotas:** `RelatorioController` (3 ações: `creditosDisponiveis`,
`produtosEmEstoqueParaContagem`, `produtosEncaminhados`) e `CreditoController`
(`index` lista `AguardandoCredito` + formulário de marcação; `marcar` invoca
`MarcarCreditoDisponivel` recebendo `rma_id` no corpo do `POST`, não como parâmetro de
rota — evita gerar uma rota por RMA e mantém a tela de crédito como uma única view de
fluxo, coerente com "reconstruir só a intenção" de `LEG-RMA-048`). Views mínimas em
`resources/views/rma/{relatorios/{rcd,rpec,rmpe},credito/index}.blade.php`, sem
fidelidade visual (Fase 8). Rotas com segmento inicial próprio
(`/rmas-credito`, `/rmas-relatorios/{rcd,rpec,rmpe}`), mesmo padrão de `/rmas-alertas`
da Fase 5 — sem conflito com `rmas/{rma}`.

**Testes:** 218/218 verdes, 390 assertions (`sail test`), mantendo os 196 das Fases
1-5. `MarcarCreditoDisponivelTest` (feature, 5 casos: marca com sucesso, nega 422 para
`solucao` diferente de `GeradoCredito`, nega 422 para `solucao=null`, nega 403 para
papel `Leitura`, redireciona visitante não autenticado ao login).
`RelatorioControllerTest` (feature, 5 casos incl. RMPE sem `data_inicio`/`data_fim` →
erro de validação de sessão, RPEC filtrando por status). `AguardandoCreditoTest` e os 3
testes unitários de relatório seguem o mesmo padrão de asserção por `contains('id', …)`
das 10 regras da Fase 5.

**Testes manuais confirmados via `tinker`:** RMA criado com `solucao=GeradoCredito`,
`credito_disponivel=false` — `MarcarCreditoDisponivel::marcar()` devolve
`creditoDisponivel=true` e o valor é confirmado persistido lendo
`App\Models\Rma::find($id)->credito_disponivel` direto do banco (não só o objeto de
domínio devolvido); RMA com `solucao=Reparo` — a mesma chamada lança
`HttpException` com `getStatusCode()===422`, confirmando a negação.

**Pendências que ficaram de fora:** fidelidade visual das views (Fase 8);
`ConsolidarFretePorCidade`/`BoletinsRelacionados` (`LEG-RMA-040`/`041`, RN-16) —
cobertos pela Fase 7 (`rma-logistica-e-historico`), não duplicados aqui apesar de
também serem consultas de leitura sobre `Rma`; PDF real de relatório (`EVO-REL-001`,
backlog evolutivo) — impressão via `Ctrl+P`, igual ao legado; automação de transição de
crédito (`EVO-AUT-002`, backlog evolutivo); dashboard de recorrência de defeito
(`EVO-REL-002`, backlog evolutivo). Todas já registradas como fora de escopo no
`proposal.md`.

**Commit:** `#F6 - Creditos e relatorios (fluxo de credito, RCD/RPEC/RMPE)` (ver hash
abaixo, aplicado junto com este log).

**Revisão pós-fase (sessão principal, 2026-08-25):** encontrados 2 gaps reais de
cobertura de teste HTTP, ambos fechados, nenhum ajuste de código de produto necessário:
- `CreditoController::index` (GET `/rmas-credito`, listagem de `AguardandoCredito`) não
  tinha nenhum teste — nem o caminho feliz (lista RMA com `solucao=PendenteCredito`,
  não lista `GeradoCredito`) nem a exigência de autenticação.
- `CreditoController::marcar` com `rma_id` inexistente não tinha teste do 404
  (`abort_if($registro === null, ...)`).

4 testes novos em `MarcarCreditoDisponivelTest.php`. `sail test`: 221/221 verdes, 396
assertions (218→221, 390→396).

---

## Fase 7 — Auditoria

**Data:** 2026-08-25.

**Pré-requisito descoberto na revisão desta fase:** o `design.md` original (redação
anterior a esta sessão) pseudocodificava `RegistrarModificacaoDeRma` como se os 8
eventos de domínio já existissem. Na prática, só `RmaConcluido` existia (disparado por
`ConcluirRma`, Fase 4) — os outros 7 (`RmaCriado`, `RmaEditado`, `RmaRecebido`,
`RmaEncaminhado`, `RmaArquivado`, `RmaRevertido`, `SolucaoRegistrada`) foram criados
nesta fase em `app/Rma/Dominio/Eventos/`, cada um com `public readonly User $ator` e
`public readonly Rma $rma`, e `::dispatch()` foi adicionado ao final dos 7 casos de uso
já implementados (`CriarRma`, `EditarRma`, `ReceberRma`, `EncaminharRma`, `ArquivarRma`,
`ReverterRmaParaEntrada`, `RegistrarSolucao`) — uma linha a mais em cada método, sem
mudar assinatura nem comportamento. `sail test --filter=...` confirmado verde para cada
arquivo tocado das Fases 3/4 antes de seguir adiante, e a suíte completa das Fases 3/4
(40 testes) e Fase 4 (`ConcluirRmaTest`, 19 testes) confirmada verde depois. `CriarRma`
e `EditarRma` não recebem `User $ator` como parâmetro (o controller nunca passou isso
para eles nas Fases 3) — o ator é lido via `Auth::user()` dentro do próprio caso de
uso, mesmo usuário que a `Gate` já validou no controller; sem sessão autenticada
(ex. chamada via `tinker`), o evento simplesmente não dispara.

**Implementado:** migration `2026_09_01_000000_create_modificacoes_de_rma_table` (FK
real para `rmas`/`users`, `on delete cascade` — o legado grava `numero`/`email` sem
constraint); enum `App\Rma\Dominio\AcaoDeModificacao` (8 cases, sem backing, mesmo
princípio de `Status`); `App\Models\ModificacaoDeRma` (cast `acao` para o enum,
`estado_apos` para `array`); `Dominio\Rma::paraSnapshot()` — método puro novo que
devolve os campos-chave do RMA (não estava no `design.md` original, que só chamava
`$evento->rma->paraSnapshot()` sem definir onde vive; adicionado ao final de
`Dominio\Rma.php`, ao lado de `classeDeAlerta()`/`prazoLegal()`); listener
`RegistrarModificacaoDeRma` — assina os 8 eventos (mapa `class-string => AcaoDeModificacao`
interno), único ponto de verdade que substitui o `registra_modificacao()` manual do
legado; `RmaPolicy::update()` — dispara `TentativaDeGravacaoNaoPermitida` explicitamente
antes de devolver `false` (evento novo, sem `rma` no payload — a Policy decide por
classe, não por instância); listener `EnviarNotificacaoDeTentativaNaoPermitida` — grava
`Log::warning` (ver desvio abaixo); listener `EnviarNotificacaoDeConclusao` + Mailable
`RmaConcluidoMailable` (markdown, `resources/views/emails/rma-concluido.blade.php`) —
assina `RmaConcluido`, destinatário via `config('rma.notificacoes.conclusao')`
(`RMA_NOTIFICACAO_CONCLUSAO` no `.env`), não envia nada se vazio; `RmaConcluido` ganhou
a propriedade `ator` (não existia na Fase 4) — necessária para `RegistrarModificacaoDeRma`
gravar `user_id`; `ConcluirRma::dispatch($ator, $atualizado)` atualizado de acordo (o
caso de uso já recebia `$ator` como parâmetro, só passou a propagá-lo).
`config/rma.php` (`notificacoes.conclusao`). `AppServiceProvider::boot()` — projeto sem
`EventServiceProvider` explícito (Laravel 13), listeners registrados via
`Event::listen()`. `ConsolidarFretePorCidade` (RN-16, TEMA V2 como especificação,
"PORTO ALEGRE" hardcoded, JOINs via relação Eloquent real — sem os aliases mortos
`FOD`/`FAD` do legado) e `BoletinsRelacionados` (paginado, exclui o próprio RMA) em
`app/Rma/Aplicacao/`. Controllers `HistoricoDeModificacaoController`,
`HistoricoDeAcessoController` (ambos exigem `Gate::authorize('gerenciar', User::class)`,
mesma Policy/Gate de `UsuarioController`) e `LogisticaController` (não estava listado
em `tasks.md` por nome, mas necessário para expor as duas rotas de `LEG-RMA-040`/`041`
— `fretePortoAlegre`/`boletinsRelacionados`). 4 views mínimas + 1 view de e-mail, sem
fidelidade visual (Fase 8). Rotas novas: `/rmas-historico`, `/historico-de-acesso`,
`/rmas-logistica/frete-porto-alegre`, `/rmas/{rma}/boletins-relacionados`.

**Desvios do OpenSpec (documentados no código):**
- `EnviarNotificacaoDeTentativaNaoPermitida` não usa Mailable — `design.md`/`tasks.md`
  listam um Mailable só para `EnviarNotificacaoDeConclusao` (`RmaConcluidoMailable`);
  para a tentativa negada, o canal escolhido foi `Log::warning` (contexto: `user_id`,
  `papel`). Justificativa: sem um template de e-mail especificado e sem destinatário
  configurado para esse caso, logar é o equivalente funcional auditável mais simples;
  trocar para `Mail::to(...)->send(...)` no futuro não exige mudar a assinatura do
  listener nem o disparo do evento.
- `BoletinsRelacionados`: o pseudocódigo do `design.md`
  (`->where('destinatario_id', $rma->destinatarioId)->orWhere('fabricante_id', ...)
  ->orWhere('fornecedor_id', ...)`) tem um efeito colateral não documentado — como o
  Query Builder do Laravel traduz `where('coluna', null)` para `coluna IS NULL`, um RMA
  de referência sem destinatário/fabricante/fornecedor casava com **todo** outro RMA
  igualmente sem esses campos (confirmado via `tinker` durante esta fase, antes de
  escrever o teste automatizado). Corrigido: cada condição só entra na query quando o
  campo correspondente do RMA de referência não é nulo; se nenhum dos 3 campos existe,
  a query devolve vazio (`whereRaw('1 = 0')`) em vez de "tudo que também não tem".
  Coberto por `BoletinsRelacionadosTest`.
- `RmaConcluido` (Fase 4) ganhou a propriedade `ator` nesta fase — não é uma mudança de
  comportamento observável (`ConcluirRmaTest` só verifica `Event::assertDispatched
  (RmaConcluido::class)`, sem inspecionar argumentos), mas é uma alteração de
  assinatura de um evento já existente, registrada aqui por transparência.

**Testes:** 248/248 verdes, 443 assertions (`sail test`), mantendo os 221 das Fases 1-6.
27 testes novos em 7 arquivos: `RegistrarModificacaoDeRmaTest` (8 casos, um por valor de
`AcaoDeModificacao`, via HTTP real em cada endpoint do ciclo de vida — não só disparo
manual do evento), `EnviarNotificacaoDeConclusaoTest` (2 casos: envia com destinatário
configurado, `Mail::assertNothingSent()` sem destinatário), `EnviarNotificacaoDeTentativaNaoPermitidaTest`
(3 casos: evento disparado quando `Leitura` tenta editar, não disparado quando
`Operador` edita, `Log::spy()` confirma o `Log::warning` com `user_id` correto),
`HistoricoDeModificacaoTest`/`HistoricoDeAcessoTest` (3 casos cada: acesso autorizado,
403 para quem não gerencia usuários, redirect ao login para visitante),
`ConsolidarFretePorCidadeTest` (5 casos: fornecedor/fabricante/assistência técnica em
Porto Alegre disparam, cidade diferente e status fora de Entrada/Recebido não disparam),
`BoletinsRelacionadosTest` (3 casos: lista relacionado por fabricante e exclui o
próprio, pagina 25 registros em páginas de 20, RMA de referência inexistente → 404).

**Testes manuais confirmados via `tinker`:** `CriarRma::criar()` autenticado como
Operador cria um RMA; `ReceberRma::receber()` no mesmo RMA confirma
`App\Models\ModificacaoDeRma::where('rma_id', ...)->where('acao', AcaoDeModificacao::
Receber)->first()` encontrado, com `estado_apos` contendo `status: "Recebido"` e os
demais campos-chave corretos (descrição, defeito, ids de fabricante/fornecedor/cliente
etc.) — confirma o listener rodando de ponta a ponta fora do contexto de teste
automatizado, inclusive a leitura de `Auth::user()` dentro de `CriarRma` funcionando
com login real via `Auth::login()`.

**Pendência registrada — a decidir com o usuário (`EVO-AUD-001`):** `modificacoes_de_rma`
grava snapshot estruturado (`estado_apos` json) com a ação nomeada (`AcaoDeModificacao`),
não diff campo-a-campo (`de` → `para` por campo). Pergunta em aberto, repetida aqui por
visibilidade: **isso já conta como ter adotado `EVO-AUD-001`, ou ainda falta o diff
campo-a-campo de verdade?** Não decidido nesta sessão — ver `proposal.md`.

**Pendências que ficaram de fora:** fidelidade visual das views (Fase 8); diff
campo-a-campo (`EVO-AUD-001`, ver acima); e-mail de fato para tentativa de gravação não
permitida (log por ora, ver desvio acima).

**Commit:** `#F7 - Auditoria (historico de modificacao, notificacoes, frete PoA,
boletins)` (ver hash abaixo, aplicado junto com este log).

**Revisão pós-fase (sessão principal, 2026-08-25):** o desvio mais importante desta
fase — a correção de `BoletinsRelacionados` para não tratar dois RMAs sem nenhuma
referência (destinatário/fabricante/fornecedor) como "relacionados" por `IS NULL`
genérico — estava documentada em comentário e confirmada só por teste manual via
`tinker`, sem nenhum teste automatizado travando a regressão. Adicionado
`test_rma_sem_nenhuma_referencia_nao_casa_com_outro_tambem_sem_referencia` (prova direta
do bug corrigido) e `test_rma_relacionado_apenas_por_fornecedor` (o único dos 3 campos
sem teste próprio até então — só fabricante e destinatário tinham caso dedicado).

`sail test`: 250/250 verdes, 447 assertions (248→250, 443→447).

---

## Fase 8 — Apresentação (Temas V1/V2)

**Data:** 2026-08-25.

**Pré-requisito confirmado no início desta fase:** as 2 pendências reais originais
(mecanismo de âncoras de TEMA V2; RN-11 em TEMA V1) e as 2 pendências de produto novas
(fonte Open Sans; assimetria pós-login) já estavam resolvidas em `design.md`/
`proposal.md` antes da implementação começar — ver esses arquivos para o detalhe
completo da investigação. Esta fase implementou o plano já fechado, sem reabrir nenhuma
das 4 decisões.

**Implementado:**
- **Vite/Sass:** `sass` e `bootstrap@3.3.5` instalados via `npm` (Vite 8 compila Sass
  nativamente, sem plugin extra — só precisa da dependência `sass` presente). 2 `input`
  novos em `vite.config.js` (`resources/js/temas/{v1,v2}.js`, cada um importando seu
  `.scss`), mantendo o `input` Tailwind do scaffold original intocado (não usado por
  nenhuma view desta fase).
- **`resources/sass/temas/_compartilhado.scss`** — porta de verdade `pattern/15.9.7.css`
  (296 linhas do legado): `TrInconformidade`/`TrUrgente`/`TrZebrada1/2`/
  `TrSemGarantia1/2`, `.breadcrumb`, `.centrodeavisos`, `.formSelect`, `.designedby`,
  `.pmo`, `@font-face` de Fira Mono (self-hostado, `.ttf` copiado do repo Legacy —
  read-only, só leitura/cópia, nada editado lá).
- **`resources/sass/temas/v1.scss`** — paleta (`$fundo`/`$acento`/`$texto`), fallback de
  fonte real (`"Arial","Fira Sans"`, NUNCA Open Sans), `$largura-fixa-tema-v1: 984px`
  nomeada (usada em `#BASE`/`#TOPO`/`#CONTEUDO`, sem NENHUM `@media`), seletores
  autorais (`.tablenovo`, `.novo_formInput`, `.buttonSave`) — zero framework CSS.
- **`resources/sass/temas/v2.scss`** — `$breakpoints-tema-v2`/`$larguras-container-tema-v2`
  nomeados (fonte real: `15.8.1/css/media.php`), `@each` sobre o mapa (nenhum dos 6
  valores redigitado), fallback de fonte real, Bootstrap 3.3.5 self-hostado.
- **`resources/js/temas/{v1,v2}.js`** — `v1.js` sem framework (toggle `.pmo` autoral);
  `v2.js` importa `jquery` + `bootstrap/js/tab` (plugin isolado, não o bundle inteiro) —
  reproduz a troca de aba client-side sem AJAX/reload confirmada no LEGACY-RUNTIME.
- **`app/Http/Middleware/ResolverTemaAtivo.php`** — resolve o tema por prefixo de rota
  (`v1.`/`v2.`, rotas de QA) ou por `tema_preferido` do usuário autenticado (fluxo
  normal), fallback `V2`; `View::share('temaAtivo', ...)`.
- **`app/Support/view_do_tema.php`** (arquivo de funções globais, registrado em
  `composer.json` → `autoload.files`) — 3 helpers: `view_do_tema($view, $data)` resolve
  `temas.{tema}.{$view}`; `rota_tema($nome, $parametros)` gera a URL respeitando o
  prefixo da rota ATUAL (permite que a MESMA Blade funcione tanto acessada via
  `/v1/...`/`/v2/...` quanto pelo fluxo normal sem prefixo); `classe_css_de_alerta()`
  mapeia o enum `ClasseDeAlerta` (Fase 5, puro) para a classe CSS real por tema (RN-11).
- **`routes/tema-{v1,v2}.php`** — prefixo `/v1`/`/v2`, MESMOS Controllers das rotas sem
  prefixo em `routes/web.php` (nenhuma lógica duplicada), registrados via `then:` em
  `bootstrap/app.php` (Laravel 13 não usa `Kernel.php` para isso).
  `ResolverTemaAtivo::class` registrado como `appendToGroup('web', ...)`.
- **Árvore de Blade por tema** (`resources/views/temas/{v1,v2}/`) — layout, `rma/
  {index,create,edit,show}`, `parceiros/{index,_form}`, `identidade/{usuarios,perfil}`.
  `identidade/login.blade.php` existe uma única vez (gateway compartilhado, visual
  próprio — Bootstrap `.login-box`/`.form-control`, reaproveitando o bundle V2 só
  porque é ele que carrega o Bootstrap self-hostado, não porque a tela "é" TEMA V2).
- **`temas/v2/rma/index.blade.php`** — painel único com os 7 tab-panes (`#inicio`,
  `#pesquisar`, `#novo_rma`, `#entrada`, `#recebido`, `#encaminhado`, `#concluido`),
  confirmado via `curl`/Playwright que os 7 aparecem todos no mesmo HTML e a troca é
  `data-toggle="tab"` puro.

**Desvios do OpenSpec (documentados no código/aqui):**
- **Bootstrap 3.3.5 sem SCSS:** `design.md` previa `@import` do "SCSS do Bootstrap"
  escopado a `v2.scss`. O pacote npm `bootstrap@3.3.5` só publica LESS e CSS
  pré-compilado, não SCSS. Resolvido importando o CSS de distribuição REAL
  (`node_modules/bootstrap/dist/css/bootstrap.css`) como CSS puro (Sass repassa
  `@import` de arquivo `.css` sem processar) — mesmos bytes que o legado carregava via
  CDN, agora self-hostado. Efeito prático idêntico ao pretendido (grid/`.form-control`/
  tabs reais, escopados só ao bundle v2), só o mecanismo de import muda.
- **Painel V2 (`#início`/`#novo_rma`) usa dados já resolvidos, sem caso de uso novo:**
  o `design.md` não especifica a fonte de dados exata de cada um dos 7 tab-panes além
  de "já resolvidos pelos Controllers/casos de uso existentes". Não existe um caso de
  uso "listar RMAs por status" na Fase 3-7 (só `BuscarRmas` por texto/serial/NF). Em vez
  de criar lógica de negócio nova (fora do escopo "puramente apresentação" desta fase),
  os painéis `entrada`/`recebido`/`encaminhado`/`concluido` particionam o MESMO
  resultado já buscado por `RmaController@index` (`$rmas`) por `$registro->status` —
  presentação pura, sem query nova. Consequência aceita: os 4 painéis só mostram
  resultado quando há um termo de busca ativo (mesma limitação que o painel
  `#pesquisar`); a aba `#início` é um texto de boas-vindas e `#novo_rma` é um
  call-to-action para a tela de criação, não o formulário embutido. Se o produto quiser
  os 4 painéis por status sempre populados independente de busca, isso é um caso de uso
  novo (fora do escopo desta fase) — registrado aqui, não decidido no escuro.
- **Nome dos arquivos Playwright:** `tasks.md` original listava
  `tests/Browser/ComparacaoVisualTemaV{1,2}Test.php`. Um arquivo `.php` nunca seria
  descoberto por `npx playwright test` (runner Node/TS) nem por `sail test`
  (PHPUnit não teria classe/namespace válidos para um `.spec` Playwright) — a extensão
  `.php` no nome original era inconsistente com "Playwright" já dito no mesmo item.
  Renomeado para `ComparacaoVisualTemaV{1,2}Test.spec.ts` (convenção Playwright real).
- **`tests/Browser/Support/breakpoints-tema-v2.json` mantido manualmente:** `design.md`
  sugere "gerado a partir do mesmo mapa Sass". Não foi escrito um gerador automático
  nesta fase (escopo/tempo) — o JSON é escrito à mão em sincronia com
  `$breakpoints-tema-v2`/`$larguras-container-tema-v2` de `v2.scss`, com comentário
  `_fonte` explicando a relação. Ainda cumpre o objetivo central (nenhum literal
  redigitado dentro dos arquivos `.spec.ts`), só não há verificação automática de que
  os dois arquivos não divirjam — pendência menor, registrada.

**Testes:**
- `tests/Feature/Temas/RenderizaTemaV1Test.php` (7 testes) e `RenderizaTemaV2Test.php`
  (6 testes) — smoke real via HTTP (`assertViewIs`/`assertSeeText`), cobrindo
  login-gateway, redirect pós-login por `tema_preferido`, RMA index/create/show,
  clientes, perfil, e — só no V2 — confirmação dos 7 `id="..."` de tab-pane no HTML.
- `sail test`: **263/263 verdes, 488 assertions** (250 das Fases 1-7 + 13 novos).
  1 falha intermitente pré-existente e não relacionada a esta fase foi observada numa
  execução (`HistoricoDeModificacaoTest`, nome Faker aleatório com apóstrofo
  HTML-escapado batendo contra `assertSee` — Fase 7, não tocada aqui) e não se repetiu
  na reexecução; não é causada por nenhuma mudança desta fase.
- **Playwright REAL, não simulado:** `@playwright/test` instalado via `npm`; Chromium
  instalado com `npx playwright install --with-deps chromium` DENTRO do container
  `laravel.test` (Sail) — funcionou de primeira, sem bloqueio de ambiente (headless
  shell + dependências X/fontes já presentes na imagem). `playwright.config.ts` aponta
  para `http://localhost` (porta 80 interna ao container — `:8095` é só o mapeamento
  externo do host, `.env`/`APP_PORT`). Rodado via
  `sail exec laravel.test npx playwright test tests/Browser/`:
  - `ComparacaoVisualTemaV1Test.spec.ts` — 3/3 passam: em 390/768/1440px, a largura
    COMPUTADA de `#BASE` (`getComputedStyle`, não `getBoundingClientRect` — esse
    inclui os 20px de padding lateral, dando 1004px, achado durante a execução, teste
    corrigido) é 984px nos 3 breakpoints, confirmando o layout fixo/não-responsivo.
  - `ComparacaoVisualTemaV2Test.spec.ts` — 2/3 passam, 1 pulado corretamente: 768px→
    largura de `.container` = 730px, 1440px→990px (ambos batendo com
    `breakpoints-tema-v2.json`); 390px pulado via `test.skip` por estar abaixo do menor
    breakpoint do tema (568px) — comportamento esperado, não falha.
- **Screenshots reais (PNG), não simulados:** `tests/Browser/CapturarScreenshotsTemas.spec.ts`
  gravou 9 arquivos em `docs/produto/screenshots-fase8/` (login/perfil/RMAs/clientes
  dos 2 temas + a aba "Entrada" do painel V2), confirmados abertos e com conteúdo
  visualmente coerente com a paleta/estrutura pretendida (V1 escuro/tabela, V2 nav
  escura + painel branco + abas Bootstrap).

**Teste manual confirmado (fora do Playwright):** usuários reais criados via `tinker`
com `tema_preferido=v1`/`v2`, login via `curl` de ponta a ponta (POST com token CSRF
real, cookies de sessão), `GET /perfil` autenticado confirmando o bundle certo
(`v1-*.js`/`v2-*.js`, `TEMA V1`/`TEMA V2` no HTML) para cada usuário; `GET /rmas` do
usuário V2 confirmado com os 7 `id="..."` de tab-pane e `data-toggle="tab"` × 7. Usuários
de teste removidos ao final (`tinker` delete).

**Pendências que ficaram de fora (não bloqueiam a Fase 8, escopo já fechado no
`design.md`):**
- Telas fora da árvore explícita do `design.md` (alertas, crédito, relatórios,
  histórico/auditoria, logística) continuam com a view mínima das Fases 5-7, sem
  estilização por tema.
- Gerador automático de `breakpoints-tema-v2.json` a partir do Sass (mantido manual
  por ora, ver desvio acima).
- Painéis por status do dashboard V2 só populados quando há busca ativa (ver desvio
  acima) — se o produto quiser diferente, é um caso de uso novo, fora do escopo desta
  fase.

**Commit:** `#F8 - Apresentacao (Tema V1 + Tema V2 fieis)` (ver hash abaixo, aplicado
junto com este log).

---
