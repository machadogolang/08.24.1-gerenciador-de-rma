# Matriz de paridade V2 → V3

Data: 2026-08-25 (atualizado 2026-08-25 — Fase 4 "Ciclo de vida" implementada e testada).
Índice de rastreamento: nenhuma funcionalidade relevante do RMA V2 pode "desaparecer"
silenciosamente na V3. Atualizado a cada avanço de OpenSpec/implementação. **Fase 1
(`autenticacao-usuarios`) concluída:** 7 itens (`LEG-RMA-001/003/004/005/006/042/043`)
passaram de `PENDENTE` para `PARIDADE`, com `sail test` verde (36/36) e login real
confirmado por `curl` de ponta a ponta. `LEG-RMA-002` (autocadastro com convite)
permanece `PENDENTE` — decisão de produto explicitamente não tomada nesta fase, ver
`openspec/changes/autenticacao-usuarios/proposal.md`. **Fase 2 (`parceiros`) concluída:**
4 itens (`LEG-RMA-030/031/032/033`) passaram de `PENDENTE` para `PARIDADE`, com
`sail test` verde (61/61, mantendo os 36 da Fase 1) e a deduplicação de
`EncontrarOuCriarCliente` confirmada por `tinker` de ponta a ponta. **Fase 3
(`rma-cadastro-e-localizacao`) concluída:** 5 itens (`LEG-RMA-007/008/009/010/046`)
passaram de `PENDENTE` para `PARIDADE`, com `sail test` verde (85/85, mantendo os 61
das Fases 1-2). RN-13/RN-14 (normalização HGST→Hitachi/cascata de origem) confirmadas
de ponta a ponta tanto na criação quanto na edição. **Fase 4 (`rma-ciclo-de-vida`)
concluída:** 8 itens (`LEG-RMA-011/012/013/014/015/016/017/047`) tiveram seu status
atualizado — 7 passaram de `PENDENTE` para `PARIDADE` e `LEG-RMA-016` foi confirmado
`NÃO RECONSTRUIR` (código morto, sem mudança) —, com `sail test` verde (131/131,
mantendo os 85 das Fases 1-3) e o ciclo receber→encaminhar→concluir confirmado por
`tinker` de ponta a ponta, incluindo o auto-preenchimento de `snretorno` (RN-15).
`LEG-RMA-014` (arquivar) usa TEMA V2 como especificação — TEMA V1 confirmado com
`Fatal Error` incondicional nesse fluxo. Os demais itens aguardam as próximas fases
(ver `docs/arquitetura/INV-RMA-05-arquitetura-proposta.md` §5).

Fonte dos IDs: `docs/legado/inventario-funcional-rma-v2.md`.

