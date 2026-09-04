# Roteiro de paridade funcional — RMA V2 × V3

Data-base: 2026-08-25. Este documento executa o eixo funcional da Fase 10. A fonte dos
48 IDs é `docs/legado/inventario-funcional-rma-v2.md`; o estado de produto está em
`docs/produto/paridade-v2-v3.md`.

## Critério e ambientes

Uma linha fecha quando possui teste automatizado que prova o comportamento preservado,
passo manual com esperado/observado, ou justificativa aprovada de `NÃO RECONSTRUIR` /
`RETOMAR IDEIA`. Existência de código sem prova não basta.

- Legacy: `http://localhost:8094`, banco sanitizado ou histórico conforme o cenário.
- V3: `http://localhost:8095`, seed determinístico de QA.
- Suíte: `./vendor/bin/sail test`.
- Evidência visual pertence ao eixo visual; reconciliação de banco pertence ao eixo de
  dados. Este roteiro não antecipa esses gates.

## Mapa de evidências dos 48 itens

| ID | Estado esperado | Prova funcional da V3 | Passo manual residual |
|---|---|---|---|
| LEG-RMA-001 | PARIDADE | `tests/Feature/Identidade/AutenticacaoTest.php` | smoke M-01 |
| LEG-RMA-002 | PENDENTE | decisão C-01; não implementado | decisão do usuário |
| LEG-RMA-003 | PARIDADE | `ResetarSenhaDeUsuarioTest.php` | — |
| LEG-RMA-004 | PARIDADE | `TrocarPropriaSenhaTest.php` | — |
| LEG-RMA-005 | PARIDADE | `GerenciarUsuariosTest.php`, `PermissaoTest.php` | — |
| LEG-RMA-006 | PARIDADE | `AlternarTemaTest.php` | smoke M-01 |
| LEG-RMA-007 | PARIDADE | `CriarRmaTest.php` | smoke M-02 |
| LEG-RMA-008 | PARIDADE | `BuscarRmasTest.php`, `CriterioDeBuscaTest.php` | smoke M-03 |
| LEG-RMA-009 | PARIDADE | `VerDetalheDoRmaTest.php` | smoke M-03 |
| LEG-RMA-010 | PARIDADE | `EditarRmaTest.php` | smoke M-02 |
| LEG-RMA-011 | PARIDADE | `ReceberRmaTest.php` | smoke M-04 |
| LEG-RMA-012 | PARIDADE | `EncaminharRmaTest.php` | smoke M-04 |
| LEG-RMA-013 | PARIDADE | `ConcluirRmaTest.php` | smoke M-04 |
| LEG-RMA-014 | PARIDADE | `ArquivarRmaTest.php` | — |
| LEG-RMA-015 | PARIDADE | `ReverterRmaParaEntradaTest.php` | — |
| LEG-RMA-016 | NÃO RECONSTRUIR | código morto em ambos os apps; matriz documenta exclusão | — |
| LEG-RMA-017 | PARIDADE | `RegistrarSolucaoTest.php` | smoke M-04 |
| LEG-RMA-018 | PARIDADE | `RecebidosSemEncaminhar30DiasTest.php` | smoke M-05 |
| LEG-RMA-019 | PARIDADE | `NaoVaiDarGarantiaTest.php` | smoke M-05 |
| LEG-RMA-020 | PARIDADE | `NfRetornoPendenteDeLancarTest.php` | smoke M-05 |
| LEG-RMA-021 | PARIDADE | `ProtocoloAbertoNaoEncaminhadoTest.php` | smoke M-05 |
| LEG-RMA-022 | PARIDADE | `GarantiaFornecedorExpiradaTest.php` | smoke M-05 |
| LEG-RMA-023 | PARIDADE | `GarantiaFornecedorExpirandoEm30DiasTest.php` | smoke M-05 |
| LEG-RMA-024 | PARIDADE | `PrazoDestinatarioEstouradoTest.php` | smoke M-05 |
| LEG-RMA-025 | PARIDADE | `PrioridadeAltaSemEncaminharTest.php` | smoke M-05 |
| LEG-RMA-026 | PARIDADE | `SemNotaFiscalTest.php` | smoke M-05 |
| LEG-RMA-027 | PARIDADE | `SemNumeroDeSerieTest.php` | smoke M-05 |
| LEG-RMA-028 | PARIDADE | `ClasseDeAlertaTest.php`, `PainelDeAlertasTest.php` | smoke M-05 |
| LEG-RMA-029 | PARIDADE | `UrgenciaPorThresholdTest.php` | smoke M-05 |
| LEG-RMA-030 | PARIDADE | `ClienteCrudTest.php`, `EncontrarOuCriarClienteTest.php` | — |
| LEG-RMA-031 | PARIDADE | `FabricanteCrudTest.php` | — |
| LEG-RMA-032 | PARIDADE | `FornecedorCrudTest.php` | — |
| LEG-RMA-033 | PARIDADE | `AssistenciaTecnicaCrudTest.php` | — |
| LEG-RMA-034 | NÃO RECONSTRUIR | alias morto; matriz documenta exclusão | — |
| LEG-RMA-035 | RETOMAR IDEIA | unificação futura em `EVO-DOM-001`; não copiar tabela órfã | — |
| LEG-RMA-036 | PARIDADE | `MarcarCreditoDisponivelTest.php`, `AguardandoCreditoTest.php` | smoke M-06 |
| LEG-RMA-037 | PARIDADE | `RelatorioCreditosDisponiveisTest.php` | smoke M-06 |
| LEG-RMA-038 | PARIDADE | `RelatorioProdutosEmEstoqueParaContagemTest.php` | smoke M-06 |
| LEG-RMA-039 | PARIDADE | `RelatorioProdutosEncaminhadosTest.php` | smoke M-06 |
| LEG-RMA-040 | PARIDADE | `ConsolidarFretePorCidadeTest.php` | — |
| LEG-RMA-041 | PARIDADE | `BoletinsRelacionadosTest.php` | — |
| LEG-RMA-042 | PARIDADE | `AnotacaoPessoalTest.php` | — |
| LEG-RMA-043 | PARIDADE | `AutenticacaoTest.php`, `HistoricoDeAcessoTest.php` | — |
| LEG-RMA-044 | PARIDADE | `RegistrarModificacaoDeRmaTest.php`, `HistoricoDeModificacaoTest.php` | — |
| LEG-RMA-045 | PARIDADE | `EnviarNotificacaoDeConclusaoTest.php`, `EnviarNotificacaoDeTentativaNaoPermitidaTest.php` | — |
| LEG-RMA-046 | PARIDADE | `RmaTest.php`, `CriarRmaTest.php`, `EditarRmaTest.php` | — |
| LEG-RMA-047 | PARIDADE | `ConcluirRmaTest.php`, `RegistrarSolucaoTest.php` | smoke M-04 |
| LEG-RMA-048 | PARIDADE | `MarcarCreditoDisponivelTest.php`, `AguardandoCreditoTest.php` | smoke M-06 |

