# Tasks — Parceiros

- [x] `database/migrations/2026_08_26_000000_create_clientes_table.php`
- [x] `database/migrations/2026_08_26_000001_create_fabricantes_table.php`
- [x] `database/migrations/2026_08_26_000002_create_fornecedores_table.php`
- [x] `database/migrations/2026_08_26_000003_create_assistencias_tecnicas_table.php`
- [x] `app/Compartilhado/Uf.php` (enum das 27 UFs — ajuste da revisão, ver
      `docs/arquitetura/revisao-fases-1-2-3.md`)
- [x] `app/Parceiros/Concerns/TemEnderecoEContato.php` (trait)
- [x] `app/Models/Cliente.php`
- [x] `app/Models/Fabricante.php`
- [x] `app/Models/Fornecedor.php`
- [x] `app/Models/AssistenciaTecnica.php`
- [x] `app/Parceiros/Aplicacao/EncontrarOuCriarCliente.php`
- [x] `app/Policies/ClientePolicy.php` (+ Fabricante/Fornecedor/AssistenciaTecnica)
- [x] `app/Http/Controllers/Parceiros/ClienteController.php` (+ 3 análogos)
- [x] `resources/views/parceiros/_form.blade.php`
- [x] `resources/views/parceiros/index.blade.php`
- [x] Rotas resource ×4 em `routes/web.php`
- [x] `database/factories/ClienteFactory.php` (+ 3 análogas)
- [x] `tests/Feature/Parceiros/ClienteCrudTest.php` (+ 3 análogos)
- [x] `tests/Feature/Parceiros/EncontrarOuCriarClienteTest.php`
- [x] `sail test` verde
- [x] Atualizar `docs/produto/paridade-v2-v3.md` (`LEG-RMA-030` a `033`)
- [x] Atualizar `docs/produto/checklist-master-v3.md` (Fase 2 concluída)
- [x] Commit `#F2 - Parceiros (cliente/fabricante/fornecedor/assistência técnica)`
