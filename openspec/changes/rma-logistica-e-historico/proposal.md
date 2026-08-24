# Proposal — Auditoria (histórico de modificação, notificações)

Fase 7 de 10 (ver `docs/arquitetura/INV-RMA-05-arquitetura-proposta.md` §12).

## Por quê

`LEG-RMA-044` (auditoria de modificação de RMA) e `LEG-RMA-045` (notificação por
e-mail) fecham o ciclo de rastreabilidade do produto — quem mudou o quê, e quem é
avisado quando. `LEG-RMA-043` (auditoria de autenticação) já existe desde a Fase 1
(tabela `tentativas_de_acesso`); esta fase só adiciona a tela de consulta. O nome desta
change (`rma-logistica-e-historico`, já catalogado em `checklist-master-v3.md` antes
desta rodada de planejamento) também reúne `LEG-RMA-040`/`041` — duas consultas de
logística/histórico sobre `Rma` que fazem mais sentido lidas junto com o histórico de
modificação do que junto ao fluxo de crédito (Fase 6).

## O que entra

- Tabela `modificacoes_de_rma` (FK real para `rmas`/`users` — o legado tinha
  `numero`/`email` sem constraint).
- `RegistrarModificacaoDeRma` — listener que assina os eventos disparados por
  `CriarRma`/`EditarRma` (Fase 3) e pelos 5 verbos de transição (Fase 4).
- `EnviarNotificacaoDeConclusao` (`LEG-RMA-045`) — assina `RmaConcluido` (Fase 4),
  destinatário configurável via `.env` (não hardcoded).
- `EnviarNotificacaoDeTentativaNaoPermitida` — dispara quando `Papel::podeGravar() ===
  false` tenta editar.
- Telas de consulta: histórico de modificação de RMA (`LEG-RMA-044`) e histórico de
  acesso (`LEG-RMA-043`, dado já existe, só falta a tela).
- `ConsolidarFretePorCidade` (`LEG-RMA-040`/RN-16) — **TEMA V2 como especificação**
  (confirmado: código idêntico existe em TEMA V1, `14.6.1/inc/startpage.php:139-165`,
  mas está inteiramente comentado — o widget foi desativado, deliberadamente ou por
  regressão não documentada). Cidade "PORTO ALEGRE" mantida hardcoded — é o
  comportamento documentado, não há política configurável no legado.
- `BoletinsRelacionados` (`LEG-RMA-041`) — paginado (o legado não tem `LIMIT`, achado
  de risco de performance já registrado); resultado percebido pelo usuário é o mesmo
  conjunto de dados, só a forma de consumir muda.

## O que não entra

- Diff campo-a-campo de verdade (`EVO-AUD-001`, backlog evolutivo) — **pendência
  registrada, não decidida aqui** (ver abaixo).
- Fidelidade visual (Fase 8).

## Decisão registrada — snapshot estruturado, não diff

`modelo-dominio-rma-legado.md` §Auditoria: `modificacao` do legado grava snapshot
desnormalizado, sem diff nem ação específica. Esta fase **não promove** `EVO-AUD-001`
(diff campo-a-campo) à Trilha A — não há evidência de que essa decisão de produto já
foi tomada, só um "candidato" registrado no backlog. Implementa o equivalente funcional
do legado (log de que uma modificação aconteceu, quem, quando, campos-chave), mas
**com o nome da ação nomeado** (`AcaoDeModificacao`, enum) em vez de só um retrato do
estado final — usa a granularidade que os Eloquent events já oferecem, sem o custo de
implementar diff de verdade.

## Pendência registrada — a decidir com o usuário

"Registrar o nome da ação (em vez de só snapshot) já conta como ter adotado
`EVO-AUD-001`, ou ainda falta o diff campo-a-campo (`de` → `para` por campo)?" — não
decidido nesta OpenSpec.

## Rastreabilidade com o legado

| Este OpenSpec | Legado |
|---|---|
| `RegistrarModificacaoDeRma` | `LEG-RMA-044` (tabela `modificacao`) |
| `EnviarNotificacaoDeConclusao` | `LEG-RMA-045` (`ezequiel()`) |
| `EnviarNotificacaoDeTentativaNaoPermitida` | `LEG-RMA-045` (`naopermitido()`) |
| Tela de histórico de acesso | `LEG-RMA-043` (dado já existe desde a Fase 1) |
| `ConsolidarFretePorCidade` | `LEG-RMA-040`, RN-16 — **TEMA V2 como especificação** |
| `BoletinsRelacionados` | `LEG-RMA-041` — paginado (correção de performance) |