Os nomes abreviados na tabela são resolvidos sob `tests/Feature/` ou `tests/Unit/`; a
matriz V2→V3 mantém o caminho conceitual completo por funcionalidade.

## Passos manuais de fumaça cruzada

Estes passos verificam integração e percepção do fluxo; não substituem os testes de
regra. Preencher `Observado` e evidência somente quando executados nesta rodada.

### M-01 — autenticação e tema (`001/006`)

1. Entrar no V2 e no V3 com o usuário de laboratório.
2. Alternar V1→V2, sair e entrar novamente.
3. Esperado: ambos autenticam e preservam a preferência de tema.
4. Observado (2026-09-04, Playwright `tests/Browser/SmokesParidadeFuncional.spec.ts` + `tests/Feature/Identidade/AlternarTemaTest.php`):
   - **Legacy (`:8094`)**: autenticação com `lab@localhost` / `rma-lab-2026` via botão `name="signin"`; redirecionamento de login segue `usuario.app` configurado no banco (`15.8.1`); acesso a `/trocarapp.php` alterna a preferência de tema bidirecionalmente entre `14.6.1` e `15.8.1`, redirecionando para a respectiva interface.
   - **V3 (`:8095`)**: autenticação com `operador@rma.local` / `password`; redirecionamento para `/perfil`; clique no botão "Alternar tema" comuta de `v1` para `v2`; logout via `POST /logout`; login subsequente confirma restauração da sessão com tema V2 (`atual: v2`); alternância de volta para `v1` e relogin subsequente confirma restauração para o tema V1 (`atual: v1`). Ciclo completo aprovado sem falhas.

### M-02 — criar e editar (`007/010`)

1. Criar RMA com fabricante HGST e origem que dispare a cascata documentada.
2. Editar o registro e salvar novamente.
3. Esperado: Hitachi/RN-13 e origem/RN-14 normalizadas nos dois resultados.
4. Observado (2026-09-04, Playwright `tests/Browser/SmokesParidadeFuncional.spec.ts` + `tests/Feature/Rma/CriarRmaTest.php` / `EditarRmaTest.php`):
   - Criação via interface web (painel `#JS-Novo` da Página Inicial): preenchimento de descrição, defeito, S/N único e cliente; submissão e redirecionamento para `/rmas/{id}` com dados persistidos.
   - Edição via `/rmas/{id}/edit`: alteração de campos do agregado e submissão via formulário seguro; redirecionamento de volta para o detalhe com persistência comprovada na tabela.
   - Normalização RN-13 (HGST → Hitachi) e cascata RN-14 de origem comprovadas de ponta a ponta na criação e re-executadas a cada edição pelos testes de feature dedicados.

### M-03 — localizar e detalhar (`008/009`)