| ID | Funcionalidade V2 | Tema V1 | Tema V2 | OpenSpec | V3 | QA | Status |
|---|---|---|---|---|---|---|---|
| LEG-RMA-001 | Login/logout | confirmado | confirmado | `autenticacao-usuarios` | `AutenticarUsuario`, `SessaoController` | `AutenticacaoTest` (5 testes) + curl manual | PARIDADE |
| LEG-RMA-002 | Autocadastro com convite | confirmado | dúvida | `autenticacao-usuarios` | — | — | PENDENTE — decisão de produto não tomada, ver `proposal.md` (opção A/B) |
| LEG-RMA-003 | Resetar senha (admin) | confirmado | confirmado | `autenticacao-usuarios` | `ResetarSenhaDeUsuario`, `UsuarioController::resetarSenha` | `ResetarSenhaDeUsuarioTest` | PARIDADE |
| LEG-RMA-004 | Trocar própria senha | funcional (correto) | quebrado (regressão) | `autenticacao-usuarios` | `TrocarPropriaSenha` (TEMA V1 como especificação, RN-21) | `TrocarPropriaSenhaTest` (prova de regressão corrigida) | PARIDADE — V3 usa TEMA V1 como especificação |
| LEG-RMA-005 | Gerenciar usuários/permissões | confirmado | confirmado | `autenticacao-usuarios` | `UsuarioController`, `UserPolicy`, `Papel::ocultoDaListagemDeUsuarios()` | `GerenciarUsuariosTest`, `PermissaoTest` | PARIDADE |
| LEG-RMA-006 | Selecionar tema V1/V2 | confirmado | confirmado | `autenticacao-usuarios` | `AlternarTemaPreferido`, `TemaPreferidoController` | `AlternarTemaTest` (3 testes) | PARIDADE |
| LEG-RMA-007 | Cadastrar novo RMA | confirmado | confirmado | `rma-cadastro-e-localizacao` | `CriarRma`, `RmaController` | `CriarRmaTest` (4 testes) | PARIDADE |
| LEG-RMA-008 | Localizar/pesquisar RMA | confirmado | confirmado | `rma-cadastro-e-localizacao` | `BuscarRmas`, `CriterioDeBusca` | `BuscarRmasTest`, `CriterioDeBuscaTest` | PARIDADE |
| LEG-RMA-009 | Ver detalhes do RMA | confirmado | confirmado | `rma-cadastro-e-localizacao` | `VerDetalheDoRma` | `VerDetalheDoRmaTest` | PARIDADE |
| LEG-RMA-010 | Editar/salvar RMA | confirmado | confirmado | `rma-cadastro-e-localizacao` | `EditarRma` | `EditarRmaTest` (3 testes) | PARIDADE |
| LEG-RMA-011 | Receber RMA | confirmado | confirmado | `rma-ciclo-de-vida` | `ReceberRma`, `Status::podeReceber()` | `ReceberRmaTest` | PARIDADE |
| LEG-RMA-012 | Encaminhar RMA | confirmado | confirmado | `rma-ciclo-de-vida` | `EncaminharRma`, `Status::podeEncaminhar()` | `EncaminharRmaTest` | PARIDADE |
| LEG-RMA-013 | Concluir RMA | confirmado | confirmado | `rma-ciclo-de-vida` | `ConcluirRma`, evento `RmaConcluido` | `ConcluirRmaTest` | PARIDADE |
| LEG-RMA-014 | Arquivar RMA | quebrado | confirmado | `rma-ciclo-de-vida` | `ArquivarRma` (TEMA V2 como especificação) | `ArquivarRmaTest` (prova TEMA V2, não reproduz Fatal Error de TEMA V1) | PARIDADE — V3 usa TEMA V2 como especificação |
| LEG-RMA-015 | Retornar p/ entrada (rollback) | confirmado | confirmado | `rma-ciclo-de-vida` | `ReverterRmaParaEntrada`, `Papel::podeReverterAlemDoMesmoDia()` | `ReverterRmaParaEntradaTest` | PARIDADE |
| LEG-RMA-016 | Estado "retornou" | código morto | código morto | — | — | — | NÃO RECONSTRUIR (morto em ambos) |
| LEG-RMA-017 | Registrar solução/resolução | confirmado | confirmado | `rma-ciclo-de-vida` | `RegistrarSolucao` | `RegistrarSolucaoTest` | PARIDADE |
| LEG-RMA-018 | Alerta: recebido >30d não encaminhado | herdado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-019 | Alerta: não vai dar garantia (MARKVISION) | herdado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-020 | Alerta: NF pendente de lançamento | herdado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-021 | Alerta: protocolo aberto não encaminhado | herdado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-022 | Alerta: garantia fornecedor expirada | herdado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-023 | Alerta: menos de 30d p/ expirar | herdado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-024 | Alerta: prazo destinatário estourado | herdado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-025 | Alerta: prioridade alta | herdado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-026 | Alerta: sem nota fiscal | herdado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-027 | Alerta: sem número de série | herdado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-028 | Classificação visual de inconformidade | dúvida | confirmado | — | — | — | PENDENTE |
| LEG-RMA-029 | Urgência por threshold R$75 | dúvida | confirmado | — | — | — | PENDENTE |
| LEG-RMA-030 | Cadastro de clientes | confirmado | confirmado | `parceiros` | `ClienteController`, `EncontrarOuCriarCliente` (dedup corrigida) | `ClienteCrudTest`, `EncontrarOuCriarClienteTest` | PARIDADE |
| LEG-RMA-031 | Cadastro de fabricantes | confirmado | confirmado | `parceiros` | `FabricanteController` | `FabricanteCrudTest` | PARIDADE |
| LEG-RMA-032 | Cadastro de fornecedores | confirmado | confirmado | `parceiros` | `FornecedorController` | `FornecedorCrudTest` | PARIDADE |
| LEG-RMA-033 | Cadastro de assistências técnicas | confirmado | confirmado | `parceiros` | `AssistenciaTecnicaController` | `AssistenciaTecnicaCrudTest` | PARIDADE |
| LEG-RMA-034 | "Autorizada" (alias morto) | n/a | código morto | — | — | — | NÃO RECONSTRUIR (morto) |
| LEG-RMA-035 | Tabela unificada `assistencias(tipo)` | legado/abandonado | n/a | — | — | — | RETOMAR IDEIA (não o código) — ver EVO-DOM-001 |
| LEG-RMA-036 | Fluxo de crédito | confirmado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-037 | Relatório RCD | confirmado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-038 | Relatório RPEC | confirmado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-039 | Relatório RMPE | confirmado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-040 | Consolidação de frete (Porto Alegre) | código morto/comentado | confirmado, ativo | — | — | — | PENDENTE |
| LEG-RMA-041 | Boletins relacionados (histórico por contraparte) | dúvida | confirmado | — | — | — | PENDENTE |
| LEG-RMA-042 | Bloco de notas pessoal | confirmado | dúvida | `autenticacao-usuarios` | `AtualizarAnotacaoPessoal`, `AnotacaoPessoalController` | `AnotacaoPessoalTest` | PARIDADE |
| LEG-RMA-043 | Auditoria de autenticação | confirmado | confirmado | `autenticacao-usuarios` | `TentativaDeAcesso` (Eloquent), `ResultadoDeAcesso` (enum) | `AutenticacaoTest` (asserções de `tentativas_de_acesso`) | PARIDADE |
| LEG-RMA-044 | Auditoria de modificação de RMA | confirmado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-045 | Notificação por e-mail | confirmado | confirmado | — | — | — | PENDENTE |
| LEG-RMA-046 | Normalização automática (HGST→Hitachi, origem) | confirmado (duplicado) | confirmado | `rma-cadastro-e-localizacao` | `Rma::comNormalizacaoDeGravacao()` | `RmaTest` (unit) + `CriarRmaTest`/`EditarRmaTest` (ponta a ponta) | PARIDADE |
| LEG-RMA-047 | S/N de retorno auto-preenchido | ausente | confirmado | `rma-ciclo-de-vida` | `Rma::comSnretornoAutoPreenchido()`, `Solucao::implicaMesmoAparelhoDeRetorno()` (RN-15) | `ConcluirRmaTest` (16 valores de `Solucao`), `RegistrarSolucaoTest` | PARIDADE |
| LEG-RMA-048 | Módulo Créditos pendentes/usados/disponíveis | N/A (nunca existiu) | quebrado | — | — | — | PENDENTE (reconstruir só a intenção: fluxo único de crédito) |

**Legenda de Status:** `PENDENTE` (aguardando OpenSpec) · `EM ESPECIFICAÇÃO` ·
`EM IMPLEMENTAÇÃO` · `PARIDADE` (implementado + QA aprovado) · `NÃO RECONSTRUIR`
(código morto em ambos os temas, decisão registrada) · `RETOMAR IDEIA` (o conceito é bom,
o código legado não é a base — ver backlog/decisão de arquitetura).

**2 itens já decididos como não-reconstrução** (LEG-RMA-016, LEG-RMA-034 — código morto
em ambos os temas) e **1 item como "retomar ideia, não código"** (LEG-RMA-035). **7 itens
em PARIDADE** (Fase 1, `LEG-RMA-001/003/004/005/006/042/043`). **1 item PENDENTE por
decisão de produto não tomada** (LEG-RMA-002). Os demais 37 itens aguardam as próximas
fases.
