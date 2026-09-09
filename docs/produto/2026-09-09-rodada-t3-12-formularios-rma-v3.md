# Rodada T3-12 - formularios de RMA no Tema V3

Data: 2026-09-09. Apos o fechamento do handoff PAR-V2 (`93fd4e4`, PUSH NAO
REALIZADO), o dono autorizou seguir com os proximos itens do plano. O proximo
item exato registrado no handoff e T3-12 - RMA formularios no Tema V3.

## Baseline real

- HEAD local: `93fd4e4387140be3d2395717b53dad3fec944136`; `origin/main`
  continua em `24ffd4e`; working tree limpa.
- PAR-V2 fechado; PHPUnit completo 532 testes / 1582 assertions verde.
- T3-00 a T3-11 concluidos; T3-12 e a proxima task.

## Escopo desta rodada

- Formularios de Novo e Editar RMA do Tema V3 (`/v3/rmas/novo`,
  `POST /v3/rmas`, `/v3/rma/{rma}/editar`, `PUT /v3/rma/{rma}`).
- Organizacao por secoes de significado (identificacao, origem/parceiros,
  fiscal, operacao, observacoes), 1 coluna no mobile e 2 colunas com relacao
  real no desktop.
- Mesmos controllers/casos de uso modernos (`RmaController`, `CriarRma`,
  `EditarRma`, Policies e tenant); nenhuma regra de negocio nova no Blade/SCSS.
- Erros de validacao no topo e junto ao campo; salvar/cancelar persistentes;
  botoes de Novo/Editar apenas quando a Policy permite.
- Testes: Feature/PHPUnit dirigido + Playwright dirigido + build Vite.

## Regras preservadas

- Tema V3 continua oculto e nao selecionavel no perfil.
- Nenhum push; commits locais por checkpoint; handoff por ultimo.
- Hifen sempre simples; sem hifen longo em nenhum conteudo gerado.

## Resultado

- Commit local: `57f1c13` (#FRONT-RMA).
- Rotas V3: `/v3/rmas/novo`, `POST /v3/rmas`, `/v3/rma/{id}/editar`,
  `PUT/PATCH /v3/rma/{id}` usando o mesmo `RmaController`/casos de uso.
- Views `create`/`edit` + partial `_form.blade.php` em secoes (Identificacao,
  Origem e parceiros, Fiscal, Operacao, Observacoes), 1 coluna no mobile e 2-3
  colunas por relacao no desktop; erros no topo; salvar/cancelar persistentes.
- Listagem e detalhe V3 ganharam Novo RMA/Editar RMA apenas quando a Policy
  permite. V3 continua oculto e nao selecionavel.
- Testes: `FormulariosRmaV3Test` (4 testes / 37 assertions),
  `FormulariosRmaV3.spec.ts` (2/2), PHPUnit completo 536 testes / 1619
  assertions 100% verde, Vite build verde. PUSH NAO REALIZADO.
- Proximo item do plano: T3-13 (Parceiros).
