# Checkpoint - T3-11: detalhe operacional do RMA no Tema V3

Data: 2026-09-09. Baseline HEAD `fd16f9d`; origin/main `fd16f9d`.

## Implementado

- Rota `GET /v3/rma/{rma}` (`v3.rmas.show`) em `routes/tema-v3.php`.
- `V3ConsoleController::detalhe()` reusa `VerDetalheDoRma` e
  `CamposDeExibicaoDoRmaEmBanco`, sem SQL em view.
- View `resources/views/temas/v3/rma/show.blade.php`:
  - cabecalho operacional: numero do RMA, descricao, status, prioridade e proxima
    acao;
  - secoes: Resumo, Produto, Parceiros e origem, Fiscal, Destinatario e logistica,
    Solucao e credito, Historico e auditoria;
  - desktop com grade densa de 2/3 colunas e mobile com secoes empilhadas.
- Listagem V3 ganhou link para o detalhe no numero (desktop e cartoes mobile).
- CSS V3 em `resources/sass/temas/v3.scss`, sem tocar V1/V2.

## QA

- PHPUnit completo: 524 testes / 1533 assertions, 100% verde.
- Feature: detalhe V3 com cabecalho e 7 secoes (RenderizaTemaV3Test).
- Playwright `DetalheRmaV3.spec.ts`: 2/2 verdes (desktop sem overflow; mobile
  empilhado a partir da listagem).
- Playwright T3-08/09/10: 3/3 verdes (regressao shell).
- Vite build verde; PUSH NAO REALIZADO.

## Proximo item exato

T3-12 - RMA formularios no Tema V3 (secoes com 2 colunas por relacao).
