# Tasks — Ciclo de vida do RMA

- [ ] `database/migrations/2026_08_28_000000_add_ciclo_de_vida_fields_to_rmas_table.php`
- [ ] `app/Rma/Dominio/Status.php`
- [ ] `app/Rma/Dominio/Solucao.php`
- [ ] `app/Rma/Dominio/Rma.php` — estender com campos de ciclo de vida +
      `comSnretornoAutoPreenchido()`
- [ ] `app/Identidade/Dominio/Papel.php` — adicionar `podeReverterAlemDoMesmoDia()`
- [ ] `app/Rma/Aplicacao/ReceberRma.php`
- [ ] `app/Rma/Aplicacao/EncaminharRma.php`
- [ ] `app/Rma/Aplicacao/ConcluirRma.php` (+ evento `RmaConcluido`)
- [ ] `app/Rma/Aplicacao/ArquivarRma.php` (TEMA V2 como especificação)
- [ ] `app/Rma/Aplicacao/ReverterRmaParaEntrada.php`
- [ ] `app/Rma/Aplicacao/RegistrarSolucao.php`
- [ ] Atualizar `app/Rma/Dominio/RepositorioDeRmas.php`/`RmasEmBanco.php` para
      persistir os novos campos
- [ ] `app/Http/Controllers/Rma/CicloDeVidaController.php`
- [ ] `resources/views/rma/_acoes_de_transicao.blade.php`
- [ ] Rotas em `routes/web.php`
- [ ] `tests/Feature/Rma/ReceberRmaTest.php`
- [ ] `tests/Feature/Rma/EncaminharRmaTest.php`
- [ ] `tests/Feature/Rma/ConcluirRmaTest.php`
- [ ] `tests/Feature/Rma/ArquivarRmaTest.php` (prova TEMA V2 — não reproduz Fatal Error de TEMA V1)
- [ ] `tests/Feature/Rma/ReverterRmaParaEntradaTest.php`
- [ ] `tests/Feature/Rma/RegistrarSolucaoTest.php`
- [ ] `tests/Unit/Rma/StatusTest.php`
- [ ] `tests/Unit/Rma/SolucaoTest.php`
- [ ] `sail test` verde
- [ ] Atualizar `docs/produto/paridade-v2-v3.md`
      (`LEG-RMA-011/012/013/014/015/016/017/047`)
- [ ] Atualizar `docs/produto/checklist-master-v3.md` (Fase 4 concluída)
- [ ] Commit `#F4 - Ciclo de vida (receber/encaminhar/concluir/arquivar/reverter)`
