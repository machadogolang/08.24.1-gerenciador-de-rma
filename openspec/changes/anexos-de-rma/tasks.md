# Tasks — Anexos de arquivo no RMA

**Pré-requisito:** Fase 10/QA de paridade da Trilha A concluída e commitada
(`INV-RMA-05` §15). Recomendado também a Fase A (Configuração de admin) já concluída,
por ordem pedida pelo usuário — não é dependência técnica dura (ver
`docs/produto/roadmap-evolucao-admin-arquivos.md` §Dependências), mas evita as duas
fases de Trilha B em paralelo sem necessidade.

- [ ] `database/migrations/{data}_create_anexos_de_rma_table.php`
- [ ] `app/Rma/Dominio/ArmazenamentoDeArquivos.php` (interface)
- [ ] `app/Rma/Dominio/AnexoDoRma.php` (value object/entidade de suporte — dados do
      anexo, sem lógica de storage)
- [ ] `app/Rma/Infraestrutura/ArmazenamentoLocal.php` (implementa a interface via
      `Illuminate\Filesystem`)
- [ ] `app/Models/AnexoDoRma.php` (Eloquent, uso interno da Infraestrutura)
- [ ] `app/Rma/Aplicacao/AnexarArquivoAoRma.php` (valida tipo/tamanho, gera caminho,
      chama `ArmazenamentoDeArquivos::guardar`, grava registro)
- [ ] `app/Rma/Aplicacao/RemoverAnexoDoRma.php` (chama
      `ArmazenamentoDeArquivos::remover` + apaga registro)
- [ ] `app/Rma/Aplicacao/BaixarAnexoDoRma.php` (chama
      `ArmazenamentoDeArquivos::baixar`, devolve conteúdo + tipo MIME)
- [ ] `app/Rma/Aplicacao/ListarAnexosDoRma.php`
- [ ] `app/Policies/AnexoDoRmaPolicy.php`
- [ ] `app/Http/Controllers/Rma/AnexoDoRmaController.php` — `index`/`store`/`show`
      (download)/`destroy`
- [ ] Rotas em `routes/web.php` (`rmas.anexos.index/store/show/destroy`, dentro do
      grupo `auth`)
- [ ] `resources/views/rma/_anexos.blade.php` (parcial — lista + form de upload +
      botão de remover, sem fidelidade visual/tema)
- [ ] Alterar `resources/views/rma/show.blade.php` — `@include('rma._anexos', ['rma'
      => $rma])` guardado por `@if(Route::has('rmas.anexos.index'))` (ver `design.md`
      §Desacoplamento — desligamento seguro)
- [ ] `config/filesystems.php` — confirmar/ajustar disco `local` (sem criar disco
      novo se o padrão do Laravel já resolve)
- [ ] `tests/Feature/Rma/AnexarArquivoAoRmaTest.php` (tipo permitido, tipo rejeitado,
      tamanho excedido, caminho sem dado pessoal)
- [ ] `tests/Feature/Rma/RemoverAnexoDoRmaTest.php`
- [ ] `tests/Feature/Rma/BaixarAnexoDoRmaTest.php` (autorização — não autenticado
      recebe 401/redirect)
- [ ] `tests/Feature/Rma/ListarAnexosDoRmaTest.php`
- [ ] **Regressão obrigatória:** rodar `sail test` completo — confirmar que
      `VerDetalheDoRmaTest.php` (Fase 3) e todos os testes de `RmaController`/
      `CicloDeVidaController` (Fases 3/4) continuam verdes sem alteração
- [ ] **Prova de desligamento seguro:** remover/comentar as rotas
      `rmas.anexos.*` temporariamente e confirmar que `show.blade.php` renderiza sem
      erro (o `@if(Route::has(...))` evita quebra); reverter antes de commitar
- [ ] Atualizar `docs/produto/backlog-evolutivo.md` (`EVO-ARQ-001` — marcar ponteiro
      para esta change como especificada)
- [ ] Commit `#ANEXOS-RMA - Sistema de anexos de arquivo no RMA (Trilha B)`
