# Tasks — Ciclo de vida do RMA

- [x] `database/migrations/2026_08_28_000000_add_ciclo_de_vida_fields_to_rmas_table.php`
- [x] `app/Rma/Dominio/Status.php`
- [x] `app/Rma/Dominio/Solucao.php`
- [x] `app/Rma/Dominio/Rma.php` — estender com campos de ciclo de vida +
      `comSnretornoAutoPreenchido()`
- [x] `app/Identidade/Dominio/Papel.php` — adicionar `podeReverterAlemDoMesmoDia()`
- [x] `app/Rma/Aplicacao/ReceberRma.php`
- [x] `app/Rma/Aplicacao/EncaminharRma.php`
- [x] `app/Rma/Aplicacao/ConcluirRma.php` (+ evento `RmaConcluido`)
- [x] `app/Rma/Aplicacao/ArquivarRma.php` (TEMA V2 como especificação)
- [x] `app/Rma/Aplicacao/ReverterRmaParaEntrada.php`
- [x] `app/Rma/Aplicacao/RegistrarSolucao.php`
- [x] Atualizar `app/Rma/Dominio/RepositorioDeRmas.php`/`RmasEmBanco.php` para
      persistir os novos campos
- [x] `app/Http/Controllers/Rma/CicloDeVidaController.php`
- [x] `resources/views/rma/_acoes_de_transicao.blade.php`
- [x] Rotas em `routes/web.php`
- [x] `tests/Feature/Rma/ReceberRmaTest.php`
- [x] `tests/Feature/Rma/EncaminharRmaTest.php`
- [x] `tests/Feature/Rma/ConcluirRmaTest.php`
- [x] `tests/Feature/Rma/ArquivarRmaTest.php` (prova TEMA V2 — não reproduz Fatal Error de TEMA V1)
- [x] `tests/Feature/Rma/ReverterRmaParaEntradaTest.php`
- [x] `tests/Feature/Rma/RegistrarSolucaoTest.php`
- [x] `tests/Unit/Rma/StatusTest.php`
- [x] `tests/Unit/Rma/SolucaoTest.php`
- [x] `sail test` verde
- [x] Atualizar `docs/produto/paridade-v2-v3.md`
      (`LEG-RMA-011/012/013/014/015/016/017/047`)
- [x] Atualizar `docs/produto/checklist-master-v3.md` (Fase 4 concluída)
- [x] Commit `#F4 - Ciclo de vida (receber/encaminhar/concluir/arquivar/reverter)`
