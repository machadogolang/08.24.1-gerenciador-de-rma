# Tasks — Rma núcleo

- [x] `database/migrations/2026_08_27_000000_create_rmas_table.php` (schema desta fase)
- [x] `app/Rma/Dominio/Rma.php`
- [x] `app/Rma/Dominio/RepositorioDeRmas.php` (ganhou `atualizar()`, não previsto no
      snippet original — necessário para `EditarRma`)
- [x] `app/Rma/Dominio/CriterioDeBusca.php`
- [x] `app/Rma/Infraestrutura/RmasEmBanco.php`
- [x] `app/Models/Rma.php` (Eloquent, uso interno da infra)
- [x] `app/Rma/Aplicacao/CriarRma.php` (aplica `comNormalizacaoDeGravacao`, RN-13/RN-14/RN-17)
- [x] `app/Rma/Aplicacao/EditarRma.php` (idem — ajuste da revisão, `LEG-RMA-010`)
- [x] `app/Rma/Aplicacao/BuscarRmas.php`
- [x] `app/Rma/Aplicacao/VerDetalheDoRma.php`
- [x] Registrar binding `RepositorioDeRmas -> RmasEmBanco` (`AppServiceProvider`)
- [x] `app/Http/Controllers/Rma/RmaController.php` (inclui `edit`/`update`)
- [x] `resources/views/rma/{index,create,edit,show}.blade.php`
- [x] Rotas em `routes/web.php`
- [x] `database/factories/RmaFactory.php`
- [x] `tests/Feature/Rma/CriarRmaTest.php`
- [x] `tests/Feature/Rma/EditarRmaTest.php`
- [x] `tests/Feature/Rma/BuscarRmasTest.php`
- [x] `tests/Feature/Rma/VerDetalheDoRmaTest.php`
- [x] `tests/Unit/Rma/CriterioDeBuscaTest.php`
- [x] `tests/Unit/Rma/RmaTest.php` (`comNormalizacaoDeGravacao` — RN-13/RN-14, casos do design.md)
- [x] `sail test` verde (85/85)
- [x] Atualizar `docs/produto/paridade-v2-v3.md` (`LEG-RMA-007`, `008`, `009`, `010`, `046`)
- [x] Atualizar `docs/produto/checklist-master-v3.md` (Fase 3 concluída)
- [x] Commit `#F3 - Rma núcleo (criação, busca, detalhe)`
