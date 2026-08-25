# Tasks — Auditoria

**Pré-requisito descoberto na revisão desta fase (não estava listado antes):** só o
evento `RmaConcluido` existe hoje. Os outros 7 precisam ser criados e dispatados a
partir dos casos de uso já implementados nas Fases 3/4 — sem isso,
`RegistrarModificacaoDeRma` não tem o que assinar.

- [x] `app/Rma/Dominio/Eventos/{RmaCriado,RmaEditado,RmaRecebido,RmaEncaminhado,RmaArquivado,RmaRevertido,SolucaoRegistrada}.php`
- [x] Adicionar `::dispatch()` ao final de `CriarRma`/`EditarRma`/`ReceberRma`/
      `EncaminharRma`/`ArquivarRma`/`ReverterRmaParaEntrada`/`RegistrarSolucao` (Fases
      3/4) — extensão aditiva, não muda o comportamento já testado
- [x] `app/Rma/Dominio/Eventos/TentativaDeGravacaoNaoPermitida.php` — disparado por
      `RmaPolicy::update()` (Fase 3) antes de devolver `false`
- [x] `database/migrations/2026_09_01_000000_create_modificacoes_de_rma_table.php`
- [x] `app/Rma/Dominio/AcaoDeModificacao.php`
- [x] `app/Models/ModificacaoDeRma.php`
- [x] `app/Rma/Aplicacao/RegistrarModificacaoDeRma.php` (listener)
- [x] `app/Rma/Aplicacao/EnviarNotificacaoDeConclusao.php` (listener, Mailable)
- [x] `app/Rma/Aplicacao/EnviarNotificacaoDeTentativaNaoPermitida.php` (listener)
- [x] `app/Mail/RmaConcluidoMailable.php`
- [x] Registrar listeners no `EventServiceProvider` — projeto sem `EventServiceProvider`
      explícito (Laravel 13); registrados em `AppServiceProvider::boot()` via
      `Event::listen()`
- [x] `config/rma.php` — `notificacoes.conclusao` (destinatário via `.env`)
- [x] `app/Http/Controllers/Rma/HistoricoDeModificacaoController.php`
- [x] `app/Http/Controllers/Identidade/HistoricoDeAcessoController.php`
- [x] `app/Rma/Aplicacao/ConsolidarFretePorCidade.php`
- [x] `app/Rma/Aplicacao/BoletinsRelacionados.php`
- [x] `resources/views/rma/historico/index.blade.php`
- [x] `resources/views/identidade/historico-de-acesso/index.blade.php`
- [x] `resources/views/rma/logistica/{frete-porto-alegre,boletins-relacionados}.blade.php`
- [x] Rotas em `routes/web.php`
- [x] `tests/Feature/Rma/RegistrarModificacaoDeRmaTest.php`
- [x] `tests/Feature/Rma/EnviarNotificacaoDeConclusaoTest.php`
- [x] `tests/Feature/Rma/EnviarNotificacaoDeTentativaNaoPermitidaTest.php`
- [x] `tests/Feature/Rma/HistoricoDeModificacaoTest.php`
- [x] `tests/Feature/Identidade/HistoricoDeAcessoTest.php`
- [x] `tests/Unit/Rma/ConsolidarFretePorCidadeTest.php`
- [x] `tests/Feature/Rma/BoletinsRelacionadosTest.php`
- [x] `sail test` verde (248/248, mantendo os 221 das Fases 1-6)
- [x] Registrar pendência de `EVO-AUD-001` (diff vs. snapshot nomeado) — perguntar ao
      usuário se este nível já satisfaz a evolução ou se falta o diff campo-a-campo
      (ver `proposal.md` — pergunta feita ao usuário, ainda não respondida nesta sessão)
- [x] Atualizar `docs/produto/paridade-v2-v3.md` (`LEG-RMA-040/041/044/045`)
- [x] Atualizar `docs/produto/checklist-master-v3.md` (Fase 7 concluída)
- [x] Commit `#F7 - Auditoria (historico de modificacao, notificacoes, frete PoA, boletins)`
