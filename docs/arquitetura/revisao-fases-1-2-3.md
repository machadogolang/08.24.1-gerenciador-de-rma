# Revisão crítica — Fases 1, 2 e 3 (Identidade, Parceiros, Rma núcleo)

Data: 2026-08-24. Revisão da Parte A da tarefa de planejamento das fases seguintes.
Compara `docs/arquitetura/INV-RMA-05-arquitetura-proposta.md` §6/7/8 e os três OpenSpecs
(`openspec/changes/{autenticacao-usuarios,parceiros,rma-cadastro-e-localizacao}/`)
contra `docs/legado/inventario-funcional-rma-v2.md`, `docs/produto/paridade-v2-v3.md`,
`docs/legado/regras-negocio-rma-legado.md` e os 5 princípios fixos do projeto. Todos os
ajustes concretos encontrados foram **aplicados diretamente** nos documentos originais
(não só listados aqui) — este documento é o registro do que mudou e por quê.

---

## Fase 1 — Identidade

**Veredito: aprovado com ajuste.**

### Cobertura de funcionalidades

O plano original cobria só `LEG-RMA-001` (login/logout), `LEG-RMA-006` (trocar tema) e
`LEG-RMA-043` (auditoria de autenticação). `LEG-RMA-002` (autocadastro com convite),
`LEG-RMA-003` (resetar senha de outro usuário), `LEG-RMA-004` (trocar a própria senha) e
`LEG-RMA-005` (gerenciar usuários e permissões) eram citados no `proposal.md` original
só como "fora do escopo, fica pra próxima fase" — mas **nenhuma das Fases 2 a 10**
(conferido em `INV-RMA-05` §5 e no `checklist-master-v3.md` Parte 3, ambos revisados
nesta rodada) reivindicava essas quatro funcionalidades. Era um item ficando sem fase
dona, apesar de aparecer em `paridade-v2-v3.md` como `PENDENTE` como qualquer outro.

**Achado mais importante:** `LEG-RMA-004` é exatamente a funcionalidade da RN-21 (a
regressão mais bem documentada de todo o levantamento — troca de senha funciona em TEMA
V1, quebrada em TEMA V2). Sem uma fase dona, havia risco real de essa correção
comprovada por evidência se perder entre uma sessão e outra.

**Ajuste aplicado:** gestão de usuários passou a fazer parte da Fase 1 (mesma fronteira
de domínio — usa `User`/`Papel`/`UserPolicy`, que já nascem aqui). Adicionado a
`INV-RMA-05` §6, `openspec/changes/autenticacao-usuarios/{proposal,design,tasks}.md` e
`checklist-master-v3.md`:
- `TrocarPropriaSenha` — implementa **TEMA V1 como especificação** (SQL único e válido),
  não o SQL quebrado de TEMA V2 (RN-21).
- `ResetarSenhaDeUsuario` — equivalente à versão correta em ambos os temas.
- `AtualizarAnotacaoPessoal` — cobre `LEG-RMA-042` (bloco de notas pessoal), que também
  não tinha fase dona.
- `UsuarioController` — cobre `LEG-RMA-005`, usa o método `ocultoDaListagemDeUsuarios()`
  do enum `Papel` que já existia desde a primeira versão da fase mas não tinha nenhum
  caller planejado.
- Migration de `users` passou a incluir `anotacao` (text, nullable).

**Pendência registrada, não decidida (conforme instrução de não inventar solução):**
`LEG-RMA-002` (autocadastro público com convite) fica como pendência explícita no
`proposal.md` — confirmado só em TEMA V1 (segredo hardcoded), `[DÚVIDA]` em TEMA V2, sem
evidência suficiente para decidir se é comportamento a preservar ou porta a fechar. Duas
opções foram registradas (autocadastro com segredo em `.env` vs. usuário só criado por
admin), a decisão fica para o usuário.

### Fidelidade às regras de negócio

Correta. O plano já usava TEMA V1 como especificação para a ordem "nega antes de checar
senha" e a correção de segurança sobre enumeração de e-mail (que é bug de segurança
documentado a não copiar, não comportamento a preservar — distinção correta).

### Arquitetura / princípios fixos

- `Papel` sem backing type, métodos nomeados: correto, sem número mágico.
- `UserPolicy` descrita em prosa como "papel ≥ Supervisor" no `INV-RMA-05` original
  podia ser lida como "comparar por ordinal" — ambíguo o suficiente para um
  implementador futuro escrever `$papel->value >= 3`. **Ajuste aplicado:** reforçado
  explicitamente que a implementação chama `$ator->papel->podeGerenciarUsuarios()`
  (método já existente no enum), nunca compara por ordinal.
