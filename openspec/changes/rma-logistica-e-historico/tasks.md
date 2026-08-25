# Tasks — Auditoria

**Pré-requisito descoberto na revisão desta fase (não estava listado antes):** só o
evento `RmaConcluido` existe hoje. Os outros 7 precisam ser criados e dispatados a
partir dos casos de uso já implementados nas Fases 3/4 — sem isso,
`RegistrarModificacaoDeRma` não tem o que assinar.

- [ ] `app/Rma/Dominio/Eventos/{RmaCriado,RmaEditado,RmaRecebido,RmaEncaminhado,RmaArquivado,RmaRevertido,SolucaoRegistrada}.php`
- [ ] Adicionar `::dispatch()` ao final de `CriarRma`/`EditarRma`/`ReceberRma`/
      `EncaminharRma`/`ArquivarRma`/`ReverterRmaParaEntrada`/`RegistrarSolucao` (Fases
      3/4) — extensão aditiva, não muda o comportamento já testado
- [ ] `app/Rma/Dominio/Eventos/TentativaDeGravacaoNaoPermitida.php` — disparado por
      `RmaPolicy::update()` (Fase 3) antes de devolver `false`
- [ ] `database/migrations/2026_08_31_000000_create_modificacoes_de_rma_table.php`
- [ ] `app/Rma/Dominio/AcaoDeModificacao.php`
- [ ] `app/Models/ModificacaoDeRma.php`
- [ ] `app/Rma/Aplicacao/RegistrarModificacaoDeRma.php` (listener)
- [ ] `app/Rma/Aplicacao/EnviarNotificacaoDeConclusao.php` (listener, Mailable)
- [ ] `app/Rma/Aplicacao/EnviarNotificacaoDeTentativaNaoPermitida.php` (listener)
- [ ] `app/Mail/RmaConcluidoMailable.php`
- [ ] Registrar listeners no `EventServiceProvider`
- [ ] `config/rma.php` — `notificacoes.conclusao` (destinatário via `.env`)
- [ ] `app/Http/Controllers/Rma/HistoricoDeModificacaoController.php`
- [ ] `app/Http/Controllers/Identidade/HistoricoDeAcessoController.php`
- [ ] `app/Rma/Aplicacao/ConsolidarFretePorCidade.php`
- [ ] `app/Rma/Aplicacao/BoletinsRelacionados.php`
- [ ] `resources/views/rma/historico/index.blade.php`
- [ ] `resources/views/identidade/historico-de-acesso/index.blade.php`
- [ ] `resources/views/rma/logistica/{frete-porto-alegre,boletins-relacionados}.blade.php`
- [ ] Rotas em `routes/web.php`
- [ ] `tests/Feature/Rma/RegistrarModificacaoDeRmaTest.php`
- [ ] `tests/Feature/Rma/EnviarNotificacaoDeConclusaoTest.php`
- [ ] `tests/Feature/Rma/EnviarNotificacaoDeTentativaNaoPermitidaTest.php`
- [ ] `tests/Feature/Rma/HistoricoDeModificacaoTest.php`
- [ ] `tests/Feature/Identidade/HistoricoDeAcessoTest.php`
- [ ] `tests/Unit/Rma/ConsolidarFretePorCidadeTest.php`
- [ ] `tests/Feature/Rma/BoletinsRelacionadosTest.php`
- [ ] `sail test` verde
- [ ] Registrar pendência de `EVO-AUD-001` (diff vs. snapshot nomeado) — perguntar ao
      usuário se este nível já satisfaz a evolução ou se falta o diff campo-a-campo
- [ ] Atualizar `docs/produto/paridade-v2-v3.md` (`LEG-RMA-040/041/044/045`)
- [ ] Atualizar `docs/produto/checklist-master-v3.md` (Fase 7 concluída)
- [ ] Commit `#F7 - Auditoria (historico de modificacao, notificacoes, frete PoA, boletins)`