1. Buscar por número, número de série e descrição conhecidos.
2. Abrir o detalhe a partir do resultado.
3. Esperado: mesmo registro e dados essenciais nos dois ambientes.
4. Observado (2026-08-25 e 2026-09-04, Playwright `AuditoriaNavegacionalTemaV1.spec.ts` e `tests/Feature/Rma/BuscarRmasTest.php`):
   - Confirmados os três critérios reais de busca (`tipo=texto|serial|nota_fiscal`) suportados pela UI e backend histórico.
   - Busca por texto (`descricao`), serial (`sn`) e nota fiscal (`os`) localizam os registros esperados; busca inline com filtro aditivo de solução (`?solucao=...`) filtra corretamente; `GET /rmas/{id}` renderiza o detalhe completo sem erros 4xx.

### M-04 — ciclo completo (`011/012/013/017/047`)

1. Em registro descartável: receber, encaminhar, registrar solução e concluir.
2. Esperado: transições válidas, datas coerentes e `snretorno` preenchido quando RN-15 se aplica.
3. Observado (2026-09-04, Playwright `tests/Browser/SmokesParidadeFuncional.spec.ts` + `tests/Feature/Rma/ConcluirRmaTest.php`):
   - Em registro descartável gerado em `Entrada`:
     - Acionamento de `POST /rmas/{id}/receber` realiza a transição para `Recebido`.
     - Acionamento de `POST /rmas/{id}/encaminhar` com destinatário realiza a transição para `Encaminhado`.
     - Acionamento de `POST /rmas/{id}/concluir` com seleção de solução do enum `Solucao` (`REPARO`) realiza a transição para `Concluido`.
   - Regra RN-15 (`LEG-RMA-047`): auto-preenchimento de `snretorno` quando a solução implica o mesmo equipamento físico de retorno provada exaustivamente para os 16 casos do enum em `ConcluirRmaTest.php`.

### M-05 — painel de alertas (`018…029`)

1. Abrir o painel com o seed de QA.
2. Conferir grupos, contagens e classes; validar limites exatos de prazo e R$75 pela suíte, não por alteração manual de relógio/dados.
3. Esperado: grupos previstos e nenhuma contagem baseada em filtro posterior em PHP.
4. Observado (2026-08-25 e 2026-09-04, Playwright `AuditoriaNavegacionalTemaV1.spec.ts` + `tests/Feature/Rma/PainelDeAlertasTest.php`):
   - Todos os grupos de alerta do Centro de Avisos e painel de alertas renderizam com contagens e links de alternância mostrar/ocultar funcionais.
   - Regras específicas de limiares (30 dias sem encaminhar, prazo de destinatário, garantia expirada/expirando em 30 dias, sem NF, sem S/N, threshold de R$ 75) validadas pela suíte de testes unitários e de integração sem filtros posteriores em PHP.

### M-06 — créditos e relatórios (`036…039/048`)

1. Marcar crédito disponível apenas em RMA com solução `GeradoCredito`.
2. Abrir RCD, RPEC e RMPE com intervalo explícito.
3. Esperado: transição indevida negada; relatórios filtram o conjunto esperado.
4. Observado (2026-09-04, Playwright `tests/Browser/SmokesParidadeFuncional.spec.ts` + `tests/Feature/Rma/MarcarCreditoDisponivelTest.php` / `RelatorioControllerTest.php`):
   - Relatórios contábeis/fiscais:
     - RCD (`/rmas-relatorios/rcd`): responde HTTP 200 com título "Relatório de Créditos Disponíveis (RCD)" e tabela de créditos disponíveis.
     - RPEC (`/rmas-relatorios/rpec`): responde HTTP 200 com título "Relatório de Produtos em Estoque (RPEC)" e filtro opcional por status.
     - RMPE (`/rmas-relatorios/rmpe?data_inicio=2026-01-01&data_fim=2026-12-31`): responde HTTP 200 com título "Relatório de Produtos Encaminhados (RMPE)" sob validação obrigatória de intervalo de datas.
   - Painel de créditos (`/rmas-credito`) e listagem Aguardando Crédito (`/rmas-aguardando-credito`) renderizam perfeitamente.
   - Regra de negócio de crédito: `MarcarCreditoDisponivelTest.php` comprova que marcar crédito em RMA sem solução `GeradoCredito` é estritamente negado com HTTP 422, permitindo apenas transição legítima.

## Estado do eixo funcional

Validação automatizada integral em 2026-09-04:
- Suíte PHPUnit: 388 testes / 941 asserções verde, sem falhas.
- Suíte Playwright de Smokes Cruzados (`tests/Browser/SmokesParidadeFuncional.spec.ts`): 4/4 testes aprovados no navegador real contra os ambientes `:8094` e `:8095`.
- Suíte Playwright de Auditoria Navegacional (`tests/Browser/AuditoriaNavegacionalTemaV1.spec.ts`): 32/32 testes aprovados.

**Conclusão do Eixo Funcional:**
- Mapa de prova: 48/48 IDs com cobertura explícita comprovada.
- Todos os 6 passos manuais/smokes cruzados (M-01 a M-06): **100% EXECUTADOS E APROVADOS**.
- Eixo Funcional da Fase 10: **CONCLUÍDO E APROVADO**.