- Sem `include()`/sessão manual — correto, usa `Auth`/`Hash` nativos desde o início.
- Nenhuma abstração desproporcional encontrada — `TentativaDeAcessoRegistrada` sem
  interface de repositório é justificado (só um INSERT de auditoria).

### Arquivos faltantes

Nenhum teste/migration/view faltando além do já corrigido acima (gestão de usuários).

---

## Fase 2 — Parceiros

**Veredito: aprovado com ajuste.**

### Cobertura de funcionalidades

`LEG-RMA-030` a `033` (CRUD dos 4 tipos) cobertos. `LEG-RMA-034` ("autorizada", código
morto) corretamente excluído, coerente com `paridade-v2-v3.md` (`NÃO RECONSTRUIR`).
`LEG-RMA-035` (tabela `assistencias` órfã) corretamente adiado para `EVO-DOM-001** —
"retomar a ideia, não o código", coerente com o próprio achado do legado (é a mesma
tentativa de unificação que o legado abandonou pela metade). Nenhuma funcionalidade
sem justificativa ficou de fora.

### Fidelidade às regras de negócio

`EncontrarOuCriarCliente` corrige o achado real do legado (`adicionar_cli()` compara
nome exato, sem trim/case-insensitive) sem mudar o comportamento percebido pelo usuário
— exatamente o tipo de correção que os princípios fixos permitem na baseline (invisível
ao usuário, corrige dado, não produto).

### Arquitetura / princípios fixos

- Sem `Dominio`/`Infraestrutura` para Parceiros — decisão correta e bem justificada (CRUD
  simples, uma única regra real), coerente com "monólito modular proporcional".
- **Achado de inconsistência:** `INV-RMA-05` §3 já prometia um "enum de UF" como exemplo
  de conteúdo de `app/Compartilhado/`, mas o schema desenhado para esta fase usava `uf
  string(2) nullable` solto nos 4 models — a mesma classe de primitiva-representando-
  conceito-fechado que o princípio "sem número mágico" (§1.1) proíbe (UF é um domínio
  fechado de 27 valores). **Ajuste aplicado:** `app/Compartilhado/Uf.php` (enum backed
  string das 27 UFs) adicionado a `INV-RMA-05` §7, `openspec/changes/parceiros/
  {design,tasks}.md` e `checklist-master-v3.md`, com cast Eloquent nativo nos 4 models.
- Trait `TemEnderecoEContato` para 3 dos 4 models (não `Cliente`, que tem schema
  genuinely diferente) é proporcional — não força uniformidade que o legado não tem.

### Arquivos faltantes

Nenhum além do enum `Uf` acima.

---

## Fase 3 — Rma núcleo

**Veredito: aprovado com ajuste (o mais significativo dos três).**

### Cobertura de funcionalidades

`LEG-RMA-007` (criar), `008` (buscar), `009` (ver detalhe) cobertos. **Achado:**
`LEG-RMA-010` ("editar/salvar RMA") — descrito no próprio `inventario-funcional-rma-v2.md`
como "o núcleo de todas as normalizações (RN-13 a RN-17)" — não tinha fase dona em
nenhuma das 10 fases (não é uma transição de ciclo de vida da Fase 4, que cobre
`LEG-RMA-011` a `017`, isto é, receber/encaminhar/concluir/arquivar/reverter/solução —
não edição geral dos campos do núcleo). Confirmado cruzando `INV-RMA-05` §5,
`checklist-master-v3.md` Parte 3 e `paridade-v2-v3.md` — o item aparecia como `PENDENTE`
sem nenhum OpenSpec previsto para reivindicá-lo.

Decorrente do mesmo achado: RN-13 (HGST→Hitachi) e RN-14 (cascata de `origem`) — que no
legado disparam tanto na criação quanto na edição (`pp/novo_rma.php`/`pp/salvar_rma.php`)
— também não tinham dono. Adiar essas normalizações para a Fase 4/5 (que dependem de
`status`/`solucao`, inexistentes nesta fase) faria o primeiro RMA criado pela V3 já
nascer sem a normalização que o legado sempre aplicou — quebra de fidelidade desde o
primeiro caso de uso implementado.

**Ajuste aplicado:** `EditarRma` adicionado como caso de uso desta fase (mesma família
de `CriarRma`/`VerDetalheDoRma` — ler/escrever o núcleo antes de qualquer
status/solução existir). RN-13/RN-14 implementadas como método puro
`Rma::comNormalizacaoDeGravacao()` no objeto de domínio, chamado por `CriarRma` e
`EditarRma`. RN-17 (`marcarestoque`) tratada explicitamente: o legado computa um valor
por `origem` e imediatamente descarta (achado já reclassificado em
`regras-negocio-rma-legado.md` como dívida técnica, não bug/regressão) — a V3 não
reproduz o cálculo morto, grava só o valor do formulário, produzindo o mesmo resultado
observável sem o código morto.

RN-15 (`snretorno` auto-preenchido, `LEG-RMA-047`) foi avaliada e **deixada de fora**
desta fase deliberadamente — depende de `solucao`, que só existe a partir da Fase 4;
registrada explicitamente no `proposal.md` como pendente para lá, não perdida.

**Segundo achado:** o schema desta fase tinha `fabricante_id` e `cliente_id` como FK
reais, mas **não `fornecedor_id`** — apesar de `bd.fornecedor` ser um campo do mesmo
grupo de "Partes" em `modelo-dominio-rma-legado.md`, preenchido na mesma tela de criação
do RMA. Não havia justificativa registrada para o tratamento assimétrico. **Ajuste
aplicado:** `fornecedor_id` adicionado ao schema desta fase (migration, objeto de
domínio `Rma`, interface de repositório).

### Fidelidade às regras de negócio

Com os ajustes acima, a fase passa a cobrir RN-13/RN-14/RN-17 corretamente (a correção
de RN-17 é a aplicação correta do princípio "fidelidade é do resultado percebido" —
reproduzir literalmente o cálculo morto do legado não mudaria nenhum resultado visível,
só adicionaria código morto de propósito, o que os princípios fixos não pedem).

### Arquitetura / princípios fixos

- Fronteira completa `Dominio`/`Aplicacao`/`Infraestrutura` com interface de
  repositório: única fase que usa esse padrão, com justificativa concreta (Fase 9
  precisa ler `rma_legacy`) — coerente com o princípio de proporcionalidade.
- `CriterioDeBusca` como value object com named constructors, não string mágica de
  `campo=` — coerente com o princípio "sem número/string mágica".
- A normalização RN-13/RN-14 implementada como `match` puro sobre parâmetros explícitos
  (não `str_replace` sobre variável de escopo do arquivo) corrige diretamente o bug
  confirmado de `$fornecedor` não inicializado do legado (RN-14) — ganho de robustez que
  não muda o resultado percebido pelo usuário em nenhum caso válido.
- Nenhuma abstração desproporcional nova introduzida pelos ajustes.

### Ordem de dependência entre fases

Confirmada correta: Fase 2 depende de Fase 1 (`Papel::podeGravar()`); Fase 3 depende de
Fase 1 (indireta, via Policy) e Fase 2 (FK para `Cliente`/`Fabricante`/`Fornecedor`,
agora que `fornecedor_id` também existe). Nenhuma dependência circular ou fora de ordem
encontrada nas três fases.

### Arquivos faltantes

`EditarRma` (caso de uso), `EditarRmaTest`, `RmaTest` (unit da normalização), view
`edit.blade.php`, migration com `fornecedor_id` — todos adicionados.

---

## Resumo dos ajustes aplicados (rastreamento)

| # | Ajuste | Documentos alterados |
|---|---|---|
| 1 | Gestão de usuários (`LEG-RMA-003/004/005/042`) incorporada à Fase 1 | `INV-RMA-05` §6, `openspec/changes/autenticacao-usuarios/*`, `checklist-master-v3.md` |
| 2 | `LEG-RMA-002` (autocadastro) registrado como pendência explícita, não decidida | `openspec/changes/autenticacao-usuarios/proposal.md`, `checklist-master-v3.md` |
| 3 | `app/Compartilhado/Uf.php` (enum de UF) adicionado à Fase 2 | `INV-RMA-05` §7, `openspec/changes/parceiros/{design,tasks}.md`, `checklist-master-v3.md` |
| 4 | `fornecedor_id` adicionado ao schema da Fase 3 | `INV-RMA-05` §8, `openspec/changes/rma-cadastro-e-localizacao/*`, `checklist-master-v3.md` |
| 5 | `EditarRma` (`LEG-RMA-010`) incorporado à Fase 3 | idem |
| 6 | RN-13/RN-14/RN-17 movidas para `CriarRma`/`EditarRma` na Fase 3 (não ficam sem dono, não são adiadas indevidamente) | idem |
| 7 | RN-15/`LEG-RMA-047` confirmada e deixada explicitamente para a Fase 4 | `openspec/changes/rma-cadastro-e-localizacao/proposal.md`, `INV-RMA-05` §8 |

Nenhum ajuste envolveu inventar comportamento sem evidência — todos os sete ajustes têm
origem rastreável em `LEG-RMA-NNN`/`RN-NN` já documentados, ou em uma inconsistência
interna do próprio `INV-RMA-05` (item 3, UF prometida e não usada). O único ponto em
aberto (item 2) foi registrado como pergunta ao usuário, não decidido unilateralmente.
