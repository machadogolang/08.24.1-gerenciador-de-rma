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
4. Observado (2026-08-25, Playwright manual, leitura apenas): autenticação
   confirmada nos dois ambientes — Legacy (`lab@localhost`) autentica e abre em
   `/15.8.1/...` com o painel real (1.379 itens, bate com o snapshot
   documentado); V3 (`operador@rma.local`) autentica e é redirecionado a
   `/perfil`. Localizado o alternador de tema real em `/perfil` → botão
   "Alternar tema (atual: v1)" (não é um link `<a>`, por isso não aparece em
   varredura de navegação por `href`). **Parcial**: o clique no alternador e a
   persistência após logout/login não foram confirmados nesta rodada — o
   script de automação expirou (60s) na etapa de logout, possivelmente porque
   "SIGN OUT" é um botão dentro de formulário POST, não um link simples.
   Login/tema não apresentaram nenhum erro visível até onde foi observado;
   falta só fechar o ciclo alternar→sair→entrar→conferir.

### M-02 — criar e editar (`007/010`)

1. Criar RMA com fabricante HGST e origem que dispare a cascata documentada.
2. Editar o registro e salvar novamente.
3. Esperado: Hitachi/RN-13 e origem/RN-14 normalizadas nos dois resultados.
4. Observado: **não executado nesta rodada**.

### M-03 — localizar e detalhar (`008/009`)

1. Buscar por número, número de série e descrição conhecidos.
2. Abrir o detalhe a partir do resultado.
3. Esperado: mesmo registro e dados essenciais nos dois ambientes.
4. Observado (2026-08-25, Playwright manual, leitura apenas, sobre RMA
   `id=1`/`PROTOCOLO-QA-0001` do seed de QA da V3): confirmados os três tipos
   de busca reais da tela (`tipo=texto|serial|nota_fiscal` — não existe um
   quarto tipo "número"/protocolo na UI nem no backend, ver achado abaixo).
   Busca por texto (`"Equipamento ficticio QA 001"`, campo `descricao`)
   encontrou o registro; busca por serial (`tipo=serial`, valor
   `SN-QA-000001`, campo `sn`) encontrou; busca por nota fiscal (`tipo=nota_fiscal`,
   valor `OS-QA-00001`, campo `os`) encontrou. `GET /rmas/1` abre o detalhe
   (`RMA #1`) sem erro. Não comparado lado a lado com um registro equivalente
   no Legacy nesta rodada — o seed de QA da V3 é fictício e não tem
   correspondente garantido no snapshot histórico do Legacy.
   **Achado, não é regressão:** a frase deste passo ("buscar por número...")
   sugere um terceiro campo de busca por número/protocolo do RMA, mas a tela
   (`resources/views/temas/v1/rma/index.blade.php:8-11`) e o backend
   (`app/Http/Controllers/Rma/RmaController.php:33-36`) só oferecem
   Texto/Serial/Nota fiscal — não há busca por protocolo. Como
   `CriterioDeBusca`/`CriterioDeBuscaTest.php` já cobrem exatamente esses três
   tipos e nenhum documento anterior desta sessão registra "número" como um
   quarto tipo pendente, isto é registrado como observação para confirmação
   de escopo, não como lacuna assumida.

### M-04 — ciclo completo (`011/012/013/017/047`)

1. Em registro descartável: receber, encaminhar, registrar solução e concluir.
2. Esperado: transições válidas, datas coerentes e `snretorno` preenchido quando RN-15
   se aplica.
3. Observado: **não executado nesta rodada**.

### M-05 — painel de alertas (`018…029`)

1. Abrir o painel com o seed de QA.
2. Conferir grupos, contagens e classes; validar limites exatos de prazo e R$75 pela
   suíte, não por alteração manual de relógio/dados.
3. Esperado: grupos previstos e nenhuma contagem baseada em filtro posterior em PHP.
4. Observado (2026-08-25, Playwright manual, leitura apenas, `GET /rmas-alertas`
   com `supervisor@rma.local`, seed de QA de 60 RMAs): os 11 grupos da Fase 5
   apareceram todos, com título e contagem entre parênteses — "Recebidos há
   mais de 30 dias sem encaminhar (12)", "Não vai dar garantia (0)", "NF de
   retorno pendente de lançar (0)", "Protocolo aberto não encaminhado (12)",
   "Garantia do fornecedor expirada (0)", "Garantia do fornecedor expirando em
   até 30 dias (0)", "Prazo do destinatário estourado (12)", "Prioridade alta
   sem encaminhar (8)", "Sem nota fiscal (12)", "Sem número de série (0)",
   "Urgência por valor (threshold R$75) (12)". Nenhum grupo ausente, nenhum
   erro. Não foi conferido nesta rodada se os limites exatos (30 dias, R$75)
   batem regra por regra — isso já é responsabilidade da suíte automatizada
   (`UrgenciaPorThresholdTest.php` etc., verde na execução mais recente
   registrada), este passo manual só confirma que a composição chega
   corretamente à tela.

### M-06 — créditos e relatórios (`036…039/048`)

1. Marcar crédito disponível apenas em RMA com solução `GeradoCredito`.
2. Abrir RCD, RPEC e RMPE com intervalo explícito.
3. Esperado: transição indevida negada; relatórios filtram o conjunto esperado.
4. Observado: **não executado nesta rodada**.

## Estado do eixo funcional

Validação automatizada renovada em 2026-08-25: 310 testes e 608 assertions aprovados,
sem skips reportados pelo runner compacto. Essa execução sustenta o mapa, mas não fecha
`F10-GATE-01`, reservado à regressão final depois dos três eixos. Nenhum teste foi
alterado ou executado de novo nesta rodada — só os smokes manuais abaixo, e nenhum
código de produção foi tocado.

- Mapa de prova: completo para 48/48 IDs.
- Execução manual desta rodada (2026-08-25, sessão seguinte): M-03 e M-05
  completos e sem achado bloqueante; M-01 parcial (login dos dois lados
  confirmado, alternância de tema localizada mas não fechada por timeout de
  automação). M-02, M-04 e M-06 continuam **não executados** — dependem de
  registro descartável (M-02/M-04) ou de decisão sobre mutação de estado
  (M-06), fora do escopo do ciclo curto desta sessão.
- Achado de escopo a confirmar (não bloqueante): a redação de M-03 cita busca
  "por número", mas a tela e o backend só implementam Texto/Serial/Nota
  fiscal — ver observação completa no passo M-03 acima.
- Decisão externa: `LEG-RMA-002`.
- Gate funcional: **aberto** — faltam fechar M-01 (persistência pós-relogin),
  executar M-02/M-04/M-06 e registrar a decisão ou adiamento explícito de
  `LEG-RMA-002`.
