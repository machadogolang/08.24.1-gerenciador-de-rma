# Design — FRONT-003

- Controllers únicos retornam `view_do_tema('<modulo>.index')` com os mesmos dados.
- Conteúdo em `resources/views/{modulo}/_conteudo.blade.php` compartilhado; wrappers
  finos em `temas/v1/{modulo}/index.blade.php` e `temas/v2/...`.
- Sem terceira folha CSS; classes V1/V2 próprias. Sem duplicar regra/Gate/tenant.
- Relatórios: wrapper de página + `@media print` escopado para esconder chrome
  (menu/sidebar/rodapé), mantendo apenas conteúdo/tabela.
- Controle V1: correção local com classe escopada
  `.controle-adicionar-representante`; sem alterar `_v1-base.scss` global.
- Identificador de RMA: auditoria em superfícies que exibem `#`/`NUMERO`; não usar
  Eloquent em Blade; decidir exposição de `numero_da_empresa` na fronteira correta.
